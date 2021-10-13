<?php
/*	
		kakaoService helpers	
		Author: Hieu Pham
		Date: Oct 06, 2021
		Last updated: Oct 14, 2021 
*/

function getStreamUrl($videoId, $username){
	if(is_numeric($videoId)){
		$endpointUrl = 'https://sdk-tv.kakao.com/katz/v3/app/cliplink/' . $videoId . '/play?autoPlay=false&section=channel&appVersion=93.0.4577.63&tid=4e14056ba22cdd928b29c22a87cd211c&dteType=PC&playerVersion=3.11.3&service=kakao_tv&contentType=&continuousPlay=false&profile=MAIN&player=monet_html5';
		$handle = curl_init($endpointUrl);
		curl_setopt_array($handle, array(
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.63 Safari/537.36',
			CURLOPT_ENCODING => '', 
			CURLOPT_CUSTOMREQUEST => 'GET', 
			CURLOPT_HTTPHEADER => array(
				'from: app',
				'Content-type: application/json',
				'Authorization: ' . getAccessToken($username)
			), 
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_FOLLOWLOCATION => 1
		));
		$ret = curl_exec($handle);
		curl_close($handle);
		@$streamUrl = json_decode($ret)->videoLocation->url;
		if($streamUrl){
			return $streamUrl;
		}
	}
}

function getVideoUrl($videoId, $username){
	if(is_numeric($videoId)){
		$streamUrl = getStreamUrl($videoId, $username);
		if($streamUrl){
			$videoData[] = array(
				'label' => '480p',
				'message' => 'OK',
				'type' => 'video/mp4',
				'file' => $streamUrl
			);
		}else{
			$videoData[] = array(
				'label' => '360p',
				'message' => 'ERROR_01',
				'type' => 'video/mp4',
				'file' => '//embedwistia-a.akamaihd.net/deliveries/1fee0ada96f6930acd9b5e0d42b23154/videoProcessing.mp4'
			);
		}
		$jwplayerData = json_encode($videoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		return $jwplayerData;
	}else{
		return 'ERROR_02';
	}
}

function generateUserData($username, $password) {
	$fileName = 'data/refreshToken_' . $username . '.json';
	if((filemtime($fileName) < time() - 5183000) || !file_exists($fileName)) {
		$data = http_build_query(array(
			'client_id' => 'ee9acc4a22a285875995bfa49969ef25',
			'lang' => 'en-US',
			'os' => 'ios',
			'webview_v' => '2',
			'app_key' => 'ee9acc4a22a285875995bfa49969ef25',
			'email' => $username,
			'password' => $password,
			'third' => 'false',
			'authenticity_token' => 'hDOj1H23E5WhGc+Ni+Sb0znJW5+EJ65eW9drmZFzhECYCxKpTTBpxzClBRmmntZFHGzD9ARTs/0wNzANaS6dQQ=='
		)); 
		$handle = curl_init('https://auth.kakao.com/kakao_accounts/login.json');
		curl_setopt_array($handle, array(
			CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_5_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148',
			CURLOPT_ENCODING => '', 
			CURLOPT_CUSTOMREQUEST => 'POST', 
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/x-www-form-urlencoded',
				'Origin: https://auth.kakao.com',
				'Referer: https://auth.kakao.com/',
				'X-Requested-With: XMLHttpRequest'
			),
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HEADER => 1
		));
		$response = curl_exec($handle);
		curl_close($handle);
		if(preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $cookie)){
			if(count(end($cookie)) == 4) {
				$handle = curl_init('https://kauth.kakao.com/oauth/authorize?client_id=ee9acc4a22a285875995bfa49969ef25&response_type=code&redirect_uri=kakaoee9acc4a22a285875995bfa49969ef25%3A%2F%2Foauth');
				curl_setopt_array($handle, array(
					CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_5_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148',
					CURLOPT_ENCODING => '', 
					CURLOPT_CUSTOMREQUEST => 'GET', 
					CURLOPT_COOKIE => end($cookie)[2], 
					CURLOPT_HTTPHEADER => array(
						'KA: sdk/1.23.8 os/ios-12.5.4 lang/en-VN res/414x736 device/iPhone7,1 origin/com.kakao.tv.live app_ver/1.14.1',
						'Referer: https://auth.kakao.com/'
					),
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_HEADER => 1
				));
				$response = curl_exec($handle);
				curl_close($handle);
				if(preg_match('/code=(.*)/', $response, $code)){
					$data = http_build_query(array(
						'client_id' => 'ee9acc4a22a285875995bfa49969ef25',
						'code' => end($code),
						'redirect_uri' => 'kakaoee9acc4a22a285875995bfa49969ef25://oauth',
						'ios_bundle_id' => 'com.kakao.tv.live',
						'grant_type' => 'authorization_code'
					)); 
					$handle = curl_init('https://kauth.kakao.com/oauth/token');
					curl_setopt_array($handle, array(
						CURLOPT_USERAGENT => 'KakaoTV/0 CFNetwork/978.0.7 Darwin/18.7.0',
						CURLOPT_ENCODING => '', 
						CURLOPT_CUSTOMREQUEST => 'POST', 
						CURLOPT_HTTPHEADER => array(
							'Content-Type: application/x-www-form-urlencoded',
						),
						CURLOPT_POSTFIELDS => $data,
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_SSL_VERIFYPEER => 0,
					));
					$response = curl_exec($handle);
					curl_close($handle);
					@$refreshToken = json_decode($response)->refresh_token;
					if($refreshToken){
						$refreshToken = saveRefreshToken($username, $refreshToken);
						return 'Account ' . $username . ' has been added successfully!' ;
					}else{
						return 'ERROR_03';
					}
				}else{
					return 'ERROR_02';
				}
			}
		}else{
			return 'ERROR_01';
		}
	} else{
		return 'Account ' . $username . ' already added successfully!';
	}
}

function saveRefreshToken($username, $refreshToken){
	$fileName = 'data/refreshToken_' . $username . '.json';
	if ((filemtime($fileName) < time() - 5183000) || !file_exists($fileName)) {
		file_put_contents($fileName, $refreshToken); 
	} 
}

function getAccessToken($username){
	$refresh_token = file_get_contents('data/refreshToken_' . $username . '.json');
	$fileName = 'data/accessToken_' . $username . '.json';
	if ((filemtime($fileName) < time() - 40000) || !file_exists($fileName)) {
		$data = http_build_query(array(
			'grant_type' => 'refresh_token',
			'client_id' => 'ee9acc4a22a285875995bfa49969ef25',
			'refresh_token' => $refresh_token,
			'ios_bundle_id' => 'com.kakao.tv.live'
		)); 
		$handle = curl_init('https://kauth.kakao.com/oauth/token');
		curl_setopt_array($handle, array(
			CURLOPT_USERAGENT => 'KakaoTV/0 CFNetwork/1240.0.4 Darwin/20.6.0',
			CURLOPT_ENCODING => '', 
			CURLOPT_CUSTOMREQUEST => 'POST', 
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_FOLLOWLOCATION => 1
		));
		$ret = curl_exec($handle);
		curl_close($handle);
		$accessToken = json_decode($ret)->access_token;
		file_put_contents($fileName, $accessToken); 
	} 
	return file_get_contents($fileName);
}