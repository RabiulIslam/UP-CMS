<?php
   include_once '../api/v1/mobile/outh.php';
	outh::checkIp($_POST['con']);

	$header = apache_request_headers();
	if($header['Authorization']!="push!UP!Qatar>m")
	exit; 
	//print_r($_POST);
	if(isset ($_POST['token'] , $_POST['title'], $_POST['message'],$_POST['deviceType'])) 
	{
		if(is_string($_POST['token']) && strpos($_POST['token'], ",") > -1){
		$params['token'] = explode(',', $_POST['token']);
		}
		else
		$params['token'] = (array)$_POST['token'];
		
		$params['date']=$_POST['date'];
		$params['title']=$_POST['title'];
		$params['message']=$_POST['message'];
		$params['deviceType']=$_POST['deviceType'];
		$params['id']=$_POST['id'];
		push::sendPush($params);
	}
	class push
	{
	  static function sendPush($params)
		{
				
			$tokens=$params['token'];	
			
			$result = -1;
			// API access key from Google API's Console
			 define( 'API_ACCESS_KEY', 'AAAAIadMUBM:APA91bEBGppl9A_v1Ezsdtc4EbKlsXHzgstOiRwzFL7aBCR9nX7Oxzw1FhKC0FCeryImYR1_SXC_ahVcKqdpg3aZLIaUbZwFDa4iDb4nUvHLYKuOeH_BfmZy1UCzDP58IJG3B3zbDjr_' );
			$arrayname="";
			if($_POST['deviceType']=='ios')
			{
				$fields = array (
				'registration_ids' => $tokens,
				'notification' => array (
				'title' 	=> $params['title'],
				'message' 	=> $params['message'],
				'date' 	=> $params['date'],
				'id' => (int)$params['id'],
				'sound' => 'default'
				));
			}
			else if($_POST['deviceType']=='android')
			{
				$fields = array (
				'registration_ids' => $tokens,
				'data' => array (
				'title' 	=> $params['title'],
				'message' 	=> $params['message'],
				'date' 	=> $params['date'],
				'id' => (int)$params['id'],
				'sound' => 'default'
				));
			}
			
			
			
			$headers = array
			(
			'Authorization: key=' . API_ACCESS_KEY,
			'Content-Type: application/json'
			);
			
			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			$result = curl_exec($ch );
			curl_close( $ch );
			print_r($result);
			
			
			//return $result > 0;
		  }
	}
	
?>