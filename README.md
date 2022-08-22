![main workflow](https://github.com/XRPLWin/XRPL/actions/workflows/main.yml/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/xrplwin/xrpl.svg?style=flat-square)](https://packagist.org/packages/xrplwin/xrpl)


# PHP XRPL REST API

## Requirements
- PHP 8.1 or higher
- [Composer](https://getcomposer.org/)

## Installation
This Package is still in **beta**, to install run

```
composer require xrplwin/xrpl
```

## Usage sample

In sample below we will be using account_tx method.

### Init Client
```PHP
$client = new \XRPLWin\XRPL\Client([
    # Following values are defined by default, uncomment to override
    //'endpoint_reporting_uri' => 'http://s1.ripple.com:51234',
    //'endpoint_fullhistory_uri' => 'https://xrplcluster.com'
]);
```

### Creating first request
```PHP
# Create new 'account_tx' method instance
$account_tx = $client->api('account_tx')->params([
    'account' => 'rAccount...',
    'limit' => 10
]);
```
This will return an instance of `XRPLWin\XRPL\Api\Methods\AccountTx`.

### Custom rate limit handling
If you wish to define custom behaviour when request is rate-limited you can via `Closure` function. Define this before executing `send()`.
```PHP
//(optional) Override default cooldown seconds (default is 5 seconds)
$account_tx->setCooldownSeconds(10);
//(optional) Override default number of tries when request is rate-limited (default is 3)
$account_tx->setTries(5);

//(optional) Define custom cooldown callback
$account_tx->setCooldownHandler(
    function(int $current_try, int $default_cooldown_seconds) {
        //Sample usage: calculate how much sleep() is needed depending on $current_try
        sleep($default_cooldown_seconds * $current_try);
    }
);
```

### Fetching response
```PHP
# Send request to Ledger
try {
    $account_tx->send();
} catch (\XRPLWin\XRPL\Exceptions\XWException $e) {
    // Handle errors
    throw $e;
}

if(!$account_tx->isSuccess()) {
    //XRPL response is returned but field result.status did not return 'success'
}

# Get fetched response as array
$response       = $account_tx->resultArray(); //array response from ledger
# Get fetched response as object
$response       = $account_tx->result();      //object response from ledger
# Get fetched final result from helper (varies method to method)
$transactions   = $account_tx->finalResult(); //array of transactions
```

Class method `send()` executes request against XRPLedger and response is stored into instance object. After that you can use one of provided class methods to retrieve result.

### Paginating result
```PHP

# Check if there is next page
//$has_next_page = $account_tx->hasNextPage(); //bool

# Fetch next page of transactions if there is next page (next() does not return null)
if($next_account_tx = $account_tx->next()) {
    $next_result = $next_account_tx->send()->finalResult();
    // ...
}
```

To quick retrieve next instance to be executed (next page) you can use `next()`. This class method will return instance of `XRPLWin\XRPL\Api\Methods\AccountTx` with same parameters plus added marker from previous request. This helper function is not mandatory, you can always create new method instance manually like this `$client->api('account_tx')->params([ ..., ['marker'] => [ ... ]]`.

See [samples](samples/paginating.php) for more information.

## Request workflow

1. Prepare instance by setting params
2. Use send() to execute request and handle errors by using try catch. (Request will be re-tried x amount of times if rate-limit is detected)
4. XRPLedger response is stored in memory and it is available to read via `->finalResult()`, `->result()` and `->resultArray()`.

## Methods

### account_tx

```PHP
$account_tx = $client->api('account_tx')->params([
    'account' => 'rAccount...',
    'limit' => 10
]);
```


## Running tests
Run all tests in "tests" directory.
```
composer test
```
or
```
./vendor/bin/phpunit --testdox
```