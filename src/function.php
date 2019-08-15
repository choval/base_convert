<?php
namespace Choval;

function base_convert(string $number, $from, $to, int $padlen=NULL, str $padchar=NULL) {
  return BaseConvert::from($number, $from)->to($to, $padlen, $padchar);
}

