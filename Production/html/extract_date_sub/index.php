<?php

include_once 'dbConfig.php';

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
			$index=5000;
			if(isset($_REQUEST['index']))
			{
			   $index=(($_REQUEST['index'] * 5000) - 5000);
			}
			$resultall= DbMethods::subscriptions_log($index);
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
		$subscriptions=array();
         $query_1 = "SELECT
		`customer_id` as user_id,
		`msisdn` as phone,
		`operator` as network,
		`start_datetime` as start_datetime,
		`expiry_datetime` as expiry_datetime,
		`status`,
		`access_code_id` as accesscode_id,
		`subscriptionContractId`,
		`premier_user`,
		`created_at`,
		`updated_at`
		FROM  `customer_subscriptions` 
		ORDER BY `id` ASC";
		$result = mysqli_query($con,$query_1) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		
		if (mysqli_num_rows($result) > 0) 
		{
			while ($row = mysqli_fetch_assoc($result)) 
			{
				
				$subscriptions[] = $row;
			}
			
			return $subscriptions;
			
			
		}
}



/* 1:------------------------------method start here all_users ------------------------------*/
static function all_notifications()
{
    	
		global $con;
		$notifications=array();
          $query_1 = "SELECT
		 `id`,
		`title`,
		`message`,
		`uudience` as audience,
		`user_status`,
		`user_created_dates` as dates,
		`user_created_date_greater` as greater_than,
		`user_created_date_less` as less_than,
		`specific_users` as specificUsers ,
		`platform`,
		`operator`,
		`push`,
		`status`,
		`created_at`,
		`updated_at`
		FROM  `notifications` 
		ORDER BY `id` ASC";
		$result = mysqli_query($con,$query_1) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		
		if (mysqli_num_rows($result) > 0) 
		{
			while ($row = mysqli_fetch_assoc($result)) 
			{
				if($row['audience']=='specific_users')
				$row['audience']="specificusers";
				else if($row['audience']=='user_created_dates')
				$row['audience']="userCreatedDate";
				else if($row['audience']=='all_users')
				$row['audience']="allusers";
				
				
				if($row['dates']=='greater')
				$row['dates']="Greater";
				else if($row['dates']=='less')
				$row['dates']="Less";
				else if($row['dates']=='both')
				$row['dates']="Both";
				
				 if($row['platform']=='both')
				$row['platform']="Both";
				
				$notifications[] = $row;
			}
			return $notifications;
			
		}
}



/* 1:------------------------------method start here all_users ------------------------------*/
static function all_readed_notifications()
{
		global $con;
		$notifications=array();
         $query_1 = "SELECT
		`id`,
		`notification_id` as notification_id,
		`customer_id` as user_id,
		`created_at`,
		`updated_at`
		FROM  `notifications_readed` 
		ORDER BY `id` ASC";
		$result = mysqli_query($con,$query_1) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		
		if (mysqli_num_rows($result) > 0) 
		{
			while ($row = mysqli_fetch_assoc($result)) 
			{
				$notifications[] = $row;
			}
			return $notifications;
		}
}

/* 1:------------------------------method start here all_users ------------------------------*/
static function accesscodes()
{
    	
		global $con;
		$notifications=array();
         $query_1 = "SELECT
		`id`,
		`parent_id`,
		`title`,
		`code`,
		`redemptions`,
		`redeemed`,
		`expiry_datetime`,
		`days`,
		`status`,
		`created_at`,
		`updated_at`
		FROM  `access_codes` 
		ORDER BY `id` ASC";
		$result = mysqli_query($con,$query_1) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		
		if (mysqli_num_rows($result) > 0) 
		{
			while ($row = mysqli_fetch_assoc($result)) 
			{
				$notifications[] = $row;
			}
			
			return $notifications;
			
			
		}
}


/* 1:------------------------------method start here non_registered_users ------------------------------*/
static function non_registered_users()
{
    	
		global $con;
		$noncustomer_subscriptions=array();
         $query_1 = "SELECT
		`id`,
		`msisdn` as phone,
		`type`,
		`created_at`,
		`updated_at`
		FROM  `non_customers` 
		ORDER BY `id` ASC";
		$result = mysqli_query($con,$query_1) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		
		if (mysqli_num_rows($result) > 0) 
		{
			while ($row = mysqli_fetch_assoc($result)) 
			{
				$noncustomer_subscriptions[] = $row;
			}
			
			return $noncustomer_subscriptions;
			
			
		}
}




/* 1:------------------------------method start here noncustomer_subscriptions ------------------------------*/
static function noncustomer_subscriptions()
{
    	
		global $con;
		$noncustomer_subscriptions=array();
         $query_1 = "SELECT
		`id`,
		`msisdn` as phone,
		`start_datetime`,
		`expiry_datetime`,
		`status`,
		`created_at`,
		`updated_at`
		FROM  `noncustomer_subscriptions` 
		ORDER BY `id` ASC";
		$result = mysqli_query($con,$query_1) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		
		if (mysqli_num_rows($result) > 0) 
		{
			while ($row = mysqli_fetch_assoc($result)) 
			{
				$noncustomer_subscriptions[] = $row;
			}
			
			return $noncustomer_subscriptions;
			
			
		}
}


/* 1:------------------------------method start here subscriptions_log ------------------------------*/
static function subscriptions_log($index)
{
    	
		global $con;
		$index2=5000;
		$noncustomer_subscriptions=array();
         $query_1 = "SELECT
		`id`,
		`customer_id` as `user_id`,
		`msisdn` as phone,
		`operator` as `network`,
		`start_datetime`,
		`expiry_datetime`,
		`price_point`,
		`type`,
		`response_code` as `response_code` ,
		`created_at`
		FROM  `subscriptions_log` 
		ORDER BY `id` ASC LIMIT ".$index.",".$index2." ";
		$result = mysqli_query($con,$query_1) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		
		if (mysqli_num_rows($result) > 0) 
		{
			while ($row = mysqli_fetch_assoc($result)) 
			{
				 if($row['start_datetime']!=NULL && $row['expiry_datetime']!=NULL && $row['type']=="subscribe")
				 $row['type']="docharge";
				 
				 if($row['type']=="docharge")
				  $row['type']="renewal";
				 
				 $noncustomer_subscriptions[] = $row;
			}
			
			return $noncustomer_subscriptions;
			
			
		}
}


/* END-----------------------------END END END END------------------------------END*/

}


?>