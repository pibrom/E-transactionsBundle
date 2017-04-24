# E-transactionsBundle 0.x-dev 

## !!! DO NOT USE UNTIL 1.0 !!!!

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
