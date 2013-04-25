<?php

require_once dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';

use MODX\GreetCommand;
use Symfony\Component\Console\Application;

$shell = new Application('MODX Shell', '0.0.1');
$shell->add(new GreetCommand());
$shell->run();
