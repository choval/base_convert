<?php

use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use React\Promise\Deferred;
use React\Promise;

use Choval\BaseConvert;
use function Choval\base_convert;
use function base_convert as php_base_convert;

class BaseConvertTest extends TestCase {

  
  /**
   * Providers
   */
  public function randomNumber($max=65536) {
    return random_int( 0, $max);
  }
  public function randomSetLarge() {
    $out = [];
    for($i=0;$i<5;$i++) {
      $out[] = [ $this->randomNumber(PHP_INT_MAX) ];
    }
    $out[] = [PHP_INT_MAX];
    return $out;
  }
  public function randomSetSmall() {
    $out = [];
    $out[] = [ 0 ];
    $out[] = [ 16 ];
    for($i=0;$i<5;$i++) {
      $out[] = [ $this->randomNumber() ];
    }
    return $out;
  }



  /**
   * @dataProvider randomSetLarge
   */
  public function testAgainstPhpVersion($number) {
    for($i=2;$i<=36;$i++) {
      $php = php_base_convert($number, 10, $i);
      $our = base_convert($number, 10, $i);
      $this->assertEquals($php, $our);
    }
  }



  /**
   * @dataProvider randomSetLarge
   */
  public function testObjectStyle($number) {
    for($i=2;$i<=36;$i++) {
      $php = php_base_convert($number, 10, $i);

      $our = BaseConvert::from($number)->to($i);
      $this->assertEquals($php, $our);

      $our2 = (new BaseConvert($number))->to($i);
      $this->assertEquals($php, $our2);

      $obj = new BaseConvert($number);
      $obj->from($php, $i);
      $back = $obj->to(10);
      $this->assertEquals($number, $back);
      $our3 = $obj->to($i);
      $this->assertEquals($php, $our3);

      $back2 = BaseConvert::from($php, $i)->to(10);
      $this->assertEquals($number, $back2);
    }
  }



  /**
   * @dataProvider randomSetSmall
   */
  public function testPadding($number) {
    $base = 36;
    $len = strlen($number);
    $php = php_base_convert($number, 10, $base );
    $obj = BaseConvert::from($number);
    $res = $obj->to($base);
    $res_len = strlen($res);
    $diff = 2;
    $res_padded = $obj->to($base, $res_len + $diff);
    $this->assertEquals( $res_len + $diff , strlen($res_padded) );
    $this->assertEquals( $res, substr($res_padded, $diff));

    $a = BaseConvert::from($res_padded, $base)->to(10);
    $this->assertEquals( $number, $a);
  }



  /**
   * @dataProvider randomSetLarge
   */
  public function testNegative($number) {
    $number *= -1;
    $base = 36;
    $php = php_base_convert($number, 10, $base);
    $our = BaseConvert::from($number)->to($base);
    $this->assertEquals( $php, $our );
  }



  /**
   * @dataProvider randomSetLarge
   */
  public function testCustomBase($number) {
    $base64 = ' ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+-.,_:;/!@#$%^&*()={}[]';
    $obj = new BaseConvert($number);
    $a = BaseConvert::from($number, 10)->to($base64);
    $b = BaseConvert::from($a, $base64)->to(10);
    $this->assertLessThan( strlen($number), strlen($a) );
    $this->assertNotEquals($number, $a);
    $this->assertEquals($number, $b);
  }



  public function testCharNotInBase() {
    $base = '12345';
    $num = '-0123-4';
    $a = BaseConvert::from($num, $base)->to(10);
    $this->assertEquals(0, $a);

    $num = '    01234';
    $b = BaseConvert::from($num, $base)->to($base);
    $this->assertEquals('234', $b);
  }



  public function testBadBase() {
    $base = ' abc ';
    $num = 1000;
    $this->expectException(\Exception::class);
    $a = BaseConvert::from($num, $base)->to(10);
  }



}

