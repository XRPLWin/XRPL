<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Utilities;

/**
 * Utilities
 */
final class Util
{
  /**
  * Decode HEX currency to symbol.
  * If already symbol returns that symbol (checked by length).
  * Examples: USD,EUR,534F4C4F00000000000000000000000000000000,LP 031234...
  * @see https://3v4l.org/Mp2Fu
  * @return string
  */
  public static function currencyToSymbol(string $currencycode, string $malformedUtf8ReturnString = '?') : string
  {
    if( \strlen($currencycode) == 40 )
    {
      if(\substr($currencycode,0,2) == '03') {
        //AMM LP token, 03 + 19 bytes of sha512
        return 'LP '.$currencycode;
      }

      if(\substr($currencycode,0,2) == '01') {
        //demurrage, convert it to utf8 for display
        //let bytes = Buffer.from(demurrageCode, "hex")
        $bytes = \array_values(unpack("C*", \hex2bin($currencycode)));
        //let code = String.fromCharCode(bytes[1]) + String.fromCharCode(bytes[2]) + String.fromCharCode(bytes[3]);
        $code = \chr($bytes[1]).\chr($bytes[2]).\chr($bytes[3]); //OK
        //let interest_start = (bytes[4] << 24) + (bytes[5] << 16) + (bytes[6] <<  8) + (bytes[7]);
        //$interest_start = (int)(($bytes[4] << 24) . ($bytes[5] << 16) . ($bytes[6] << 8) . $bytes[7]);
        //let interest_period = ieee754Float.fromBytes(bytes.slice(8, 16));
        $interest_period = self::ieee754FloatFromBytes(\array_slice($bytes,8,8));
        if($interest_period === null || $interest_period === true) {
          return $code.' (?% pa)';
        }
        //const year_seconds = 31536000; // By convention, the XRP Ledger's interest/demurrage rules use a fixed number of seconds per year (31536000), which is not adjusted for leap days or leap seconds
        $year_seconds = 31536000;
        //let interest_after_year = precision(Math.pow(Math.E, (interest_start+year_seconds - interest_start) / interest_period), 14);
        $interest_after_year = \pow(\exp(1), $year_seconds / $interest_period);
        $interest = ($interest_after_year*100) - 100;
        return $code.' ('.round($interest,1).'% pa)';
      }

      $r = \trim(\hex2bin($currencycode));
      $r = preg_replace('/[\x00-\x1F\x7F]/', '', $r); //remove first 32 ascii characters and \x7F https://en.wikipedia.org/wiki/Control_character

      if((bool)preg_match( '|[^\x20-\x7E]|', $r )) {
        //binary eg: 80474F4C44000000000000000000000000000000
        return preg_replace('/[[:^print:]]/', '', $r);
      }

      if(preg_match('//u', $r)) //This will will return 0 (with no additional information) if an invalid string is given.
        return $r;
      return $malformedUtf8ReturnString; //malformed UTF-8 string
    }
    return $currencycode;
  }

  /**
   * IEEE754 floating-point.
   *
   * Supports single- or double-precision
   */
  public static function ieee754FloatFromBytes(array $bytes): float|null|bool
  {
    $b = '';
    $n = count($bytes);
    for ($i = 0; $i < $n; $i++) {
      $bits = base_convert((string)($bytes[$i] & 0xff),10,2);//.toString(2);
      if(strlen($bits)<8) $bits = \str_pad($bits,8,'0',STR_PAD_LEFT);
      $b = $b.$bits;
    }
    
    // Determine configuration.  This could have all been precomputed but it is fast enough.
    $exponentBits = count($bytes) === 4 ? 4 : 11;
    $mantissaBits = (count($bytes) * 8) - $exponentBits - 1;
    $bias = \pow(2,$exponentBits - 1) - 1;
    $minExponent = 1 - $bias - $mantissaBits;
    // Break up the binary representation into its pieces for easier processing.
    $s = $b[0];
    $e = \substr($b,1,$exponentBits);
    $m = \substr($b,$exponentBits+1);
    $value = 0;
    $multiplier = ($s === '0' ? 1 : -1);

    if(\preg_match('/^0+$/', $e)) { //regexp: allzeros
      // Zero or denormalized
      if(\preg_match('/^0+$/', $m)) { //regexp: allzeros
        // Value is zero
      } else {
        $value = \intval($m, 2) * \pow(2, $minExponent);
      }
    } else if (\preg_match('/^1+$/', $e)) { //regexp: allones
      // Infinity or NaN
      if(\preg_match('/^0+$/', $m)) { //regexp: allzeros
        $value = true; //Infinity
      } else {
        $value = null; //NaN
      }
    } else {
      // Normalized
      $exponent = \intval($e,2) - $bias;
      $mantissa = \intval($m,2);
      $value = (1+($mantissa  * \pow(2,-$mantissaBits))) * \pow(2,$exponent);
    }
    if($value === false) return false;
    if($value === null) return null;
    return $value * $multiplier;


  }

}