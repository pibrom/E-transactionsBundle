<?php

namespace Snowbaha\EtransactionsBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Monolog\Logger;

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
        'retour' => "amount:M;ref:R;error:E;auto:A;sign:S", // M : amount (pbx_total) ; R : reference (pbx_cmd) ; E : error ; A : Autorisation number ; S : signature (must be the last)
    );

    /**
     * @var string
     */
    private $key;

    protected $logger;

    public function __construct(Logger $logger_etransaction)
    {
        $this->logger = $logger_etransaction;
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
        $this->mandatoryFields['PBX_HMAC'] = $this->getSignature();

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

        $retour['sucessPayment'] = false;
        $retour['ref'] = $query['ref'];
        $retour['amount'] = $query['amount'];
        $retour['error'] = $query['error'];
        $retour['auto'] = $query['auto'];

        // Check signature
        if (!empty($query['sign']))
        {
            $signature = $query['sign'];
            unset ($query['sign']);
            if ($signature == $this->getSignature($query))
            {
                $this->writeLog( json_encode($query) );

                if ($query['error'] == "00000") :
                    $retour['sucessPayment'] = true;
                endif;
            }else{
                $this->writeErrorLog( "Fail check signature with ref : [".$retour['ref']."] ".json_encode($query) );
            }
        }else{
            $this->writeErrorLog( "Empty signature with ref : [".$retour['ref']."] ".json_encode($query) );
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
    protected function getSignature($fields = null)
    {
        $hash = $this->mandatoryFields['hash']; // before to prefix

        if (!$fields) :
            $fields = $this->mandatoryFields = $this->setPrefixToFields($this->mandatoryFields);
        endif;

        $content_signature = "";
        foreach ($fields as $field => $value) :
            $content_signature .= strtoupper($field)."=".$value."&";
        endforeach;

        $content_signature = rtrim($content_signature, "&"); // remove the last "&"

        $binKey = pack("H*", $this->key); //  ASCII to binary

        // FRENCH OFFICAL DOC :
        // On calcule l’empreinte (à renseigner dans le paramètre PBX_HMAC) grâce à la fonction hash_hmac et la clé binaire
        // On envoi via la variable PBX_HASH l'algorithme de hachage qui a été utilisé (SHA512 dans ce cas)
        // Pour afficher la liste des algorithmes disponibles sur votre environnement, décommentez la ligne suivante

        $signature = strtoupper(hash_hmac($hash, $content_signature, $binKey));

        return $signature;
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
     * @param $site int/string
     * @param $retour
     */
    public function setParameterFields(int $identifiant,int $site, $rang)
    {
        $this->mandatoryFields['identifiant'] = $identifiant;
        $this->mandatoryFields['site'] = $site;
        $this->mandatoryFields['rang'] = $rang;
    }
}
