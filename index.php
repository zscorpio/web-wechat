<?php
	define('UID', '1024');
	include 'weixin.php';
	$weixin = new Weixin();
	$url = $weixin->genQrcode();
?>
	<script type="text/javascript" src="./jquery-1.11.0.min.js"></script>
	<img src="<?=$url?>">
	<script type="text/javascript">
		$(function(){
			// 循环请求
			window.setInterval(tryLogin, 1000);
			function tryLogin(){
				$.get('./getSidUin.php', function(data) {
					if(data == 'success'){
						window.location.href = "./logined.php";
					}
				});				
			}
		})
	</script>