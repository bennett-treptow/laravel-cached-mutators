# Laravel Cached Mutators

This package provides a trait for your Eloquent models to automatically cache any mutators and attributes you choose. Good use cases for this trait are for expensive mutators that you only want to run once per request / certain time period and want to keep your mutator code free from any manual caching.

This package works by hooking into the Eloquent model's `getAttributeValue` function, and passing thru the value if the specified attribute does not need to be cached as per the `$cacheAttributes`.

## Install

`composer require bennett-treptow/laravel-cached-mutators`

## Usage

### Basic Usage

```php
<?php
use CachedMutators\HasCachedMutators;

/**
 * @property string $customer_id
 * @property \Stripe\Customer $associated_stripe_customer
 */
class MyModel extends Model {
    use HasCachedMutators;

    //declare your auto cached attribute keys
    protected static $cacheAttributes = [
        'associated_stripe_customer'
    ];

    /**
    * @return \Stripe\Customer
     */
    public function getAssociatedStripeCustomer(){
        //call to an external service such as Stripe
        //this call will be proxied through the Cache
        //and will only call the external service once

        return \Stripe\Customer::retrieve($this->customer_id);
    }  
}
```

### Advanced Usage

```php
<?php
use CachedMutators\HasCachedMutators;

/**
 * @property string $customer_id
 * @property \Stripe\Customer $associated_stripe_customer
 * @property \Stripe\Source[] $associated_payment_methods
 */
class MyModel extends Model {
    use HasCachedMutators;

    //declare your auto cached attribute keys
    protected static $cacheAttributes = [
        'associated_stripe_customer' => [
            'store' => 'redis',
            'ttl' => null
        ],
        'associated_payment_methods' => [
            'store' => 'redis',
            'ttl' => 1000
        ]
    ];

    /**
    * @return \Stripe\Customer
     */
    public function getAssociatedStripeCustomer(){
        //call to an external service such as Stripe
        //this call will be proxied through the Cache
        //and will only call the external service once

        return \Stripe\Customer::retrieve($this->customer_id);
    }  
    
    /** 
     * @return \Stripe\Source[]
     */
    public function getAssociatedPaymentMethods(){
        return \Stripe\Customer::allSources($this->customer_id, [
            'object' => 'card', 
            'limit' => 3
        ]);
    }
}
```

The `$cacheAttributes` array can be configured to cache mutators per attribute by defining `store` and `ttl`. 

By default, the `store` will follow your application's default cache store, which is usually the `file` store.
Defining a `ttl` will call the cache repository's `remember` function, and making `ttl` null or not part of the array will use the cache repository's `rememberForever` function.

#### Clearing Cached Mutators
Need to clear your cached mutators to get a fresh copy?

##### clearCachedMutators($key = null)
```php
<?php
$myModel = new MyModel();
$stripeCustomer = $myModel->associated_stripe_customer;

//do some stuff..

$myModel->clearCachedMutators(); //will clear all declared mutators in $cacheAttributes
$myModel->clearCachedMutators('associated_stripe_customer'); //to just clear one key
```