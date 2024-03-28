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
  public static function currencyToSymbol($currencycode, $malformedUtf8ReturnString = '?') : string
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
        dd($interest_period);
        //const year_seconds = 31536000; // By convention, the XRP Ledger's interest/demurrage rules use a fixed number of seconds per year (31536000), which is not adjusted for leap days or leap seconds
        $year_seconds = 31536000;
        //let interest_after_year = precision(Math.pow(Math.E, (interest_start+year_seconds - interest_start) / interest_period), 14);
        $interest_after_year = $year_seconds / $interest_period;
        dd($interest_start,$interest_after_year);
        return 'DE '.$currencycode;
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
   * IEEE 754 floating-point.
   *
   * Supports single- or double-precision
   */
  public static function ieee754FloatFromBytes(array $bytes)
  {
    dd($bytes,pack('h', $bytes));
    $data = unpack('f', pack('i', $bytes));
    if($data === false)
      throw new \Exception('Unable to extract ieee754FloatFromBytes');
    return \array_values($data)[0];
  }

}