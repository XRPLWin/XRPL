<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Utilities;

use Brick\Math\BigDecimal;

/**
 * XRPL Flags
 * @see https://js.xrpl.org/enums/OfferCreateFlags.html
 */
final class Flags
{

  public const FLAGS = [
    '_GLOBAL' => [
      'tfFullyCanonicalSig'   => 0x80000000
    ],
    'EnableAmendment' => [
      'tfGotMajority'         => 0x00010000,
      'tfLostMajority'        => 0x00020000
    ],
    'NFTokenCreateOffer' => [
      'tfSellNFToken'         => 0x00000001
    ],
    'NFTokenMint' => [
      'tfBurnable'            => 0x00000001,
      'tfOnlyXRP'             => 0x00000002,
      'tfTrustLine'           => 0x00000004,
      'tfTransferable'        => 0x00000008,
      'tfMutable'             => 0x00000010
    ],
    'URITokenMint' => [
      'tfBurnable'            => 0x00000001,
    ],
    'OfferCreate' => [
      'tfPassive'             => 0x00010000,
      'tfImmediateOrCancel'   => 0x00020000,
      'tfFillOrKill'          => 0x00040000,
      'tfSell'                => 0x00080000
    ],
    'PaymentChannelClaim' => [
      'tfRenew'               => 0x00010000,
      'tfClose'               => 0x00020000
    ],
    'Payment' => [
      'tfNoDirectRipple'      => 0x00010000,
      'tfPartialPayment'      => 0x00020000,
      'tfLimitQuality'        => 0x00040000,
    ],
    'TrustSet' => [
      'tfSetfAuth'            => 0x00010000,
      'tfSetNoRipple'         => 0x00020000,
      'tfClearNoRipple'       => 0x00040000,
      'tfSetFreeze'           => 0x00100000,
      'tfClearFreeze'         => 0x00200000,
      'tfSetDeepFreeze'       => 0x00400000,
      'tfClearDeepFreeze'     => 0x00800000
    ],
    'AccountSet' => [
      'tfRequireDestTag'      => 0x00010000,
      'tfOptionalDestTag'     => 0x00020000,
      'tfRequireAuth'         => 0x00040000,
      'tfOptionalAuth'        => 0x00080000,
      'tfDisallowXRP'         => 0x00100000,
      'tfAllowXRP'            => 0x00200000
    ],
    'ClaimReward' => [
      'tfOptOut'              => 0x00000001
    ],
    'AMMDeposit' => [
      'tfLPToken'             => 0x00010000,
      'tfSingleAsset'         => 0x00080000,
      'tfTwoAsset'            => 0x00100000,
      'tfOneAssetLPToken'     => 0x00200000,
      'tfLimitLPToken'        => 0x00400000,
      'tfTwoAssetIfEmpty'     => 0x00800000
    ],
    'AMMWithdraw' => [
      'tfLPToken'             => 0x00010000,
      'tfWithdrawAll'         => 0x00020000,
      'tfOneAssetWithdrawAll' => 0x00040000,
      'tfSingleAsset'         => 0x00080000,
      'tfTwoAsset'            => 0x00100000,
      'tfOneAssetLPToken'     => 0x00200000,
      'tfLimitLPToken'        => 0x00400000,
    ],
    'XChainModifyBridge' => [
      'tfClearAccountCreateAmount' => 0x00010000,
    ],
    'Batch' => [
      'tfAllOrNothing'        => 0x00010000,
      'tfOnlyOne'             => 0x00020000,
      'tfUntilFailure'        => 0x00040000,
      'tfIndependent'         => 0x00080000,
    ],
    'MPTokenIssuanceCreate' => [
      'tfMPTCanLock'          => 0x00000002,
      'tfMPTRequireAuth'      => 0x00000004,
      'tfMPTCanEscrow'        => 0x00000008,
      'tfMPTCanTrade'         => 0x00000010,
      'tfMPTCanTransfer'      => 0x00000020,
      'tfMPTCanClawback'      => 0x00000040,
    ],
    'MPTokenIssuanceSet' => [
      'tfMPTLock'             => 0x00000001,
      'tfMPTUnlock'           => 0x00000002,
    ],
  ];

  //todo account set  asf flags... https://js.xrpl.org/enums/AccountSetAsfFlags.html

  /**
   * Extract flag names from Flags for specific transaction type.
   * @param int $flags
   * @param string $transactionType
   * @return array
   */
  public static function extract(int $flags, string $transactionType): array
  {
    $r = [];

    $definedFlags = [];
    if(isset(self::FLAGS[$transactionType]))
      $definedFlags = self::FLAGS[$transactionType];
    
    $definedFlags = \array_merge(self::FLAGS['_GLOBAL'],$definedFlags);
    
    foreach($definedFlags as $name => $v) {
      if(self::hasFlag($flags,$v)) {
        $r[] = $name;
      }
    }
    
    return $r;
  }

  /**
   * Check if $check is included in $flags using bitwise-and operator.
   * @return bool
   */
  public static function hasFlag(int $flags, int $check): bool
  {
  	return ($flags & $check) ? true : false;
  }

  public static function description(string $transactiontype, string $flagname, bool $htmlFormat = false): string
  {
    if($flagname == 'tfFullyCanonicalSig')
      $transactiontype = '';

    $path = $transactiontype.'_'.$flagname;

    $html = '';

    switch($path) {
      case '_GLOBAL_tfFullyCanonicalSig':
      case '_tfFullyCanonicalSig':
        $html = 'DEPRECATED No effect. (If the <a href="https://xrpl.org/known-amendments.html#requirefullycanonicalsig">RequireFullyCanonicalSig amendment</a> is not enabled, this flag enforces a <a href="https://xrpl.org/transaction-malleability.html#alternate-secp256k1-signatures">fully-canonical signature</a>.)';
        break;
      case 'EnableAmendment_tfGotMajority':
        $html = 'The <code>tfGotMajority</code> flag means the amendment has more than 80% support.';
        break;
      case 'EnableAmendment_tfLostMajority':
        $html = 'The <code>tfLostMajority</code> flag means support for the amendment has decreased to 80% or less.';
        break;
      case 'NFTokenCreateOffer_tfSellNFToken':
        $html = 'If set, indicates that the offer is a sell offer. Otherwise, it is a buy offer.';
        break;
      case 'NFTokenMint_tfBurnable':
        $html = 'If set, indicates that the minted token may be burned by the issuer even if the issuer does not currently hold the token. The current holder of the token may always burn it.';
        break;
      case 'URITokenMint_tfBurnable':
        $html = 'If set, indicates that the minted token may be burned by the issuer (or an entity authorized by the issuer) even if the issuer does not currently hold the token. The current holder of the token may always burn it.';
        break;
      case 'NFTokenMint_tfOnlyXRP':
        $html = 'If set, indicates that the token may only be offered or sold for XRP.';
        break;
      case 'NFTokenMint_tfTrustLine':
        $html = 'If set, indicates that the issuer wants a trustline to be automatically created.';
        break;
      case 'NFTokenMint_tfTransferable':
        $html = 'If set, indicates that this NFT can be transferred. This flag has no effect if the token is being transferred from the issuer or to the issuer.';
        break;
      case 'NFTokenMint_tfMutable':
        $html = 'The URI field of the minted NFToken can be updated using the NFTokenModify transaction.';
        break;
      case 'OfferCreate_tfPassive':
        $html = 'If enabled, the offer does not consume offers that exactly match it, and instead becomes an Offer object in the ledger. It still consumes offers that cross it.';
        break;
      case 'OfferCreate_tfImmediateOrCancel':
        $html = 'Treat the offer as an Immediate or Cancel order. If enabled, the offer never becomes a ledger object: it only tries to match existing offers in the ledger. If the offer cannot match any offers immediately, it executes <i>successfully</i> without trading any currency. In this case, the transaction has the result code <code>tesSUCCESS</code>, but creates no Offer objects in the ledger.';
        break;
      case 'OfferCreate_tfFillOrKill':
        $html = 'Treat the offer as a Fill or Kill order . Only try to match existing offers in the ledger, and only do so if the entire TakerPays quantity can be obtained. If the <a href="https://xrpl.org/known-amendments.html#fix1578">fix1578 amendment</a> is enabled and the offer cannot be executed when placed, the transaction has the result code <code>tecKILLED</code>; otherwise, the transaction uses the result code <code>tesSUCCESS</code> even when it was killed without trading any currency.';
        break;
      case 'OfferCreate_tfSell':
        $html = 'Exchange the entire TakerGets amount, even if it means obtaining more than the TakerPays amount in exchange.';
        break;
      case 'PaymentChannelClaim_tfRenew':
        $html = 'Clear the channel\'s Expiration time. (Expiration is different from the channel\'s immutable CancelAfter time.) Only the source address of the payment channel can use this flag.';
        break;
      case 'PaymentChannelClaim_tfClose':
        $html = 'Request to close the channel. Only the channel source and destination addresses can use this flag. This flag closes the channel immediately if it has no more XRP allocated to it after processing the current claim, or if the destination address uses it. If the source address uses this flag when the channel still holds XRP, this schedules the channel to close after SettleDelay seconds have passed. (Specifically, this sets the Expiration of the channel to the close time of the previous ledger plus the channel\'s SettleDelay time, unless the channel already has an earlier Expiration time.) If the destination address uses this flag when the channel still holds XRP, any XRP that remains after processing the claim is returned to the source address.';
        break;
      case 'Payment_tfNoDirectRipple':
        $html = 'Do not use the default path; only use paths included in the Paths field. This is intended to force the transaction to take arbitrage opportunities.';
        break;
      case 'Payment_tfPartialPayment':
        $html = 'If the specified Amount cannot be sent without spending more than SendMax, reduce the received amount instead of failing outright.';
        break;
      case 'Payment_tfLimitQuality':
        $html = 'Only take paths where all the conversions have an input:output ratio that is equal or better than the ratio of Amount:SendMax.';
        break;
      case 'TrustSet_tfSetfAuth':
        $html = 'Authorize the other party to hold currency issued by this account. (No effect unless using the <code>asfRequireAuth</code> AccountSet flag.) Cannot be unset.';
        break;
      case 'TrustSet_tfSetNoRipple':
        $html = 'Enable the No Ripple flag, which blocks rippling between two trust lines of the same currency if this flag is enabled on both.';
        break;
      case 'TrustSet_tfClearNoRipple':
        $html = 'Disable the No Ripple flag, which blocks rippling between two trust lines of the same currency if this flag is enabled on both.';
        break;
      case 'TrustSet_tfSetFreeze':
        $html = '<a href="https://xrpl.org/freezes.html">Freeze</a> the trust line.';
        break;
      case 'TrustSet_tfClearFreeze':
        $html = 'Disable individual <a href="https://xrpl.org/freezes.html">Freeze</a> on the specific trust line.';
        break;
      case 'TrustSet_tfSetDeepFreeze':
        $html = 'Deep freeze the trust line.';
        break;
      case 'TrustSet_tfClearDeepFreeze':
        $html = 'Clear a deep-freeze on the trust line.';
        break;
      case 'AccountSet_tfRequireDestTag':
      case 'AccountSet_asfRequireDest':
        $html = 'Require a destination tag to send transactions to this account.';
        break;
      case 'AccountSet_tfOptionalDestTag':
        $html = 'Disable requirement that destination tag is required to send transactions to this account.';
        break;
      case 'AccountSet_tfRequireAuth':
      case 'AccountSet_asfRequireAuth':
        $html = 'Require authorization for users to hold balances issued by this address can only be enabled if the address has no trust lines connected to it.';
        break;
      case 'AccountSet_tfOptionalAuth':
        $html = 'Disable requirement that authorization for users to hold balances issued by this address can only be enabled if the address has no trust lines connected to it.';
        break;
      case 'AccountSet_tfDisallowXRP':
      case 'AccountSet_asfDisallowXRP':
        $html = 'XRP should not be sent to this account.';
        break;
      case 'AccountSet_tfAllowXRP':
        $html = 'XRP is allowed to be sent to this account.';
        break;
      case 'ClaimReward_tfOptOut':
        $html = 'The isOptOut flag in the ClaimReward is used to opt-out an account from rewards by removing reward-related fields from the account object in the ledger if the sfFlags field in the transaction is set to 1.';
        break;
      case 'AMMDeposit_tfLPToken':
        $html = 'Perform a double-asset deposit and receive the specified amount of LP Tokens.';
        break;
      case 'AMMDeposit_tfSingleAsset':
        $html = 'Perform a single-asset deposit with a specified amount of the asset to deposit.';
        break;
      case 'AMMDeposit_tfTwoAsset':
        $html = 'Perform a double-asset deposit with specified amounts of both assets.';
        break;
      case 'AMMDeposit_tfOneAssetLPToken':
        $html = 'Perform a single-asset deposit and receive the specified amount of LP Tokens.';
        break;
      case 'AMMDeposit_tfLimitLPToken':
        $html = 'Perform a single-asset deposit with a specified effective price.';
        break;
      case 'AMMDeposit_tfTwoAssetIfEmpty':
        $html = 'Perform a special double-asset deposit to an AMM with an empty pool.';
        break;
      case 'AMMWithdraw_tfLPToken':
        $html = 'Return the specified amount of LP Tokens and receive both assets from the AMM\'s pool in amounts based on the returned LP Token\'s share of the total LP Tokens issued.';
        break;
      case 'AMMWithdraw_tfWithdrawAll':
        $html = 'Return <i>all</i> of your LP Tokens and receive as much as you can of both assets in the AMM\'s pool.';
        break;
      case 'AMMWithdraw_tfOneAssetWithdrawAll':
        $html = 'Withdraw at least the specified amount of one asset, by returning all of your LP Tokens. Fails if you can\'t receive at least the specified amount. The specified amount can be 0, meaning the transaction succeeds if it withdraws any positive amount.';
        break;
      case 'AMMWithdraw_tfSingleAsset':
        $html = 'Withdraw exactly the specified amount of one asset, by returning as many LP Tokens as necessary.';
        break;
      case 'AMMWithdraw_tfTwoAsset':
        $html = 'Withdraw both of this AMM\'s assets, in up to the specified amounts. The actual amounts received maintains the balance of assets in the AMM\'s pool.';
        break;
      case 'AMMWithdraw_tfOneAssetLPToken':
        $html = 'Withdraw up to the specified amount of one asset, by returning up to the specified amount of LP Tokens.';
        break;
      case 'AMMWithdraw_tfLimitLPToken':
        $html = 'Withdraw up to the specified amount of one asset, but pay no more than the specified effective price in LP Tokens per unit of the asset received.';
        break;
      case 'XChainModifyBridge_tfClearAccountCreateAmount':
        $html = 'Clears the MinAccountCreateAmount of the bridge.';
        break;
      case 'Batch_tfAllOrNothing':
        $html = 'All transactions must succeed or else the whole batch fails.';
        break;
      case 'Batch_tfOnlyOne':
        $html = 'Only the first successful transaction is applied. All transactions afterward fail or are skipped.';
        break;
      case 'Batch_tfUntilFailure':
        $html = 'All transactions are applied until the first failure; subsequent transactions are skipped.';
        break;
      case 'Batch_tfIndependent':
        $html = 'All transactions will be applied, regardless of failure.';
        break;

      case 'MPTokenIssuanceCreate_tfMPTCanLock':
        $html = 'If set, indicates that the MPT can be locked both individually and globally. If not set, the MPT cannot be locked in any way.';
        break;
      case 'MPTokenIssuanceCreate_tfMPTRequireAuth':
        $html = 'If set, indicates that individual holders must be authorized. This enables issuers to limit who can hold their assets.';
        break;
      case 'MPTokenIssuanceCreate_tfMPTCanEscrow':
        $html = 'If set, indicates that individual holders can place their balances into an escrow.';
        break;
      case 'MPTokenIssuanceCreate_tfMPTCanTrade':
        $html = 'If set, indicates that individual holders can trade their balances using the XRP Ledger DEX.';
        break;
      case 'MPTokenIssuanceCreate_tfMPTCanTransfer':
        $html = 'If set, indicates that tokens can be transferred to other accounts that are not the issuer.';
        break;
      case 'MPTokenIssuanceCreate_tfMPTCanClawback':
        $html = 'If set, indicates that the issuer can use the Clawback transaction to claw back value from individual holders.';
        break;

      case 'MPTokenIssuanceSet_tfMPTLock':
        $html = 'If set, indicates that all MPT balances for this asset should be locked.';
        break;
      case 'MPTokenIssuanceSet_tfMPTUnlock':
        $html = 'If set, indicates that all MPT balances for this asset should be unlocked.';
        break;
    }

    if(!$htmlFormat)
      return \strip_tags($html);

    return $html;
  }
}
