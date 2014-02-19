<?php
error_reporting(E_ALL);
ini_set('display_errors',16384);

function runtest(){
  $start_time = microtime(true);
  $start_mem = memory_get_usage();
  ob_start();
  mytest2();
  $end_time = microtime(true);
  $end_mem = memory_get_usage();
  ob_end_flush();
  var_dump($end_time-$start_time);
  var_dump($end_mem-$start_mem);
  var_dump(memory_get_peak_usage());
}

?>