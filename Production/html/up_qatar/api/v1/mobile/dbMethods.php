<?php
include(__DIR__).'/../../../vendor/autoload.php';
include (__DIR__).'/../subscription/premierUser.php';
class DbMethods {
	/* 1:------------------------------method start here addUser ------------------------------*/
static function addUser($params) 
{
	$con =$params['dbconnection'];
	$eligibility="0";
	$phone="";
	if(DbMethods::checkEmail($params)=='emailexist')return  'emailexist';
	if($params['phone'] !="")
	{
	   if(DbMethods::checkPhone($params)=='phoneexist')return  'phoneexist';
	   $phone=" `phone`='{$params['phone']}' ,";  
	}
	$password=sha1($params['password']);	  
	$query_n0 = "INSERT INTO `users` SET  
	`name`='{$params['name']}',
	`gender`='{$params['gender']}',
	`DOB`='{$params['DOB']}',
	`network`='{$params['network']}',
	`email`='{$params['email']}',
	`password`='{$params['password']}',
	`deviceType`='{$params['deviceType']}',
	`device_info`='{$params['device_info']}',
	`app_id`='{$params['user_app_id']}',
	".$phone."
	`nationality`='{$params['nationality']}'" ;
	$result_no = mysqli_query($con,$query_n0) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	$id=mysqli_insert_id($con);
	$params['user_id']=$id;
	$auth_key=DbMethods::addAuthKey($params);
	if($params['phone'] !="")
	{
		$eligibility=premierUserMethods::eligibilitychecker($params);
		if($eligibility=='ELIGIBLE')
		$eligibility='1';
		else
		$eligibility="0";
		
		$premier_user=DbMethods::isPremierUser($params);
		$params["Text"]="";
		
		
		if($premier_user=='1')
		{
			if($eligibility=="0")
			{
				$premier_user='0';
				DbMethods::deletePremierUser($params);
			}
			else
			{
				$dir = str_replace( 'mobile', 'subscription', $params['apiBasePath'] ) ;
				$params["Text"]=substr(mt_rand(),3,4).substr(mt_rand(),5,2);
				$dir =$dir ."sendMT"; 
				DbMethods:: post_async($dir ,array('phone'=>$params['phone'],'Text'=>$params['Text'],'Authorization'=>$params['Authorization']));
			}
		}
		
	}
	
	return array("id"=>(int)$id,"premier_user"=>$premier_user,"eligibility"=>$eligibility,"verificationCode"=>$params["Text"] ,"Authorization"=>$auth_key) ;
}
/* 1:------------------------------method start here signIn ------------------------------*/
static function signIn($params) 
{
		$con =$params['dbconnection'];	
	    $query = "SELECT 
		u.`id`,
		u.`name`,
		u.`email`,
		u.`phone`,
		u.`password`,
		u.`gender`,
		u.`DOB`,
		u.`network`,
		u.`nationality`
		FROM `users` as u 
		WHERE u.`email`='{$params['email']}' AND `app_id`='{$params['user_app_id']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{	
			   $row = mysqli_fetch_assoc($result);	
			   if(strtolower($row['email'])==strtolower(TRIM($params['email'])) && $row['password']==TRIM($params['password']))
				{
					
						mysqli_query($con,"UPDATE `users` SET `deviceType`='{$params['deviceType']}', `device_info`='{$params['device_info']}'
						WHERE `id`='{$row['id']}'");
						$params['user_id']=$row['id'];
	                    $auth_key=DbMethods::addAuthKey($params);
						$row['Authorization']=$auth_key;
						unset( $row['password']);
						$row['id']=(int)$row['id'];
						return $row;
				}
				else
				return 'not_valid_credential_pass';
			  }
		 else
		 return 'not_valid_credential_email';
}

/* 1:------------------------------method start here checkEmail ---------------------------1*/
static function checkEmail($params) 
{
	$con =$params['dbconnection'];
	$querysub="";
	if($params['user_id'] !='')
	$querysub="  AND 	`id` !='{$params['user_id']}' ";
	
  	$query = "SELECT `email` FROM  `users` WHERE `email` ='{$params['email']}'  ".$querysub."  ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	return 'emailexist';
	else
	return array('email' =>$params['email']);
}
/* 1:------------------------------method start here checkPhone ---------------------------1*/
static function checkPhone($params) 
{
	
	$con =$params['dbconnection'];
	$querysub="";
	if($params['user_id'] !='')
	$querysub="  AND 	`id` !='{$params['user_id']}' ";
	
   $query = "SELECT `phone` FROM  `users` WHERE `phone` ='{$params['phone']}'  ".$querysub."  ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	return 'phoneexist';
	else
	return array('phone' =>$params['phone']);
}

/* 1:------------------------------method start here updatePermission ---------------------------1*/
static function updatePermission($params) 
{
		$con =$params['dbconnection'];
		$push_permission="";
		$locationPermission="";
		if($params['permission'] =='pushPermission')
		$push_permission="`push_permission`='{$params['pushPermission']}'";
		else if($params['permission'] =='locationPermission')
		$push_permission="`location_permission`='{$params['locationPermission']}'";
	
		$query= "UPDATE `users` SET 
		".$push_permission."
		WHERE `id`='{$params['user_id']}'";
		//Umer Shah Code Starts Here
		mysqli_query($con,$query);
		//Umer Shah Code Ends Here
		
		if($params['token']!='')
		{
			$query= "UPDATE `authkeys` SET 
			`token`='{$params['token']}' 
			WHERE `auth_key`='{$params['Authorization']}'";
			mysqli_query($con,$query) ;
		}
		return "updated";
}
/* 1:------------------------------method start here forgotPassword ------------------------------*/
static function forgotPassword($params) 
{
		$con =$params['dbconnection'];
		if($params['email'] !='')
		if(DbMethods::checkEmail($params) !='emailexist') return  'emailnotexist';
		
		$password_reset_token=sha1($params['email'].microtime());
		$query= "UPDATE `users` SET 
		`password_reset_token`='{$password_reset_token}' 
		WHERE `email`='{$params['email']}' AND `app_id`='{$params['user_app_id']}'";
		mysqli_query($con,$query) ;
		
		
		$baseurl= "http://18.185.217.28/up_qatar/cms/#/auth/reset-pin";
		$resetLink= "?token=".$password_reset_token;
		$resetLink= $baseurl.$resetLink."&type=user";
		
		$angertag="  <a href='".$resetLink."' style='background: #b64645 none repeat scroll 0 0; border-radius: 2px; color: #fff; font-size: 14px; font-weight: 400; padding: 4px 28px; display: block; max-width: 160px; font-size: 16px; font-weight: 600; text-decoration: none; margin: 10px auto 8px; padding: 15px 25px;text-align:center; ' target='_blank'>Reset Pin</a>";
		
		
		$params['to']=$params['email'];
		$params['subject']="Pin Recovery";
		$params['body']= "<html>
		<head>
		<title>Pin Recovery</title>
		</head>
		<body>
		<h3>Dear user,</h3>
		<p>Click on Link to reset Pin: <b>".$angertag ." </b></p>
		<p></p>
		<p>Regards, </p>
		<p>UP</p>
		</body>
		</html>";
		//HelpingMethods::sendEmail($params);//send mail
		
		$dir = str_replace( 'api/v1/mobile', 'common/sendEmail.php', $params['apiBasePath'] ) ;
		
		DbMethods:: post_async($dir ,array('to'=>$params['to'],'subject'=>$params['subject']
		,'body'=>$params['body'],'con'=>$params['dbconnection'],"Authorization"=>"UP!and$"));
		return "sended";
}

/* 1:------------------------------method start here changePassword ------------------------------*/
static function changePassword($params) 
{
		$con =$params['dbconnection'];
		$query = "SELECT `id` FROM `users` 
		WHERE `password_reset_token`='{$params['password_reset_token']}' AND `app_id`='{$params['user_app_id']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) == 0) 
		return "tokeninvalid";
		
		
		$query= "UPDATE `users` SET 
		`password`='{$params['password']}',
		`password_reset_token`=''
		WHERE `password_reset_token`='{$params['password_reset_token']}' AND `app_id`='{$params['user_app_id']}'";
		mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		return 'updated';
}
/* 1:------------------------------method start here checkEmail ---------------------------1*/
static function checkPassword($params) 
{
		$con =$params['dbconnection'];
		if($params['old_password']==$params['password'])
		return "same";
		
		$query = "SELECT `id` FROM `users` 
		WHERE `id`='{$params['user_id']}' AND `password`='{$params['old_password']}' ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) == 0) 
		return "wrong";
		else
		return NULL;
}
/* 1:------------------------------method start here getProfile ------------------------------*/
static function getProfile($params) 
{
	    $con =$params['dbconnection'];	
		$query = "SELECT 
		u.`id`,
		u.`name`,
		u.`email`,
		u.`phone`,
		u.`password`,
		u.`gender`,
		u.`DOB`,
		u.`network`,
		u.`nationality`
		FROM `users` as u
		WHERE u.`id`='{$params['user_id']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{
				$row = mysqli_fetch_assoc($result);		
				return $row;
			}
}

/* 1:------------------------------method start here updateProfile ------------------------------*/
static function updateProfile($params) 
{
	$con =$params['dbconnection'];
	if($params['email'] !='')
	if(DbMethods::checkEmail($params)=='emailexist')return  'emailexist';
	if($params['phone'] !='')
	if(DbMethods::checkPhone($params)=='phoneexist')return  'phoneexist';
	$name='';
	$email='';
	$phone='';
	$password='';
	$network='';
	$gender='';
	$DOB='';
	$nationality="";
	$subquery="";
	if($params['name']!=""){   $name=" `name`='{$params['name']}' ,";  }
	if($params['email']!=""){  $email=" `email`='{$params['email']}' ,";}
	if($params['phone']!=""){  $phone=" `phone`='{$params['phone']}' ,";   } 
	if($params['network']!=""){ $network=" `network`='{$params['network']}' ,";} 
	if($params['gender']!=""){ $gender=" `gender`='{$params['gender']}' ,";}
	if($params['DOB']!=""){    $DOB=" `DOB`='{$params['DOB']}' ,";  }
	if($params['nationality']!=""){    $nationality=" `nationality`='{$params['nationality']}' ,";  }
	if($params['password']!=""){    $password=" `password`='{$params['password']}' ,";  }
	
	
	if($params['password'] !='')
	{
		$checkPassword=DbMethods::checkPassword($params);
		if($checkPassword!=NULL)
		return $checkPassword;
	}
	
	 $query= "UPDATE `users` SET 
	".$name."
	".$email."
	".$phone."
	".$password."
	".$network."
	".$gender."
	".$DOB."
	".$nationality."
	`id`='{$params['user_id']}' 
	WHERE `id`='{$params['user_id']}'"	;
	mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	return 'updated';
}

/* 1:------------------------------method start here updateAppVersion ------------------------------*/
static function updateAppVersion($params) 
{
	$con =$params['dbconnection'];
	
	mysqli_query($con,"UPDATE `users` SET  `app_version`='{$params['app_version']}' WHERE `id`='{$params['user_id']}'") ;
	
	return "add";
	
}


/* 1:------------------------------method start here homeApi ------------------------------*/
static function homeApi($params) 
{
		$specailOffers=array();
		$mostLovedOffers=array();
		$categories=array();
		$defaults=array();
		$sub=0;
		$newOffer=0;
		$review=0;
		$unReadNotification=0;
		$version='';
		$subscription=0;
		$subscriptionType="0";
		$premier_user=0;
		$phone='';
		$network='';
		
		if($params['app_version'] !='')
	    DbMethods::updateAppVersion($params);
		
		$sub=DbMethods::checkSubscription($params);
		
		/*$subscription=$sub['subscription'];
		$phone=$sub['phone'];
		$subscriptionType=$sub['network'];
		$premier_user=$sub['premier_user'];
		$status=$sub['status'];*/
		
		
		$review=DbMethods::getPendingReview($params);
		
		$params['isNewOffer']='isNewOffer';
		if(DbMethods::getOffers($params)!=NULL)
		$newOffer=1;
		
		$unReadNotification=DbMethods::getUnreadNotification($params);
		
		$defaults=DbMethods::getDefaults($params);
		
		$params['special']='special';
		$specailOffers=DbMethods::getOffers($params);
		unset($params['special']);
		$params['mostLovedOffers']='mostLovedOffers';
		$mostLovedOffers=DbMethods::getOffers($params); 
		$categories=DbMethods::getCategories($params); 
		return array("subscription"=>$sub,"newOffer"=>$newOffer ,"review"=>$review 
		,"unReadNotification"=>$unReadNotification,"defaults"=>$defaults,
		'specailOffers' => $specailOffers, 'mostLovedOffers' => $mostLovedOffers, 'categories' => $categories,"super_access_pin"=>"1585" );
}


  /* 1:------------------------------method start here checkSubscription 1------------------------------*/
static function checkSubscription($params)
{
	    $con =$params['dbconnection'];
		$subscription='0';
		$premier_user='0';
		$phone="";
		$network="";
		$status="";
		
	    $query = "SELECT 
		s.*,
		NOW() as cuurentdate,
		u.`phone` as user_phone
		FROM `users` as u LEFT OUTER JOIN `subscriptions` as s ON(u.`id`=s.`user_id`)
		WHERE u.`id`='{$params['user_id']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{	
			    $row = mysqli_fetch_assoc($result);	
				if($row['id'] !="")
				{
					if($row['network']=='ooredoo' && $row['status']=='1')
					$row['expiry_datetime'] =date('Y-m-d H:i:s',strtotime('+48 hour',strtotime($row['expiry_datetime'])));
					
					if(strtotime($row['expiry_datetime']) > strtotime($row['cuurentdate']))
					$subscription='1';
					
					$phone=$row['phone'];
					$network=$row['network'];
					$status=$row['status'];
					$premier_user=$row['premier_user'];
				}
				
				if($phone=="" && $row['user_phone'] !="")
				$phone=$row['user_phone'];
				
				return array('subscription'=>$subscription,'phone'=>$phone,'network'=>$network,'premier_user'=>$premier_user,'status'=>$status);
			}
}


 /* 1:------------------------------method start here isPremierUser 1------------------------------*/
static function isPremierUser($params)
{
	$con =$params['dbconnection'];
     $query = "SELECT 
	`id`,
	`created_at`,
	TIMESTAMPDIFF(DAY,`created_at`,NOW()) as timediffer
	FROM `non_registered_users`
	WHERE `premier_user`='1' AND `phone`='{$params['phone']}'  AND `phone` !=''";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0)
	{	
			$row = mysqli_fetch_assoc($result);	
			if($row['timediffer'] > 60)
			{
				 $queryd = "DELETE  FROM  `non_registered_users` WHERE `phone`='{$params['phone']}'";
				mysqli_query($con,$queryd) ;
				return '0';
			}
			else
			return '1';
	}
	else
	return '0';
}


/* 1:------------------------------method start here deletePremierUser 1------------------------------*/
static function deletePremierUser($params)
{
		$con =$params['dbconnection'];
		$queryd = "DELETE  FROM  `non_registered_users` WHERE `premier_user`='1' AND `phone`='{$params['phone']}'  AND `phone` !=''";
		mysqli_query($con,$queryd) ;
		
		return true;
}
  /* 1:------------------------------method start here getPendingReview 1------------------------------*/
static function getPendingReview($params)
{
	    $con =$params['dbconnection'];
		$reviewCount=0;
		if ($review = $con->query("SELECT 
		o.`id`
		FROM `orders` as o 
		LEFT OUTER JOIN `reviews`  r ON(r.`order_id`=o.`id` )
		WHERE o.`user_id`='{$params['user_id']}' AND r.`id` IS NULL")) 
		$reviewCount = $review->num_rows;
		
		return $reviewCount;
} 
/* 1:------------------------------method start here getDefaults 1------------------------------*/
static function getDefaults($params)
{
	    $con =$params['dbconnection'];
		$defaults=array();
		$defaults['home_page']=NULL;
		$defaults['uber']=NULL;
		$version=array();
		$version['version']="0";
		$version['forcefully_updated']="0";
		$defaults['subscription_text_1']=array();
		$defaults['subscription_text_2']=array();
		$subscription_text_1=array();
		$subscription_text_2=array();
		$defaults['creditcardPackages']=array();
		$subtext=array();
		$query = "SELECT 
		`text`,
		`uber`,
		`type`,
		`paragraph`
		FROM `defaults`
		ORDER BY `paragraph` ASC LIMIT 0,20";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{
			while ($row = mysqli_fetch_assoc($result)) 
			{
				if($row['type']=='home-page')
				$defaults['home_page']=$row['text'];
				elseif($row['type']=='uber')
				$defaults['uber']=$row['uber'];
				elseif($row['type']=='subscription')
				{
					if($row['paragraph']==1)
					{
					  $subtext['text']=$row['text'];
					  $subscription_text_2[]=$subtext;
					}
					else
					{
					  $subtext['text']=$row['text'];
					  $subscription_text_1[]=$subtext;
					}
				}
				
			}
		}
		$query = "SELECT 
		`version_ios`,
		`version_android`,
		`forcefully_updated_ios`,
		`forcefully_updated_android`
		FROM `version`
		ORDER BY `id` LIMIT 1";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{	
			    $row = mysqli_fetch_assoc($result);	
				if($params['user_deviceType']=='ios')
				{
					$version['version']=$row['version_ios'];
					$version['forcefully_updated']=$row['forcefully_updated_ios'];
				}
				elseif($params['user_deviceType']=='android')
				{
					$version['version']=$row['version_android'];
					$version['forcefully_updated']=$row['forcefully_updated_android'];
				}
			}
			
			
			if(DbMethods::getCreditcardPackages($params) !="")
			$defaults['creditcardPackages']=DbMethods::getCreditcardPackages($params);
			$defaults['version']=$version;
			$defaults['subscription_text_1']=$subscription_text_1;
			$defaults['subscription_text_2']=$subscription_text_2;
			return $defaults;	
}

/* 21:------------------------------method start here getCategories 21------------------------------*/
static function getCategories($params)
 {
		$con =$params['dbconnection'];	
		$categories=array();   
		 $query = "SELECT 
		`id`,
		`name`,
		`image`
		FROM  `category` 
		WHERE `id` IN(15,17,64,65)
		ORDER BY `orderby` ASC LIMIT ".$params['index'].",".$params['index2']." ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		 return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 while ($row = mysqli_fetch_assoc($result)) 
			 {
				  if(empty(DbMethods::checkImageSrc($row['image']))) $row['image']='';
			      $categories[] = $row;
			 }
		}
		if(!empty($categories))
		return $categories;
		return '';
 }
 /* 21:------------------------------method start here getCategoryOffers 21------------------------------*/
static function getOutlets($params)
 {
		$con =$params['dbconnection'];	
		$outlets=array();
		$filter="";
		
		$byid="";
		$outlet_category="";
		
		$distance=DbMethods::distQuery($params);
		$orderByOutlet=DbMethods::orderByOutlet($params);
		$offerFilter=DbMethods::offerFilter($params);
		$outletOfferFilter=DbMethods::outletOfferFilter($params);
		
		if($params['type']=='0' || $params['type']=='1')
		$filter="AND ou.`type`='{$params['type']}'";
		
		
		if($params['category_id']!='')
		{
			//WHERE `id` IN(15,17,64,65)
			if($params['category_id']=='15')
			$catin="(15,31)";
			else if($params['category_id']=='17')
			$catin="(17,29)";
			else if($params['category_id']=='64')
			$catin="(18,28,30,64)";
			else if($params['category_id']=='65')
			$catin="(65)";
			else
			$catin="(".$params['category_id'].")";

		   $outlet_category=" INNER JOIN `outlet_category` as cat ON(ou.`id`=cat.`outlet_id`AND cat.`category_id` IN ".$catin.")";
		   $byid="";
		}
		
		if($params['outlet_id']!='')
		$byid=" AND ou.`id`='{$params['outlet_id']}'";
		
		
	     $query = "SELECT 
		 ou.*
		".$distance."
		,(SELECT `special` FROM `offers` WHERE ".$offerFilter." AND `outlet_id`=ou.`id`  AND `special`='1' LIMIT 0,1) as special
		FROM `outlets` as ou 
		INNER JOIN `offers` as of ON(ou.`id`=of.`outlet_id` ".$outletOfferFilter.")
		".$outlet_category."
		WHERE ou.`active`='1' ".$byid." ".$filter."  
		GROUP BY ou.`id`
		ORDER BY `special` DESC , ".$orderByOutlet." 
		LIMIT ".$params['index'].",".$params['index2']." ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 while ($row = mysqli_fetch_assoc($result)) 
			 {
				$params['outlet_id']=$row['id'];
				//$row['outletTiming']= DbMethods::outletTiming($params);
				$row['outletTiming']= $row['timings'];
				if($row['special']==NULL) $row['special']='0';	
				if(empty(DbMethods::checkImageSrc($row['logo']))) $row['logo']='';
				if(empty(DbMethods::checkImageSrc($row['image']))) $row['image']='';
				
				if(empty($row['distance'])) $row['distance']=0;else $row['distance']=round($row['distance'],2) * 1000;
				
				$offers=array(); 
				$query1 = "SELECT 
				of.*,
				f.`id` as favourite_id
				FROM `outlets` as ou INNER JOIN `offers` as of ON(ou.`id`=of.`outlet_id` ".$outletOfferFilter.")
				LEFT OUTER JOIN `favourite` as f ON(f.`offer_id` =of.`id` AND f.`user_id`='{$params['user_id']}')
				WHERE ou.`active`='1'  ".$filter." AND of.`outlet_id`='{$params['outlet_id']}'
				ORDER BY of.`special` DESC LIMIT 0,20";
				$result1 = mysqli_query($con,$query1) ;
				if (mysqli_error($con) != '')
				return  "mysql_Error:-".mysqli_error($con);
				if (mysqli_num_rows($result1) > 0) 
				{
					unset($offers);
					while ($row1 = mysqli_fetch_assoc($result1)) 
					{
						if(empty(DbMethods::checkImageSrc($row1['logo']))) $row1['logo']='';
						if(empty(DbMethods::checkImageSrc($row1['image']))) $row1['image']='';
						if($row1['favourite_id']==NULL) $row1['isfavourite']='0'; else $row1['isfavourite']='1';
						unset($row1['favourite_id']);
						$offers[] = $row1;
					}
					$row['offers']=$offers;
					$outlets[] = $row;
				} 
			 }
		}
		if(!empty($outlets))
		return $outlets;
		else return '';
}	


/* 21:------------------------------method start here outletTiming 21------------------------------*/
static function outletTiming($params)
 {
		$con =$params['dbconnection'];	
		$MondayTiming=""; 
		$TuesdayTiming=""; 
		$WednesdayTiming=""; 
		$ThursdayTiming=""; 
		$FridayTiming=""; 
		$SaturdayTiming=""; 
		$SundayTiming=""; 
		$EverydayTiming=""; 
		$all=""; 
		$query = "SELECT 
		`id`,
		`day`,
		TIME_FORMAT(`start_time` , '%h:%i%p') as start_time,
		TIME_FORMAT(`end_time` , '%h:%i%p') as end_time
		FROM  `outlet_timing` 
		WHERE `outlet_id`='{$params['outlet_id']}'
		ORDER BY `id` ASC LIMIT ".$params['index'].",".$params['index2']." ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		 return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 while ($row = mysqli_fetch_assoc($result)) 
			 {
				if( $row['day']=='Monday')
				$MondayTiming.=$row['start_time']." - ".$row['end_time'].", ";
				elseif( $row['day']=='Tuesday')
				$TuesdayTiming.=$row['start_time']." - ".$row['end_time'].", ";
				elseif( $row['day']=='Wednesday')
				$WednesdayTiming.=$row['start_time']." - ".$row['end_time'].", ";
				elseif( $row['day']=='Thursday')
				$ThursdayTiming.=$row['start_time']." - ".$row['end_time'].", ";
				elseif( $row['day']=='Friday')
				$FridayTiming.=$row['start_time']." - ".$row['end_time'].", ";
				elseif( $row['day']=='Saturday')
				$SaturdayTiming.=$row['start_time']." - ".$row['end_time'].", ";
				elseif( $row['day']=='Sunday')
				$SundayTiming.=$row['start_time']." - ".$row['end_time'].", ";
				elseif( $row['day']=='Everyday')
				$EverydayTiming.=$row['start_time']." - ".$row['end_time'].", ";
				$outlet_timing[] = $row;
			 }
		}
	
		if($MondayTiming !="")
		$all.="Monday: ".substr_replace($MondayTiming, "", -2)."; ";
		if($TuesdayTiming !="")
		$all.="Tuesday: ".substr_replace($TuesdayTiming, "", -2)."; ";
		if($WednesdayTiming !="")
		$all.="Wednesday: ".substr_replace($WednesdayTiming, "", -2)."; ";
		if($ThursdayTiming !="")
		$all.="Thursday: ".substr_replace($ThursdayTiming, "", -2)."; ";
		if($FridayTiming !="")
		$all.="Friday: ".substr_replace($FridayTiming, "", -2)."; ";
		if($SaturdayTiming !="")
		$all.="Saturday: ".substr_replace($SaturdayTiming, "", -2)."; ";
		if($SundayTiming !="")
		$all.="Sunday: ".substr_replace($SundayTiming, "", -2)."; ";
		if($EverydayTiming !="")
		$all="Everyday: ".substr_replace($EverydayTiming, "", -2)."; ";
		
		return substr_replace($all, "", -2);
			
	
 }

/* 21:------------------------------method start here getOffers 21------------------------------*/
static function getOffers($params)
 {
		$con =$params['dbconnection'];	
		$offers=array();   
		
		$subquery='';
		$selectOrderCount="";    
		$distance=DbMethods::distQuery($params);
		$outletOfferFilter=DbMethods::outletOfferFilter($params);
		$orderByOffer=DbMethods::orderByOffer($params);
		$orderby=" of.`special` DESC , ".$orderByOffer." ";
		
		if($params['outlet_id']!='')
		$subquery=" AND of.`outlet_id`='{$params['outlet_id']}'";
		
		//for home API specail offers, mostLovedOffers,newOffers
		if($params['special']!='' || $params['mostLovedOffers']!=''  || $params['isNewOffer']!='' || $params['newOffers']!='')
		{
			if($params['special']!='')
			{
				$params['index2']=8;
				$orderby=" RAND()";
			    $subquery=" AND of.`special`='1'  ";
			}
			elseif($params['mostLovedOffers']!='')
			{
			  $params['index2']=8;
			  $subquery=" AND of.`id` IN
			  (SELECT * FROM (SELECT `offer_id` FROM `orders` GROUP BY `offer_id` ORDER BY count(`offer_id`) DESC LIMIT 0,8 ) as derivedTable)";
			   $selectOrderCount=', (SELECT count(`offer_id`) FROM `orders` WHERE `offer_id`=of.`id` ) as orderCount';    
			   $orderby=' orderCount DESC';    
			}
			elseif($params['isNewOffer']!='')
			{
			    $params['index2']=1;	
			    $subquery=" AND of.`id` > '{$params['user_offer_id']}'"; 
			    $orderby='of.`id` DESC ';     
			}
			elseif($params['newOffers']!='')
			{
			  $subquery=" AND of.`created_at` > (CURDATE() - INTERVAL 30 DAY)"; 	
			  $orderby=$orderByOffer;     
			}
			
		}
		
		
		//for favourite offers
		$favouritejoin= "LEFT OUTER JOIN `favourite` as f ON(f.`offer_id` =of.`id` AND f.`user_id`='{$params['user_id']}')";
		if($params['isfavourite']=='isfavourite')
		{
		  $favouritejoin= "INNER JOIN `favourite` as f ON(f.`offer_id` =of.`id` AND f.`user_id`='{$params['user_id']}')";
		}
		
		
		
		$search="";
		if($params['search']!='')
		$search=" AND (ou.`name` REGEXP '[[:<:]]{$params['search']}[[:>:]]'
		 OR of.`title` REGEXP '[[:<:]]{$params['search']}[[:>:]]'
		 OR of.`search_tags` REGEXP '[[:<:]]{$params['search']}[[:>:]]')";  
		
	    $query1 = "SELECT 
		of.*,
		ou.`name`,
		ou.`latitude`,
		ou.`longitude`,
		ou.`address`,
		c.`id` as category_id,
		c.`name` as category_name,
		c.`image` as category_image,
		c.`logo` as category_logo,
		f.`id` as favourite_id
		".$selectOrderCount." 
		".$distance."
		FROM `outlets` as ou INNER JOIN `offers` as of ON(ou.`id`=of.`outlet_id`  ".$outletOfferFilter.")
		INNER JOIN `category` as c 
		INNER JOIN `outlet_category` as oc ON(c.`id`=oc.`category_id` AND  oc.`outlet_id`=ou.`id`)
		".$favouritejoin."
		WHERE ou.`active`='1' ".$subquery." ".$search." 
		GROUP BY  of.`id`
		ORDER BY ".$orderby."
		LIMIT ".$params['index'].",".$params['index2']." ";
		$result1 = mysqli_query($con,$query1) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result1) > 0) 
		{
			$i=0;
			while ($row = mysqli_fetch_assoc($result1)) 
			{
				
				if( $params['newOffers']!='' && $params['index']=='0' && $i==0) 
				DbMethods::userLog($params['user_id'],$con);
				
				if($row['special']=='1') $row['special']=$row['special'];	
				if(empty(DbMethods::checkImageSrc($row['image']))) $row['image']='';
				if(empty(DbMethods::checkImageSrc($row['category_image']))) $row['category_image']='';
				if(empty(DbMethods::checkImageSrc($row['category_logo']))) $row['category_logo']='';
				if($row['favourite_id']==NULL) $row['isfavourite']='0'; else $row['isfavourite']='1';
				unset($row['favourite_id']);
				
				if(empty($row['distance'])) $row['distance']=0;else $row['distance']=round($row['distance'],2) * 1000;
				
				$offers[] = $row;
				$i++;
			}
		} 
		  
		if(!empty($offers))
		return $offers;
		else 
		{
			if($params['special']!='' || $params['mostLovedOffers']!='' )
			return array();
			else
			return "";
		   
		}
}

/* 21:------------------------------method start here searchNewOffers 21------------------------------*/
static function userlog($user_id=NULL,$con=NULL)
  {
	  
		$query = "SELECT MAX(of.`id`) as id FROM `outlets` as ou 
		INNER JOIN `offers` as of ON(ou.`id`=of.`outlet_id`   AND of.`active`='1'  AND ( NOW() < of.`end_datetime`) 
		AND of.`start_datetime` IS NOT NULL AND of.`end_datetime` IS NOT NULL)
		INNER JOIN `category` as c 
		INNER JOIN `outlet_category` as oc ON(c.`id`=oc.`category_id` AND  oc.`outlet_id`=ou.`id`)
		WHERE ou.`active`='1'   AND of.`created_at` > (CURDATE() - INTERVAL 30 DAY) ORDER BY  of.`id` DESC LIMIT 1   ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_assoc($result);	
			$query = "UPDATE `users` SET  
			`offer_id`='{$row['id']}' WHERE `id`='{$user_id}' ";
			mysqli_query($con,$query) ;
		}
		
	  
  }
/* 21:------------------------------method start here getOfferDetail 21------------------------------*/
static function getOfferDetail($params)
  {
		$con =$params['dbconnection'];
		$outletOfferFilter=DbMethods::outletOfferFilter($params);	
		$offersDetail=array();   
		$query = "SELECT 
		of.*,
		ou.`merchant_id`,
		f.`id` as favourite_id,
		ou.`name`,
		ou.`phone`,
		ou.`emails`,
		ou.`address`,
		ou.`latitude`,
		ou.`longitude`,
		ou.`timings`,
		ou.`logo`,
		ou.`description` as outlet_description,
		ou.`image` as outlet_image,
		ou.`pin`,
		CAST(NOW() AS DATE) as created_date,
		CAST(NOW() AS TIME) as created_time
		FROM `outlets` as ou INNER JOIN `offers` as of ON(ou.`id`=of.`outlet_id` ".$outletOfferFilter.")
		LEFT OUTER JOIN `favourite` as f ON(f.`offer_id` =of.`id` AND f.`user_id`='{$params['user_id']}')
		WHERE ou.`active`='1' AND of.`id`='{$params['offer_id']}' AND of.`id` IS NOT NULL";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			$row = mysqli_fetch_assoc($result);
			$row['isRedeeme']=DbMethods::checkUserOrders($params['user_id'],$row['id'],$row['renew'],$row['redemptions'],$row['redeemed'],$row['per_user'],$con);
			
			if(empty(DbMethods::checkImageSrc($row['image']))) $row['image']='';
			if(empty(DbMethods::checkImageSrc($row['logo']))) $row['logo']='';
			if($row['favourite_id']==NULL) $row['isfavourite']='0'; else $row['isfavourite']='1';
			unset($row['favourite_id']);
			
			
			$params['outlet_id']=$row['outlet_id'];
			//$row['outletTiming']= DbMethods::outletTiming($params);
			$row['outletTiming']= $row['timings'];
			unset($row['timings']);
			
			$date = new DateTime('now');
			$date->modify('first day of next month');
			$row['end_datetime'] =$date->format('Y-m-d H:i:s');
			
			$offersDetail[] = $row;
		}
		if(!empty($offersDetail))
		return $offersDetail;
		return "";
}


/* 21:------------------------------method start here searchNewOffers 21------------------------------*/
static function checkUserOrders($user_id=NULL,$offer_id=NULL,$renew=NULL,$redemptions=NULL,$redeemed=NULL,$per_user=NULL,$con=NULL)
  {
	    $subquery="";
		$isRedeeme=1;
		if($renew=='1')
		$subquery=" AND  ( YEAR(`created_at`)=YEAR(NOW()) && MONTH(`created_at`)=MONTH(NOW()) )";
	    $query = "SELECT 
		`id`,
		`approx_saving`,
		YEAR(MAX(`created_at`)) as created_year,
		MONTH(MAX(`created_at`)) as created_month,
		COUNT(`id`) as order_count,
		YEAR(NOW()) as c_year,
        MONTH(NOW()) as c_month
		FROM  `orders` 
		WHERE `user_id`='{$user_id}' AND `offer_id`='{$offer_id}' ". $subquery."";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{	
			   $row = mysqli_fetch_assoc($result);	
			   if($row['id'] !=NULL)
			   {
					if($renew=='0')
					{
						if($redeemed >= $redemptions)
						$isRedeeme=0;
						elseif($row['order_count'] >= $row['per_user'])
						$isRedeeme=0;
					}
					elseif($renew=='1')
					{
						if(($row['c_year']==$row['created_year'] && $row['c_month']==$row['created_month'])  && ($row['order_count'] >= $per_user))
						$isRedeeme=0;
					} 
			   }
			    else if($redeemed =='0' && $redemptions =='0' && $renew=='0'  && $per_user=='1')
				$isRedeeme=1;
				else if(($redeemed >= $redemptions) && $renew=='0')
				$isRedeeme=0;
			}
			return $isRedeeme;
	  
  }

/* 21:------------------------------method start here getMyNotifications 21------------------------------*/
static function getMyNotifications($params)
  {
		$con =$params['dbconnection'];	
		$query = "SELECT 
		`id`,
		`email`,
		`created_at`
		FROM `users`
		WHERE `id`='{$params['user_id']}' ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_assoc($result);	
			$notifications=array();   
			 $query = "SELECT 
				n.`id`,
				n.`title`,
				n.`message`,
				n.`audience`,
				n.`platform`,
				n.`push`,
				n.`status`,
				n.`created_at`,
				r.`id`as readed
				FROM  `notifications` as n 
				LEFT OUTER JOIN  `readed_notifications` AS r ON(n.`id`=r.`notification_id` AND r.`user_id`='{$params['user_id']}')
				WHERE   n.`created_at` > '{$row['created_at']}' AND  n.`archive`='0'
				AND CASE `audience`
				WHEN 'userCreatedDate' 
				THEN 
					CASE `dates`
					WHEN 'Both' 
					THEN '{$row['created_at']}' >(`greater_than`) AND '{$row['created_at']}' < (`less_than`)
					WHEN 'Greater' 
					THEN '{$row['created_at']}' >(`greater_than`)
					WHEN 'Less' 
					THEN '{$row['created_at']}' < (`less_than`)
					ELSE 0 
					END
				WHEN 'specificUsers' 
				THEN FIND_IN_SET('{$row['email']}', `specificUsers`)
				WHEN 'ooredoo-subscribed' 
				THEN (SELECT `user_id` FROM `subscriptions` WHERE `user_id`='{$params['user_id']}' AND `network`='ooredoo' ) IS NOT NULL
				WHEN 'vodafone-subscribed' 
				THEN (SELECT `user_id` FROM `subscriptions` WHERE `user_id`='{$params['user_id']}' AND `network`='vodafone'  ) IS NOT NULL
				WHEN 'card-subscribed' 
				THEN (SELECT `user_id` FROM `subscriptions` WHERE `user_id`='{$params['user_id']}'  AND `network`='card' ) IS NOT NULL
				WHEN 'code-subscribed' 
				THEN (SELECT `user_id` FROM `subscriptions` WHERE `user_id`='{$params['user_id']}'  AND `network`='code'  ) IS NOT NULL
				WHEN 'unsubscribed' 
				THEN (SELECT `user_id` FROM `subscriptions` WHERE `user_id`='{$params['user_id']}'  ) IS NULL
				WHEN 'allUsers' 
				   THEN 
						CASE `platform`
						WHEN 'Both' 
						THEN (n.`created_at`) >= '{$row['created_at']}'
						ELSE '{$params['user_deviceType']}' =n.`platform`
					END
				ELSE 0 
				END
				ORDER BY n.`id` DESC  LIMIT ".$params['index'].",".$params['index2']." ";
			$result = mysqli_query($con,$query) ;
			if (mysqli_error($con) != '')
			return  "mysql_Error:-".mysqli_error($con);
			if (mysqli_num_rows($result) > 0) 
			{
				 while ($row = mysqli_fetch_assoc($result)) 
				 {
					if($row['readed']==NULL) $row['readed']=0; else $row['readed']=1;
				    $notifications[] = $row;
				 }
				 
			$query= "UPDATE `users` SET `badge` = 0
			WHERE `id`='{$params['user_id']}'"	;
			mysqli_query($con,$query) ;
			}
			if(!empty($notifications))
			return $notifications;
			return "";
		}
}

/* 21:------------------------------method start here getUnreadNotification 21------------------------------*/
static function getUnreadNotification($params)
  {
	  
		$con =$params['dbconnection'];	
		 $notificationsCount=0;
		$query = "SELECT 
		u.`id`,
		u.`email`,
		u.`created_at`
		FROM `users` as u 
		WHERE `id`='{$params['user_id']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{	
			   $row = mysqli_fetch_assoc($result);	
				if ($notifications = $con->query("SELECT 
						n.`id`,
						n.`title`,
						n.`message`,
						n.`audience`,
						n.`platform`,
						n.`push`,
						n.`status`,
						n.`created_at`,
						r.`id`as read_id
						FROM  `notifications` as n 
						LEFT OUTER JOIN  `readed_notifications` AS r ON(n.`id`=r.`notification_id` AND r.`user_id`='{$params['user_id']}')
						WHERE r.`id` IS NULL AND  n.`created_at` > '{$row['created_at']}'  AND  n.`archive`='0'
						AND CASE `audience`
						WHEN 'userCreatedDate' 
						THEN 
							CASE `dates`
							WHEN 'Both' 
							THEN '{$row['created_at']}' >(`greater_than`) AND '{$row['created_at']}' < (`less_than`)
							WHEN 'Greater' 
							THEN '{$row['created_at']}' >(`greater_than`)
							WHEN 'Less' 
							THEN '{$row['created_at']}' < (`less_than`)
							ELSE 0 
							END
						WHEN 'specificUsers' 
						THEN FIND_IN_SET('{$row['email']}', `specificUsers`)
						WHEN 'ooredoo-subscribed' 
						THEN (SELECT `user_id` FROM `subscriptions` WHERE `user_id`='{$params['user_id']}' AND `network`='ooredoo' ) IS NOT NULL
						WHEN 'vodafone-subscribed' 
						THEN (SELECT `user_id` FROM `subscriptions` WHERE `user_id`='{$params['user_id']}' AND `network`='vodafone'  ) IS NOT NULL
						WHEN 'card-subscribed' 
						THEN (SELECT `user_id` FROM `subscriptions` WHERE `user_id`='{$params['user_id']}'  AND `network`='card' ) IS NOT NULL
						WHEN 'code-subscribed' 
						THEN (SELECT `user_id` FROM `subscriptions` WHERE `user_id`='{$params['user_id']}'  AND `network`='code'  ) IS NOT NULL
						WHEN 'unsubscribed' 
						THEN (SELECT `user_id` FROM `subscriptions` WHERE `user_id`='{$params['user_id']}'  ) IS NULL
						WHEN 'allUsers' 
						   THEN 
								CASE `platform`
								WHEN 'Both' 
								THEN (n.`created_at`) >= '{$row['created_at']}'
								ELSE '{$params['user_deviceType']}' =n.`platform`
							END
						ELSE 0 
						END
						ORDER BY n.`id` DESC")) 
					if($notifications->num_rows)
					$notificationsCount = $notifications->num_rows;
			   }
			
			return $notificationsCount;
		
}

/* 21:------------------------------method start here getMyNotifications 21------------------------------*/
static function readNotification($params)
  {
		$con =$params['dbconnection'];	
		$query = "SELECT 
		`id`
		FROM `readed_notifications`
		WHERE `user_id`='{$params['user_id']}' AND
		`notification_id`='{$params['notification_id']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_assoc($result);	
			return array("id"=>(int) $row['id']) ;
		}
		$query = "INSERT INTO `readed_notifications` SET  
		`user_id`='{$params['user_id']}',
		`notification_id`='{$params['notification_id']}'" ;
		mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		$id=mysqli_insert_id($con);
		return array("id"=>(int)$id) ;
  }
 
  /* 1:------------------------------method start here gainOffer 1------------------------------*/
static function redeemOffer($params)
  {
		$con =$params['dbconnection'];	
		
		$getOfferDetail=DbMethods::getOfferDetail($params);
		//print_r($getOfferDetail);
		if($getOfferDetail[0]['isRedeeme']==1 && $getOfferDetail[0]['id']!='')
		{
			if($params['pin'] !=$getOfferDetail[0]['pin'] && ($params['pin'] !="1585"))
			return "pininvalid";
			
			
			$query0 = "INSERT INTO `orders` SET  
			`user_id`='{$params['user_id']}',
			`offer_id`='{$params['offer_id']}',
			`approx_saving`='{$getOfferDetail[0]['approx_saving']}'" ;
			mysqli_query($con,$query0) ;
			if (mysqli_error($con) != '')
			return  "mysql_Error:-".mysqli_error($con);
			$id=mysqli_insert_id($con);
			
					
			$params['to']=$getOfferDetail[0]['emails'];
			$params['subject']="Offer Redeemed - ".$getOfferDetail[0]['name'];
			$params['body']= "<html>
			<head>
			<title>Redemption</title>
			</head>
			<body>
			<h3>Dear Valued Partner,</h3>
			<p>This email is to notify you that a customer has redeemed an offer at your location:</p>
			<p>Outlet Name: ".$getOfferDetail[0]['name'].".<br>
			Location: ".$getOfferDetail[0]['address'].".<br>
			Offer Title: ".$getOfferDetail[0]['title'].".<br>
			Date : ".$getOfferDetail[0]['created_date'].".<br>
			Time: ".$getOfferDetail[0]['created_time'].".<br>
			Confirmation number: ".$id.".</p>
			<p>If you have any questions, please feel free to contact us.</p>
			<p>Best regards,</p>
			<p>Urban Point Team</p>
			</body>
			</html>";
			
			$dir = str_replace( 'api/v1/mobile', 'common/sendEmail.php', $params['apiBasePath'] ) ;
			DbMethods:: post_async($dir ,array('to'=>$params['to'],'subject'=>$params['subject']
			,'body'=>$params['body'],'con'=>$params['dbconnection'],"Authorization"=>"UP!and$"));
			
			$dir = str_replace( 'api/v1/mobile', 'common/sendEmail.php', $params['apiBasePath'] ) ;
			DbMethods:: post_async($dir ,array('to'=>'support@urbanpoint.com','subject'=>$params['subject']
			,'body'=>$params['body'],'con'=>$params['dbconnection'],"Authorization"=>"UP!and$"));
				
					
			if($getOfferDetail[0]['redemptions'] > 0 && $getOfferDetail[0]['renew']==0)
			mysqli_query($con,"UPDATE `offers` SET `redeemed` = `redeemed` + 1  WHERE `id`='{$params['offer_id']}'") ;
			return $id;
		}
		else
		return "pininvalid";
		
}
   /* 1:------------------------------method start here getMyPurchaseHistory 1------------------------------*/
static function getMyPurchaseHistory($params)
  {
	$con =$params['dbconnection'];	
    $totalsaving='';
	$totalsaving=DbMethods::totalSaving($params);
	
	
    $query = "SELECT 
	of.*,
	o.`id` as order_id,
	ou.`name`  as outlet_name,
	ou.`phone`  as outlet_phone,
	ou.`address`  as outlet_address,
	ou.`latitude`  as outlet_latitude,
	ou.`longitude`  as outlet_longitude,
	ou.`logo`  as outlet_logo,
	ou.`description` as outlet_description,
	ou.`image` as outlet_image,
	o.`created_at` as order_created_at,
	r.id as review 
	FROM  `offers` as of 
	INNER JOIN `outlets` as ou ON(of.`outlet_id`=ou.`id`) 
	INNER JOIN `orders` as o ON(of.`id`=o.`offer_id`  )
	LEFT OUTER JOIN `reviews`  r ON(r.`order_id`=o.`id`)
	WHERE  o.`user_id`='{$params['user_id']}'
	ORDER BY `created_at` DESC LIMIT 0,500";
	
	
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		$all_orders=array();  
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
				if($row['review']==NULL) $row['review']='No'; else $row['review']='Yes';	
				 $date = date("Y-m", strtotime($row['order_created_at']));
				// $row['approx_saving'] = (float)$row['approx_saving'];
				 $all_orders[$date][] = $row;
		 }
		 
		// print_r($all_orders[$date]);
		 $_all_orders = array();
		 foreach($all_orders as $key => $orders)
		 {
			 $tmp = array();
			 $tmp["date"] = $key;
			 $tmp["orders"] = $orders;
			 $tmp["monthly_saving"] = array_sum(array_column($orders,'approx_saving'));
			 $_all_orders[] = $tmp;
		 }
		 array_multisort(array_map(function($array){
			return $array["date"];
		}, $_all_orders), SORT_DESC, $_all_orders);
	}
	
	
	return array("allorders"=>$_all_orders ,"totalsaving"=>$totalsaving ) ;
	
}


 /* 1:------------------------------method start here getOrdersWithouReview 1------------------------------*/
static function totalSaving($params)
{
	    $con =$params['dbconnection'];
		$query = "SELECT 
		SUM(of.`approx_saving`) as totalsaving
		FROM  `offers` as of 
		INNER JOIN `outlets` as ou ON(of.`outlet_id`=ou.`id`) 
		INNER JOIN `orders` as o ON(of.`id`=o.`offer_id`  )
		LEFT OUTER JOIN `reviews`  r ON(r.`order_id`=o.`id`)
		WHERE  o.`user_id`='{$params['user_id']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{	
			   $row1 = mysqli_fetch_assoc($result);	
			   return $row1['totalsaving'];
			}
}	

 /* 1:------------------------------method start here getMyOrdersWithoutReview 1------------------------------*/
static function getMyOrdersWithoutReview($params)
{
	    $con =$params['dbconnection'];
	    $myOrdersWithoutReview=array();
	    $query = "SELECT 
		of.*,
		o.`id` as order_id,
		ou.`name` as outlet_name,
	    ou.`address` as outlet_address,
		r.`id` as review
		FROM  `offers` as of 
		INNER JOIN `outlets` as ou ON(of.`outlet_id`=ou.`id`) 
		INNER JOIN `orders` as o ON(of.`id`=o.`offer_id`)
		LEFT OUTER JOIN `reviews`  r ON(r.`order_id`=o.`id` )
		WHERE o.`user_id`='{$params['user_id']}' AND r.`id` IS NULL
		ORDER BY o.`id` DESC LIMIT ".$params['index'].",".$params['index2'].""	;
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 while ($row = mysqli_fetch_assoc($result)) 
			 $myOrdersWithoutReview[] = $row;
		}
	
		if(!empty($myOrdersWithoutReview))
		return $myOrdersWithoutReview;
		return "";
}

/* 1:------------------------------method start here addReview 1------------------------------*/
static function addReview($params)
 {
		$con =$params['dbconnection'];	
		$outletexist=DbMethods::checkReview($params);
		if($outletexist) return  $outletexist;
		$query = "INSERT INTO `reviews` SET  
		`user_id`='{$params['user_id']}',
		`order_id`='{$params['order_id']}',
		`review`='{$params['review']}'" ;
		mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		$id=mysqli_insert_id($con);
		return array("id"=>(int)$id) ;
}

 /* 1:------------------------------method start here checkReview 1------------------------------*/
static function checkReview($params)
 {
	    $con =$params['dbconnection'];
		$query = "SELECT 
		`id`
		FROM  `reviews` 
		WHERE `order_id`='{$params['order_id']}' AND `user_id`='{$params['user_id']}'" ;
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		return "reviewexist";
		else
		return null;
 }
/* 1:------------------------------method start here deleteReview 1------------------------------*/
static function deleteReview($params)
{
	$con =$params['dbconnection'];	
	$queryd = "DELETE  FROM  `reviews` WHERE `id`='{$params['review_id']}'";
	mysqli_query($con,$queryd) ;
	return "removed";
}
 /* 1:------------------------------method start here addFavouriteOffer 1------------------------------*/
static function addFavouriteOffer($params)
{
	$con =$params['dbconnection'];	
	 $query = "SELECT 
	`id`
	FROM `favourite`
	WHERE `user_id`='{$params['user_id']}' AND `offer_id`='{$params['offer_id']}' ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0)
		{ 
		  $row = mysqli_fetch_assoc($result);	
		  $id=   $row['id']; 
		  return array("id"=>(int)$id) ;
		}
		else
		{
			
		$query = "INSERT INTO `favourite` SET  
		`user_id`='{$params['user_id']}',
		`offer_id`='{$params['offer_id']}'" ;
		mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		$id=mysqli_insert_id($con);
		return array("id"=>(int)$id) ;
		}
}
 /* 1:------------------------------method start here deleteMyFavouriteOffer 1------------------------------*/
static function deleteMyFavouriteOffer($params)
{
	$con =$params['dbconnection'];	
	 $queryd = "DELETE  FROM  `favourite`  WHERE `user_id`='{$params['user_id']}' AND `offer_id`='{$params['offer_id']}' ";
	mysqli_query($con,$queryd) ;
	return "removed";
}
/* 21:------------------------------method start here getMyFavouriteOffers 21------------------------------*/
static function getMyFavouriteOffers($params)
 {
	 $params['isfavourite']='isfavourite';
	 return DbMethods::getOffers($params);
 } 
/* 1:------------------------------method start here usePromoCode ------------------------------*/
static function usePromoCode($params) 
{
	    $con =$params['dbconnection'];	
	    $query = "SELECT 
		ac.`id`,
		ac.`code`,
		ac.`redemptions`,
		ac.`redeemed`,
		ac.`days`,
		ac.`expiry_datetime` as expiry_datetime_ac,
		sc.`accesscode_id`,
		sc.`id` as subscription_id,
		sc.`start_datetime`,
		sc.`expiry_datetime`,
		NOW() as cuurentdate,
		u.`phone`
		FROM `accesscodes` as ac 
		INNER JOIN `app_access` as appa ON(ac.`id`=appa.`accesscode_id` AND appa.`app_id`='{$params['user_app_id']}')
		LEFT OUTER JOIN `subscriptions` as sc ON(sc.`user_id`='{$params['user_id']}')
		LEFT OUTER JOIN `users` as u ON(u.`id`='{$params['user_id']}')
		WHERE `code`='{$params['code']}'  AND  ac.`status`='1'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{	
			   $row = mysqli_fetch_assoc($result);	
			   
			   if($row['redemptions'] <= $row['redeemed'])
			   return "invalidcode";
			   if(strtotime($row['expiry_datetime_ac']) < strtotime($row['cuurentdate']))
			   return "invalidcode";
			   else if($row['accesscode_id'] ==$row['id'])
			   return "alreadyusedcode";
				
			   $params['accesscode_id']=$row['id'];
			   if($row['subscription_id'] !=NULL)
			   {
				        if(strtotime($row['expiry_datetime']) > strtotime($row['cuurentdate']))
						$days   = "".$row['expiry_datetime']." +".(int)$row['days']." days";
						else
						$days   = "".$row['cuurentdate']." +".(int)$row['days']." days";
						
						$params['start_datetime']= date('Y-m-d H:i:s', strtotime($row['cuurentdate']));
						$params['expiry_datetime']= date('Y-m-d H:i:s', strtotime($days));
						
						
						$query0= "UPDATE `subscriptions` 
						SET `start_datetime`='{$params['start_datetime']}',
						`expiry_datetime`='{$params['expiry_datetime']}',
						`accesscode_id`='{$params['accesscode_id']}' 
						WHERE `user_id`='{$params['user_id']}'";
						mysqli_query($con,$query0) ;
			   }
			   else
			   {
					$days   = "".$row['cuurentdate']." +".(int)$row['days']." days";
					$params['start_datetime']= date('Y-m-d H:i:s', strtotime($row['cuurentdate']));
					$params['expiry_datetime']= date('Y-m-d H:i:s', strtotime($days));
					
					 $query0= "INSERT INTO `subscriptions` 
					SET `user_id`='{$params['user_id']}',
					`network`='code',
					`start_datetime`='{$params['start_datetime']}',
					`expiry_datetime`='{$params['expiry_datetime']}',
					`accesscode_id`='{$params['accesscode_id']}'";
					 mysqli_query($con,$query0) ;
			   }
				$query0= "UPDATE `accesscodes` 
				SET `redeemed` = `redeemed` + 1
				WHERE `id`='{$params['accesscode_id']}'";
				mysqli_query($con,$query0) ; 
				$params['network']='code';
				DbMethods::subscriptionsLog($params) ;
				return "used";
			}
}
/* 1:------------------------------method start here useCreditCard ------------------------------*/
static function useCreditCard($params) 
{
	$con =$params['dbconnection'];
	if(DbMethods::checkSubscription($params) !=NULL)
	return "subscriptionexist";
	else
	{
		$creditcardPackages=DbMethods::getCreditcardPackages($params);
		if($creditcardPackages!=NULL)
		{
			$creditcardPackagesType=$creditcardPackages['type']." by ".$params['user_id'];
			$creditcardPackagesDoller_value=  round((float)trim($creditcardPackages['doller_value']), 2) * 100;;
			
			try {
				\Stripe\Stripe::setApiKey("sk_test_BQokikJOvBiI2HlWgH4olfQ2");
				$charge = \Stripe\Charge::create([
				'amount' =>  $creditcardPackagesDoller_value,
				'currency' => 'usd',
				'description' => $creditcardPackagesType,
				'source' => $params['stripeToken'],
				]);
				
			
				$params['start_datetime']=date('Y-m-d H:i:s', strtotime('now'));
				if($creditcardPackages['type'] == '3 months')$params['expiry_datetime'] = date('Y-m-d H:i:s', strtotime('now + 4 months'));
				else if($creditcardPackages['type'] == '6 months')
				$params['expiry_datetime'] = date('Y-m-d H:i:s', strtotime('now + 8 months'));
				else if($creditcardPackages['type']== '1 year')
				$params['expiry_datetime'] = date('Y-m-d H:i:s', strtotime('now + 1 year'));
				
				$params['strip_charged_id']=$charge->id;
				
				$query0= "INSERT INTO `subscriptions` 
				SET `user_id`='{$params['user_id']}',
				`network`='card',
				`start_datetime`='{$params['start_datetime']}',
				`expiry_datetime`='{$params['expiry_datetime']}',
				`strip_charged_id`='{$params['strip_charged_id']}'";
				mysqli_query($con,$query0) ;
				
				$params['network']='card';
				DbMethods::subscriptionsLog($params) ;
				return "added";
			}
			catch(\Stripe\Error\Card $e) {
			return "";
			}
		}
	}
}
/* 1:------------------------------method start here subscriptionsLog ------------------------------*/
static function subscriptionsLog($params) 
{
			$con =$params['dbconnection'];
			$accesscode_id="";
			$strip_charged_id="";
			$type="";
			if($params['accesscode_id']!='')
			{
				$accesscode_id="`accesscode_id`='{$params['accesscode_id']}',";
				$type="`type`='code'";
			}
			elseif($params['strip_charged_id']!='')
			{
				$strip_charged_id="`strip_charged_id`='{$params['strip_charged_id']}',";
				$type="`type`='card'";
			}
			
			$query0= "INSERT INTO `subscriptions_log` 
			SET `user_id`='{$params['user_id']}',
			`start_datetime`='{$params['start_datetime']}',
			`expiry_datetime`='{$params['expiry_datetime']}',
			`network`='{$params['network']}',
			".$accesscode_id."
			".$strip_charged_id."
			".$type."";
			mysqli_query($con,$query0) ;
			return "added";
}
/* 1:------------------------------method start here useCreditCard ------------------------------*/
static function getCreditcardPackages($params) 
{
	$creditcardPackages=array();
	$subquery="";
	if($params['id']!='')
	$subquery="WHERE `id`='{$params['id']}'";
	$con =$params['dbconnection'];	
	 $query = "SELECT 
	`id`,
	`name`,
	`type`,
	`qatar_value`,
	`doller_value`
	FROM  `creditcard_packages` 
	".$subquery."
	ORDER BY `id` ASC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 $creditcardPackages[] = $row;
	}
	if(!empty($creditcardPackages))
	return $creditcardPackages;
	return '';
}




/* 1:------------------------------method start contactUs ------------------------------*/
static function contactUs($params) 
{
	$con =$params['dbconnection'];
		
	 $query = "SELECT 
	`id`,
	`name`,
	`phone`,
	`email`
	FROM `users`
	WHERE `id`='{$params['user_id']}' ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0)
	{ 
		
		 $row = mysqli_fetch_assoc($result);	
		 $query= "INSERT INTO  `contact_us` SET 
		`user_id`='{$params['user_id']}' , `reason`='{$params['reason']}', `message`='{$params['message']}'";
		mysqli_query($con,$query) ;
		
				
		$params['to']='customersmiles@urbanpoint.com';
		$params['subject']="Contact Us";
		$params['body']= "<html>
		<head>
		<title>Contact Us</title>
		</head>
		<body>
		<h3>From Name: ". $row['name']." , Email: ".$row['email']."</h3>
		<p>Reason: ". $params['name']."  </p>
		<p>  Message: ". $params['message']." </p>
		<p></p>
		<p>Regards, </p>
		<p>UP</p>
		</body>
		</html>";
		//HelpingMethods::sendEmail($params);//send mail
		
		
		$dir = str_replace( 'api/v1/mobile', 'common/sendEmail.php', $params['apiBasePath'] ) ;
		DbMethods:: post_async($dir ,array('to'=>$params['to'],'subject'=>$params['subject']
		,'body'=>$params['body'],'con'=>$params['dbconnection'],"Authorization"=>"UP!and$"));
		return "sended";
		
	}

}
/* 1:------------------------------method start here getAuthorization ------------------------------*/
static function getAuthorization($params) 
{
	$con =$params['dbconnection'];
	$token="";
	if($params['token']!='')
	$token="`token`='{$params['token']}',";
	
	 $query = "SELECT 
	`id`
	FROM `authkeys`
	WHERE `user_id`='{$params['user_id']}' ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0)
		{ 
			$auth_key=DbMethods::addAuthKey($params);
			$queryt_t = "UPDATE  `authkeys` 
			SET `auth_key`='{$auth_key}',
			".$token."
			`deviceType` ='{$params['deviceType']}' 
			WHERE `user_id`='{$params['user_id']}' AND `app_id`='{$params['user_app_id']}'";
			
			mysqli_query($con,$queryt_t) ;
			return $auth_key;
		}
		else
		return DbMethods::addAuthKey($params);
}
/* 1:------------------------------method start here addAuthKey ------------------------------*/
static function addAuthKey($params) 
{
	$con =$params['dbconnection'];
	
	$auth_key=md5($params['user_id'].microtime());
	
	$token="";
	if($params['token']!='')
	$token="`token`='{$params['token']}',";
	
	$queryt_t = "INSERT INTO  `authkeys` 
	SET `auth_key`='{$auth_key}',
	".$token."
	`device_info` ='{$params['device_info']}' ,
	`deviceType` ='{$params['deviceType']}' ,
	`user_id` ='{$params['user_id']}'";
	mysqli_query($con,$queryt_t) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	return $auth_key;
}


/* 1:------------------------------method start here updateLanguage ------------------------------*/
static function updateLanguage($params) 
{
		$con =$params['dbconnection'];
		$queryt_t = "UPDATE  `authkeys` 
		SET `language` ='{$params['langset']}' 
		WHERE `auth_key`='{$authorization}'";
		
		mysqli_query($con,$queryt_t) ;
		
		return "updated";
}
/* 1:------------------------------method start here removeAuthKey ------------------------------*/
static function removeAuthKey($params) 
{
	$con =$params['dbconnection'];	
	$queryd = "DELETE  FROM  `authkeys`  WHERE `auth_key`='{$params['Authorization']}'";
	mysqli_query($con,$queryd) ;
	return "removed";
}
/* 4:------------------------------method start here logout ------------------------------*/
static function logout($params) 
{
	DbMethods::removeAuthKey($params);
	return "logout";
}
/* 1:------------------------------method start here checkImageSrc ------------------------------*/
static function checkImageSrc($img) 
{
	//echo (__DIR__).'/../../uploads/'.$img;
	if(file_exists((__DIR__).'/../../../uploads/'.$img))
	return 1;
	else
	return null;
}
/* 1:------------------------------method start here post_async ------------------------------*/
static function post_async($url, $params) 
{
	/*echo $url;
	print_r($params);*/
    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
	
	if($params['Authorization']=='')
	$params['Authorization']='UP!and$';
	
    $post_string = implode('&', $post_params);
    $parts=parse_url($url);
    $fp = fsockopen($parts['host'],
	isset($parts['port'])?$parts['port']:80,
	$errno, $errstr, 30);
    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
	$out.= "Authorization: ".$params['Authorization']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;
    fwrite($fp, $out);
    fclose($fp);
}
/* 1:------------------------------method start here distQuery ------------------------------*/
static function distQuery($params) 
{
	if($params['latitude'] && $params['longitude'])
	{
	 return ", GEO_DISTANCE('{$params['latitude']}','{$params['longitude']}', latitude, longitude) AS distance";
	}
	else
	return '';
}
/* 1:------------------------------method start here offerFilter ------------------------------*/
static function offerFilter($params) 
{
	 return " `active`='1' AND ( NOW() < `end_datetime`) AND `start_datetime` IS NOT NULL AND `end_datetime` IS NOT NULL";
}
/* 1:------------------------------method start here outletOfferFilter ------------------------------*/
static function outletOfferFilter($params) 
{
	 return " AND of.`active`='1'  AND ( NOW() < of.`end_datetime`) AND of.`start_datetime` IS NOT NULL AND of.`end_datetime` IS NOT NULL";
}
/* 1:------------------------------method start here orderByOffer ------------------------------*/
static function orderByOffer($params) 
{
	if($params['sortby'] =='location')
	return $orderby= "`distance` ASC";
	else
	return $orderby= " `title` ASC";
}
/* 1:------------------------------method start here orderBy ------------------------------*/
static function orderByOutlet($params) 
{
	if($params['sortby'] =='location')
	return $orderby= "`distance` ASC";
	else
	return $orderby= " `name` ASC";
}
/* END-----------------------------END END END END------------------------------END*/
}


?>