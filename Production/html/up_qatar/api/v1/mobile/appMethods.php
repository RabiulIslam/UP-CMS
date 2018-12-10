<?php
include (__DIR__).'/dbMethods.php';

class AppMethods 
{  
/* 1:------------------------------method start here addUser 1------------------------------*/
static function addUser($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::addUser($params);
		
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
					$response["status"] = 409;
					$response["message"] = 'This phone number is already registered to an account.';
					header("HTTP/2.0 409");
				}
				else if ($result == "emailexist")
				{
					$response["status"] = 409;
					$response["message"] = 'This email is already registered to an account.';
					header("HTTP/2.0 409");
				}
				else if ($result !='')
				{
					$response["status"] = 201;
					$response["message"] = "Welcome to UP!";
					$response["data"] = $result;
					header("HTTP/2.0 201");
				}
				else
				header("HTTP/2.0 404");
		}
		return json_encode($response);
}
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
					$response["message"] = 'PIN is incorrect.';
					header("HTTP/1.1 401");
				}
			else if ($result !='')
				{
					$response["message"] = 'User authenticated Successfully.';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				header("HTTP/1.1 404");
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
			header("HTTP/1.1 404");
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
				header("HTTP/1.1 404");
		}
		return json_encode($response);	 
	}

/* 2:------------------------------method start here updateToken 2------------------------------*/
static function updatePermission($params)
	{
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::updatePermission($params);
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
			$response["status"] = 200;
			$response["message"] = 'Data';
			header("HTTP/1.1 200");
		}
		return json_encode($response);	 
	}
/* 2:------------------------------method start here getAuthorization 2------------------------------*/
static function getAuthorization($params)
	{
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getAuthorization($params);
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
			$response["status"] = 200;
			$response["message"] = 'Data';
			$response["data"] = $result ;
			header("HTTP/1.1 200");
				
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
			else if ($result !='')
				{
					$response["message"] = 'Check your email.';
					$response["data"] = $result;
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				header("HTTP/1.1 404");
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
					$response["message"] = 'It is not a valid password_reset_token.';
					header("HTTP/1.1 401");
				}
			else if ($result !='')
				{
					$response["message"] = 'changed.';
					$response["status"] = 200;
					header("HTTP/1.1 200");
				}
				else
				header("HTTP/1.1 404");
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}

/* 1:------------------------------method start here updateProfile 1------------------------------*/
static function updateProfile($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::updateProfile($params);
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


/* 21:------------------------------method start here homeApi 21------------------------------*/
static function homeApi($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::homeApi($params);
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
				header("HTTP/1.1 404");
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
				header("HTTP/1.1 404");
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
				header("HTTP/1.1 404");
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}
/* 21:------------------------------method start here getMyNotifications 21------------------------------*/
static function getMyNotifications($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getMyNotifications($params);
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}


/* 21:------------------------------method start here getMyNotifications 21------------------------------*/
static function getUnreadNotification($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getUnreadNotification($params);
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}


/* 21:------------------------------method start here readNotification 21------------------------------*/
static function readNotification($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::readNotification($params);
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}


/* 21:------------------------------method start here redeemOffer 21------------------------------*/
static function redeemOffer($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::redeemOffer($params);
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
					if ($result == "pininvalid")
					{
						$response["status"] = 403;
						$response["message"] ='Merchant Pin is invalid.';
						header("HTTP/1.1 403");
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}


/* 21:------------------------------method start here getMyPurchaseHistory 21------------------------------*/
static function getMyPurchaseHistory($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getMyPurchaseHistory($params);
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}


/* 21:------------------------------method start here getMyOrdersWithoutReview 21------------------------------*/
static function getMyOrdersWithoutReview($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getMyOrdersWithoutReview($params);
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}
/* 21:------------------------------method start here addReview 21------------------------------*/
static function addReview($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::addReview($params);
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}
/* 21:------------------------------method start here deleteReview 21------------------------------*/
static function deleteReview($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::deleteReview($params);
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}
/* 21:------------------------------method start here addFavouriteOffer 21------------------------------*/
static function addFavouriteOffer($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::addFavouriteOffer($params);
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}


/* 21:------------------------------method start here deleteMyFavouriteOffer 21------------------------------*/
static function deleteMyFavouriteOffer($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::deleteMyFavouriteOffer($params);
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}
/* 21:------------------------------method start here getMyFavouriteOffers 21------------------------------*/
static function getMyFavouriteOffers($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::getMyFavouriteOffers($params);
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}


/* 21:------------------------------method start here usePromoCode 21------------------------------*/
static function usePromoCode($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::usePromoCode($params);
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
					if ($result == "invalidcode")
					{
						$response["status"] = 403;
						$response["message"] ='code is invalid';
						header("HTTP/1.1 403");
					}
					else if ($result == "alreadyusedcode")
					{
						$response["status"] = 403;
						$response["message"] ='Already used the following code';
						header("HTTP/1.1 403");
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
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}


/* 21:------------------------------method start here getMyNotifications 21------------------------------*/
static function useCreditCard($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::useCreditCard($params);
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
					if ($result == 'added') 
					{   
					    $response["message"] = 'Data';
						$response["data"] ='subscribed';
						$response["status"] = 200;
						header("HTTP/1.1 200");
					}
					else
					{
						$response["message"] =  $result;
						$response["status"] = 403;
						header("HTTP/1.1 403");
					}
				}
				else
				header("HTTP/1.1 404");
		}
			return json_encode($response);
}
/* 21:------------------------------method start here contactUs 21------------------------------*/
static function contactUs($params)
 {
		$response = array("status" => NULL, "message" => NULL, "data" => NULL);
		$result = DbMethods::contactUs($params);
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
			//$response["data"] ='subscribed';
			$response["status"] = 200;
			header("HTTP/1.1 200");
		}
			return json_encode($response);
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
				header("HTTP/1.1 404");
		}
		return json_encode($response);
}
/* END-----------------------------END END END END-----------------------------END-*/
}

?>