<?php

/**
 * This file is a part of dakujem/time package.
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */
//
$dir = '.';

// run the test
$time = microtime(TRUE);
require_once($dir . '/time.phpt');
print '<hr/><pre>time.phpt | Finished at: ' . date('Y-m-d H:i:s') . ' | Runtime: ' . (microtime(TRUE) - $time ) . 's</pre>';
