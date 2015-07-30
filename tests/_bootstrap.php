<?php
// This is global bootstrap for autoloading
use tad\FunctionMocker\FunctionMocker;

include_once dirname( dirname( __FILE__ ) ) . '/tad-reschedule.php';
FunctionMocker::init();
