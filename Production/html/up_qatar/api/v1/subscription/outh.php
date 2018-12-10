<?php
include (__DIR__).'/../../../common/dbConfig.php';
class outh 
{
	static public $params=array();
 /* :------------------------------methodParamscheck check  ------------------------------*/
  static function methodParamscheck()
	 {
		$json=array();
		
		$path = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
		$file = substr( strrchr( $path, "/" ), 1) ; 
		self::$params['apiBasePath']=str_replace( $file, '', $path ) ;
		
		$con=dbConfigMethods::dbConnection();
		//$checkIp=outh::checkIp($con);
		if($checkIp=="blocked")
		{
			$response = array("status" => 403, "message" => 'Forbidden', "data" => NULL);
			echo json_encode($response);
			header("HTTP/1.1 401");
			exit();	
		}
		
		$methodName = '';
		if(isset($_SERVER['REQUEST_METHOD']) && isset($_GET['method']))
		{
			$method=  $_SERVER['REQUEST_METHOD'];
			$methodName= $_GET['method'];
		}
		if($methodName =='' || $method =="OPTIONS")
		 {
				$response = array("status" => 200, "message" => 'Wellcome to UP', "data" => NULL);
				echo json_encode($response);
				header("HTTP/1.1 200");
				exit();
		 }
		$header = apache_request_headers();
		
		self::$params['langset']='en';
		if(isset($header['lang']) && $header['lang']=="ar")
		self::$params['langset']="ar";
		
		if(isset($header['app_id']))
		self::$params['user_app_id']=$header['app_id'];
		
		$postdata = @file_get_contents("php://input");
		if($postdata)
		 {    
			$json = json_decode($postdata, true);
			 if(!empty($json)){
					if($json ){
						foreach($json as $item => $value)
						if($methodName=='sendEmail')
						self::$params[$item] = (isset($value)) ? $value : '';
						else
						self::$params[$item] = (isset($value)) ? addslashes($value) : '';
					}
				}
		 }
		 if(!empty($_POST))
		 {
			  foreach($_POST as $item => $value)
			  if($methodName=='sendEmail')
			  self::$params[$item] = (isset($value)) ? $value : '';
			  else
			  self::$params[$item] = (isset($value)) ? addslashes($value) : '';
		 }
		 
		 if(!empty($_GET))
		 {
			 foreach($_GET as $item => $value)
			 self::$params[$item] = (isset($value)) ? addslashes($value) : '';
		 }
		// print_r(self::$params);
		
		
		
		
		$results=outh::outhforAll($methodName,$con,$header['Authorization']);
		//print_r($results);
		if($results['user_id'] !="")
		self::$params['user_id']=$results['user_id'];
		self::$params['user_deviceType']=$results['deviceType'];
		self::$params['dbconnection']=$con;
		
		//below if else statement only for magento user
		if(isset($_POST['customer_id']) && $_POST['customer_id']!='')
		{
		  self::$params['user_id']=$_POST['customer_id'];
		  unset($_POST['customer_id']);
		  unset(self::$params['customer_id']);
		}
		else
		if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id']!='')
		{
			self::$params['user_id']=$_REQUEST['customer_id'];
			unset($_REQUEST['customer_id']);
			unset(self::$params['customer_id']);
		}
		
		
		/*print_r(self::$params);
		exit;*/
		return array("methodName"=>$methodName, "method"=> $method, "params"=> self::$params);
	 }
	 
 /* :------------------------------outhentication check  ------------------------------*/	 
	static function outhforAll($methodName,$con,$authorization)
	 {
		  $withoutsessionarray = array("addPremierUser" ,"unsubPremierUser" ,"subExtra" ,"unsubExtra" ,"mobileSubscription" 
		  ,"mobileUnsubscribe" ,"cronjob"  ,"runNonRCronJob" ,"doChargeMonthly" ,"doChargeWeekly" ,"doChargeDaily" ,"addPremierUser" ,"sendMTtoremierNonUser",
		  "unsubscribe" );
		  $doCharge = array("doChargeMonthly","doChargeWeekly","doChargeDaily","runNonRCronJob" );
		  $bothwithorwithout=array("sendMT");
		  
		  if($methodName=='sendMT' || $methodName=='addPremierUser' || $methodName=='unsubPremierUser' || $methodName=='subExtra' 
		  || $methodName=='unsubExtra' || $methodName=='mobileSubscription' || $methodName=='mobileUnsubscribe' || $methodName=='unsubscribe')
		  $authorization="UP!and$";
		
		  self::$params['Authorization']=$authorization;
		  //$authorization="UP!and$";// remove this one
		  outh::pagination();
		  if(!empty($authorization) )
		  {
			 if (in_array($methodName, $withoutsessionarray))
			 {
				if($authorization!="UP!and$" )
				$error="1";
				else
				{
					
					$row=array();
					$row['user_id']=NULL;
					$row['deviceType']=NULL;
					return $row;
				}
			}
			else if((in_array($methodName, $doCharge)) && ($authorization!="UP!and$doCharge" ))
			{
			   $error="1";
			}
			else
			{
				if((in_array($methodName, $bothwithorwithout)) && ($authorization=="UP!and$" ))
				{
				    $row=array();
					$row['user_id']=NULL;
					$row['deviceType']=NULL;
					return $row;
				}
				else
				{
					$userinfo= array();
					$query = "SELECT 
					au.`user_id`,
					au.`deviceType`
					FROM `authkeys` as au 
					INNER JOIN `users` as u ON(u.`id`=au.`user_id`)
					WHERE au.`auth_key`='{$authorization}' AND au.`auth_key` !='' ";
					$result_1 = mysqli_query($con,$query) ;
					if (mysqli_error($con) != '')
					return  "mysql_Error:-".mysqli_error($con);
					if (mysqli_num_rows($result_1) > 0)
						{
							$row = mysqli_fetch_assoc($result_1);
							$row['deviceType']='subscription';
							return $row;
						}
						else
						$error="1";
				}
			 }
		  }
		  else
		  $error="1";
		  if($error !="")
		  {
			    if($error=='2')
				$response = array("status" => 403, "message" => 'Forbidden', "data" => NULL);
				else
				$response = array("status" => 401, "message" => 'Invalid Parameter: Headers', "data" => NULL);
				
				echo json_encode($response);
				header("HTTP/1.1 401");
				exit();
		  }
		  else
		   return $headers['id'];
	 }
 /* :------------------------------outhentication check  ------------------------------*/	 
	static function pagination()
	 {
			$headers = apache_request_headers();
			if(isset($headers['index']))
			{
				$index = preg_match('/[^0-9]/', $headers['index']);
				if ($index > 0 || $headers['index'] ==0)
				self::$params['index']=1;
				else
				self::$params['index']=$headers['index'];
			}
			else if(isset($_REQUEST['index']))
			{
				$index = preg_match('/[^0-9]/', $_REQUEST['index']);
				if ($index > 0 || $_REQUEST['index'] ==0)
				self::$params['index']=1;
				else
				self::$params['index']=$_REQUEST['index'];
			} 
			else
			self::$params['index']=1;
			self::$params['index'] = ((self::$params['index'] * 20) - 20);
			self::$params['index2']=20;
	 }
	  /* :------------------------------outhentication checkIp  ------------------------------*/	 
	static function checkIp($con)
	 {
		 
			$ip = $_SERVER['REMOTE_ADDR']; 
			//$ip="100.82.63.68";
			if($ip!="18.185.217.28")
			{
				$hits="";
				$created_at="";
				$block="";
				$blockNumber=1150;
				$updatedDuration=2250;
				
				$return= NULL;
				$query = "SELECT 
				`id`,
				`ip`,
				`hits`,
				`block`,
				`created_at`,
				`updated_at`,
				DATE_ADD(`created_at`, INTERVAL 5 MINUTE) as updated_at_five,
				NOW() as currentDateTime
				FROM  `ipaddress`
				WHERE `ip`= '{$ip}' ";
				$result = mysqli_query($con,$query) ;
				if (mysqli_error($con) != '')
				return  "mysql_Error:-".mysqli_error($con);
				if (mysqli_num_rows($result) > 0) 
				{
					$row = mysqli_fetch_assoc($result);
					 $seconds = strtotime($row['currentDateTime']) - strtotime($row['created_at']);	
					//echo "==".$row['hits'];
					$hits="`hits` = '1' ,";
					$created_at="`created_at`=NOW(),  ";
					if($seconds < $updatedDuration)
					{
						$created_at="";
						$hits="`hits` = `hits` + 1 ,";
						
						if($row['hits'] > $blockNumber)
						{
							$block="`block`='1' ,";
							$return= "blocked";
							if( $row['block']=='0')
							outh::logErr('Deny from '.$ip);
						}
					}
					$queryu = "UPDATE `ipaddress` 
					SET 
					".$hits."
					".$block."
					".$created_at."
					`ip`= '{$ip}'
					WHERE `ip`= '{$ip}'";
					mysqli_query($con,$queryu) ;
					
					return $return;
				}
				else
				{
					$queryi = "INSERT INTO `ipaddress` SET  
					`ip`='{$ip}',
					`hits`=1 " ;
					mysqli_query($con,$queryi) ;
					return NULL;
				}
			}
	 }
 /* :------------------------------outhentication logErr  ------------------------------*/			
		static function logErr($data)
		{
			$file=fopen((__DIR__)."/../../../.htaccess","r+");
			$fWrite = fopen((__DIR__)."/../../../.htaccess","a");
			$wrote = fwrite($fWrite, $data."\n");
			fclose($fWrite);
			//echo readfile(".htaccess");
		}
}

?>