<?php

/**
 * This file is a part of dakujem/time package.
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */


namespace Dakujem\Time\Test;

use Tester\Assert,
	Tester\Environment,
	Tracy\Debugger;
use function getallheaders;

define('ROOT', __DIR__);

require_once __DIR__ . '/../vendor/autoload.php';
if (!class_exists(Assert::class)) {
	echo "Install Nette Tester using `composer update --dev`\n";
	exit(1);
}

// tester
Environment::setup();

// debugging
if (function_exists('getallheaders') && !empty(getallheaders()) && class_exists(Debugger::class)) {
	Debugger::$strictMode = TRUE;
	Debugger::enable();
	Debugger::$maxDepth = 10;
	Debugger::$maxLen = 500;
}


// dump shortcut
function dump($var, $return = FALSE)
{
	return Debugger::dump($var, $return);
}
