<?php
error_reporting(0);
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

header('content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');  
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With,AccessToken');


include_once 'appMethods.php';
include_once 'outh.php';

Page::Load();
class Page {
  static function Load() 
  {
	try 
	{
		$pattern = '/[^0-9]/'; 	
		$patternemail = "/([\w\-]+\@[\w\-]+\.[\w\-]+)/";
		$regdecimal ='/^[0-9]+(\.[0-9]{1,2})?$/'; 
		$reglat ='/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/'; 
		$reglong ='/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/'; 
		$bothmethods=outh::methodParamscheck();
		extract($bothmethods);
		$response = array("status" => 400, "message" => NULL, "data" => NULL);	
		switch (array($methodName, $method)) 
		{
			case array("addUser", "POST"):
			  {
				$errors=array();  
				$passwordsize=4;
				$password = preg_match($pattern, $params['password']);
				if( $params['name'] == '' || (strlen($params['name']) >  50))
					{
						$errors['Name']= "name Required";
						$errors['type']= "alphanumeric";
						$errors['formate_size']= "Max size 50 characters";
						throw new Exception(implode(" | ",$errors),400);
					}
				if(($params['gender'] == '' || ($params['gender'] !='male' && $params['gender'] !='female' )))
					{
						$errors['Name']= "gender";
						$errors['type']= "Enum";
						$errors['formate_size']= "male or female";
						throw new Exception(implode(" | ",$errors),400);
					}	
				if( $params['DOB'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2})$/",$params['DOB'])))
					{
						$errors['Name']= "DOB Required";
						$errors['type']= "alphanumeric";
						$errors['formate_size']= "Must be date formate etc 2018-01-12";
						throw new Exception(implode(" | ",$errors),400);
					}
				if( $params['network'] == '' || ($params['network'] !="ooredoo" && $params['network'] !="vodafone" ))
					{
						$errors['Name']= "network";
						$errors['type']= "Enum";
						$errors['formate_size']= "ooredoo, vodafone";
						throw new Exception(implode(" | ",$errors),400);
					}			
				if($params['email'] =='' || !preg_match($patternemail ,$params['email']) || (strlen($params['email']) >  50)) 
					{
						$errors['Name']= "email Required";
						$errors['type']= "alphanumeric";
						$errors['formate_size']= "Max size 50 characters and must be valid email address";
						throw new Exception(implode(" | ",$errors),400);
					}	
				if($params['password'] =='' || (strlen($params['password']) !=  $passwordsize) || ($password > 0)) 
					{
						$errors['Name']= "password Required";
						$errors['type']= "int";
						$errors['formate_size']= "Max size 4 characters";
						throw new Exception(implode(" | ",$errors),400);
					} 
				if($params['phone'] !='') 
					{
						if(strlen($params['phone']) >=20 ||  (!(is_numeric($params['phone']))))
						{
							$errors['Name']= "phone Required";
							$errors['type']= "int";
							$errors['formate_size']= "Must be 20 characters";
							throw new Exception(implode(" | ",$errors),400);
						}
					}
				if(($params['deviceType'] == '' || ($params['deviceType'] !='android' && $params['deviceType'] !='ios'  )))
				{
					$errors['Name']= "deviceType";
					$errors['type']= "Enum";
					$errors['formate_size']= "android,ios";
					throw new Exception(implode(" | ",$errors),400);
				}	
				
				echo  AppMethods::addUser($params);
				break;	
			}
			
/* 1:------------------------------method start here signIn------------------------------*/
			case array("signIn", "POST"):
			  {
				$errors=array(); 
				$passwordsize=4;
				$password = preg_match($pattern, $params['password']);
				if($params['email'] =='' || !preg_match($patternemail ,$params['email']) || (strlen($params['email']) >  50)) 
					{
						$errors['Name']= "email Required";
						$errors['type']= "alphanumeric";
						$errors['formate_size']= "Max size 50 characters and must be valid email address";
						throw new Exception(implode(" | ",$errors),400);
					}
				if($params['password'] =='' || (strlen($params['password']) !=  $passwordsize) || ($password > 0)) 
					{
						$errors['Name']= "password Required";
						$errors['type']= "int";
						$errors['formate_size']= "Max size 4 characters";
						throw new Exception(implode(" | ",$errors),400);
					} 
				if(($params['deviceType'] == '' || ($params['deviceType'] !='android' && $params['deviceType'] !='ios'  )))
				{
					$errors['Name']= "deviceType";
					$errors['type']= "Enum";
					$errors['formate_size']= "android,ios";
					throw new Exception(implode(" | ",$errors),400);
				}		
				
				echo AppMethods::signIn($params);
				break;	
			}			
/* 1:------------------------------method start here checkEmail------------------------------*/
			case array("checkEmail", "POST"):
			  {
				$errors=array();  
				if($params['email'] =='' || !preg_match($patternemail ,$params['email']) || (strlen($params['email']) >  50)) 
					{
						$errors['Name']= "email Required";
						$errors['type']= "alphanumeric";
						$errors['formate_size']= "Max size 50 characters and must be valid email address";
						throw new Exception(implode(" | ",$errors),400);
					}	
				echo  AppMethods::checkEmail($params);
				break;	
			}
/* 1:------------------------------method start here checkPhone------------------------------*/
			case array("checkPhone", "POST"):
			  {
				 $errors=array();  
				if($params['phone'] =='' || strlen($params['phone']) >=20 ||  (!(is_numeric($params['phone']))))
					{
						$errors['Name']= "phone Required";
						$errors['type']= "int";
						$errors['formate_size']= "Must be 20 characters";
						throw new Exception(implode(" | ",$errors),400);
					}
				echo  AppMethods::checkPhone($params);
				break;	
			}
			
/* 1:------------------------------method start here updateToken------------------------------*/
			case array("updatePermission", "POST"):
			  {
				$errors=array();  
				if(($params['permission'] == '' || ($params['permission'] !='pushPermission' && $params['permission'] !='locationPermission'  )))
				{
					$errors['Name']= "permission";
					$errors['type']= "Enum";
					$errors['formate_size']= "pushPermission,locationPermission";
					throw new Exception(implode(" | ",$errors),400);
				}
				if(($params['permission'] == 'pushPermission' ))
				{
				  if(($params['pushPermission'] == '' || ($params['pushPermission'] !='0' && $params['pushPermission'] !='1'  )))
					{
						$errors['Name']= "pushPermission";
						$errors['type']= "Enum";
						$errors['formate_size']= "0,1";
						throw new Exception(implode(" | ",$errors),400);
					}
					if($params['pushPermission'] == '1' )
					{
						if( $params['token'] == '' )
							{
								$errors['Name']= "token Required";
								$errors['type']= "alphanumeric";
								$errors['formate_size']= "Max size 50 characters";
								throw new Exception(implode(" | ",$errors),400);
							}
					}
				}
				
				else if(($params['permission'] == 'locationPermission' ))
				{
				  if(($params['locationPermission'] == '' || ($params['locationPermission'] !='0' && $params['locationPermission'] !='1'  )))
					{
						$errors['Name']= "locationPermission";
						$errors['type']= "Enum";
						$errors['formate_size']= "0,1";
						throw new Exception(implode(" | ",$errors),400);
					}
				}
				echo  AppMethods::updatePermission($params);
				break;	
			}			
			
/* 1:------------------------------method start here getAuthorization------------------------------*/
			case array("getAuthorization", "POST"):
			  {
				$errors=array();  
				$user_id = preg_match($pattern, $params['user_id']);
				if ($params['user_id'] == '' || ($user_id > 0 || $params['user_id'] ==0)   ) 
				{
					$errors['Name']= "user_id Required";
					$errors['type']= "int";
					$errors['formate_size']= "Max size 11 characters";
					throw new Exception(implode(" | ",$errors),400);
				}
				if(($params['deviceType'] == '' || ($params['deviceType'] !='android' && $params['deviceType'] !='ios'  )))
				{
					$errors['Name']= "deviceType";
					$errors['type']= "Enum";
					$errors['formate_size']= "android,ios";
					throw new Exception(implode(" | ",$errors),400);
				}		
				
				echo  AppMethods::getAuthorization($params);
				break;	
			}							
/* 1:------------------------------method start here forgotPassword------------------------------*/
			case array("forgotPassword", "POST"):
			  {
				 $errors=array();  
				if($params['email'] =='' || !preg_match($patternemail ,$params['email']) || (strlen($params['email']) >  50)) 
					{
						$errors['Name']= "email Required";
						$errors['type']= "alphanumeric";
						$errors['formate_size']= "Max size 50 characters and must be valid email address";
						throw new Exception(implode(" | ",$errors),400);
					}		
				echo  AppMethods::forgotPassword($params);
				break;	
			}
			
/* 1:------------------------------method start here changePassword------------------------------*/
			case array("changePassword", "POST"):
			  {
				 $errors=array();  
				$passwordsize=4;
				$password = preg_match($pattern, $params['password']);
				if($params['password'] =='' || (strlen($params['password']) !=  $passwordsize) || ($password > 0)) 
					{
						$errors['Name']= "password Required";
						$errors['type']= "int";
						$errors['formate_size']= "Max size 4 characters";
						throw new Exception(implode(" | ",$errors),400);
					} 	
				if($params['password_reset_token'] =='' ) 
					{
						$errors['Name']= "password_reset_token Required";
						$errors['type']= "int";
						$errors['formate_size']= "alphanumeric";
						throw new Exception(implode(" | ",$errors),400);
					}			
				echo  AppMethods::changePassword($params);
				break;	
			}		
/* 1:------------------------------method start here getProfile------------------------------*/
			case array("getProfile", "GET"):
			  {
				 echo  AppMethods::getProfile($params);
				 break;	
			  }		
/* 1:------------------------------method start here updateProfile------------------------------*/
			case array("updateProfile", "POST"):
			  {
				$errors=array();  
				if($params['email'] !='')
				{
				  if(!preg_match($patternemail ,$params['email']) || (strlen($params['email']) >  50)) 
					{
						$errors['Name']= "email";
						$errors['type']= "alphanumeric";
						$errors['formate_size']= "Max size 50 characters and must be valid email address";
						throw new Exception(implode(" | ",$errors),400);
					}
				}
				if($params['gender'] !='')
				{
				  if((($params['gender'] !='male' && $params['gender'] !='female' )))
					{
						$errors['Name']= "gender";
						$errors['type']= "Enum";
						$errors['formate_size']= "male or female";
						throw new Exception(implode(" | ",$errors),400);
					}	
				}
				if($params['network'] !='')
				{
				   if( $params['network'] == '' || ($params['network'] !="ooredoo" && $params['network'] !="vodafone" ))
					{
						$errors['Name']= "network";
						$errors['type']= "Enum";
						$errors['formate_size']= "ooredoo, vodafone";
						throw new Exception(implode(" | ",$errors),400);
					}
				}
				if($params['phone'] !='') 
					{
						if(strlen($params['phone']) >=20 ||  (!(is_numeric($params['phone']))))
						{
							$errors['Name']= "phone Required";
							$errors['type']= "int";
							$errors['formate_size']= "Must be 20 characters";
							throw new Exception(implode(" | ",$errors),400);
						}
					}
				if($params['DOB'] !='') 
					{	
					 if((!preg_match("/^(\d{4})-(\d{2})-(\d{2})$/",$params['DOB'])))
						{
							$errors['Name']= "DOB Required";
							$errors['type']= "alphanumeric";
							$errors['formate_size']= "Must be date formate etc 2018-01-12";
							throw new Exception(implode(" | ",$errors),400);
						}
					}
				if($params['password'] !='') 
					{
						$password = preg_match($pattern, $params['password']);
						$old_password  = preg_match($pattern, $params['old_password']);
						if((strlen($params['password']) >  4) || ($password > 0)) 
						{
							$errors['Name']= "password Required";
							$errors['type']= "int";
							$errors['formate_size']= "Max size 4 characters";
							throw new Exception(implode(" | ",$errors),400);
						} 
						if($params['old_password'] =='' || (strlen($params['old_password']) >  4) || ($old_password  > 0)) 
						{
							$errors['Name']= "old_password  Required";
							$errors['type']= "int";
							$errors['formate_size']= "Max size 4 characters";
							throw new Exception(implode(" | ",$errors),400);
						} 
					}
				echo  AppMethods::updateProfile($params);
				break;	
			}

/* 1:------------------------------method start here homeApi------------------------------*/
			case array("homeApi", "GET"):
			  {
				 echo  AppMethods::homeApi($params);
				 break;	
			  }	
/* 1:------------------------------method start here getOutlets------------------------------*/
			case array("getOutlets", "GET"):
			  {
				$errors=array();  
				if($params['category_id'] !='') 
					{
						$category_id = preg_match($pattern, $params['category_id']);
						if ($params['category_id'] == '' || ($category_id > 0 || $params['category_id'] ==0)   ) 
						{
							$errors['Name']= "category_id Required";
							$errors['type']= "int";
							$errors['formate_size']= "Max size 11 characters";
							throw new Exception(implode(" | ",$errors),400);
						}
					}
				if($params['outlet_id'] !='') 
					{
						$outlet_id = preg_match($pattern, $params['outlet_id']);
						if ($params['outlet_id'] == '' || ($outlet_id > 0 || $params['outlet_id'] ==0)   ) 
						{
							$errors['Name']= "outlet_id Required";
							$errors['type']= "int";
							$errors['formate_size']= "Max size 11 characters";
							throw new Exception(implode(" | ",$errors),400);
						}
					}	
				if($params['sortby'] !='') 
					{
						if( $params['sortby'] == '' || ($params['sortby'] !="alphabetically" && $params['sortby'] !="location" ))
						{
							$errors['Name']= "sortby";
							$errors['type']= "Enum";
							$errors['formate_size']= "alphabetically, location";
							throw new Exception(implode(" | ",$errors),400);
						}
						if($params['sortby'] =='location') 
						{
							$params['latitude']= floatval($params['latitude']);
							$params['longitude']= floatval($params['longitude']);
							if(!is_double ($params['latitude']) || $params['latitude'] ==0 )
							{
								$errors['Name']= "latitude Required";
								$errors['type']= "double";
								$errors['formate_size']= "Max size 50 characters";
								throw new Exception(implode(" | ",$errors),400);
							}
							if(!is_double ($params['longitude']) || $params['longitude'] ==0 )
							{
								$errors['Name']= "longitude Required";
								$errors['type']= "double";
								$errors['formate_size']= "Max size 50 characters";
								throw new Exception(implode(" | ",$errors),400);
							}
						}
					}
					
				if($params['type'] !='') 
					{
						if( $params['type'] == '' || ($params['type'] !="0" && $params['type'] !="1" && $params['type'] !="2"))
						{
							$errors['Name']= "type";
							$errors['type']= "Enum";
							$errors['formate_size']= "0,1,2";
							throw new Exception(implode(" | ",$errors),400);
						}
						
					}
				 echo  AppMethods::getOutlets($params);
				 break;	
			  }	
/* 1:------------------------------method start here getOffers------------------------------*/
			case array("getOffers", "GET"):
			  {
				 echo  AppMethods::getOffers($params);
				 break;	
			  }
/* 1:------------------------------method start here getOffers------------------------------*/
			case array("getOfferDetail", "GET"):
			  {
				$errors=array();    
				$offer_id = preg_match($pattern, $params['offer_id']);
				if ($params['offer_id'] == '' || ($offer_id > 0 || $params['offer_id'] ==0)   ) 
				{
					$errors['Name']= "offer_id Required";
					$errors['type']= "int";
					$errors['formate_size']= "Max size 11 characters";
					throw new Exception(implode(" | ",$errors),400);
				} 
				echo  AppMethods::getOfferDetail($params);
				break;	
			  }
			  
/* 1:------------------------------method start here getMyNotifications------------------------------*/
			case array("getMyNotifications", "GET"):
			  {
				 echo  AppMethods::getMyNotifications($params);
				 break;	
			  }
/* 1:------------------------------method start here getUnreadNotification------------------------------*/
			case array("getUnreadNotification", "GET"):
			  {
				 echo  AppMethods::getUnreadNotification($params);
				 break;	
			  }
			  
/* 1:------------------------------method start here readNotification------------------------------*/
			case array("readNotification", "POST"):
			  {
					$errors=array();    
					$notification_id = preg_match($pattern, $params['notification_id']);
					if ($params['notification_id'] == '' || ($notification_id > 0 || $params['notification_id'] ==0)   ) 
					{
						$errors['Name']= "notification_id Required";
						$errors['type']= "int";
						$errors['formate_size']= "Max size 11 characters";
						throw new Exception(implode(" | ",$errors),400);
					} 
					 echo  AppMethods::readNotification($params);
					 break;	
			  }
/* 1:------------------------------method start here redeemOffer------------------------------*/
			case array("redeemOffer", "POST"):
			  {
					$errors=array();    
					$offer_id = preg_match($pattern, $params['offer_id']);
					$pin = preg_match($pattern, $params['pin']);
					if ($params['offer_id'] == '' || ($offer_id > 0 || $params['offer_id'] ==0)   ) 
					{
						$errors['Name']= "offer_id Required";
						$errors['type']= "int";
						$errors['formate_size']= "Max size 11 characters";
						throw new Exception(implode(" | ",$errors),400);
					} 
					if($params['pin'] =='' || (strlen($params['pin']) !=  4) || ($pin > 0)) 
						{
							$errors['Name']= "pin Required";
							$errors['type']= "int";
							$errors['formate_size']= "Must Be 4 characters";
							throw new Exception(implode(" | ",$errors),400);
						} 
					 echo  AppMethods::redeemOffer($params);
					 break;	
			  }
/* 1:------------------------------method start here getMyPurchaseHistory------------------------------*/
			case array("getMyPurchaseHistory", "GET"):
			  {
				 echo  AppMethods::getMyPurchaseHistory($params);
				 break;	
			  }
/* 1:------------------------------method start here getMyOrdersWithoutReview------------------------------*/
			case array("getMyOrdersWithoutReview", "GET"):
			  {
				 echo  AppMethods::getMyOrdersWithoutReview($params);
				 break;	
			  }
/* 1:------------------------------method start here addReview------------------------------*/
			case array("addReview", "POST"):
			 {
				$errors=array();    
				$order_id = preg_match($pattern, $params['order_id']);
				if ($params['order_id'] == '' || ($order_id > 0 || $params['order_id'] ==0)   ) 
				{
					$errors['Name']= "order_id Required";
					$errors['type']= "int";
					$errors['formate_size']= "Max size 11 characters";
					throw new Exception(implode(" | ",$errors),400);
				} 
				if(($params['review'] == '' || ($params['review'] !='OK' && $params['review'] !='Love it'  && $params['review'] !='Not Happy' )))
				{
					$errors['Name']= "review";
					$errors['type']= "Enum";
					$errors['formate_size']= "OK or Love it";
					throw new Exception(implode(" | ",$errors),400);
				}
				echo  AppMethods::addReview($params);
				break;	
			  }
/* 1:------------------------------method start here deleteReview------------------------------*/
		case array("deleteReview", "POST"):
		  {
				 $errors=array();    
				 $review_id = preg_match($pattern, $params['review_id']);
				  if ($params['review_id'] == '' || ($review_id > 0 || $params['review_id'] ==0)   ) 
				  {
					  $errors['Name']= "review_id Required";
					  $errors['type']= "int";
					  $errors['formate_size']= "Max size 11 characters";
					  throw new Exception(implode(" | ",$errors),400);
				  } 
				echo  AppMethods::deleteReview($params);
				break;	
		}
/* 1:------------------------------method start here addFavouriteOffer------------------------------*/
		case array("addFavouriteOffer", "POST"):
		  {
				$errors=array();    
				$offer_id = preg_match($pattern, $params['offer_id']);
				if ($params['offer_id'] == '' || ($offer_id > 0 || $params['offer_id'] ==0)   ) 
				{
					$errors['Name']= "offer_id Required";
					$errors['type']= "int";
					$errors['formate_size']= "Max size 11 characters";
					throw new Exception(implode(" | ",$errors),400);
				}    
				echo  AppMethods::addFavouriteOffer($params);
				break;	
		 }
/* 1:------------------------------method start here deleteMyFavouriteOffer------------------------------*/
		case array("deleteMyFavouriteOffer", "POST"):
		  {
				 $errors=array();    
				 $offer_id = preg_match($pattern, $params['offer_id']);
				  if ($params['offer_id'] == '' || ($offer_id > 0 || $params['offer_id'] ==0)   ) 
				  {
					  $errors['Name']= "offer_id Required";
					  $errors['type']= "int";
					  $errors['formate_size']= "Max size 11 characters";
					  throw new Exception(implode(" | ",$errors),400);
				  } 
				echo  AppMethods::deleteMyFavouriteOffer($params);
				break;	
		 }		  			  		  
/* 1:------------------------------method start here getMyFavouriteOffers------------------------------*/
		case array("getMyFavouriteOffers", "GET"):
		  {
			 $errors=array();  
			 if($params['sortby'] !='')
				{
				  if(( ($params['sortby'] !='location' && $params['sortby'] !='alphabetically' )))
					{
						$errors['Name']= "gender";
						$errors['type']= "Enum";
						$errors['formate_size']= "location or alphabetically";
						throw new Exception(implode(" | ",$errors),400);
					}	
				} 
				if($params['sortby'] =='location')
					{
						$params['latitude']= floatval($params['latitude']);
						$params['longitude']= floatval($params['longitude']);
						if(!is_double ($params['latitude']) || $params['latitude'] ==0 )
						  {
							  $errors['Name']= "latitude Required";
							  $errors['type']= "double";
							  $errors['formate_size']= "Max size 50 characters";
							  throw new Exception(implode(" | ",$errors),400);
						  }
					   if(!is_double ($params['longitude']) || $params['longitude'] ==0 )
						  {
							  $errors['Name']= "longitude Required";
							  $errors['type']= "double";
							  $errors['formate_size']= "Max size 50 characters";
							  throw new Exception(implode(" | ",$errors),400);
						  }
					}      
				echo  AppMethods::getMyFavouriteOffers($params);
				break;	
		  }
		  
/* 1:------------------------------method start here usePromoCode------------------------------*/
		case array("usePromoCode", "POST"):
		  {
			   $errors=array();    
				if( $params['code'] == '' || (strlen($params['code']) >  10))
				{
					$errors['Name']= "code Required";
					$errors['type']= "alphanumeric";
					$errors['formate_size']= "Max size 10 characters";
					throw new Exception(implode(" | ",$errors),400);
				}
				echo  AppMethods::usePromoCode($params);
				break;	
		 }	
		 
/* 1:------------------------------method start here useCreditCard------------------------------*/
		case array("useCreditCard", "POST"):
		  {
				$errors=array();
				$creditcard_package_id = preg_match($pattern, $params['creditcard_package_id']);
				if ($params['creditcard_package_id'] == '' || ($creditcard_package_id > 0 || $params['creditcard_package_id'] ==0)   ) 
				{
					$errors['Name']= "creditcard_package_id Required";
					$errors['type']= "int";
					$errors['formate_size']= "Max size 11 characters";
					throw new Exception(implode(" | ",$errors),400);
				}     
				if( $params['stripeToken'] == '' || (strlen($params['stripeToken']) >  10))
				{
					$errors['Name']= "stripeToken Required";
					$errors['type']= "alphanumeric";
					$errors['formate_size']= "Max size 10 characters";
					throw new Exception(implode(" | ",$errors),400);
				}
				
				echo  AppMethods::useCreditCard($params);
				break;	
		  }
		  
		  
/* 1:------------------------------method start here contactUs------------------------------*/
		case array("contactUs", "POST"):
		  {
				$errors=array();
				 
				if( $params['reason'] == '' || (strlen($params['reason']) >  50))
				{
					$errors['Name']= "reason Required";
					$errors['type']= "alphanumeric";
					$errors['formate_size']= "Max size 50 characters";
					throw new Exception(implode(" | ",$errors),400);
				}
				
				if( $params['message'] == '' || (strlen($params['message']) >  200))
				{
					$errors['Name']= "message Required";
					$errors['type']= "alphanumeric";
					$errors['formate_size']= "Max size 50 characters";
					throw new Exception(implode(" | ",$errors),400);
				}
				
				echo  AppMethods::contactUs($params);
				break;	
		  }			  		 	  
		  			  			  			  			  			  			  		  		
/* 1:------------------------------method start here logout------------------------------*/
		case array("logout", "GET"):
		  {
				echo AppMethods::logout($params);
				break;	
		 }			
	/* 1:------------------------------default default default default default------------------------------*/
		   default : 
		   {
				$errors['Name']= "method Required";
				$errors['type']= "string";
				$errors['formate_size']= 'Invalid Method Name '.$methodName." Or Routes ".$_SERVER['REQUEST_METHOD'];
				throw new Exception(implode(" | ",$errors),405);
		   }
	  }
	}
	catch(Exception $e) 
	{
		$response["status"] = $e->getCode();
		$response["message"] =$e->getMessage();
		echo json_encode($response);
		header("HTTP/1.1 ".$e->getCode()." ".$e->getMessage()."");
	}
  }
  

/* 2:------------------------------method start here uploadimg 1------------------------------*/ 

}		
?>