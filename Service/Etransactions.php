<?php

namespace Snowbaha\EtransactionsBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpKernel\Config\FileLocator;

/**
 * Class Etransactions
 * @package Snowbaha\EtransactionsBundle\Service
 */
class Etransactions
{
    /**
     * @var string
     */
    private $paymentUrl;

    /**
     * DEFINITE THE VALUES in setParameterFields()
     * @var array
     */
    private $mandatoryFields = array(
        'identifiant' => null,
        'site' => null,
        'rang' => null,
        'hash' => "SHA512",
        'retour' => "amount:M;ref:R;error:E;auto:A;sign:K", // M : amount (pbx_total) ; R : reference (pbx_cmd) ; E : error ; A : Autorisation number ; K : signature (must be the last)
    );

    /**
     * Key HMAC private to send data to IPN
     * @var string
     */
    private $key;

    /**
     * Key .PEM to check Signature
     * @var string
     */
    private $publicKey;

    protected $logger;
    protected $check_signature;

    public function __construct(Logger $logger_etransaction, $check_signature)
    {
        $this->logger = $logger_etransaction;
        $this->check_signature = $check_signature;
    }

    /**
     * @param  $cmd
     * @param int $amount (cents)
     * Use int :
     * 10,28 € = 1028
     * 95 € = 9500
     * @param int $currency
     * Euro => 978
     * US Dollar => 840
     * @return $this
     */
    public function init($cmd, $amount, $email_buyer, $currency = 978)
    {
        $this->mandatoryFields['total'] = $amount; // total price cents
        $this->mandatoryFields['devise'] = $currency;
        $this->mandatoryFields['porteur'] = $email_buyer; // email of the buyer
        $this->mandatoryFields['cmd'] = $cmd; // Reference of the order
        $this->mandatoryFields['time'] = date("c"); // ISO-8601
        return $this;
    }

    /**
     * Add array of new fields
     * @param array $fields
     * @return $this
     */
    public function setOptionnalFields(array $fields)
    {
        foreach ($fields as $field => $value) :
            if (empty($this->mandatoryFields[$field])) :
                $this->mandatoryFields[$field] = $value;
            endif;
        endforeach;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $this->mandatoryFields['PBX_HMAC'] = $this->getHmac();

        return $this->mandatoryFields;
    }

    /**
     * Check the verification from the bank server
     * @param Request $request
     * @return array $retour
     */
    public function responseBankServer(Request $request)
    {
        $query = $request->query->all();

        // if the request is empty
        if(!isset($query['ref']) && !isset($query['amount']) ) :
            return null;
        endif;

        $retour['sucessPayment'] = false;
        $retour['signatureValid'] = $this->check_signature === true ? false : true; // init the var to check signature
        $retour['ref'] = $query['ref'] ?? null;
        $retour['amount'] = $query['amount'] ?? null;
        $retour['error'] = $query['error'] ?? null;
        $retour['auto'] = $query['auto'] ?? null;

        // Check signature
        if (!empty($query['sign']) && $this->check_signature === true)
        {
            $retour['sign'] = $query['sign'];

            if ($this->checkSignature( $retour['sign'], $request->getQueryString() ) === true )
            {
                $retour['signatureValid'] = true;
            }else{
                $this->writeErrorLog( "Fail check signature with ref : [".$retour['ref']."] and the data_query [".$request->getQueryString()."] ".json_encode($query) );
            }
        }else{
            $this->writeErrorLog( "Empty or No check signature signature with ref : [".$retour['ref']."] ".json_encode($query) );
        }

        // Check
        if( $retour['signatureValid'] === true ){
            $this->writeLog( json_encode($query) );

            if ($retour['error'] == "00000") :
                $retour['sucessPayment'] = true;
            endif;
        }

        return $retour;
    }

    /**
     * @return string
     */
    public function getPaymentUrl()
    {
        return $this->paymentUrl;
    }


    /**
     * @param array $fields
     * @return array
     */
    protected function setPrefixToFields(array $fields)
    {
        $newTab = array();
        foreach ($fields as $field => $value) :
            $newTab[sprintf('pbx_%s', $field)] = $value;
        endforeach;

        return $newTab;
    }

    /**
     * @param null $fields
     * @return string
     */
    protected function getHmac($fields = null)
    {
        $hash = $this->mandatoryFields['hash']; // before to prefix

        if (!$fields) :
            $fields = $this->mandatoryFields = $this->setPrefixToFields($this->mandatoryFields);
        endif;

        $content_hmac= "";
        foreach ($fields as $field => $value) :
            $content_hmac.= strtoupper($field)."=".$value."&";
        endforeach;

        $content_hmac= rtrim($content_hmac, "&"); // remove the last "&"

        $binKey = pack("H*", $this->key); //  ASCII to binary

        // FRENCH OFFICAL DOC :
        // On calcule l’empreinte (à renseigner dans le paramètre PBX_HMAC) grâce à la fonction hash_hmac et la clé binaire
        // On envoi via la variable PBX_HASH l'algorithme de hachage qui a été utilisé (SHA512 dans ce cas)
        // Pour afficher la liste des algorithmes disponibles sur votre environnement, décommentez la ligne suivante

        $hmac = strtoupper(hash_hmac($hash, $content_hmac, $binKey));

        return $hmac;
    }

    /**
     * Check the signature
     * @param $sign
     * @return bool
     */
    public function checkSignature($sign, $query_string )
    {
        $pos_sign = strrpos( $query_string, '&' ); // search the last
        $data = substr( $query_string, 0, $pos_sign ); // data without the signature
        $signature_base = base64_decode( urldecode( $sign ));

        return openssl_verify( $data, $signature_base, $this->publicKey );
    }

    /**
     * Write INFO element in a specifig log to E-transaction
     * @param $string
     */
    public function writeLog($string)
    {
        $this->logger->info($string);
    }

    /**
     * Write ERROR in the log to E-transaction
     * @param $string
     */
    public function writeErrorLog($string)
    {
        $this->logger->error($string);
    }

    /**
     * Set the good Key with the good environment
     * @param string $env_mode
     * @param string $key_dev
     * @param string $key_prod
     */
    public function setKey(string $env_mode, string $key_dev, string $key_prod)
    {
        if ($env_mode == "TEST") :
            $this->key = $key_dev;
            $this->paymentUrl = 'https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi';
        else :
            $this->key = $key_prod;
            $this->paymentUrl = 'https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi';
        endif;
    }

    /**
     * Hydratation of the default array
     * @param int $identifiant
     * @param int $site
     * @param $rang
     */
    public function setParameterFields(int $identifiant, int $site, $rang)
    {
        $this->mandatoryFields['identifiant'] = $identifiant;
        $this->mandatoryFields['site'] = $site;
        $this->mandatoryFields['rang'] = $rang;
    }

    /**
     * set the public key to check the signature
     * @param FileLocator $fileLocator
     * @param $kernel_root
     */
    public function setPublicKey( FileLocator $fileLocator, $kernel_root )
    {
        $file_key = $fileLocator->locate($kernel_root.'/../vendor/snowbaha/etransactions-bundle/Resources/pubkey.pem');

        $fsize =  filesize( $file_key );
        $fp = fopen( $file_key, 'r' );
        $file_key_data = fread( $fp, $fsize ); // read content
        fclose( $fp );

        $this->publicKey = openssl_pkey_get_public( $file_key_data );
    }
}
