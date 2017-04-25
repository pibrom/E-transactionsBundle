# E-transactionsBundle 0.x-dev 

## !!! DO NOT USE UNTIL 1.0 !!!!

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/480895ee-8a76-4bd6-a823-9e0a90f32576/big.png)](https://insight.sensiolabs.com/projects/480895ee-8a76-4bd6-a823-9e0a90f32576)

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
# E-Transactions
    etransactions_id: ~
    etransactions_certif_test: ~
    etransactions_certif_prod: ~
```

Mandatory fields CONFIG :
```yaml
snowbaha_etransactions:
    # Credentials
    site: "%etransactions_id%"
    # Keys
    key_dev: "%etransactions_certif_test%"
    key_prod: "%etransactions_certif_prod%"
    # Return
    url_return: http://www.example.com/payment_return
```

Optionnal fields (here the fields have their default values) :
```yaml
    # Possible values for env_mode : TEST / PRODUCTION
    env_mode: TEST

```


