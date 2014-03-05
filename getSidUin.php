<?php
	define('UID', '1024');
	include 'weixin.php';
	$weixin = new Weixin();
	$result = $weixin->getSidUin();
	echo $result;