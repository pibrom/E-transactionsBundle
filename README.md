# E-transactionsBundle

## !!! DO NOT USE UNTIL 1.0 STILL in DEV !!!!

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/480895ee-8a76-4bd6-a823-9e0a90f32576/big.png)](https://insight.sensiolabs.com/projects/480895ee-8a76-4bd6-a823-9e0a90f32576) [![Latest Stable Version](https://poser.pugx.org/snowbaha/etransactions-bundle/v/stable)](https://packagist.org/packages/snowbaha/etransactions-bundle)  [![Total Downloads](https://poser.pugx.org/snowbaha/etransactions-bundle/downloads)](https://packagist.org/packages/snowbaha/etransactions-bundle)

This bundle allows to implement a Payment Solution working with [E-transactions](https://www.e-transactions.fr) for your symfony projet.
E-transactions is a payment gateway proposed by the following bank "Crédit Agricole"

## Installation
### Step 1 : Import using Composer
Using composer :
```json
{
    "require": {
        "snowbaha/etransactions-bundle": "dev-master"
    }
}
```

### Step 2 : Enable the plugin
Enable the bundle in the kernel:
```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Snowbaha\EtransactionsBundle\SnowbahaEtransactionsBundle(),
    );
}
```

### Step 3 : Configure the bundle
Mandatory fields PARAMETER:
```yaml
# E-Transactions (don't forget the validation of the HMAC key 'certif_test/prod' by email)
    etransactions_id: ~
    etransactions_site: ~
    etransactions_certif_test: ~
    etransactions_certif_prod: ~
```

Mandatory fields CONFIG :
```yaml
snowbaha_etransactions:
    # Credentials
    id: "%etransactions_id%"
    site: "%etransactions_site%"
    rang: "your_rank"
    # Keys
    key_dev: "%etransactions_certif_test%"
    key_prod: "%etransactions_certif_prod%"
    # SETTING
    env_mode: TEST # Possible values : TEST / PRODUCTION

```

## How to use
### Controller
#### Create a Transaction
To intantiate a new Transaction, you need to create an action in one of your controller and call the `snowbaha.etransactions` service. 
All mandatory fields are used with their default value. You can configure all the common fields of your transactions in the `app/config/config.yml` file.

To see what fields are available see the PDF doc.

##### Service Method
* `init($order_id, $amount, $email_buyer, $currency = 978)` allows you to specify the amount and the currency of the transaction.
* `setOptionnalFields(array)` allows you to specify any field.

##### Example
```php
    /**
     * @Route("/initiate-payment/id-{id}", name="pay_online")
     * @Template()
     */
    public function payOnlineAction($id)
    {
        // ...
        $etransactions = $this->get('snowbaha.etransactions')
            ->init(99, 100, 'buyer@buy.com')
            ->setOptionnalFields(array(
                'PBX_ERREUR' => 'http://www.example.com/error'
            ))
        ;

        return $this->render('YOURBUNDLEBundle:Etransactions:formToSend.html.twig', array(
                    'paymentUrl' => $etransactions->getPaymentUrl(),
                    'fields' => $etransactions->getFields(),
                ));
    }
```
#### Handle the response from the server
This route will be called by the E-Transactions service to update you about the payment status. This is the only way to correctly handle payment verfication.

Service Method:
* `responseBankServer(Request)` is used to update the transaction status (in database)

##### Example
```php
    // YOUR CONTROLLER
    /**
     * @Route("/payment/verification")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentVerificationAction(Request $request)
    {
        // ...
        $this->get('snowbaha.etransactions')
            ->responseHandler($request)
        ;

        return new Response();
    }
```

### Template
This is how the template for the `payOnlineAction()` may look like. You can use the `paiementForm` twig function to automatically generate the form based on the fields created in the service and returned by the `getFields()` function.
```twig
    <html>

        <i class="fa fa-refresh fa-spin margin-top margin-bottom" style="font-size: 50px"></i> {# With http://fontawesome.io/icons/ #}
        <h3>Redirect to the paiement page...</h3>
        <form action="{{ paymentUrl }}" method="POST" id="etransactions-form">
            {{ paiementForm(fields) }}

            {# If no JS, show the button to submit#}
            <noscript>
                <input type="submit" value="Pay">
            </noscript>
        </form>
        
    </html>
    <script type="text/javascript">
        document.getElementById('etransactions-form').submit();
    </script>
```
