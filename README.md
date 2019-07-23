# TryAGI
Asterisk gateway interface (AGI) library

## Installation

It's recommended that you use Composer to install TryAGI.

```
$ composer require userator/tryagi "~1.0.0"
```
This will install TryAGI and all required dependencies. TryAGI requires PHP 7.3.0 or newer.

## Usage

Create an agi.php file with the following contents:

```
#!/usr/bin/php7.3 -q
<?php

require 'vendor/autoload.php';

$agi = new \TryAGI\Client(STDIN, STDOUT, new \Psr\Log\NullLogger());

$agi->init();

$result = $agi->send('VERBOSE "test verbose message"');

```

## Tests
To execute the test suite, you'll need phpunit7.

```
$ phpunit7
```
