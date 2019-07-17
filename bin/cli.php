#!/usr/bin/php7.3
<?php

include __DIR__ . '/../vendor/autoload.php';

$logger = new \Psr\Log\NullLogger();
$agi = new \TryAGI\Client($logger);

$agi->init();
$agi->send('VERBOSE "test verbose message"');