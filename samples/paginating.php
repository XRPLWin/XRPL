<?php

/**
 * This sample code will run max 20 tx pages for this account.
 */
$client = new \XRPLWin\XRPL\Client([]);
$account_tx = $client->api('account_tx')
  ->params([
    'account' => 'rLNaPoKeeBjZe2qs6x52yVPZpZ8td4dc6w',
    'ledger_index' => 'current',
    'ledger_index_min' => -1,
    'ledger_index_max' => -1,
    'binary' => false,
    'forward' => true,
    'limit' => 10
  ]);

$do = true;
$maxPages = 20;
$i = 0;
while($do) {
  $i++;
  try {
    $account_tx->send();
  } catch (\XRPLWin\XRPL\Exceptions\XWException $e) {
    $do = false;
    // Handle errors
    throw $e;
  }
  $txs = $account_tx->finalResult();

  foreach($txs as $tx) {
    //Do something with transaction response
  }

  if($account_tx = $account_tx->next()) {
    //There are more pages...

    if($maxPages < $i)
      $do = false; //Stop executing.
    
    //continuing to next page...
  }
  else
    $do = false;
}