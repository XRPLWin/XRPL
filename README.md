![main workflow](https://github.com/XRPLWin/XRPL/actions/workflows/main.yml/badge.svg)
[![GitHub license](https://img.shields.io/github/license/XRPLWin/XRPL)](https://github.com/XRPLWin/XRPL/blob/main/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/xrplwin/xrpl.svg?style=flat)](https://packagist.org/packages/xrplwin/xrpl)

# PHP XRPL API Connector

## Requirements
- PHP 8.1 or higher
- [Composer](https://getcomposer.org/)

## Installation
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
//(optional) Set http timeout to 3 seconds (default is 0 - eg no timeout)
//           After 3 seconds method will throw \XRPLWin\XRPL\Exceptions\BadRequestException on timeout
$account_tx->setTimeout(3);

//(optional) Define custom cooldown callback
$account_tx->setCooldownHandler(
    /**
     * @param int $current_try Current try 1 to max
     * @param int $default_cooldown_seconds Predefined cooldown seconds
     * @return void|boolean return false to stop process
     */
    function(int $current_try, int $default_cooldown_seconds) {
        //Sample usage: calculate how much sleep() is needed depending on 
        $sec = $default_cooldown_seconds * $current_try;
        if($sec > 15) return false; //force stop
        sleep($sec);
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

### Promises
Alternative to fetch syncronous response as defined above, you can get `Promise`  
[Read more](https://github.com/guzzle/promises) about Promises. Returned object is [Promises/A+](https://promisesaplus.com/) implementation. 
```PHP
$promise = $account_tx->requestAsync();

$promises = [
    'rAcct1' => $promise,
    //...more promises
];

// Wait for the requests to complete
// Throws a ConnectException if any of the requests fail
$responses = \GuzzleHttp\Promise\Utils::unwrap($promises);

//Fill response data back into $account_tx instance
$account_tx->fill($responses['rAcct1']);

//...
$transactions = $account_tx->finalResult();

```
*Notes*: You will need to handle rate limiting and exception handling yourself when using Promises. Also each promise must be created from new instance of `\XRPLWin\XRPL\Client` this is to make sure each promise has its own seperated HttpClient instance.

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
4. XRPLedger response is stored in memory and it is available to read via  
`->result()`  
`->resultArray()`  
`->finalResult()`

## Methods

### account_tx

```PHP
$account_tx = $client->api('account_tx')->params([
    'account' => 'rAccount...',
    'limit' => 10
]);
```

### tx

```PHP
$account_tx = $client->api('tx')->params([
    'transaction' => 'DE80B0064677CEFFDE...',
    'binary' => false
]);
```
For other methods refer to https://xrpl.org/websocket-api-tool.html and [src/Api/Methods](src/Api/Methods)

## Utilities

There are few utilities available with this package:
- Balance changes
- Flags
- UNLReport Flag Ledger
- Currency dode to readable currency code

### Flags

```PHP
use XRPLWin\XRPL\Utilities\Flags;

//Methods:
Flags::extract(int $flags, string $transactionType): array
Flags::description(string $transactiontype, string $flagname, bool $htmlFormat = false): string
Flags::hasFlag(int $flags, int $check): bool
```

### UNLReportFlagLedger

Flag ledger is calculated using modulo formula LedgerIndex % 256.

```PHP
use XRPLWin\XRPL\Utilities\UNLReportFlagLedger;

UNLReportFlagLedger::isFlag(256);   //for ledger sequence 256 - true
UNLReportFlagLedger::isFlag(257);   //for ledger sequence 257 - false
UNLReportFlagLedger::prev(6873600);          //6873344
UNLReportFlagLedger::prevOrCurrent(6873600); //6873600
UNLReportFlagLedger::next(6873600);          //6873856
UNLReportFlagLedger::nextOrCurrent(6873600); //6873600
```

### Util class
Converts Currency Code ISO or HEX to human readable representation.  

```PHP
use XRPLWin\XRPL\Utilities\Util;
#Syntax: Util::currencyToSymbol(string $ISO_or_HEX, $malformedUtf8ReturnString = '?')

//ISO Currency Code
Util::currencyToSymbol('EUR') //EUR 
//Deprecated Demurrage Currency Code
Util::currencyToSymbol('0158415500000000C1F76FF6ECB0BAC600000000') //XAU (-0.5% pa)
//Nonstandard Currency Code
Util::currencyToSymbol('534F4C4F00000000000000000000000000000000') //SOLO
```
Read more:
- https://xrpl.org/docs/references/protocol/data-types/currency-formats/#currency-formats
- https://xrpl.org/docs/concepts/tokens/fungible-tokens/demurrage/

## Running tests
Run all tests in "tests" directory.
```
composer test
```
or
```
./vendor/bin/phpunit --testdox
```
