#!/usr/bin/php7.3
<?php

$agi = new \TryAGI\Client(STDIN, STDOUT, new \Psr\Log\NullLogger());
$agi->init();

$result = $agi->send('VERBOSE "test verbose message"');

