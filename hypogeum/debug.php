<?php
#error_reporting(E_ALL);
#ini_set('display_errors',16384);
#session_start();
require_once ('hypogeum.php');

foreach($_SESSION['debug']['libs'] as $key => $val){
  foreach($val as $k => $v){
    $libs[$key][$k] = unserialize($v);
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>HYPOGEUM::DEBUG</title>
</head>

<body>
<h1>Last Request: <?= floor(mktime() - $_SESSION['debug']['request_start'])?> seconds ago</h1>
<h1>PHP request time: <?= $_SESSION['debug']['request_end']-$_SESSION['debug']['request_start'] ?> ms</h1>
<h1>Request URI: <?= $_SESSION['debug']['request_uri'] ?></h1>
<h1>REST vars:</h1>
<ul>
<li><h3>GET</h3><ul>
<?php
foreach($_SESSION['debug']['get_vars'] as $key => $val){
  echo "<li>{$key} => {$val}</li>";
}
?>
</ul></li></ul>

<ul>
<li><h3>POST</h3><ul>
<?php
foreach($_SESSION['debug']['post_vars'] as $key => $val){
  echo "<li>{$key} => {$val}</li>";
}
?>
</ul></li></ul>

<hr />
<h1>SQL Queries: <?= count($_SESSION['debug']['queries']); ?></h1>
<ul>
<?
foreach($_SESSION['debug']['queries'] as $key => $val){
  echo '<li>'.$val.'</li>';
}
?>
</ul>
<hr />
<h1>Errors:</h1>
<ul>
<!-- -->
<li><h3>Major Errors (halt):</h3><ul>
<?php
foreach($_SESSION['debug']['errors']['major'] as $key => $val){
  echo "<li><pre>{$val}</pre></li>";
}
?>
</li></ul>
<!-- -->
<li><h3>Public Errors (report to js):</h3><ul>
<?php
foreach($_SESSION['debug']['errors']['public'] as $key => $val){
  echo "<li><pre>{$val}</pre></li>";
}
?>
</li></ul>
<!-- -->
<li><h3>Minor Errors (no halt):</h3><ul>
<?php
foreach($_SESSION['debug']['errors']['minor'] as $key => $val){
  echo "<li><pre>{$val}</pre></li>";
}
?>
</li></ul>
<!-- -->
</ul>

<hr />
<h1>Hypogeum chapters:</h1>
<ul>
<?php
$libs = ( isset($libs) ) ? $libs : array();
foreach($libs as $key => $val){
  echo '<li><h3>'.$key.' ('.count($val).' objects)</h3><ul>';
  foreach($val as $k => $v){
    echo '<li>'.$k.'<ul>';
	  foreach($v as $_k => $_v){
	    echo "<li>{$_k} => {$_v}</li>";
	  }
	echo '</ul></li>';
  }
  echo '</ul></li>';
}
?>
</ul>
<?php 


foreach($_SESSION['debug']['queries'] as $key => $val){
  #echo $val;
}




?>
</body>
</html>
