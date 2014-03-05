<?php
	class HttpRequest {

		public static function request(
			$url,
			$argsGet     = null,
			$argsPost    = null,
			$headerOnly  = false,
			$binaryMode  = false,
			$timeout     = 300,
			$maxRedirs   = 3,
			$postType    = 'txt',
			$jsonDecode  = false,
			$decoAsArray = true,
			$proxy       = array(),
			$cstRequest  = ''
		) {
			if ($url) {
				if ($argsGet) {
					$url.= (strpos($url, '?') ? '&' : '?')
						. http_build_query($argsGet);
				}
				$objCurl = curl_init();
				curl_setopt($objCurl, CURLOPT_URL,            $url);
				curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($objCurl, CURLOPT_HEADER,         $headerOnly);
				curl_setopt($objCurl, CURLOPT_BINARYTRANSFER, $binaryMode);
				curl_setopt($objCurl, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt($objCurl, CURLOPT_MAXREDIRS,      $maxRedirs);
				curl_setopt($objCurl, CURLOPT_FOLLOWLOCATION, 1);
				if ($proxy && $proxy['type'] && $proxy['addr'] && $proxy['port']) {
					curl_setopt($objCurl, CURLOPT_PROXY,     $proxy['addr']);
					curl_setopt($objCurl, CURLOPT_PROXYPORT, $proxy['port']);
					if ($proxy['type'] === 'socks') {
						curl_setopt($objCurl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
					}
				}
				if ($argsPost !== null) {
					switch ($postType) {
						case 'json':
							$argsPost = json_encode($argsPost);
							break;
						case 'form':
							$argsPost = http_build_query($argsPost);
					}
					if (!$cstRequest) {
						curl_setopt($objCurl, CURLOPT_POST, 1);
					}
					curl_setopt($objCurl, CURLOPT_POSTFIELDS, $argsPost);
				}
				if ($cstRequest) {
					curl_setopt($objCurl, CURLOPT_CUSTOMREQUEST, $cstRequest);
				}
				$rawData     = @curl_exec($objCurl);
				$intHttpCode = @curl_getinfo($objCurl, CURLINFO_HTTP_CODE);
				$result = array(
					'data'      => $rawData,
					'http_code' => $intHttpCode,
					'infos'     => @curl_getinfo($objCurl),
				);
				curl_close($objCurl);
				if ($jsonDecode) {
					$result['json'] = @json_decode($rawData, $decoAsArray);
				}
			}
			return $result;
		}
	}