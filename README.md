# Choval/base_convert


A `base_convert` replacement that doesn't lose precision on large numbers.

A few differences from this convert:

* Second and third parameters (bases) accept:
	* an int between 2 and 64 (0-9a-zA-Z-_)
	* a string of unique characters
	* an array of unique characters
* A fourth parameter allows passing a padding length for the output
* A fifth parameter can be passed to be used as the padding character, else the first char of the base is used.

Negative numbers are converted to positive, just like `base_convert`.

## Install

This function uses the GMP or the BCMath extension. Install one of them.

```
apt install php-gmp
apt install php-bcmath
```

Uses GMP > BCMath > PHP. If vanilla PHP, a `E_USER_WARNING` is triggered.

To Install this library:

```
composer require choval/base_convert
```

## Usage

Procedural:

```php
use function Choval\base_convert;
echo base_convert(1000, 10, 16);
// 3e8
```

Object:

```php
use Choval\BaseConvert;
echo BaseConvert::from(1000)->to(16);
// 3e8

echo BaseConvert::from('3e8', 16)->to(64);
// fE
```

Padding:

```php
$base = 'abcdefghijklmnopqrstuvwxyz';

echo BaseConvert::from(1000)->to($base);
// bmm

echo BaseConvert::from(1000)->to($base, 10);
// aaaaaaabmm

echo BaseConvert::from(1000)->to($base, 10, '#');
// #######bmm

echo base_convert(1000, 10, $base, 10, '#');
// #######bmm

echo BaseConvert::from('#######bmm', $base)->to(10);
// 1000

echo base_convert('#######bmm', $base, 10);
// 1000
```

Keep in mind the type of the parameters, `'10'` and `10` are not the same.

```php
$base = 'abcdefghijklmnopqrstuvwxyz';

echo BaseConvert::from('bmm', $base)->to(10);
// 1000

echo BaseConvert::from('bmm', $base)->to('10');
// 0000010111

echo BaseConvert::from('bmm', $base)->to('01');
// 1111101000

echo BaseConvert::from('bmm', $base)->to(2);
// 1111101000
```

## License

MIT, see LICENSE

