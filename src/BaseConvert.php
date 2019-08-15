<?php
namespace Choval;

class BaseConvert {



  /**
   * Base chars
   */
  const BASE_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
  const BASE_DECIMAL = ['0','1','2','3','4','5','6','7','8','9'];
  const BASE_MAX_LENGTH = 64;
  const BASE_MIN_LENGTH = 2;



  /**
   * Settings
   */
  protected static $math_lib = false;



  /**
   * Variables
   */
  protected $number;
  protected $from_base;
  protected $value;



  /**
   * Constructor
   */
  public function __construct(string $number, $from=10) {
    $this->number = $number;
    $this->from_base = static::parseBase($from);
    $this->value = static::toValue($this->number, $this->from_base);
  }



  /**
   * Converts to base
   */
  public function to($base, int $padlen=NULL, string $padchar=NULL) : string {
    $base = static::parseBase($base);
    $res = static::valueTo( $this->value, $base );
    if(!is_null($padlen)) {
      if(is_null($padchar)) {
        $padchar = current($base);
      }
      $res = str_pad( $res, $padlen, $padchar, \STR_PAD_LEFT);
    }
    return $res;
  }



  /**
   * Converts from
   */
  public static function from(string $number, $from=10) : self {
    return new static($number, $from);
  }



  /**
   * Parses a base and returns an ordered
   * array with the characters of the base
   */
  protected static function parseBase($base) : array {
    if(is_int($base)) {
      if($base < static::BASE_MIN_LENGTH || $base > static::BASE_MAX_LENGTH) {
        throw new \Exception("Non valid base ($base)");
      }
      $chars = str_split(substr(static::BASE_CHARS, 0, $base));
      return $chars;
    }
    if(is_string($base)) {
      $base = str_split($base);
    }
    if(!is_array($base)) {
      throw new \Exception('Non valid base');
    }
    foreach($base as $char) {
      if(!is_string($char) || strlen($char) !== 1) {
        throw new \Exception("Non valid base ($base)");
      }
    }
    $size = count($base);
    $base = array_unique($base);
    if(count($base) != $size) {
      throw new \Exception('Non valid base, repeated characters');
    }
    if($size < static::BASE_MIN_LENGTH) {
      throw new \Exception("Non valid base ($base)");
    }
    return $base;
  }



  /**
   * Converts to a numeric value
   */
  protected static function toValue(string $number, array $from) {
    $number = static::trim($number, $from);
    if($from === static::BASE_DECIMAL) {
      return $number;
    }
    $number_chars = str_split($number);
    $from_len = count($from);
    $number_len = strlen($number);
    $out = 0;
    for($i=0;$i<$number_len;$i++) {
      $out = static::add(
        $out,
        static::mul(array_search($number_chars[$i], $from),
          static::pow($from_len, $number_len-$i-1)
        )
      );
    }
    return $out;
  }



  /**
   * Converts to a base from a value
   */
  protected static function valueTo(string $value, array $to) : string {
    if($to === static::BASE_DECIMAL) {
      return $value; 
    }
    $value_len = strlen($value);
    $to_len = count($to);
    if($value >= 0 && $value < $to_len) {
      return $to[(int)$value];
    }
    $out = '';
    while($value != '0') {
      $pos = static::mod( $value, $to_len );
      $out = $to[ $pos ].$out;
      $value = static::div( $value, $to_len, 0);
    }
    return $out;
  }



  /**
   * Trims characters not in the base
   */
  protected static function trim(string $number, array $base) : string {
    $chars = str_split($number);
    $chars = array_unique($chars);
    $diffs = array_diff($chars, $base);
    $first = current($base);
    $number = trim($number, implode('', $diffs) );
    $chars = str_split($number);
    $extras = array_intersect($chars, $diffs);
    if(!empty($extras)) {
      return $first;
    }
    return $number;
  }



  /**
   * Math functions
   */
  protected static function mathLib() {
    if(static::$math_lib) {
      return static::$math_lib;
    }
    if(function_exists('gmp_add')) {
      static::$math_lib = 'gmp';
    }
    else if(function_exists('bcadd')) {
      static::$math_lib = 'bc';
    }
    else {
      static::$math_lib = 'php';
      trigger_error('No math library is available (gmp/bcmath), results are not reliable.', \E_USER_WARNING);
    }
    return static::$math_lib;
  }
  protected static function add($a, $b) {
    switch(static::mathLib()) {
      case 'gmp':
        return (string)gmp_add($a, $b);
      case 'bc':
        return bcadd($a, $b);
    }
    return $a+$b;
  }
  protected static function mul($a, $b) {
    switch(static::mathLib()) {
      case 'gmp':
        return (string)gmp_mul($a, $b);
      case 'bc':
        return bcmul($a, $b);
    }
    return $a*$b;
  }
  protected static function pow($a, $b) {
    switch(static::mathLib()) {
      case 'gmp':
        return (string)gmp_pow($a, $b);
      case 'bc':
        return bcpow($a, $b);
    }
    return pow($a,$b);
  }
  protected static function mod($a, $b) {
    switch(static::mathLib()) {
      case 'gmp':
        return (string)gmp_mod($a, $b);
      case 'bc':
        return bcmod($a, $b);
    }
    return $a%$b;
  }
  protected static function div($a, $b) {
    switch(static::mathLib()) {
      case 'gmp':
        return (string)gmp_div($a, $b);
      case 'bc':
        return bcdiv($a, $b);
    }
    return floor($a/$b);
  }



}


