<?php
include (__DIR__).'/dbMethods.php';

class AppMethods 
{
	
/* 2:------------------------------method start here signIn 2------------------------------*/
static function signIn($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::signIn($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result == 'not_valid_credential_email') 
				{
					$response["status"] = 401 ;
					$response["message"] = 'Email is incorrect.';
					header("HTTP/1.1 401");
				}
		   else if ($result == 'not_valid_credential_pass') 
				{
					$response["status"] = 401 ;
					$response["message"] = 'Password is incorrect.';
					header("HTTP/1.1 401");
				}
			else if ($result == 'blocked') 
				{
					$response["status"] = 403 ;
					 $response["message"] = 'Following user has been blocked by UP.';
					 header("HTTP/1.1 403");
				}	
			else if ($result !='')
				{
					$response["message"] = 'Admin authenticated Successfully.';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		  }
			return json_encode($response);
}	

 /* 2:------------------------------method start here checkEmail 2------------------------------*/
static function checkEmail($params)
	{
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::checkEmail($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result == "emailexist")
			{
				$response["status"] = 403;
				$response["message"] = 'This email is already registered to an account.';
				 header("HTTP/1.1 403");
			}
			else if ($result !='')
			{
				$response["status"] = 200;
				$response["message"] = 'Data';
				$response["data"] = 'Email Available';
				header("HTTP/1.1 200");
			}
			else
			return json_encode($response);
		}
		return json_encode($response);	 
	}
/* 2:------------------------------method start here checkPhone 2------------------------------*/
static function checkPhone($params)
	{
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::checkPhone($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
				if ($result == "phoneexist")
				{
					$response["status"] = 403;
					$response["message"] = 'This phone number is already registered to an account.';
					header("HTTP/1.1 403");
				}
				else if ($result !='')
				{
					$response["status"] = 200;
					$response["message"] = 'Data';
					$response["data"] = 'Phone Available';
				    header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);	 
	}

/* 3:------------------------------method start here forgotPassword 1------------------------------*/
static function forgotPassword($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::forgotPassword($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result == 'emailnotexist') 
				{
						$response["status"] = 401 ;
						$response["message"] = 'It is not a registered email.';
						header("HTTP/1.1 401");
				}
			else if($result=='blocked')
					{
						$response["status"] = 403 ;
						$response["message"] = 'Following user has been blocked by UP.';
						header("HTTP/1.1 403");
					}
			else if ($result !='')
				{
					$response["message"] = 'Check your email.';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		  }
			return json_encode($response);
	 }
	 
/* 3:------------------------------method start here changePassword 1------------------------------*/
static function changePassword($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::changePassword($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result == 'tokeninvalid') 
				{
					$response["status"] = 401 ;
					$response["message"] = 'This reset password link has been expired. Please use the link from latest reset password email that have been sent to you.';
					header("HTTP/1.1 401");
				}
			else if ($result !='')
				{
					$response["message"] = 'changed.';
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		  }
			return json_encode($response);
	 }	 
/* 21:------------------------------method start here getProfile 21------------------------------*/
static function getProfile($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getProfile($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
			return json_encode($response);
}

/* 21:------------------------------method start here addAdmin 21------------------------------*/
static function addAdmin($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::addAdmin($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if($result == "emailexist")
				{
					$response["status"] = 409;
					$response["message"] = 'This email is already registered to an account.';
					header("HTTP/2.0 409");
				}
			else if($result == "phoneexist")
				{
					$response["status"] = 409;
					$response["message"] = 'This phone is already registered to an account.';
					header("HTTP/2.0 409");
				}
			else if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 201;
					header("HTTP/1.1 201");
				}
				else
				return json_encode($response);
		}
	   return json_encode($response);	
 }
 /* 21:------------------------------method start here updateAdmin 21------------------------------*/
static function updateAdmin($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::updateAdmin($params);
		$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if($result == "emailexist")
				{
					$response["status"] = 409;
					$response["message"] = 'This email is already registered to an account.';
					header("HTTP/2.0 409");
				}
			else if($result == "phoneexist")
				{
					$response["status"] = 409;
					$response["message"] = 'This phone is already registered to an account.';
					header("HTTP/2.0 409");
				}
		      else if ($result == "same")
				{
					$response["status"] = 403;
					$response["message"] = 'Old and new PIN are same.';
					header("HTTP/1.1 403");
				}
				
				else if ($result == "wrong")
				{
					$response["status"] = 403;
					$response["message"] = 'Old PIN is wrong.';
					header("HTTP/1.1 403");
				}		
			else if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 201");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 /* 21:------------------------------method start here deleteAdmin 21------------------------------*/
static function deleteAdmin($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::deleteAdmin($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 201");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
/* 21:------------------------------method start here getAdmins 21------------------------------*/
static function getAdmins($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getAdmins($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}

/* 21:------------------------------method start here addCategory 21------------------------------*/
static function addCategory($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::addCategory($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					if($result == "categoryexist")
					{
						$response["status"] = 409;
						$response["message"] = 'This category is already exist.';
						header("HTTP/2.0 409");
					}
					else
					{
						$response["message"] = 'Data';
						$response["data"] = $result;
						$response["status"] = 201;
						header("HTTP/1.1 201");
					}
				}
				else
				return json_encode($response);
		}
	   return json_encode($response);	
 }
 /* 21:------------------------------method start here updateCategory 21------------------------------*/
static function updateCategory($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::updateCategory($params);
		$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
				   if($result == "categoryexist")
					{
						$response["status"] = 409;
						$response["message"] = 'This category is already exist.';
						header("HTTP/2.0 409");
					}
					else
					{
						$response["message"] = 'Data';
						$response["data"] = $result;
						$response["status"] = 200;
						header("HTTP/1.1 200");
					}
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 /* 21:------------------------------method start here deleteCategory 21------------------------------*/
static function deleteCategory($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::deleteCategory($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 201");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
/* 21:------------------------------method start here getCategories 21------------------------------*/
static function getCategories($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getCategories($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}

 /* 1:------------------------------method start here addMerchant 1------------------------------*/
static function addMerchant($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::addMerchant($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			    if ($result !="") 
				{
					if ($result == "emailexist")
					{
						$response["status"] = 409;
						$response["message"] = 'This Email number is already registered to an account.';
						header("HTTP/1.1 409");
					}
					else
					{ 
						$response["message"] = 'Data';
						$response["data"] = $result;
						$response["status"] = 201;
						header("HTTP/1.1 201");
					}
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
 /* 1:------------------------------method start here addMerchants 1------------------------------*/
static function addMerchants($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::addMerchants($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 201;
					header("HTTP/1.1 201");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 } 
 /* 1:------------------------------method start here updateMerchant 1------------------------------*/
static function updateMerchant($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::updateMerchant($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
				   if ($result == "emailexist")
					{
						$response["status"] = 409;
						$response["message"] = 'This Email number is already registered to an account.';
						header("HTTP/1.1 409");
					}
					else
					{ 
						$response["message"] = 'Data';
						$response["data"] = $result;
						$response["status"] = 200;
						header("HTTP/1.1 200");
					}
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
/* 1:------------------------------method start here deleteMerchant 1------------------------------*/
static function deleteMerchant($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::deleteMerchant($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
/* 1:------------------------------method start here ADMerchant 1------------------------------*/
static function ADMerchant($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::ADMerchant($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 } 
 /* 1:------------------------------method start here getMerchants 1------------------------------*/
static function getMerchants($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getMerchants($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
 /* 1:------------------------------method start here getAllMerchants 1------------------------------*/
static function getAllMerchants($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getAllMerchants($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 } 

/* 1:------------------------------method start here addOutlet 1------------------------------*/
static function addOutlet($params)
 {
	 
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::addOutlet($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					if ($result == "outletexist")
					{
						$response["status"] = 409;
						$response["message"] = 'This outlet is already registered.';
						header("HTTP/1.1 409");
					}
					else
					{ 
						$response["message"] = 'Data';
						$response["data"] = $result;
						$response["status"] = 201;
						header("HTTP/1.1 201");
					}
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
/* 1:------------------------------method start here addOutlets 1------------------------------*/
static function addOutlets($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::addOutlets($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					if ($result == "outletexist")
					{
						$response["status"] = 409;
						$response["message"] = 'This outlet is already registered.';
						header("HTTP/1.1 409");
					}
					else
					{ 
						$response["message"] = 'Data';
						$response["data"] = $result;
						$response["status"] = 201;
						header("HTTP/1.1 201");
					}
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 } 
/* 1:------------------------------method start here updateOutlet 1------------------------------*/
static function updateOutlet($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::updateOutlet($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
				   if ($result == "outletexist")
					{
						$response["status"] = 409;
						$response["message"] = 'This outlet is already registered.';
						header("HTTP/1.1 409");
					}
					else
					{ 
						$response["message"] = 'Data';
						$response["data"] = $result;
						$response["status"] = 200;
						header("HTTP/1.1 200");
					}
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
/* 1:------------------------------method start here deleteOutlet 1------------------------------*/
static function deleteOutlet($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::deleteOutlet($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 } 
 
  /* 1:------------------------------method start here ADOutlet 1------------------------------*/
static function ADOutlet($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::ADOutlet($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 } 
/* 21:------------------------------method start here getOutlets 21------------------------------*/
static function getOutlets($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getOutlets($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}

/* 1:------------------------------method start here addOffer 1------------------------------*/
static function addOffer($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::addOffer($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
				   if ($result == "offertexist")
					{
						$response["status"] = 409;
						$response["message"] = 'This offer is already registered.';
						header("HTTP/1.1 409");
					}
					else
					{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 201;
					header("HTTP/1.1 201");
					}
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
 
/* 1:------------------------------method start here addOffers 1------------------------------*/
static function addOffers($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::addOffers($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
				   if ($result['msg'] == "exists")
					{
						unset($result['msg']);
						$response["status"] = 409;
						$response["message"] = 'Following entities are exist';
						$response["data"] = $result;
						header("HTTP/1.1 409");
					}
					else
					{ 
						$response["message"] = 'Data';
						$response["data"] = $result;
						$response["status"] = 201;
						header("HTTP/1.1 201");
					}
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 } 
 /* 1:------------------------------method start here updateOffer 1------------------------------*/
static function updateOffer($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::updateOffer($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
				  if ($result == "offertexist")
					{
						$response["status"] = 409;
						$response["message"] = 'This offer is already registered.';
						header("HTTP/1.1 409");
					}
					else
					{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
					}
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
 
 /* 1:------------------------------method start here updateMultipleOffer 1------------------------------*/
static function updateMultipleOffer($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::updateMultipleOffer($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 /* 1:------------------------------method start here deleteOffer 1------------------------------*/
static function deleteOffer($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::deleteOffer($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
 /* 1:------------------------------method start here ADOffer 1------------------------------*/
static function ADOffer($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::ADOffer($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
/* 21:------------------------------method start here getOffers 21------------------------------*/
static function getOffers($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getOffers($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}

/* 21:------------------------------method start here getAllOutlets 21------------------------------*/
static function getAllOutlets($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getAllOutlets($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}


/* 21:------------------------------method start here getOfferDetail 21------------------------------*/
static function getOfferDetail($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getOfferDetail($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}
/* 1:------------------------------method start here addNotification 1------------------------------*/
static function addNotification($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::addNotification($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 201;
					header("HTTP/1.1 201");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
/* 1:------------------------------method start here reSendNotification 1------------------------------*/
static function reSendNotification($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::reSendNotification($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 201;
					header("HTTP/1.1 201");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 } 
  /* 1:------------------------------method start here updateNotification 1------------------------------*/
static function updateNotification($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::updateNotification($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 /* 1:------------------------------method start here deleteNotification 1------------------------------*/
static function deleteNotification($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::deleteNotification($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
/* 21:------------------------------method start here getNotifications 21------------------------------*/
static function getNotifications($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getNotifications($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}
/* 1:------------------------------method start here addAccessCode 1------------------------------*/
static function addAccessCode($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::addAccessCode($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
				if ($result == "codeexist")
				{
					$response["status"] = 409;
					$response["message"] = 'This code is already exist.';
					header("HTTP/2.0 409");
				}
				else
				{
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 201;
					header("HTTP/1.1 201");
				}
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
/* 1:------------------------------method start here updateAccessCode 1------------------------------*/
static function updateAccessCode($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::updateAccessCode($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					if ($result == "codeexist")
					{
						$response["status"] = 409;
						$response["message"] = 'This code is already exist.';
						header("HTTP/2.0 409");
					}
					else
					{
						$response["message"] = 'Data';
						$response["data"] = $result;
						$response["status"] = 200;
						header("HTTP/1.1 200");
					}
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
/* 1:------------------------------method start here deleteAccessCode 1------------------------------*/
static function deleteAccessCode($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
	$result = DbMethods::deleteAccessCode($params);
	$error = strpos($result, 'mysql_Error:-');
	if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 /* 1:------------------------------method start here getAccessCodes 1------------------------------*/
static function getAccessCodes($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getAccessCodes($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
 
 
 /* 1:------------------------------method start here getMultipleAccessCode 1------------------------------*/
static function getMultipleAccessCode($params)
 {
	$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getMultipleAccessCode($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 

 /* 1:------------------------------method start here getUsers 1------------------------------*/
static function getUsers($params)
 {
	   $response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getUsers($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
 
 
 
 /* 1:------------------------------method start here getNonUsers 1------------------------------*/
static function getNonUsers($params)
 {
	   $response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getNonUsers($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
 
/* 1:------------------------------method start here updateUser 1------------------------------*/
static function updateUser($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::updateUser($params);
		
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			 if ($result == "emailexist")
				{
					$response["status"] = 403;
					$response["message"] = 'This email is already registered to an account.';
					header("HTTP/1.1 403");
				}
				elseif ($result == "phoneexist")
				{
					$response["status"] = 403;
					$response["message"] = 'This phone number is already registered to an account.';
					header("HTTP/1.1 403");
				}
				else if ($result == "same")
				{
					$response["status"] = 403;
					$response["message"] = 'Old and new PIN are same.';
					header("HTTP/1.1 403");
				}
				
				else if ($result == "wrong")
				{
					$response["status"] = 403;
					$response["message"] = 'Old PIN is wrong.';
					header("HTTP/1.1 403");
				}
				else if ($result !='')
				{
					$response["status"] = 200;
					$response["message"] = 'Updated';
					$response["data"] = $result;
					header("HTTP/1.1 200");
				}
				else
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}


  /* 1:------------------------------method start here getSubscriptions 1------------------------------*/
static function getSubscriptions($params)
 {
	   $response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getSubscriptions($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
 
 
 /* 1:------------------------------method start here getSubscriptionLogs 1------------------------------*/
static function getSubscriptionLogs($params)
 {
	   $response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getSubscriptionLogs($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
 
 /* 1:------------------------------method start here unsubscribe 1------------------------------*/
static function unsubscribe($params)
 {
	   $response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::unsubscribe($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 
  /* 1:------------------------------method start here getFavouriteOffers 1------------------------------*/
static function getFavouriteOffers($params)
 {
	   $response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getFavouriteOffers($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }

/* 1:------------------------------method start here getOrders 1------------------------------*/
static function getOrders($params)
 {
	   $response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getOrders($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }

  /* 1:------------------------------method start here getOrderReviews 1------------------------------*/
static function getOrderReviews($params)
 {
	   $response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getOrderReviews($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
 }
 

/* 21:------------------------------method start here getVersion 21------------------------------*/
static function getVersion($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getVersion($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}
/* 21:------------------------------method start here updateVersion 21------------------------------*/
static function updateVersion($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::updateVersion($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}



/* 21:------------------------------method start here getDefaults 21------------------------------*/
static function getDefaults($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getDefaults($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}
/* 21:------------------------------method start here addUpdateDefault 21------------------------------*/
static function addUpdateDefault($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::addUpdateDefault($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}


/*  21:------------------------------method start here getCreditcardPackages 21------------------------------*/
static function getCreditcardPackages($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getCreditcardPackages($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Data';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}



/*  21:------------------------------method start here updateCreditcardPackages 21------------------------------*/
static function updateCreditcardPackages($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::updateCreditcardPackages($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			$response["message"] = 'Data';
			$response["status"] = 200;
			header("HTTP/1.1 200");
				
		}
		return json_encode($response);
}


/* 5:------------------------------method start here logs 1------------------------------*/
static function logs($params)
 {
		return  DbMethods::logs($params);
 }

/* 5:------------------------------method start here logout 1------------------------------*/
static function logout($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::logout($params);
		$error = strpos($result, 'mysql_Error:-');
		if($error ===0)
		{
			$response["status"] = 500;
			$response["message"] = "Your device has lost connection due to database error.";
			$response["data"] = $result;
			header("HTTP/1.1 500");
		}
		else
		{
			if ($result !="") 
				{ 
					$response["message"] = 'Logout';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				return json_encode($response);
		}
		return json_encode($response);
}
/* END-----------------------------END END END END-----------------------------END-*/
}

?>