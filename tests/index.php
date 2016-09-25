<?php

/**
 * This file is a part of dakujem/time package.
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */
//
$dir = '.';

// run the test
$time1 = microtime(TRUE);
require_once($dir . '/helper.phpt');
print '<hr/><pre>helper.phpt | Finished at: ' . date('Y-m-d H:i:s') . ' | Runtime: ' . (microtime(TRUE) - $time1 ) . 's</pre>';

$time2 = microtime(TRUE);
require_once($dir . '/time.phpt');
print '<hr/><pre>time.phpt | Finished at: ' . date('Y-m-d H:i:s') . ' | Runtime: ' . (microtime(TRUE) - $time2 ) . 's</pre>';
