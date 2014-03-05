<?php
	include 'config.php';
	include 'HttpRequest.php';
	/**
	 * 微信class
	 */
	class Weixin
	{

		/**
		 * 默认启动redis存储
		 */
		public function __construct(){
			$this->redis = new Redis();
			$this->redis->connect(REDIS_HOST, 6379, 3);
		}
		
		/**
		 * 微信专用拼凑json数据
		 * @param  [type] $data [description]
		 * @return [type]       [description]
		 */
		public function encode($data){
			if(!is_array($data)){
				return $this->encode_str($data);
			}
			$ds = array();
			foreach($data as $k => $v){
				$ds [] = "\"$k\":" . $this->encode($v);
			}
			return '{' . join(',', $ds) . '}';
		}

		/**
		 * 微信专用拼凑json数据
		 * @param  [type] $data [description]
		 * @return [type]       [description]
		 */
		public function encode_str($str){
			if(preg_match('|^\d+$|', $str)){
				return $str;
			}
			return '"' . str_replace('"', '\"', iconv('GBK', 'UTF-8', $str)) . '"';
		}


		public function isLogin(){

		}

		/**
		 * 生成登录用二维码
		 * @return [type] [description]
		 */
		public function genQrcode(){
			$url = 'https://login.weixin.qq.com/jslogin?appid=wx782c26e4c19acffb&redirect_uri=https%3A%2F%2Fwx.qq.com%2Fcgi-bin%2Fmmwebwx-bin%2Fwebwxnewloginpage&fun=new&lang=zh_CN';
			$content = HttpRequest::request($url);
			$uuid = preg_replace('/.*"(.*)".*/', '$1', $content['data']);
			$this->redis->set('WXuuid'.UID,$uuid);
			$url = "https://login.weixin.qq.com/qrcode/".$uuid."?t=webwx";
			return $url;
		}

		/**
		 * 获取sid和uin
		 * @return [type] [description]
		 */
		public function getSidUin(){
			$url = 'https://login.weixin.qq.com/cgi-bin/mmwebwx-bin/login?uuid='.$this->redis->get('WXuuid'.UID).'&tip=1';
			$content = HttpRequest::request($url);
			$tmp = explode('window.redirect_uri="', $content['data']);
			if(isset($tmp[1])){
				$url = str_replace('";','',$tmp[1]);
				$content = HttpRequest::request($url,null,null,true);
				if(strpos($content['data'],'Set-Cookie: wxuin=') !== false){
					$result = explode('
						', $content['data']);
					$reg = '/Set-Cookie: ([^\s=]+)=([^;]*);/';
					preg_match_all($reg, $result[0], $matches);
					$this->redis->set('WXsid'.UID,$matches[2][1]);
					$this->redis->set('WXuin'.UID,$matches[2][0]);
					return 'success';
				}
			}
		}

		/**
		 * 获取信息
		 * @return [type] [description]
		 */
		public function getInfo(){
			$data = array(
				"BaseRequest"=>array(
					"DeviceID"  => DeviceID,
					"Sid"   	=> $this->redis->get('WXsid'.UID),
					"Skey"  	=> "",
					"Uin"   	=> $this->redis->get("WXuin".UID)
				)
			);
			$url = 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxinit';
			$result = HttpRequest::request($url, null, $this->encode($data));
			$result = json_decode($result['data'],1);
			$this->redis->set('WXuser'.UID,json_encode($result['User']));
			$this->redis->set('WXuserSKey'.UID,$result['SKey']);
		}

		/**
		 * 发送消息
		 * @param  [type] $to      [description]
		 * @param  [type] $content [description]
		 * @return [type]          [description]
		 */
		public function send($to,$content){
			$user = json_decode($this->redis->get('WXuser'.UID),1);
			$data = array(
				'BaseRequest' => array(
					'DeviceID' => DeviceID,
					'Sid'      => $this->redis->get('WXsid'.UID),
					'Skey'     => $this->redis->get('WXuserSKey'.UID),
					'Uin'      => $this->redis->get('WXuin'.UID)
				),
				'Msg' => array(
					'FromUserName' => $user['UserName'],
					'ToUserName'   => $to,
					'Type'         => 1,
					'Content'      => $content,
					'ClientMsgId'  => 1,
					'LocalID'      => 1,
				),
			);

			$data = $this->encode($data);
			$result = HttpRequest::request('https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxsendmsg?sid=' . urlencode($this->redis->get('WXsid'.UID)) . '&r=' . 1, null, $data);
			$result = json_decode($result['data'],1);
			if($result['MsgID']){
				return 'success';
			}
		}

	}