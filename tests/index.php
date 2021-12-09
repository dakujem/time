<?php

declare(strict_types=1);

/**
 * This file is a part of dakujem/time package.
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */
//
$dir = '.';

// run the test
$time1 = microtime(true);
require_once($dir . '/helper.phpt');
print '<hr/><pre>helper.phpt | Finished at: ' . date('Y-m-d H:i:s') . ' | Runtime: ' . (microtime(true) - $time1) . 's</pre>';

$time2 = microtime(true);
require_once($dir . '/time.phpt');
print '<hr/><pre>time.phpt | Finished at: ' . date('Y-m-d H:i:s') . ' | Runtime: ' . (microtime(true) - $time2) . 's</pre>';
