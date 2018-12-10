<?php
include_once 'dbConfig2.php';


if(isset ($_REQUEST['export'])) 
	{
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		if($_REQUEST['export']=='all_subscriptions')
		{
			$resultall=DbMethods::all_subscriptions();
			
			$response["status"] = 200;
			$response["message"] = "data";
			$response["data"] = $resultall;
			
			echo  json_encode($response);
		}
		if($_REQUEST['export']=='all_notifications')
		{
		  $resultall= DbMethods::all_notifications();
		  $response["status"] = 200;
			$response["message"] = "data";
			$response["data"] = $resultall;
			echo  json_encode($response);
		}
		
		if($_REQUEST['export']=='all_readed_notifications')
		{
		  $resultall= DbMethods::all_readed_notifications();
		  $response["status"] = 200;
			$response["message"] = "data";
			$response["data"] = $resultall;
			echo  json_encode($response);
		}
		
		if($_REQUEST['export']=='accesscodes')
		{
			$resultall= DbMethods::accesscodes();
			$response["status"] = 200;
			$response["message"] = "data";
			$response["data"] = $resultall;
			echo  json_encode($response);
		}
		
		if($_REQUEST['export']=='non_registered_users')
		{
			$resultall= DbMethods::non_registered_users();
			$response["status"] = 200;
			$response["message"] = "data";
			$response["data"] = $resultall;
			echo  json_encode($response);
		}
		
		
		if($_REQUEST['export']=='noncustomer_subscriptions')
		{
			$resultall= DbMethods::noncustomer_subscriptions();
			$response["status"] = 200;
			$response["message"] = "data";
			$response["data"] = $resultall;
			echo  json_encode($response);
		}
		
		if($_REQUEST['export']=='subscriptions_log')
		{
			$index=1;
		   for($i=0;$i<600;$i++)
		   {
			   echo $index."_";
			$resultall= DbMethods::subscriptions_log($index);
			$index++;
		   }
			$response["status"] = 200;
			$response["message"] = "data";
			$response["data"] = $resultall;
			
		   
			echo  json_encode($response);
		}
	}
class DbMethods {
/* 1:------------------------------method start here all_users ------------------------------*/
static function all_subscriptions()
{
		   global $con;
		   $url="http://18.185.217.28/extract_date_sub/?export=all_subscriptions";
			
			static $ch;
			if(empty($ch))
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_ENCODING , "gzip"); 
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			$data  = curl_exec($ch);
			curl_close($ch);
			if($data)
			{
				$multiplequery="INSERT INTO 
				`subscriptions`(`user_id`,`phone`,`network`,`start_datetime`,`expiry_datetime`,`status`,`premier_user`,`accesscode_id`,
				`subscriptionContractId`,`created_at`,`updated_at`) VALUES ";
				$data = stripslashes($data);
				$data = json_decode($data,true);	
				//print_r($data['data']);
				$multiplequerycol="";
				for($i=0;$i<count($data['data']); $i++)
				{
				  $user_id="NULL ,"; $phone="NULL ,"; $network="NULL ,";  $start_datetime="NULL ,"; 
				  $expiry_datetime="NULL ,"; $status="NULL ,"; $premier_user="NULL ,"; $accesscode_id="NULL ,"; $subscriptionContractId="NULL ,";
				  $created_at="NOW(),";$updated_at="NOW()";
				  
				if($data['data'][$i]['user_id']!="")	
				$user_id="'{$data['data'][$i]['user_id']}',";
				if($data['data'][$i]['phone']!="")	
				$phone="'{$data['data'][$i]['phone']}',";
				if($data['data'][$i]['network']!="")	
				$network="'{$data['data'][$i]['network']}',";
				if($data['data'][$i]['start_datetime']!="")	
				$start_datetime="'{$data['data'][$i]['start_datetime']}',";
				if($data['data'][$i]['expiry_datetime']!="")	
				$expiry_datetime="'{$data['data'][$i]['expiry_datetime']}',";
				if($data['data'][$i]['status']!="")	
				$status="'{$data['data'][$i]['status']}',";
				if($data['data'][$i]['premier_user']!="")	
				$premier_user="'{$data['data'][$i]['premier_user']}',";
				if($data['data'][$i]['accesscode_id']!="")	
				$accesscode_id="'{$data['data'][$i]['accesscode_id']}',";
				if($data['data'][$i]['subscriptionContractId']!="")	
				$subscriptionContractId="'{$data['data'][$i]['subscriptionContractId']}',";
				if($data['data'][$i]['created_at']!="")	
				$created_at="'{$data['data'][$i]['created_at']}',";
				if($data['data'][$i]['updated_at']!="")	
				$updated_at="'{$data['data'][$i]['updated_at']}'";
				$multiplequerycol.="(".$user_id.$phone.$network.$start_datetime.$expiry_datetime.$status.$premier_user.$accesscode_id.$subscriptionContractId.$created_at.$updated_at."),";   
				}
				
				$multiplequerycol= substr($multiplequerycol, 0, -1);
				echo  $query=$multiplequery.$multiplequerycol;
				mysqli_query($con,$query) ;
				
				return "added";
			}
			
		
}



/* 1:------------------------------method start here all_users ------------------------------*/
static function all_notifications()
{
		   global $con;
		   $url="http://18.185.217.28/extract_date_sub/?export=all_notifications";
			
			static $ch;
			if(empty($ch))
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_ENCODING , "gzip"); 
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			$data  = curl_exec($ch);
			curl_close($ch);
			
			if($data)
			{
			   
				
				$data = json_decode($data,true);	
				/*echo "<pre>";
				print_r($data['data']);
				exit;*/
				
				$multiplequerycol="";
				for($i=0;$i<count($data['data']); $i++)
				{
                 				
	            $id=""; $admin_id=""; $title=""; $message=""; $platform=""; 
				$audience=""; $operator=""; $dates=""; 
				$greater_than=""; $less_than=""; $specificUsers="";$push="";$status="";
				$created_at="";$updated_at="";
				
				if($data['data'][$i]['push']=="on")	
				$data['data'][$i]['push']=1;
				else
				$data['data'][$i]['push']=0;
				
				if($data['data'][$i]['title'] !="")
				$data['data'][$i]['title']=addslashes($data['data'][$i]['title']);
				
				if($data['data'][$i]['message'] !="")
				$data['data'][$i]['message']=addslashes($data['data'][$i]['message']);
				  
				if($data['data'][$i]['id']!="")	
				$id="id='{$data['data'][$i]['id']}',";
				if($data['data'][$i]['title']!="")	
				$title="title='{$data['data'][$i]['title']}',";
				if($data['data'][$i]['message']!="")	
				$message="message='{$data['data'][$i]['message']}',";
				if($data['data'][$i]['platform']!="")	
				$platform="platform='{$data['data'][$i]['platform']}',";
				if($data['data'][$i]['audience']!="")	
				$audience="audience='{$data['data'][$i]['audience']}',";
				if($data['data'][$i]['operator']!="")	
				$operator="operator='{$data['data'][$i]['operator']}',";
				if($data['data'][$i]['dates']!="")	
				$dates="dates='{$data['data'][$i]['dates']}',";
				if($data['data'][$i]['greater_than']!="")	
				$greater_than="greater_than='{$data['data'][$i]['greater_than']}',";
				if($data['data'][$i]['less_than']!="")	
				$less_than="less_than='{$data['data'][$i]['less_than']}',";
				if($data['data'][$i]['specificUsers']!="")	
				$specificUsers="specificUsers='{$data['data'][$i]['specificUsers']}',";
				if($data['data'][$i]['push']!="")	
				$push="push='{$data['data'][$i]['push']}',";
				if($data['data'][$i]['status']!="")	
				$status="status='{$data['data'][$i]['status']}',";
				if($data['data'][$i]['created_at']!="")	
				$created_at="created_at='{$data['data'][$i]['created_at']}',";
				if($data['data'][$i]['updated_at']!="")	
				$updated_at="updated_at='{$data['data'][$i]['updated_at']}'";
				
				
				$admin_id="admin_id='1',";
				echo  $query = "INSERT INTO `notifications` SET  
				".$id.$admin_id.$title.$message.$platform.$audience.$operator.$dates.$greater_than.$less_than
				.$specificUsers.$push.$status.$created_at.$updated_at."" ;
				mysqli_query($con,$query) ;
				
				
				}
				
				
				return "added";
			}
		
		
}



/* 1:------------------------------method start here all_users ------------------------------*/
static function all_readed_notifications()
{
		  global $con;
		   $url="http://18.185.217.28/extract_date_sub/?export=all_readed_notifications";
			
			static $ch;
			if(empty($ch))
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_ENCODING , "gzip"); 
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			$data  = curl_exec($ch);
			curl_close($ch);
			
			if($data)
			{
				$data = json_decode($data,true);	
				/*echo "<pre>";
				print_r($data['data']);
				exit;*/
				
				$multiplequerycol="";
				for($i=0;$i<count($data['data']); $i++)
				{
                 				
					$user_id=""; $notification_id="";
					$created_at="";$updated_at="";
					
					
					  
					if($data['data'][$i]['user_id']!="")	
					$user_id="user_id='{$data['data'][$i]['user_id']}',";
					if($data['data'][$i]['notification_id']!="")	
					$notification_id="notification_id='{$data['data'][$i]['notification_id']}',";
					if($data['data'][$i]['created_at']!="")	
					$created_at="created_at='{$data['data'][$i]['created_at']}',";
					if($data['data'][$i]['updated_at']!="")	
					$updated_at="updated_at='{$data['data'][$i]['updated_at']}'";
					
					
					echo $query = "INSERT INTO `readed_notifications` SET  
					".$user_id.$notification_id.$created_at.$updated_at."  `id` =`id`" ;
					mysqli_query($con,$query) ;
				
				
				echo "<br />";
				
				}
				
				
				return "added";
			}
		
		
}

 /*1:------------------------------method start here all_users ------------------------------*/
static function accesscodes()
{
		   global $con;
		   $url="http://18.185.217.28/extract_date_sub/?export=accesscodes";
			
			static $ch;
			if(empty($ch))
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_ENCODING , "gzip"); 
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			$data  = curl_exec($ch);
			curl_close($ch);
			if($data)
			{
			     $multiplequery="INSERT INTO 
				 `accesscodes`(`id`,`parent_id`,`title`,`code`,`redemptions`,`redeemed`,`expiry_datetime`,`days`,`status`,`created_at`,`updated_at`)
				  VALUES ";
				$data = stripslashes($data);
				$data = json_decode($data,true);	
				//print_r($data['data']);
				$multiplequerycol="";
				for($i=0;$i<count($data['data']); $i++)
				{
				  $id="NULL ,"; $parent_id="NULL ,"; $title="NULL ,"; $code="NULL ,"; $redemptions="NULL ,"; 
				  $redeemed="NULL ,"; $expiry_datetime="NULL ,"; $days="NULL ,"; $status="NULL ,";
				  $created_at="NOW(),";$updated_at="NOW()";
				  
					if($data['data'][$i]['id']!="")	
					$id="'{$data['data'][$i]['id']}',";
					if($data['data'][$i]['parent_id']!="")	
					$parent_id="'{$data['data'][$i]['parent_id']}',";
					if($data['data'][$i]['title']!="")	
					$title="'{$data['data'][$i]['title']}',";
					if($data['data'][$i]['code']!="")	
					$code="'{$data['data'][$i]['code']}',";
					if($data['data'][$i]['redemptions']!="")	
					$redemptions="'{$data['data'][$i]['redemptions']}',";
					if($data['data'][$i]['redeemed']!="")	
					$redeemed="'{$data['data'][$i]['redeemed']}',";
					if($data['data'][$i]['expiry_datetime']!="")	
					$expiry_datetime="'{$data['data'][$i]['expiry_datetime']}',";
					if($data['data'][$i]['days']!="")	
					$days="'{$data['data'][$i]['days']}',";
					if($data['data'][$i]['status']!="")	
					$status="'{$data['data'][$i]['status']}',";
					if($data['data'][$i]['created_at']!="")	
					$created_at="'{$data['data'][$i]['created_at']}',";
					if($data['data'][$i]['updated_at']!="")	
					$updated_at="'{$data['data'][$i]['updated_at']}'";
					$multiplequerycol.="(".$id.$parent_id.$title.$code.$redemptions.$redeemed.$expiry_datetime.$days.$status.$created_at.$updated_at."),";   
				}
				
				$multiplequerycol= substr($multiplequerycol, 0, -1);
				echo  $query=$multiplequery.$multiplequerycol;
				mysqli_query($con,$query) ;
				
				mysqli_query($con,"UPDATE `accesscodes` SET `multiple`='1' WHERE `parent_id` IS NOT  NULL AND `multiple`='0'");
				
				
				return "added";
			}
		
		
}




 /*1:------------------------------method start here non_registered_users ------------------------------*/
static function non_registered_users()
{
		   global $con;
		   $url="http://18.185.217.28/extract_date_sub/?export=non_registered_users";
			
			static $ch;
			if(empty($ch))
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_ENCODING , "gzip"); 
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			$data  = curl_exec($ch);
			curl_close($ch);
			
			if($data)
			{
			     $multiplequery="INSERT INTO 
				 `non_registered_users`(`phone`,`type`,`created_at`,`updated_at`)
				  VALUES ";
				$data = stripslashes($data);
				$data = json_decode($data,true);	
				//print_r($data['data']);
				$multiplequerycol="";
				for($i=0;$i<count($data['data']); $i++)
				{
				  $id="NULL ,"; $phone="NULL ,"; $premier_user="NULL ,"; $type="NULL ,";  $created_at="NOW(),";$updated_at="NOW()";
				  
				/*if($data['data'][$i]['id']!="")	
				$id="'{$data['data'][$i]['id']}',";*/
				if($data['data'][$i]['phone']!="")	
				$phone="'{$data['data'][$i]['phone']}',";
				/*if($data['data'][$i]['premier_user']!="")	
				$premier_user="'{$data['data'][$i]['premier_user']}',";*/
				if($data['data'][$i]['type']!="")	
				$type="'{$data['data'][$i]['type']}',";
				if($data['data'][$i]['created_at']!="")	
				$created_at="'{$data['data'][$i]['created_at']}',";
				if($data['data'][$i]['updated_at']!="")	
				$updated_at="'{$data['data'][$i]['updated_at']}'";
				$multiplequerycol.="(".$phone.$type.$created_at.$updated_at."),";   
				}
				
				$multiplequerycol= substr($multiplequerycol, 0, -1);
				echo  $query=$multiplequery.$multiplequerycol;
				mysqli_query($con,$query) ;
				
				return "added";
			}
		
		
}




/*1:------------------------------method start here noncustomer_subscriptions ------------------------------*/
static function noncustomer_subscriptions()
{
		   global $con;
		   $url="http://18.185.217.28/extract_date_sub/?export=noncustomer_subscriptions";
			
			static $ch;
			if(empty($ch))
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_ENCODING , "gzip"); 
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			$data  = curl_exec($ch);
			curl_close($ch);
			if($data)
			{
			     $multiplequery="INSERT INTO 
				 `nonregisteredusers_sub`(`phone`,`start_datetime`,`expiry_datetime`,`status`,`created_at`,`updated_at`)
				  VALUES ";
				$data = stripslashes($data);
				$data = json_decode($data,true);	
				//print_r($data['data']);
				$multiplequerycol="";
				for($i=0;$i<count($data['data']); $i++)
				{
				  $id="NULL ,"; $phone="NULL ,"; $start_datetime="NULL ,"; $expiry_datetime="NULL ,"; $status="NULL ,"; 
				  $created_at="NOW(),";$updated_at="NOW()";
				  
				/*if($data['data'][$i]['id']!="")	
				$id="'{$data['data'][$i]['id']}',";*/
				if($data['data'][$i]['phone']!="")	
				$phone="'{$data['data'][$i]['phone']}',";
				if($data['data'][$i]['start_datetime']!="")	
				$start_datetime="'{$data['data'][$i]['start_datetime']}',";
				if($data['data'][$i]['expiry_datetime']!="")	
				$expiry_datetime="'{$data['data'][$i]['expiry_datetime']}',";
				if($data['data'][$i]['status']!="")	
				$status="'{$data['data'][$i]['status']}',";
				if($data['data'][$i]['created_at']!="")	
				$created_at="'{$data['data'][$i]['created_at']}',";
				if($data['data'][$i]['updated_at']!="")	
				$updated_at="'{$data['data'][$i]['updated_at']}'";
				$multiplequerycol.="(".$phone.$start_datetime.$expiry_datetime.$status.$created_at.$updated_at."),";   
				}
				
				$multiplequerycol= substr($multiplequerycol, 0, -1);
				echo  $query=$multiplequery.$multiplequerycol;
				mysqli_query($con,$query) ;
				
				return "added";
			}
		
		
}




/*1:------------------------------method start here subscriptions_log ------------------------------*/
static function subscriptions_log($index)
{
		   global $con;
		  
		   $url="http://18.185.217.28/extract_date_sub/?export=subscriptions_log&index=".$index."";
			
			/*static $ch;
			if(empty($ch))*/
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_ENCODING , "gzip"); 
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			$data  = curl_exec($ch);
			curl_close($ch);
			if($data)
			{
			     $multiplequery="INSERT INTO 
				 `subscriptions_log`(`user_id`,`phone`,`network`,`start_datetime`,`expiry_datetime`,`price_point`,`response_code`,
				 `type`,`created_at`)
				  VALUES ";
				$data = stripslashes($data);
				$data = json_decode($data,true);	
				//print_r($data['data']);
				$multiplequerycol="";
				for($i=0;$i<count($data['data']); $i++)
				{
				  $user_id="NULL ,"; $phone="NULL ,"; $network=""; $start_datetime="NULL ,"; $expiry_datetime="NULL ,"; $price_point="NULL ,"; 
				  $response_code="";$type=""; $created_at="NOW()";
				  
				/*if($data['data'][$i]['id']!="")	
				$id="'{$data['data'][$i]['id']}',";*/
				if($data['data'][$i]['user_id']!="")	
				$user_id="'{$data['data'][$i]['user_id']}',";
				if($data['data'][$i]['phone']!="")	
				$phone="'{$data['data'][$i]['phone']}',";
				
				if($data['data'][$i]['network']!="")	
				$network="'{$data['data'][$i]['network']}',";
				
				if($data['data'][$i]['start_datetime']!="")	
				$start_datetime="'{$data['data'][$i]['start_datetime']}',";
				if($data['data'][$i]['expiry_datetime']!="")	
				$expiry_datetime="'{$data['data'][$i]['expiry_datetime']}',";
				if($data['data'][$i]['price_point']!="")	
				$price_point="'{$data['data'][$i]['price_point']}',";
				
				if($data['data'][$i]['response_code']!="")	
				$response_code="'{$data['data'][$i]['response_code']}',";
				
				if($data['data'][$i]['type']!="")	
				$type="'{$data['data'][$i]['type']}',";
				
				if($data['data'][$i]['created_at']!="")	
				$created_at="'{$data['data'][$i]['created_at']}'";
				
				$multiplequerycol.="(".$user_id.$phone.$network.$start_datetime.$expiry_datetime.$price_point.$response_code.$type.$created_at."),";   
				}
				
				$multiplequerycol= substr($multiplequerycol, 0, -1);
				$query=$multiplequery.$multiplequerycol;
				mysqli_query($con,$query) ;
				
				$index++;;
				
			 
			}
		
		return "added";
}



/* END-----------------------------END END END END------------------------------END*/

}


?>