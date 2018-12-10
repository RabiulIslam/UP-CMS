<?php
$filetype="dbMethods";
class DbMethods {

/* 1:------------------------------method start here addUser ------------------------------*/
static function addUser($params) 
{
	$con =$params['dbconnection'];
	$phone="";
	 $email="";  
	if($params['email'] !="")
	{
	  if(DbMethods::checkEmail($params)=='emailexist')return  'emailexist';
	   $email=" `email`='{$params['email']}' ,";  
	}
	
	if($params['phone'] !="")
	{
	  if(DbMethods::checkPhone($params)=='phoneexist')return  'phoneexist';
	   $phone=" `phone`='{$params['phone']}' ,";  
	}
	$password=sha1($params['password']);	  
	$query_n0 = "INSERT INTO `users` SET  
	`name`='{$params['name']}',
	".$email."
	".$phone."
	`password`='{$password}'" ;
	$result_no = mysqli_query($con,$query_n0) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if(mysqli_affected_rows($con)==0)
	return "";
	$id=mysqli_insert_id($con);
	$params['user_id']=$id;
	$auth_key=DbMethods::addAuthKey($params);
	return array("id"=>(int)$id ,"Authorization"=>$auth_key) ;
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
		u.`password`
		FROM `users` as u 
		WHERE u.`phone`='{$params['phone']}' ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{	
			   $row = mysqli_fetch_assoc($result);	
			   if($row['phone']==TRIM($params['phone']) && $row['password']==TRIM(sha1($params['password'])))
				{
					
					$params['user_id']=$row['id'];
					$auth_key=DbMethods::addAuthKey($params);
					$row['Authorization']=$auth_key;
					unset( $row['password']);
					$row['id']=(int)$row['id'];
					$row['type']='1';
					return $row;
				}
				else
				{
				   $params['type']='driver';
				   $adminSignIn=DbMethods::adminSignIn($params);
				   if($adminSignIn=="not_valid_credential_pass")
				   return 'not_valid_credential_pass';
				   else  if($adminSignIn=="not_valid_credential_email")
				   return 'not_valid_credential_pass';
				   else
			       return $adminSignIn;
				}
			  }
		 else
		 {
				$params['type']='driver';
				$adminSignIn=DbMethods::adminSignIn($params);	
				if($adminSignIn=="not_valid_credential_email")
				return 'not_valid_credential_phone';
				else
				return $adminSignIn;
		 }
}


/* 1:------------------------------method start here addAdmin ------------------------------*/
static function addAdmin($params) 
{
	$con =$params['dbconnection'];
	$phone="";
	$params['admin']=true;
	if(DbMethods::checkEmail($params)=='emailexist')return  'emailexist';
	
	if($params['phone'] !="")
	{
	  if(DbMethods::checkPhone($params)=='phoneexist')return  'phoneexist';
	   $phone=" `phone`='{$params['phone']}' ,";  
	} 

	$password=sha1($params['password']);	  
	$query_n0 = "INSERT INTO `admin` SET  
	`name`='{$params['name']}',
	`email`='{$params['email']}',
	".$phone."
	`type`='{$params['type']}',
	`password`='{$password}'" ;
	$result_no = mysqli_query($con,$query_n0) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if(mysqli_affected_rows($con)==0)
	return "";
	$id=mysqli_insert_id($con);
	$params['admin_id']=$id;
	$auth_key=DbMethods::addAuthKey($params);
	return array("id"=>(int)$id ,"Authorization"=>$auth_key) ;
	
}


  /* 21:------------------------------method start here getAdmins 21------------------------------*/
static function getAdmins($params)
 {
	$con =$params['dbconnection'];	
	$admins=array();  
	$adminsCount="";
	$search="";
	
	if($params['search']!="")
	$search=" AND (`name` LIKE '%".$params['search']."%')";
	
	if($params['admin_id'] !="")
	{
		$query = "SELECT COUNT(`id`) as adminsCount  FROM  `admin` WHERE `type`='{$params['type']}' AND `id` !='{$params['admin_id']}' ".$search."";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		 return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 $row = mysqli_fetch_assoc($result);	
			 $adminsCount=$row['adminsCount'];
		}
	} 
	 $query = "SELECT 
	`id`,
	`name`,
	`phone`,
	`email`,
	`updated_at`
	FROM  `admin` WHERE `type`='{$params['type']}' AND `id` !='{$params['admin_id']}' ".$search."
	ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 $admins[] = $row;
	}
	if(!empty($admins))
	{
		if( $adminsCount !="")
		return array("admins"=>$admins ,"adminsCount"=>$adminsCount ) ;
		return $admins;
	}
	else return '';
 }
 



  /*:-------------------------------method start here updateAdmin ------------------------------*/
static function updateAdmin($params)
 { 
 
    $con =$params['dbconnection'];
	$params['admin']=true;
	if($params['email'] !="")
	if(DbMethods::checkEmail($params)=='emailexist')return  'emailexist';
	
	if($params['phone'] !="")
	if(DbMethods::checkPhone($params)=='phoneexist')return  'phoneexist';

	$name='';
	$email='';
	$phone='';
	$password='';
	$subquery="";
	
	if(!empty($params['name'])){   $name=" `name`='{$params['name']}' ,";  }
	if(!empty($params['email'])){  $email=" `email`='{$params['email']}' ,";}
	if(!empty($params['phone'])){  $phone=" `phone`='{$params['phone']}' ,";   } 
	if(!empty($params['password'])){  $password=sha1($params['password']);  $password="`password`='{$password}' ,";  }
	
	
	/*if($params['password'] !='')
    if(DbMethods::checkPassword($params) =='wrong') return 'wrong';*/
	
	  $query= "UPDATE `admin` SET 
	".$name."
	".$email."
	".$phone."
	".$password."
	`id`='{$params['id']}' 
	WHERE `id`='{$params['id']}'"	;
	mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	/*if(mysqli_affected_rows($con)==0)
	return "";*/
	return 'updated';
	
	}
  /*:-------------------------------method start here deleteAdmin ------------------------------*/
static function deleteAdmin($params)
 {
	$con =$params['dbconnection'];	
	$queryd = "DELETE  FROM  `admin`  WHERE `id`='{$params['id']}'";
	mysqli_query($con,$queryd) ;
	if(mysqli_affected_rows($con)==0)
	return "";
	return "removed";
 }

/* 1:------------------------------method start here signIn ------------------------------*/
static function adminSignIn($params) 
{
		$con =$params['dbconnection'];
		
		if($params['deviceType']=='ios')
		return 'not_valid_credential_email';
		
		$subquery="`email`='{$params['email']}'";
		if($params['phone']!='')
		$subquery="`phone`='{$params['phone']}' AND `type`='2'";
		
		 $query = "SELECT 
		`id`,
		`name`,
		`email`,
		`phone`,
		`type`,
		`password`
		FROM `admin`  
		WHERE ".$subquery." ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{	
			   $row = mysqli_fetch_assoc($result);	
			   if(($params['type']=='admin' && ($row['type']=='0' || $row['type']=='1')) || ($params['type']=='driver' && $row['type']=='2'))
			   {
				   if($row['password']==TRIM(sha1($params['password'])))
					{
						$params['admin_id']=$row['id'];
						$auth_key=DbMethods::addAuthKey($params);
						$row['Authorization']=$auth_key;
						unset( $row['password']);
						/*$params['user_type']=$row['type'];
						$row['maxId']=0;
						$getCounts=DbMethods::getCounts($params);
						if(!empty($getCounts))
						$row['maxId']=(int)$getCounts['maxId'];*/
						$row['id']=(int)$row['id'];
						return $row;
					}
					else
					return 'not_valid_credential_pass';
			   }
			   else
		       return 'not_valid_credential_email';
			  }
		 else
		 return 'not_valid_credential_email';
}
/* 1:------------------------------method start here getProfile ------------------------------*/
static function getProfile($params) 
{
	    $con =$params['dbconnection'];	
		$query = "SELECT 
		u.`id`,
		u.`name`,
		u.`email`,
		u.`phone`
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
/* 1:------------------------------method start here forgotPassword ------------------------------*/
static function forgotPassword($params) 
{
		$con =$params['dbconnection'];
		$table="`users`";
		$type="user";
		if($params['type']=='admin')
		{
		  $params['admin']=true;	
	      $table="`admin`";
		  $type="admin";
		}
		
		
		if($params['email'] !='')
		if(DbMethods::checkEmail($params) =='emailexist') 
		{
			$password_reset_token=sha1($params['email']);
			$query= "UPDATE ".$table." SET 
			`password_reset_token`='{$password_reset_token}'
			WHERE `email`='{$params['email']}' ";
			mysqli_query($con,$query) ;
			
			$angertag= '<a href="13.126.174.129/iosweb/mankoosha/admin/v1/#/home/reset-password?token='.$password_reset_token.'&type='.$type.'">
			Reset Password</a>';
			
			$params['to']=$params['email'];
			$params['subject']="Password Recovery";
			$params['body']= "<html>
			<head>
			<title>Password Recovery</title>
			</head>
			<body>
			<h3>Dear user,</h3>
			<p>Click on Link to reset Password: <b>".$angertag ." </b></p>
			<p></p>
			<p>Regards, </p>
			<p>Mankoosha</p>
			</body>
			</html>";
			
			
			$dir=$params['apiBasePath']."sendEmail";
			DbMethods:: post_async($dir ,array('to'=>$params['to'],'subject'=>$params['subject'],'body'=>$params['body'] ,
			'Authorization'=>$params['Authorization']));
			return "sended";
		}
		else
		{
			$params['type']='admin';
			$params['admin']=true;
			return DbMethods::forgotPassword($params);
		}
}

/* 1:------------------------------method start here changePassword ------------------------------*/
static function changePassword($params) 
{
		$con =$params['dbconnection'];
		
		if($params['old_password']==$params['password'])
	    return "same";
		
		$params['code']=sha1($params['code']);
		 $query = "SELECT `id`,`type` FROM  `users` WHERE `password_reset_token` ='{$params['code']}'  AND `phone` ='{$params['phone']}' 
		UNION 
		SELECT `id`,`type` FROM  `admin` WHERE `password_reset_token` ='{$params['code']}' AND `type`='2' AND `phone` ='{$params['phone']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
			{
				$row = mysqli_fetch_assoc($result);		
				$id=$row['id'];
				$table="`users`";
				if($row['type']=='2')
				$table="`admin`";
				
				$params['password']=sha1($params['password']);
				
				$query= "UPDATE ".$table." SET 
				`password`='{$params['password']}',
				`password_reset_token`=NULL
				WHERE `id`='{$id}' ";
				mysqli_query($con,$query) ;
				if (mysqli_error($con) != '')
				return  "mysql_Error:-".mysqli_error($con);
				return 'updated';
			}
			else
			return "codeinvalid";
	
		
		
		
		
		
}

/* 1:------------------------------method start here resetPassword ------------------------------*/
static function resetPassword($params) 
{
		$con =$params['dbconnection'];
		$table="`users`";
		if($params['type']=='admin')
		{
		  $params['admin']=true;	
	      $table="`admin`";
		}
		 $query = "SELECT `id` FROM ".$table."
		WHERE `password_reset_token`='{$params['password_reset_token']}' ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) == 0) 
		return "tokeninvalid";
		
		
		$query= "UPDATE ".$table." SET 
		`password`='{$params['password']}',
		`password_reset_token`=''
		WHERE `password_reset_token`='{$params['password_reset_token']}' ";
		mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		return 'updated';
		
}
/* 1:------------------------------method start here updateProfile ------------------------------*/
static function updateProfile($params) 
{
	$con =$params['dbconnection'];
	if($params['type']=='2')
	{
		return DbMethods::updateAdmin($params);
	}
	if($params['email'] !='')
	if(DbMethods::checkEmail($params)=='emailexist')return  'emailexist';
	if($params['phone'] !='')
	if(DbMethods::checkPhone($params)=='phoneexist')return  'phoneexist';
	$name='';
	$email='';
	$phone='';
	$password='';
	
	$subquery="";
	
	if(!empty($params['name'])){   $name=" `name`='{$params['name']}' ,";  }
	if(!empty($params['email'])){  $email=" `email`='{$params['email']}' ,";}
	if(!empty($params['phone'])){  $phone=" `phone`='{$params['phone']}' ,";   } 
	if(!empty($params['password'])){  $password=sha1($params['password']);  $password="`password`='{$password}' ,";  }
	
	
	if($params['password'] !='')
    if(DbMethods::checkPassword($params) =='wrong') return 'wrong';
	
	  $query= "UPDATE `users` SET 
	".$name."
	".$email."
	".$phone."
	".$password."
	`id`='{$params['user_id']}' 
	WHERE `id`='{$params['user_id']}'"	;
	mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if(mysqli_affected_rows($con)==0)
	return "";
	return 'updated';
}

/* 1:------------------------------method start here checkPassword ---------------------------1*/
static function checkPassword($params) 
{
	$con =$params['dbconnection'];
	
	
	$querysub="";
	$table="`users`";
	if($params['admin'])
	{
	   $table="`admin`";
	   if($params['id'] !='')
	   $querysub="  AND 	`id` !='{$params['id']}' ";
	}
	else
	{
		if($params['user_id'] !='')
		$querysub="  AND 	`id` !='{$params['user_id']}' ";
	}
	
	if($params['old_password']==$params['password'])
	return "same";
	$old_password=sha1($params['old_password']);
	 $query = "SELECT `id` FROM ".$table."
	WHERE `password`='{$old_password}' ".$querysub."";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) == 0) 
	return "wrong";
	else
	return NULL;
}
/* 1:------------------------------method start here checkEmail ---------------------------1*/
static function checkEmail($params) 
{
	$con =$params['dbconnection'];
	$user_id="";
	$admin_id="";
	
	if($params['user_id'] !='')
	$user_id="  AND 	`id` !='{$params['user_id']}' ";
	
	if($params['id'] !='')
	$admin_id="  AND 	`id` !='{$params['id']}' ";
	else if($params['admin_id'] !='')
	$admin_id="  AND 	`id` !='{$params['admin_id']}' ";
	
	
	
    $query = "SELECT `email` FROM  `users` WHERE `email` ='{$params['email']}' ".$user_id."  
	UNION 
	SELECT `email` FROM  `admin` WHERE `email` ='{$params['email']}' ".$admin_id."";
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
	$user_id="";
	$admin_id="";
	if($params['user_id'] !='')
	$user_id="  AND 	`id` !='{$params['user_id']}' ";
	
	if($params['id'] !='')
	$admin_id="  AND 	`id` !='{$params['id']}' ";
	else if($params['admin_id'] !='')
	$admin_id="  AND 	`id` !='{$params['admin_id']}' ";
	
	 
  	$query = "SELECT `phone` FROM  `users` WHERE `phone` ='{$params['phone']}' ".$user_id."  
	UNION 
	SELECT `phone` FROM  `admin` WHERE `phone` ='{$params['phone']}' ".$admin_id."";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	return 'phoneexist';
	else
	return array('phone' =>$params['phone']);
}

/* 1:------------------------------method start here checkPhone ---------------------------1*/
static function sendCode($params) 
{
		$con =$params['dbconnection'];
		$params['code']=substr(mt_rand(),3,4).substr(mt_rand(),5,2);
		$params['code']=1234;
		
		if($params['type']=='forgotPassword')
		{
			
			 $query = "SELECT `id`,`type` FROM  `users` WHERE `phone` ='{$params['phone']}'   
			UNION 
			SELECT `id`,`type` FROM  `admin` WHERE `phone` ='{$params['phone']}'  AND `type`='2'  ";
			$result = mysqli_query($con,$query) ;
			if (mysqli_error($con) != '')
			return  "mysql_Error:-".mysqli_error($con);
			if (mysqli_num_rows($result) > 0)
				{
					$row = mysqli_fetch_assoc($result);		
					$id=$row['id'];
					$table="`users`";
					if($row['type']=='2')
					$table="`admin`";
					
					$password_reset_token=sha1($params['code']);
					$query= "UPDATE ".$table." SET 
					`password_reset_token`='{$password_reset_token}'
					WHERE `id`='{$id}' ";
					mysqli_query($con,$query) ;
					//return 'updated';
				}
				else
				return "invalidPhone";
		}
		
		$dir=$params['apiBasePath']."/twilio.php";
		DbMethods:: post_async($dir ,array('phone'=>$params['phone'],'code'=>$params['code'] ));
	
	    return array('code' =>$params['code']);
}
/* 1:------------------------------method start here addAuthKey ------------------------------*/
static function addAuthKey($params) 
{
	$con =$params['dbconnection'];
	$user_id='';
	$admin_id='';
	$deviceType="";
	$auth_key=md5($params['user_id'].microtime());
	if($params['user_id'])
	$user_id="`user_id` ='{$params['user_id']}'";
	else if($params['admin_id'])
	$admin_id="`admin_id` ='{$params['admin_id']}'";
	
	if($params['deviceType'] =='ios' || $params['deviceType'] =='android')
	$deviceType="`deviceType` ='{$params['deviceType']}' ,";
	
	$queryt_t = "INSERT INTO  `authkeys` 
	SET `auth_key`='{$auth_key}',
	".$deviceType."
	`token`='{$params['token']}',
	".$user_id . $admin_id."";
	mysqli_query($con,$queryt_t) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if(mysqli_affected_rows($con)==0)
	return "";
	return $auth_key;
}

/* 21:------------------------------method start here getCategories 21------------------------------*/
static function getCategories($params)
 {
	$con =$params['dbconnection'];	
	$categories=array();   
	 $query = "SELECT 
	*
	FROM  `category` 
	ORDER BY `id` ASC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			   unset($row['type']);
			   if($params['langset']=='0')
			   unset($row['name_ar']);else {$row['name']=$row['name_ar'];  unset($row['name_ar']);};
			   if(empty(DbMethods::checkImageSrc($row['image_on']))) $row['image_on']='';
			   if(empty(DbMethods::checkImageSrc($row['image_off']))) $row['image_off']='';	
			   unset($row['status']); 
			   $categories[] = $row;
		 }
	}
	if(!empty($categories))
	return $categories;
	return '';
	
 }
 

 
   /* 21:------------------------------method start here getCoordinators 21------------------------------*/
static function getCoordinators($params)
 {
	$con =$params['dbconnection'];	
	$coordinators=array();  
	$query = "SELECT 
	`id`,
	`name`,
	`email`
	FROM  `admin` WHERE `type`='1' 
	AND `id` NOT IN(SELECT `admin_id` FROM `branches` WHERE `admin_id` IS NOT NULL)
	ORDER BY `id` DESC LIMIT 0,100 ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 $coordinators[] = $row;
	}
	if(!empty($coordinators))
	return $coordinators;
	return '';
 }
 
 /* 21:------------------------------method start here getDrivers 21------------------------------*/
static function getDrivers($params)
 {
	$con =$params['dbconnection'];	
	$drivers=array();   
	 $query = "SELECT 
	`id`,
	`name`,
	`email`
	FROM  `admin` WHERE `type`='2'
	ORDER BY `id` DESC LIMIT 0,200 ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 $drivers[] = $row;
	}
	if(!empty($drivers))
	return $drivers;
	return '';
 }

/* 21:------------------------------method start here getCategories 21------------------------------*/
static function getNewOffers($params)
 {
	$con =$params['dbconnection'];	
	$offers=array(); 
	
	$query = "SELECT 
	*
	FROM  `menu_items` 
	WHERE `offer`='1' 
	ORDER BY `id` DESC LIMIT 0,10 ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			unset($row['category_id']);
			unset($row['offer']);
			if($params['langset']=='0')
			unset($row['name_ar'],$row['description_ar']);
			else { $row['name']=$row['name_ar']; $row['description']=$row['description_ar'];  unset($row['name_ar'],$row['description_ar']);};
			if(empty(DbMethods::checkImageSrc($row['icon']))) $row['icon']='';
			if(empty(DbMethods::checkImageSrc($row['image']))) $row['image']='';
		    $offers[] = $row;
		 }
	}
	
	if(!empty($offers))
	return $offers;
	else return '';
 } 
 
   /*:-------------------------------method start here addBranch ------------------------------*/
static function addBranch($params)
 {
	$con =$params['dbconnection'];
	$coordinator_id="";
	$start_1_timing='';
	$end_1_timing='';
	$start_2_timing='';
	$end_2_timing='';
	$start_3_timing='';
	$end_3_timing='';
	
	if(!empty($params['coordinator_id'])){   $coordinator_id=" `admin_id`='{$params['coordinator_id']}' ,";  }
	if(!empty($params['start_1_timing'])){   $start_1_timing=" `start_1_timing`='{$params['start_1_timing']}' ,";  }
	if(!empty($params['end_1_timing'])){   $end_1_timing=" `end_1_timing`='{$params['end_1_timing']}' ,";  }
	if(!empty($params['start_2_timing'])){   $start_2_timing=" `start_2_timing`='{$params['start_2_timing']}' ,";  }
	if(!empty($params['end_2_timing'])){   $end_2_timing=" `end_2_timing`='{$params['end_2_timing']}' ,";  }
	if(!empty($params['start_3_timing'])){   $start_3_timing=" `start_3_timing`='{$params['start_3_timing']}' ,";  }
	if(!empty($params['end_3_timing'])){   $end_3_timing=" `end_3_timing`='{$params['end_3_timing']}' ,";  }
	
	//if(DbMethods::checkBranchName($params)=='alreadyexist') return  'alreadyexist';
	$queryi = "INSERT INTO `branches` SET  
	`name`='{$params['name']}',
	`name_ar`='{$params['name_ar']}',
	`address`='{$params['address']}',
	".$coordinator_id."
	".$start_1_timing."
	".$end_1_timing."
	".$start_2_timing."
	".$end_2_timing."
	".$start_3_timing."
	".$end_3_timing."
	`latitude`='{$params['latitude']}',
	`longitude`='{$params['longitude']}'" ;
	mysqli_query($con,$queryi) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if(mysqli_affected_rows($con)==0)
	return "";
	$id=mysqli_insert_id($con);
	return array("id"=>(int)$id) ;
	
 }
 
/*:-------------------------------method start here updateBranch ------------------------------*/
static function updateBranch($params)
 {
	 
		$con =$params['dbconnection'];	
		$coordinator_id="";
		$name='';
		$name_ar='';
		$address='';
		$latitude='';
		$longitude='';
		$start_1_timing='';
		$end_1_timing='';
		$start_2_timing='';
		$end_2_timing='';
		$start_3_timing='';
		$end_3_timing='';
		/*if(!empty($params['name']))
		if(DbMethods::checkBranchName($params)=='alreadyexist') return  'alreadyexist';*/
		
		if(!empty($params['coordinator_id'])){   $coordinator_id=" `admin_id`='{$params['coordinator_id']}' ,";  }
		if(!empty($params['name'])){   $name=" `name`='{$params['name']}' ,";  }
		if(!empty($params['name_ar'])){   $name_ar=" `name_ar`='{$params['name_ar']}' ,";  }
		if(!empty($params['address'])){   $address=" `address`='{$params['address']}' ,";  }
		if(!empty($params['latitude'])){   $latitude=" `latitude`='{$params['latitude']}' ,";  }
		if(!empty($params['longitude'])){   $longitude=" `longitude`='{$params['longitude']}' ,";  }
		
		if(!empty($params['start_1_timing'])){   $start_1_timing=" `start_1_timing`='{$params['start_1_timing']}' ,";  }
		else
		$start_1_timing=" `start_1_timing`=NULL ,";
		if(!empty($params['end_1_timing'])){   $end_1_timing=" `end_1_timing`='{$params['end_1_timing']}' ,";  }
		else
		$end_1_timing=" `end_1_timing`=NULL ,";
		if(!empty($params['start_2_timing'])){   $start_2_timing=" `start_2_timing`='{$params['start_2_timing']}' ,";  }
		else
		$start_2_timing=" `start_2_timing`=NULL ,";
		if(!empty($params['end_2_timing'])){   $end_2_timing=" `end_2_timing`='{$params['end_2_timing']}' ,";  }
		else
		$end_2_timing=" `end_2_timing`=NULL ,";
		if(!empty($params['start_3_timing'])){   $start_3_timing=" `start_3_timing`='{$params['start_3_timing']}' ,";  }
		else
		$start_3_timing=" `start_3_timing`=NULL ,";
		if(!empty($params['end_3_timing'])){   $end_3_timing=" `end_3_timing`='{$params['end_3_timing']}' ,";  }
		else
		$end_3_timing=" `end_3_timing`=NULL ,";
		
		
	
		
		   $query= "UPDATE `branches` SET 
		".$coordinator_id."
		".$name."
		".$name_ar."
		".$address."
		".$latitude."
		".$longitude."
		".$start_1_timing."
		".$end_1_timing."
		".$start_2_timing."
		".$end_2_timing."
		".$start_3_timing."
		".$end_3_timing."
		`id`='{$params['id']}' 
		WHERE `id`='{$params['id']}'"	;
		mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		/*if(mysqli_affected_rows($con)==0)
		return "";*/
		
		return 'updated';
 }
 
 /*:-------------------------------method start here checkBranchName ------------------------------*/
static function checkBranchName($params)
 {
	$con =$params['dbconnection'];	
	
	$querysub="";
	if($params['id'] !='')
	$querysub="  AND 	`id` !='{$params['id']}' ";
	
	 $query = "SELECT 
	`id`
	FROM  `branches` 
	WHERE `name`='{$params['name']}' ".$querysub."";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	return "alreadyexist";
	else
	return NULL;
 }
 
/*:-------------------------------method start here deleteBranch ------------------------------*/
static function deleteBranch($params)
 {
	$con =$params['dbconnection'];	
	$queryd = "DELETE  FROM  `branches`  WHERE `id`='{$params['id']}'";
	mysqli_query($con,$queryd) ;
	if(mysqli_affected_rows($con)==0)
	return "";
	return "removed";
 }
 
/* 21:------------------------------method start here getAllBranches 21------------------------------*/
static function getAllBranches($params)
 {
	$con =$params['dbconnection'];	
	$branches=array(); 
	$branchesCount="";
	$search="";
	if($params['search']!="")
	$search="WHERE (b.`name` LIKE '%".$params['search']."%')";
	
	
	if($params['admin_id'] !="")
	{
		$query = "SELECT COUNT(b.`id`) as branchesCount  FROM  `branches` 
		as b INNER JOIN `admin` as a ON(b.`admin_id`=a.`id`)".$search."";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		 return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 $row = mysqli_fetch_assoc($result);	
			 $branchesCount=$row['branchesCount'];
		}
	}
	
	$query = "SELECT 
	b.*,
	a.`name` as branchManagerName
	FROM  `branches` as b INNER JOIN `admin` as a ON(b.`admin_id`=a.`id`)
	".$search."
	ORDER BY  b.`id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
				$row['coordinator_id']=$row['admin_id'];
				unset($row['admin_id']);
				$branches[] = $row;
		 }
	}
	
	if(!empty($branches))
	{
		if( $branchesCount !="")
		return array("branches"=>$branches ,"branchesCount"=>$branchesCount ) ;
		return $branches;
	}
	else return '';
 } 
 
 
 /* 21:------------------------------method start here getCategories 21------------------------------*/
static function getBranches($params)
 {
	
	$con =$params['dbconnection'];	
	$branches=array(); 
	
	$subquery="";
	if($params['admin_id'] =='')
	{
		if($params['localTime']=='')
		$params['localTime']='02:00:00';
		$subquery="WHERE (('{$params['localTime']}' between CAST(`start_1_timing` AS TIME) AND CAST(`end_1_timing` AS TIME))
		OR ('{$params['localTime']}' between CAST(`start_2_timing` AS TIME) AND CAST(`end_2_timing` AS TIME))
		OR ('{$params['localTime']}' between CAST(`start_3_timing` AS TIME) AND CAST(`end_3_timing` AS TIME)))";
	}
	
	$query = "SELECT 
	`id`,
	`admin_id` as coordinator_id,
	`name`,
	`name_ar`,
	`address`,
	`latitude`,
	`longitude`
	FROM  `branches` 
	".$subquery."
	ORDER BY `id` DESC LIMIT 0,100 ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			
			if($params['langset']=='0')
			unset($row['name_ar']);else {$row['name']=$row['name_ar'];  unset($row['name_ar']);}; 
			$row['localTime']=$params['localTime'];
		    $branches[] = $row;
		 }
	}
	
	if(!empty($branches))
	return $branches;
	else return '';
 } 

/* 21:------------------------------method start here getPreOrder 21------------------------------*/
static function getPreOrder($params)
 {
	    $categories=array();
		$offers=array();
		$branches=array();
		$getDefaults=array();
		$categories=DbMethods::getCategories($params); 
		$offers=DbMethods::getNewOffers($params);
		$branches=DbMethods::getBranches($params);
		$getDefaults=DbMethods::getDefaults($params);
		return array("categories"=>$categories ,"offers"=>$offers ,"branches"=>$branches,"getDefaults"=>$getDefaults) ;
 }
 
 /* 21:------------------------------method start here getCategoryItems 21------------------------------*/
static function getCategoryItems($params)
 {
	$con =$params['dbconnection'];	
	$menu_items=array(); 
	 $query = "SELECT 
	*
	FROM  `menu_items` 
	WHERE `category_id` ='{$params['category_id']}'
	ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			if($params['langset']=='0')
			unset($row['name_ar'],$row['description_ar']);
			else { $row['name']=$row['name_ar']; $row['description']=$row['description_ar'];  unset($row['name_ar'],$row['description_ar']);};
			if(empty(DbMethods::checkImageSrc($row['icon']))) $row['icon']='';
			if(empty(DbMethods::checkImageSrc($row['image']))) $row['image']='';	
			
			unset($row['offer']); 
			$menu_items[] = $row;
		 }
	}
	
	if(!empty($menu_items))
	return $menu_items;
	else return '';
	 
 }
 /*:-------------------------------method start here addMenuItem ------------------------------*/
static function addMenuItem($params)
 {
	$con =$params['dbconnection'];
	$params['offer']='0';
	$query = "SELECT 
	`type`
	FROM  `category` 
	WHERE `id`='{$params['category_id']}' ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
	     $row = mysqli_fetch_assoc($result);	
		 if($row['type']=='1')
		 $params['offer']='1';
		 $queryi = "INSERT INTO `menu_items` SET  
		`category_id`='{$params['category_id']}', 
		`name`='{$params['name']}',
		`name_ar`='{$params['name_ar']}',
		`price`='{$params['price']}',
		`icon`='{$params['icon']}',
		`image`='{$params['image']}',
		`offer`='{$params['offer']}',
		`description`='{$params['description']}',
		`description_ar`='{$params['description_ar']}'" ;
		mysqli_query($con,$queryi) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if(mysqli_affected_rows($con)==0)
		return "";
		$id=mysqli_insert_id($con);
		return array("id"=>(int)$id) ;
	}
	else
	return "";
 }
  /*:-------------------------------method start here updateMenuItem ------------------------------*/
static function updateMenuItem($params)
 {
	$con =$params['dbconnection'];	
	$name='';
	$name_ar='';
	$price='';
	$icon='';
	$image='';
	$description='';
	$description_ar='';
	
	if(!empty($params['name'])){   $name=" `name`='{$params['name']}' ,";  }
	if(!empty($params['name_ar'])){   $name_ar=" `name_ar`='{$params['name_ar']}' ,";  }
	if(!empty($params['price'])){   $price=" `price`='{$params['price']}' ,";  }
	if(!empty($params['icon'])){   $icon=" `icon`='{$params['icon']}' ,";  }
	if(!empty($params['image'])){   $image=" `image`='{$params['image']}' ,";  }
	if(!empty($params['description'])){   $description=" `description`='{$params['description']}' ,";  }
	if(!empty($params['description_ar'])){   $description_ar=" `description_ar`='{$params['description_ar']}' ,";  }
	
	 $query= "UPDATE `menu_items` SET 
	".$name."
	".$name_ar."
	".$price."
	".$icon."
	".$image."
	".$description."
	".$description_ar."
	`id`='{$params['id']}' 
	WHERE `id`='{$params['id']}'"	;
	mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	/*if(mysqli_affected_rows($con)==0)
	return "";*/
	
	return 'updated';
 }
  /*:-------------------------------method start here deleteMenuItem ------------------------------*/
static function deleteMenuItem($params)
 {
	$con =$params['dbconnection'];	
	$queryd = "DELETE  FROM  `menu_items`  WHERE `id`='{$params['id']}'";
	mysqli_query($con,$queryd) ;
	if(mysqli_affected_rows($con)==0)
	return "";
	return "removed";
 }
  /*:-------------------------------method start here getMenuItems ------------------------------*/
static function getMenuItems($params)
 {
	$con =$params['dbconnection'];	
	$menu_items=array(); 
	$category_id='';
	$search="";
	if($params['category_id'] !='')
	{
		$category_id="WHERE	`category_id` ='{$params['category_id']}' ";  
		if($params['search']!="")
		$search=" AND (`name` LIKE '%".$params['search']."%' OR `name_ar` LIKE '%".$params['search']."%')";
	}
	else if($params['search']!="")
	$search="WHERE (`name` LIKE '%".$params['search']."%' OR `name_ar` LIKE '%".$params['search']."%')";
	
	$menu_itemsCount="";
	if($params['admin_id'] !="")
	{
		$query = "SELECT COUNT(`id`) as menu_itemsCount  FROM  `menu_items` ".$category_id." ".$search." ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		 return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 $row = mysqli_fetch_assoc($result);	
			 $menu_itemsCount=$row['menu_itemsCount'];
		}
	}
	
	$query = "SELECT 
	*
	FROM  `menu_items` 
	".$category_id." ".$search." 
	ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			  if(empty(DbMethods::checkImageSrc($row['icon']))) $row['icon']='';
			  if(empty(DbMethods::checkImageSrc($row['image']))) $row['image']='';	 
			  $menu_items[] = $row;
		 }
	}
	
	if(!empty($menu_items))
	{
		if( $menu_itemsCount !="")
		return array("menu_items"=>$menu_items ,"menu_itemsCount"=>$menu_itemsCount ) ;
		return $menu_items;
	}
	else return '';
 }
 
  /*:-------------------------------method start here getOffers ------------------------------*/
static function getOffers($params)
 {
	$con =$params['dbconnection'];	
	$menu_items=array(); 
	
	$search="";
	if($params['search']!="")
	$search=" AND (`name` LIKE '%".$params['search']."%')";
	
	
	$offerCount="";
	if($params['admin_id'] !="")
	{
		$query = "SELECT COUNT(`id`) as offerCount  FROM  `menu_items` WHERE `offer`='1' ".$search."";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		 return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 $row = mysqli_fetch_assoc($result);	
			 $offerCount=$row['offerCount'];
		}
	}
	
	$query = "SELECT 
	*
	FROM  `menu_items` 
	WHERE `offer`='1'  ".$search."
	ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			unset($row['category_id']);
			unset($row['offer']);
			if(empty(DbMethods::checkImageSrc($row['icon']))) $row['icon']='';
			if(empty(DbMethods::checkImageSrc($row['image']))) $row['image']='';
		    $menu_items[] = $row;
		 }
	}
	
	if(!empty($menu_items))
	{
		if( $offerCount !="")
		return array("menu_items"=>$menu_items ,"offerCount"=>$offerCount ) ;
		return $menu_items;
	}
	else return '';
 }
  
  /*:-------------------------------method start here addOrder ------------------------------*/
static function addOrder($params)
 {
	$con =$params['dbconnection'];
	
	$delivery_charges="";
	$tax="";
	$accepted_delivery_time="";
	$estimated_carry_out_time="";
	$defaults = "SELECT 
	`id`,
	`tax`,
	`accepted_delivery_time`,
	`estimated_carry_out_time`,
	`delivery_charges`
	FROM  `defaults` 
	ORDER BY `id` DESC LIMIT 0,1 ";
	$resultd = mysqli_query($con,$defaults) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($resultd) > 0) 
	{
		$rowd = mysqli_fetch_assoc($resultd);
		
		if($params['order_type']=='delivery' && $rowd['delivery_charges'] !=NULL)
		$delivery_charges="`delivery_charges`='{$rowd['delivery_charges']}',";
		
		if($rowd['tax'] !=NULL)
		$tax="`tax`='{$rowd['tax']}',";
		if($params['order_type']=='delivery')
		{	
			if($rowd['accepted_delivery_time'] !=NULL)
			$accepted_delivery_time="`accepted_delivery_time`='{$rowd['accepted_delivery_time']}',";
			else
			$accepted_delivery_time="`accepted_delivery_time`='1',";
			
		}
		else if($params['order_type']=='carry_out')
		{
			if($rowd['estimated_carry_out_time'] !=NULL)
			$estimated_carry_out_time="`estimated_carry_out_time`='{$rowd['estimated_carry_out_time']}',";
			else
			$estimated_carry_out_time="`estimated_carry_out_time`='1',";
		}
	}

    $discount='';
	$discounttype='';
	if($params['code'] !="")
	{
		$checkCode=DbMethods::checkCode($params);
	    if($checkCode=='' || $checkCode ==NULL ) return  'promoCodeNotvalid';
		else if($checkCode['type']=='flat')
		{
		 if(!empty($checkCode['discount']) || $checkCode['discount'] !=''){   $discount=" `discount`='{$checkCode['discount']}' ,";  }
		}
		else
		$discounttype=$checkCode['type'];
		
	}
	$totalamount="";
	$con->begin_transaction();
	try { 
			$latlong="";
			if($params['latitude'] !="" && $params['longitude'] !="")
			$latlong="`latitude`='{$params['latitude']}', `longitude`='{$params['longitude']}',";
			 
			 $query = "INSERT INTO `orders` SET  
			`user_id`='{$params['user_id']}',
			`branche_id`='{$params['branche_id']}',
			`order_type`='{$params['order_type']}',
			".$latlong."
			".$tax."
			".$discount."
			".$delivery_charges."
			".$accepted_delivery_time."
			".$estimated_carry_out_time."
			`payment_order`='{$params['payment_order']}'" ;
			mysqli_query($con,$query) ;
			if (mysqli_error($con) != '')
			return  "mysql_Error:-".mysqli_error($con);
			if(mysqli_affected_rows($con)==0)
			throw new Exception(mysqli_error($con));
			$order_id=mysqli_insert_id($con);
			
			$order_items = stripslashes($params['order_items']);
			$order_items = json_decode($order_items,true);	
			$outletObj=array();
			for($i=0;$i<count($order_items); $i++)
			{
				$queryid = "SELECT 
				`price`
				FROM  `menu_items` 
				WHERE `id`='{$order_items[$i]['menu_item_id']}'";
				$resultid = mysqli_query($con,$queryid) ;
				if (mysqli_error($con) != '')
				throw new Exception(mysqli_error($con));
				if (mysqli_num_rows($resultid) > 0) 
				{
					$rowid = mysqli_fetch_assoc($resultid);
					 $amount=($order_items[$i]['quantity'] * $rowid['price']);
					 $totalamount= $totalamount + ($order_items[$i]['quantity'] * $rowid['price']);
					 $queryi = "INSERT INTO `order_items` SET  
					`order_id`='{$order_id}',
					`menu_item_id`='{$order_items[$i]['menu_item_id']}',
					`quantity`='{$order_items[$i]['quantity']}',
					`amount`='{$amount}'" ;
					 mysqli_query($con,$queryi) ;
					 if (mysqli_error($con) != '')
					 throw new Exception(mysqli_error($con));
				}
				else
				throw new Exception('mysql_Error:- menu_item_id is wrong');
			}
			$con->commit();
			
			if($discounttype=='percentage')
			{
				$discount=($checkCode['discount'] / 100) * $totalamount;
				if($discount !='')
				mysqli_query($con,"UPDATE `orders` SET `discount`='{$discount}' WHERE `id`='{$order_id}'") ;
			}
			
			if(!empty($checkCode['id']))
			{
			  mysqli_query($con,"INSERT INTO `used_promo_codes` SET `promo_code_id`='{$checkCode['id']}', `user_id`='{$params['user_id']}'") ;	
			}
			
			$authorization=$params['Authorization'];
			$dbconnection=$params['dbconnection'];
			$apiBasePath=$params['apiBasePath'];
			unset($params);
			$params['order_id']=$order_id;
			$params['status']='0';
			$params['Authorization']=$authorization;
			$params['dbconnection']=$dbconnection;
			$params['apiBasePath']=$apiBasePath;
			DbMethods::changeOrderStatus($params);
			return array("order_id"=>(int)$order_id ) ;
			
	} catch(\Exception $e) {
		$con->rollBack();
		throw $e;
	} catch(\Throwable $e) {
		$con->rollBack();
		throw $e;
	}
 }
 
  /*:-------------------------------method start here cancelOrder ------------------------------*/
static function cancelOrder($params)
 {
	$con =$params['dbconnection'];
	$query = "SELECT 
	`id`,
	`user_id`,
	`status`
	FROM  `orders` 
	WHERE `id`='{$params['order_id']}' AND `user_id`='{$params['user_id']}' AND (`status`='0' OR `status`='1')";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 $query = "UPDATE `orders` SET  
		`cancel`='1'
		WHERE `id`='{$params['order_id']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		
		/*$dir=$params['apiBasePath']."addNotification";
		DbMethods:: post_async($dir ,array('receiver_id'=>$params['receiver_id'],'order_id'=>$params['order_id'],
		'title'=>$params['title'],'message'=>$params['message'] ,'type'=>$params['status']  ,'Authorization'=>$params['Authorization']));*/
	    return "updated";
	}
	else
	return "wrongStatus";
 }
  /*:-------------------------------method start here changeOrderStatus ------------------------------*/
static function changeOrderStatus($params)
 {
	
	$con =$params['dbconnection'];
	 $query = "SELECT 
	`id`,
	`user_id`,
	`status`,
	`order_type`
	FROM  `orders` 
	WHERE `id`='{$params['order_id']}' AND `cancel`='0' ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
	    $row = mysqli_fetch_assoc($result);	
		$params['receiver_id']=$row['user_id'];
		$params['title']="Order Status";
		$params['message']='';
		$params['message_ar']='';
		if($params['status']==0)
		{
		   $params['message']="Your Order # ".$params['order_id']." has been placed.";
		   $params['message_ar']="لقد تم وضع طلبك رقم ".$params['order_id']."";
		}
		elseif($params['status']==1)
		{
		  $params['message']="Your Order # ".$params['order_id']." has been confirmed.";
		  $params['message_ar']="لقد تم تأكيد طلبك رقم ".$params['order_id']."";
		}
		elseif($params['status']==2)
		{
		  $params['message']="Your order # ".$params['order_id']." is now in the oven.";
		  $params['message_ar']="طلبك رقم ".$params['order_id']." هو الآن في الفرن";
		}
		elseif($params['status']==3)
		{
		  if($row['order_type']=='delivery')	
		  {
		     $params['message']="Your Order # ".$params['order_id']." is now ready.";
			  $params['message_ar']="طلبك رقم ".$params['order_id']." جاهز الآن";
		  }
		  else
		  {
		    $params['message']="Your Order # ".$params['order_id']." is now ready. Please pickup your order from the respective branch.";
		    $params['message_ar']="طلبك رقم ".$params['order_id']." جاهز الآن. يرجى التقاط طلبك من الفرع المحدد";
		  }
		}
		
		elseif($params['status']==4 && $row['order_type']=='carry_out')
		{
			 $params['message']="Your Order # ".$params['order_id']." has been successfully completed. We hope you enjoy your meal."; 
		     $params['message_ar']="اكتمل طلبك رقم ".$params['order_id']." بنجاح. نأمل أن تستمتع بوجبتك";
		}
		
		elseif($params['status']==5 && $row['order_type']=='delivery')
		{
			 $params['message']="Your Order # ".$params['order_id']." is on your way. The delivery guy will call you once he’s nearby. ";
		     $params['message_ar']="طلبك رقم ".$params['order_id']." في طريقك. سيتصل بك صاحب التوصيل بمجرد الوصول إليه";
		}
		
		elseif($params['status']==6 && $row['order_type']=='delivery')
		{
			  $params['message']="Your Order # ".$params['order_id']." has been delivered. We hope you enjoy your meal.";
			  $params['message_ar']="تم تسليم طلبك رقم ".$params['order_id'].". نأمل أن تستمتع بوجبتك";
		}
		
		elseif($params['status']==6 && $row['order_type']=='carry_out')
		{
			   $params['message']="Your Order # ".$params['order_id']." has been delivered. We hope you enjoy your meal."; 
			  $params['message_ar']="تم تسليم طلبك رقم ".$params['order_id'].". نأمل أن تستمتع بوجبتك";
		}
	
	if($params['status'] =='1' || $params['status'] =='2' || $params['status'] =='3' || $params['status'] =='4' || $params['status'] =='5' || $params['status'] =='6')
	{
			if($params['status']!='6')
			if((((int)$row['status'] + 1)!= (int)$params['status']) || ($params['status']=='6'))
			return "wrongStatus";
			 $query = "UPDATE `orders` SET  
			`status`='{$params['status']}'
			WHERE `id`='{$params['order_id']}'";
			$result = mysqli_query($con,$query) ;
			if (mysqli_error($con) != '')
			return  "mysql_Error:-".mysqli_error($con);
			if(mysqli_affected_rows($con)==0)
			return "";
	}
		
		
		if($params['message'] !='')
		{
			$dir=$params['apiBasePath']."addNotification";
			DbMethods:: post_async($dir ,array('receiver_id'=>$params['receiver_id'],'order_id'=>$params['order_id'],
			'title'=>$params['title'],'message'=>$params['message'] ,'message_ar'=>$params['message_ar'],'langset'=>$params['langset'],'type'=>$params['status']  ,'Authorization'=>$params['Authorization'] ));
		}
		
	    return "updated";
	}
 }
  /*:-------------------------------method start here assignOrderToDriver ------------------------------*/
static function assignOrderToDriver($params)
 {
		$con =$params['dbconnection'];
		$query = "SELECT 
		`id`
		FROM  `orders` 
		WHERE `id`='{$params['order_id']}' AND `status`='3' AND `driver_id` IS NULL
		AND '{$params['driver_id']}'=(SELECT `id` FROM `admin` WHERE `type`='2' AND `id`='{$params['driver_id']}')";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
			{
				$query = "UPDATE `orders` SET  
				`status`='4',
				`driver_id`='{$params['driver_id']}'
				WHERE `id`='{$params['order_id']}'";
				$result = mysqli_query($con,$query) ;
				if (mysqli_error($con) != '')
				return  "mysql_Error:-".mysqli_error($con);
				if(mysqli_affected_rows($con)==0)
				return "";
				$params['status']='4';
				//DbMethods::changeOrderStatus($params);
				
				$params['type']='7';
				$params['title']="Order Status";
				$params['message']="Order # ".$params['order_id']." has been assigned to you for delivery.";
				$params['message_ar']="مفقود عربي";
				DbMethods::addNotification($params);
				return "updated";
		}
		else
		return "not_ready";
 }
 
  /*:-------------------------------method start here getCustomers ------------------------------*/
static function getCustomers($params)
 {
	 
	$con =$params['dbconnection'];	
	$users=array(); 
	$usersCount="";
	$search="";
	if($params['search']!="")
	$search="WHERE (`name` LIKE '%".$params['search']."%')";
	
	if($params['admin_id'] !="")
	{
		$query = "SELECT COUNT(`id`) as usersCount  FROM  `users` ".$search." ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		 return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 $row = mysqli_fetch_assoc($result);	
			 $usersCount=$row['usersCount'];
		}
	}
	
	
	$query = "SELECT 
	*
	FROM  `users` 
	".$search."
	ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			 unset($row['password']);
			 unset($row['password_reset_token']);
		    $users[] = $row;
		 }
	}
	
	if(!empty($users))
	{
		if( $usersCount !="")
		return array("users"=>$users ,"usersCount"=>$usersCount ) ;
		return $users;
	}
	else return '';
 
	 
 }
 
 /*:-------------------------------method start here getOrders ------------------------------*/
static function getOrders($params)
 {
	$con =$params['dbconnection'];	
	$orders=array(); 
	$user_id='';
	$status="";
	$cancel="0";
	$coordinator_id='';
	$search="";
		
	if($params['user_id'] !='')
	$user_id="  AND 	`user_id` ='{$params['user_id']}' ";  
	
	if($params['type'] =='0' || $params['type'] =='1'  || $params['type'] =='2' || $params['type'] =='3'  || $params['type'] =='4'  || $params['type'] =='5'
	|| $params['type'] =='6')
	$status=" AND `status` ='{$params['type']}'";
	else if($params['type'] =='cancel')
	$cancel="1"; 
	
	if($params['user_type'] =='1')
	$coordinator_id="  AND 	b.`admin_id` ='{$params['admin_id']}' "; 
	
	if($params['search']!="")
	$search=" AND (u.`name` LIKE '%".$params['search']."%' OR u.`phone` = '{$params['search']}')"; 
	
	$ordersCount="";
	if($params['admin_id'] !="")
	{
		$query = "SELECT COUNT(o.`id`) as ordersCount  
		FROM `orders` as o
		INNER JOIN `users` as u ON(o.`user_id`=u.`id`) 
		INNER JOIN `branches` as b ON(o.`branche_id`=b.`id`) 
		LEFT OUTER JOIN `users` as d ON(o.`driver_id`=d.`id`)  
		WHERE  `cancel`='{$cancel}' AND `status` !='' ".$status."  ".$user_id."  ".$coordinator_id." ".$search."";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		 return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 $row = mysqli_fetch_assoc($result);	
			 $ordersCount=$row['ordersCount'];
		}
	}
	
	$query = "SELECT 
	o.*,
	u.`name` as userName,
	b.`name` as branchName,
	d.`name` as driverName
	FROM  `orders`  as o
	INNER JOIN `users` as u ON(o.`user_id`=u.`id`) 
	INNER JOIN `branches` as b ON(o.`branche_id`=b.`id`) 
	LEFT OUTER JOIN `admin` as d ON(o.`driver_id`=d.`id`)  
	WHERE o.`cancel`='{$cancel}' AND o.`status` !='' ".$status." ".$user_id."  ".$coordinator_id." ".$search." 
	ORDER BY o.`id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			$row['accepted_delivery_time']=(int)$row['accepted_delivery_time'];
			$row['estimated_carry_out_time']=(int)$row['estimated_carry_out_time'];
		    $orders[] = $row;
		 }
	}
	
	if(!empty($orders))
	{
		if( $ordersCount !="")
		return array("orders"=>$orders ,"ordersCount"=>$ordersCount ) ;
		return $orders;
	}
	else return '';
 
 }
 
  /*:-------------------------------method start here getOrderDetail ------------------------------*/
static function getOrderDetail($params)
 {
	$con =$params['dbconnection'];	
	//$orders=array(); 
	$items=array();
	$rating=NULL;
	$cancel=" AND o.`cancel`='0'";
	if($params['admin_id'] !='')
	$cancel="";
	 
	$query = "SELECT 
	o.*,
	u.`name`,
	u.`email`,
	u.`phone`,
	a.`name` as driverName,
	a.`email` as driverEmail,
	a.`phone` as driverPhone,
	b.`name` as branchName,
	b.`latitude` as branchLat,
	b.`longitude` as branchLong
	FROM  `orders` as o 
	INNER JOIN `users` as u ON(o.`user_id`=u.`id`) 
	INNER JOIN `branches` as b ON(o.`branche_id`=b.`id`) 
	LEFT OUTER JOIN `admin` as a ON(o.`driver_id`=a.`id`) 
	WHERE o.`id`= '{$params['order_id']}' ".$cancel." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		$row = mysqli_fetch_assoc($result);
		
		$row['accepted_delivery_time']=(int)$row['accepted_delivery_time'];
		$row['estimated_carry_out_time']=(int)$row['estimated_carry_out_time'];
		
		$queryi = "SELECT 
		ot .`id`,
		ot .`menu_item_id`,
		ot .`quantity`,
		ot .`amount`,
		mt.`name`,
		mt.`name_ar`,
		mt.`price`
		FROM  `order_items` as ot INNER JOIN `menu_items` as mt On(ot.`menu_item_id`=mt.`id`) 
		WHERE ot .`order_id` ='{$params['order_id']}'";
		$resulti = mysqli_query($con,$queryi) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($resulti) > 0) 
		{
			while ($row1 = mysqli_fetch_assoc($resulti)) 
			{
				if($params['langset']=='0')
				unset($row1['name_ar']);else {$row1['name']=$row1['name_ar'];  unset($row1['name_ar']);};
				$items[] = $row1;
			}
			$row['items']=$items;
		}
		
		
		$row['rating']=NULL;
		if($row['status']=='6')
		{
			$query = "SELECT 
			`serviceRating`,
			`mealRating`,
			`riderRating`,
			`experienceRating`,
			`serviceComment`,
			`mealComment`,
			`riderComment`,
			`experienceComment`,
			`created_at`
			FROM  `rating` 
			WHERE `order_id`= '{$row['id']}' ";
			$resultr = mysqli_query($con,$query) ;
			if (mysqli_error($con) != '')
			return  "mysql_Error:-".mysqli_error($con);
			if (mysqli_num_rows($resultr) > 0) 
			{
			   $rating = mysqli_fetch_assoc($resultr);
			   $row['rating']=$rating ;
			}
		}
		
		if(!empty($row))
		return $row;
		else return '';
	}
 }
  /*:-------------------------------method start here getUserCurrentOrders ------------------------------*/
static function getUserCurrentOrders($params)
 {
	$con =$params['dbconnection'];	
	$orders=array(); 
	$query = "SELECT 
	o.*,
	b.`name` as branchName,
	b.`latitude` as branchLat,
	b.`longitude` as branchLong,
	(SELECT sum(`amount`) FROM `order_items` WHERE `order_id`=o.`id`) as total_amount
	FROM  `orders` as o 
	INNER JOIN  `order_items` as ot ON(o.`id`=ot.`order_id`)
	INNER JOIN `branches` as b ON(o.`branche_id`=b.`id`) 
	WHERE o.`status` !='6' AND o.`user_id` ='{$params['user_id']}' AND o.`cancel`='0' 
	GROUP BY ot.`order_id`
	ORDER BY o.`id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result))
		 { 
		    $row['accepted_delivery_time']=(int)$row['accepted_delivery_time'];
			$row['estimated_carry_out_time']=(int)$row['estimated_carry_out_time'];
		    $orders[] = $row;
		 }
	}
	
	if(!empty($orders))
	return $orders;
	else return '';
 }
  /*:-------------------------------method start here getUserCompletedOrders ------------------------------*/
static function getUserCompletedOrders($params)
 {
	$con =$params['dbconnection'];	
	$orders=array(); 
	$query = "SELECT 
	o.*,
	r.`id` as rating_id,
	(SELECT sum(`amount`) FROM `order_items` WHERE `order_id`=o .`id`) as total_amount
	FROM  `orders` as o INNER JOIN  `order_items` as ot ON(o.`id`=ot.`order_id`)
	LEFT OUTER JOIN  `rating` as r ON(o.`id`=r.`order_id`)
	WHERE o.`status` ='6' AND o.`user_id` ='{$params['user_id']}' AND o.`cancel`='0'  
	GROUP BY ot.`order_id`
	ORDER BY o.`id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			 
			 $row['accepted_delivery_time']=(int)$row['accepted_delivery_time'];
			  $row['estimated_carry_out_time']=(int)$row['estimated_carry_out_time'];
			 if($row['rating_id']==NULL)
			 $row['rating']='0';
			 else
			 $row['rating']='1';
			 
			 unset($row['rating_id']);
			 
		     $orders[] = $row;
		 }
	}
	
	if(!empty($orders))
	return $orders;
	else return '';
 }
  /*:-------------------------------method start here getDriverAssignedOrders ------------------------------*/
static function getDriverAssignedOrders($params)
 {
	$con =$params['dbconnection'];	
	$orders=array(); 
	$query = "SELECT 
	o.*
	FROM  `orders` as o INNER JOIN  `order_items` as ot ON(o.`id`=ot.`order_id`)
	WHERE `status` !='6' AND `driver_id` ='{$params['admin_id']}'  AND o.`cancel`='0' 
	GROUP BY ot.`order_id`
	ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 $orders[] = $row;
	}
	
	if(!empty($orders))
	return $orders;
	else return '';
 }
  /*:-------------------------------method start here getDriverCompletedOrders ------------------------------*/
static function getDriverCompletedOrders($params)
 {
	$con =$params['dbconnection'];	
	$orders=array(); 
	$query = "SELECT 
	o.*
	FROM  `orders` as o INNER JOIN  `order_items` as ot ON(o.`id`=ot.`order_id`)
	WHERE `status` ='6' AND `driver_id` ='{$params['admin_id']}' AND o.`cancel`='0'  
	GROUP BY ot.`order_id`
	ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 $orders[] = $row;
	}
	
	if(!empty($orders))
	return $orders;
	else return '';
 }
 
/*:-------------------------------method start here orderDelivered ------------------------------*/
static function orderDelivered($params)
 {
		$con =$params['dbconnection'];
	 	$query = "SELECT 
		`id`
		FROM  `orders` 
		WHERE `id`='{$params['order_id']}' AND `status`='5'  
		AND `driver_id`='{$params['admin_id']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
			{
				$params['status']='6';
				DbMethods::changeOrderStatus($params);
				return "updated";
		}
		else
		return "not_ready";
 } 
   /*:-------------------------------method start here addNotification ------------------------------*/
static function addNotification($params)
 {
		//print_r($params);
		$con =$params['dbconnection'];
		
		$id="";
		if($params['receiver_id'] !='')
		$id=" `user_id` ='{$params['receiver_id']}' ,";
		else if($params['driver_id'] !='')
		$id=" `driver_id` ='{$params['driver_id']}' , ";
		
		$query = "INSERT INTO `notifications` SET  
		 ".$id."
		`order_id`='{$params['order_id']}',
		`title`='{$params['title']}',
		`message`='{$params['message']}',
		`message_ar`='{$params['message_ar']}',
		`type`='{$params['type']}'" ;
		mysqli_query($con,$query) ;
		if(mysqli_affected_rows($con)==0)
		return "";
		
		if($params['receiver_id'] !='')
		$params['user_id']=$params['receiver_id'];
		
		
		
		DbMethods::sendNotification($params);
		return "added";
 }
 
 
 /* 1:------------------------------method start here sendNotification ------------------------------*/
/*static function sendNotification($params) 
{
	$con =$params['dbconnection'];
	
	$id="";
	if($params['user_id'] !='')
	$id=" AND  `user_id` ='{$params['user_id']}' ";
	else if($params['driver_id'] !='')
	$id=" AND  `admin_id` ='{$params['driver_id']}' ";
	
	 $query = "SELECT 
	DISTINCT(`token`) as token,
	`deviceType`,
	`language`
	FROM  `authkeys` 
	WHERE  `token` !=''  ".$id."
	ORDER BY `id` DESC LIMIT 0,2";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			//$row = mysqli_fetch_assoc($result);	
			$params['token']=$row['token'];
			$dir=$params['apiBasePath']."/pushNotification.php";
			$params['deviceType']='android';
			if($row['deviceType']=='ios')
			$params['deviceType']='ios';
			
			
			if($params['language']=='0')
			unset($params['message_ar']);
			else {$params['message']=$params['message_ar'];  unset($params['message_ar']); $params['title']="حالة الطلب";};
			
			DbMethods:: post_async($dir ,array('token'=>$params['token']
			,'title'=>$params['title'],'message'=>$params['message'] ,'type'=>$params['type'],'order_id'=>$params['order_id'],'deviceType'=>$params['deviceType']));
			
	       }
			return "added";
	}
}
*/ 
 /* 1:------------------------------method start here sendNotification ------------------------------*/
static function sendNotification($params) 
{
	//print_r($params);
	$con =$params['dbconnection'];
	$tokens_ios=array();  
	$tokens_android=array();  
	$tokens_all=array();  
	$limit="";
	$send_to="";
	$user_id="";
	if($params['send_to']==0)
	{
		$limit="LIMIT 0,1";
		if($params['user_id'] !='')
		$user_id=" AND `user_id`='{$params['user_id']}' ";
		else if($params['driver_id'] !='')
		$user_id=" AND `admin_id`='{$params['driver_id']}'";
	}
	else if($params['send_to']==1)
	{
		$send_to=" AND `deviceType`='ios'";
	}
	else if($params['send_to']==2)
	{
		$send_to=" AND `deviceType`='android'";
	}
	
	$language="";
	$query = "SELECT 
	DISTINCT(`token`) as token,
	`deviceType`,
	`language`
	FROM  `authkeys` 
	WHERE  `token` !='' ".$user_id." ".$send_to." 
	ORDER BY `id` DESC ".$limit."";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			$language=$row['language']; 
			if($row['deviceType']=='ios' )
			$tokens_ios[] = $row['token'];
			else if($row['deviceType']=='android')
			$tokens_android[] = $row['token']; 
	      }
		  
	}
	
	
	/*print_r($tokens_ios);
	echo "========";
	print_r($tokens_android);*/

	if(!empty($tokens_ios) && count($tokens_ios) > 0)
	{
		$tokens_ios=array_chunk($tokens_ios, 1000);
		
		foreach ($tokens_ios as $iostokens) {
			
			$params['token']=$iostokens;
			$dir=$params['apiBasePath']."/pushNotification.php";
			$params['deviceType']='ios';
			
			if($language=='0' && $params['send_to']==0)
			unset($params['message_ar']);
			else {$params['message']=$params['message_ar'];  unset($params['message_ar']); $params['title']="حالة الطلب";};
			
			DbMethods:: post_async($dir ,array('token'=>$params['token']
			,'title'=>$params['title'],'message'=>$params['message'] ,'type'=>$params['type'],'order_id'=>$params['order_id'],'deviceType'=>$params['deviceType']));
		
		}
	}
	
	if(!empty($tokens_android) && count($tokens_android) > 0)
	{
		$tokens_android=array_chunk($tokens_android, 1000);
		foreach ($tokens_android as $androidtokens) {
			$params['token']=$androidtokens;
			$dir=$params['apiBasePath']."/pushNotification.php";
			$params['deviceType']='android';
			
			if($language=='0' && $params['send_to']==0)
			unset($params['message_ar']);
			else {$params['message']=$params['message_ar'];  unset($params['message_ar']); $params['title']="حالة الطلب";};
			
			DbMethods:: post_async($dir ,array('token'=>$params['token']
			,'title'=>$params['title'],'message'=>$params['message'] ,'type'=>$params['type'],'order_id'=>$params['order_id'],'deviceType'=>$params['deviceType']));
		
		}
	}
	
	
return "added";
	

}
  /*:-------------------------------method start here getNotifications ------------------------------*/
static function getNotifications($params)
 {
	$con =$params['dbconnection'];	
	$notifications=array();   
	
	$id="";
	if($params['user_id'] !='')
	$id="WHERE  `user_id` ='{$params['user_id']}' ";
	else if($params['admin_id'] !='')
	$id="WHERE  `driver_id` ='{$params['admin_id']}' ";
	
	
	$query = "SELECT 
	*
	FROM  `notifications`
	".$id."
	ORDER BY `read` ASC, `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			if($params['langset']=='0')
			{
			  unset($row['message_ar']);
			}
			
			else
			{$row['message']=$row['message_ar'];  unset($row['message_ar']); $row['title']="حالة الطلب";};
		    $notifications[] = $row;
		 }
	}
	if(!empty($notifications))
	return $notifications;
	else return '';
 }
 
/* 1:------------------------------method start here getCounts 1------------------------------*/
static function getCounts($params)
 {
		$con =$params['dbconnection'];	
		$ordersCount=array();
		$coordinator_id='';
		if($params['user_type'] =='1')
		$coordinator_id="  AND 	b.`admin_id` ='{$params['admin_id']}' ";  
		
	    $query = "SELECT COUNT(o.`id`) as ordersCount,MAX(o.`id`) as maxId    
		FROM `orders` as o 
		INNER JOIN `branches` as b ON(o.`branche_id`=b.`id`) 
		INNER JOIN `users` as u ON(o.`user_id`=u.`id`) 
		WHERE o.`id` > '{$params['maxId']}' 
		AND   o.`cancel` ='0'  AND o.`status` ='0'  ".$coordinator_id."
		ORDER BY o.`id`  DESC LIMIT 0,500";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		 return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 $row1 = mysqli_fetch_assoc($result);
			 if($row1['ordersCount'] > 0)	
			 $ordersCount=$row1;
		}
		
		if(!empty($ordersCount))
		return $ordersCount;
		else return '';
 }
 
  /*:-------------------------------method start here getWebNotifications ------------------------------*/
static function getWebNotifications($params)
 {
	$con =$params['dbconnection'];	
	$orders=array();
	$coordinator_id='';
	if($params['user_type'] =='1')
	$coordinator_id="  AND 	b.`admin_id` ='{$params['admin_id']}' ";  
	
	   
	$query = "SELECT 
	o.`id`,
	o.`order_type`,
	u.`name` as userName
	FROM `orders` as o 
	INNER JOIN `branches` as b ON(o.`branche_id`=b.`id`) 
	INNER JOIN `users` as u ON(o.`user_id`=u.`id`) 
	WHERE  o.`cancel` ='0'  AND o.`status` ='0'  ".$coordinator_id."
	ORDER BY o.`id`  DESC LIMIT 0,500 ";
	$result2 = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result2) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result2)) 
		 {
		   $row['message']= "".$row['userName']." has been placed Order No 99, type ".$row['order_type']."";
		   unset($row['userName']);
		   unset($row['order_type']);
		   $orders[] = $row;
		 }
	}
	if(!empty($orders))
	return $orders;
	else return '';
 }
 
   /*:-------------------------------method start here readNotification ------------------------------*/
static function readNotification($params)
 {
	$con =$params['dbconnection'];	
	
	$query= "UPDATE `notifications` SET 
	`read`='1' 
	WHERE   (`user_id` ='{$params['user_id']}' || `driver_id` ='{$params['admin_id']}') AND `read`='0'";
	mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	
	
	return 'updated';
	
 }

  /*:-------------------------------method start here addRating ------------------------------*/
static function addRating($params)
 {
	    $con =$params['dbconnection'];
		 $query = "SELECT 
		`id`,
		`status`
		FROM  `orders` 
		WHERE `id`='{$params['order_id']}' 
		AND `user_id`='{$params['user_id']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
			{
				$row = mysqli_fetch_assoc($result);
				$query1 = "SELECT 
				`id`
				FROM  `rating` 
				WHERE `order_id`='{$params['order_id']}'";
				$result1 = mysqli_query($con,$query1) ;
				if (mysqli_error($con) != '')
				return  "mysql_Error:-".mysqli_error($con);
				if (mysqli_num_rows($result1) > 0) 
				return "alreadyexist";
				else
				{
					if($row['status'] !='6')
					return "not_completed";
					$query = "INSERT INTO `rating` SET  
					`order_id`='{$params['order_id']}',
					`serviceRating`='{$params['serviceRating']}',
					`mealRating`='{$params['mealRating']}',
					`riderRating`='{$params['riderRating']}',
					`experienceRating`='{$params['experienceRating']}',
					`serviceComment`='{$params['serviceComment']}',
					`mealComment`='{$params['mealComment']}',
					`riderComment`='{$params['riderComment']}',
					`experienceComment`='{$params['experienceComment']}'" ;
					mysqli_query($con,$query) ;
					if(mysqli_affected_rows($con)==0)
					return "";
					return "added";
				}
		}
		else
		return "";
 }
 
 
 /*:-------------------------------method start here addComplain ------------------------------*/
static function addComplain($params)
 {
	    $con =$params['dbconnection'];
		 $queryi = "INSERT INTO `complain` SET  
		`user_id`='{$params['user_id']}', 
		`type`='{$params['type']}',
		`complaint`='{$params['complaint']}'" ;
		mysqli_query($con,$queryi) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if(mysqli_affected_rows($con)==0)
		return "";
		$id=mysqli_insert_id($con);
		return array("id"=>(int)$id) ;
	
 }
 
 
  /*:-------------------------------method start here getComplaints ------------------------------*/
static function getComplaints($params)
 {
	$con =$params['dbconnection'];	
	$orders=array(); 
	$query = "SELECT 
	c.*,
	u.`name`,
	u.`email`,
	u.`phone`
	FROM  `complain` as c INNER JOIN  `users` as u ON(c.`user_id`=u.`id`)
	ORDER BY c.`id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 $orders[] = $row;
	}
	
	if(!empty($orders))
	return $orders;
	else return '';
 }
  /*:-------------------------------method start here addPromoCode ------------------------------*/
static function addPromoCode($params)
 {
	 
	 for($i=0;$i<$params['number']; $i++)
	 {
	    $con =$params['dbconnection'];
		$params['code']=dbMethods::createCode();
		 $queryi = "INSERT INTO `promo_codes` SET  
		`title`='{$params['title']}', 
		`code`='{$params['code']}',
		`discount`='{$params['discount']}',
		`type`='{$params['type']}',
		`start_date`='{$params['start_date']}',
		`end_date`='{$params['end_date']}'" ;
		mysqli_query($con,$queryi) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if(mysqli_affected_rows($con)==0)
		return "";
		$id=mysqli_insert_id($con);
		
	 }
		return "added";
	
 }
   /*:-------------------------------method start here updatePromoCode ------------------------------*/
static function updatePromoCode($params)
 {
	
	$con =$params['dbconnection'];	
	//$params['code']=dbMethods::createCode();	
	$title='';
	$code='';
	$discount='';
	$type='';
	$start_date='';
	$end_date='';
	$status='';
	
	if(!empty($params['title'])){   $title=" `title`='{$params['title']}' ,";  }
	if(!empty($params['code'])){   $code=" `code`='{$params['code']}' ,";  }
	if(!empty($params['discount'])){   $discount=" `discount`='{$params['discount']}' ,";  }
	if(!empty($params['type'])){   $type=" `type`='{$params['type']}' ,";  }
	if(!empty($params['start_date'])){   $start_date=" `start_date`='{$params['start_date']}' ,";  }
	if(!empty($params['end_date'])){   $end_date=" `end_date`='{$params['end_date']}' ,";  }
	if(!empty($params['status']) || $params['status'] !=''){   $status=" `status`='{$params['status']}' ,";  }
	
	
	
    $query= "UPDATE `promo_codes` SET 
	".$title."
	".$status."
	".$discount."
	".$type."
	".$start_date."
	".$end_date."
	".$status."
	`id`='{$params['id']}' 
	WHERE `id`='{$params['id']}'"	;
	mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	/*if(mysqli_affected_rows($con)==0)
	return "";*/
	
	return 'updated';
 }
 /*:-------------------------------method start here updatePromoCode ------------------------------*/
static function deletePromoCode($params)
 {
	$con =$params['dbconnection'];	
	$queryd = "DELETE  FROM  `promo_codes`  WHERE `id`='{$params['id']}'";
	mysqli_query($con,$queryd) ;
	if(mysqli_affected_rows($con)==0)
	return "";
	return "removed";
 }
  /*:-------------------------------method start here getPromoCodes ------------------------------*/
static function getPromoCodes($params)
 {
	
	$con =$params['dbconnection'];	
	$promoCodes=array(); 
	$search="";
	if($params['search']!="")
	$search="WHERE (`title` LIKE '%".$params['search']."%')";
	
	$promoCodesCount="";
	if($params['admin_id'] !="")
	{
		$query = "SELECT COUNT(`id`) as promoCodesCount  FROM  `promo_codes` ".$search." ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		 return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 $row = mysqli_fetch_assoc($result);	
			 $promoCodesCount=$row['promoCodesCount'];
		}
	}
	
	$query = "SELECT 
	*,
	CAST(`start_date` as DATE) as start_date,
	CAST(`end_date` as DATE) as end_date
	FROM  `promo_codes`
	".$search." 
	ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
			  $promoCodes[] = $row;
		 }
	}
	
	if(!empty($promoCodes))
	{
		if( $promoCodesCount !="")
		return array("promoCodes"=>$promoCodes ,"promoCodesCount"=>$promoCodesCount ) ;
		return $promoCodes;
	}
	else return '';
  }
  
 /* 1:------------------------------method start here checkCode ---------------------------1*/
static function checkCode($params) 
{
		$con =$params['dbconnection'];	
		
		
		 $query = "SELECT p.`id`
		FROM  `promo_codes`  as p INNER JOIN `used_promo_codes` pu ON(p.`id`=pu.`promo_code_id`) 
		WHERE p.`code`='{$params['code']}' AND pu.`user_id`='{$params['user_id']}'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		return '';
		
		
		$query = "SELECT `id`,`code`,`discount`,`type` FROM  `promo_codes` WHERE 
		`code`='{$params['code']}' 
		AND now() >= `start_date` and now() <= `end_date` 
		AND `status`='0'";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		return  $row = mysqli_fetch_assoc($result);	
		else
		return '';
} 


/* 1:------------------------------method start here addUser ------------------------------*/		
 public function createCode()
    {
		try{
			$datetime=  date('Y-m-d H:i:s');
			$string_1 = "";
			$chars = "AB1CD2EF3GH4IJ5KL6MN7OP8QR9ST0UVWXYZabcdefghijklmnopqrstuvwxyz";
			for($k=0;$k<10;$k++)
			$string_1.=substr($chars,rand(0,strlen($chars)),1);
			
			$string_f=substr($string_1,-1).substr(md5($datetime .$string_1.substr($string_1,1)),-5) ;
			return $string_f;
		}
			catch(\Exception $e){
			throw $e;
		}
	}


 /*:-------------------------------method start here getDefaults ------------------------------*/
static function getDefaults($params)
 {
		$con =$params['dbconnection'];	
		
		
		$terms_and_conditions="";
		$about_us="";
		$privacy_policy="";
		$sub="";
		if($params['type']=='1')
		$terms_and_conditions="`terms_and_conditions`";
		else if($params['type']=='2')
		$about_us="`about_us`";
		else if($params['type']=='3')
		$privacy_policy="`privacy_policy`";
		else
		$sub="`tax`,
		`delivery_charges`,
		`estimated_carry_out_time`,
		`accepted_delivery_time`";
		
		 $query = "SELECT 
		".$terms_and_conditions."
		".$about_us."
		".$privacy_policy."
		".$sub."
		FROM `defaults` ";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0)
		{	
		   $row = mysqli_fetch_assoc($result);
		   if( ($params['user_type'] ==NULL ||  $params['user_type'] =='2'))
		   {
			   $row['about_us_page']=str_replace('/api/v1/','',$params['apiBasePath'])."/admin/v2/#/mankoosha/defaults/about_us";
			   $row['terms_and_conditions_page']=str_replace('/api/v1/','',$params['apiBasePath'])."/admin/v2/#/mankoosha/defaults/terms_and_conditions";
			   $row['privacy_policy_page']=str_replace('/api/v1/','',$params['apiBasePath'])."/admin/v2/#/mankoosha/defaults/privacy_policy";
		   }
	  
		   return $row ;	
		}
		
	 
 }
  /*:-------------------------------method start here updateDefaults ------------------------------*/
static function updateDefaults($params)
 {
	 
	$con =$params['dbconnection'];	
	$tax='';
	$delivery_charges='';
	$accepted_delivery_time='';
	$estimated_carry_out_time='';
	$terms_and_conditions='';
	$about_us='';
	$privacy_policy='';
	
	if(!empty($params['tax'])){   $tax=" `tax`='{$params['tax']}' ,";  }
	if(!empty($params['delivery_charges'])){   $delivery_charges=" `delivery_charges`='{$params['delivery_charges']}' ,";  }
	if(!empty($params['accepted_delivery_time'])){   $accepted_delivery_time=" `accepted_delivery_time`='{$params['accepted_delivery_time']}' ,";  }
	if(!empty($params['estimated_carry_out_time'])){   $estimated_carry_out_time=" `estimated_carry_out_time`='{$params['estimated_carry_out_time']}' ,";  }
	
	if(!empty($params['terms_and_conditions'])){   $terms_and_conditions=" `terms_and_conditions`='{$params['terms_and_conditions']}' ,";  }
	if(!empty($params['about_us'])){   $about_us=" `about_us`='{$params['about_us']}' ,";  }
	if(!empty($params['privacy_policy'])){   $privacy_policy=" `privacy_policy`='{$params['privacy_policy']}' ,";  }
	
	 $query= "UPDATE `defaults` SET 
	".$tax."
	".$delivery_charges."
	".$accepted_delivery_time."
	".$estimated_carry_out_time."
	".$terms_and_conditions."
	".$about_us."
	".$privacy_policy."
	`id`=`id`"	;
	mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	/*if(mysqli_affected_rows($con)==0)
	return "";*/
	
	return 'updated';
 } 
 
 
 /* 1:------------------------------method start here generateNotification 1------------------------------*/
static function generateNotification($params)
{
    $con =$params['dbconnection'];	
	
	$query = "INSERT INTO `notifications` SET  
	`title`='{$params['title']}',
	`message`='{$params['message']}',
	`send_to`='{$params['send_to']}',
	`type`='8'" ;
	mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	$id=mysqli_insert_id($con);
	$params['id']=$id;
	DbMethods::sendNotification($params);
	return array("id"=>(int)$id) ;
}

/* 1:------------------------------method start here resendNotification 1------------------------------*/
static function resendNotification($params)
{
	$con =$params['dbconnection'];	
	 $query = "SELECT 
	*
	FROM `notifications`
	WHERE `id`='{$params['id']}' ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_assoc($result);	
			$params['title']=$row['title'];
			$params['message']=$row['message'];
			$params['send_to']=$row['send_to'];
			$params['type']='8';
			
			//DbMethods::sendNotification($params);
			return DbMethods::generateNotification($params);
		}
	
}

  /*:-------------------------------method start here getNotificationList ------------------------------*/
static function getNotificationList($params)
 {
	
	$con =$params['dbconnection'];	
	$notifications=array(); 
	$notificationsCount=array(); 
	
	if($params['admin_id'] !="")
	{
		$query = "SELECT COUNT(`id`) as notificationsCount  FROM  `notifications` WHERE `type`='8'	";
		$result = mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		 return  "mysql_Error:-".mysqli_error($con);
		if (mysqli_num_rows($result) > 0) 
		{
			 $row = mysqli_fetch_assoc($result);	
			 $notificationsCount=$row['notificationsCount'];
		}
	}
	
	$query = "SELECT 
	*
	FROM  `notifications`
	WHERE `type`='8'	
	ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
	$result = mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	 return  "mysql_Error:-".mysqli_error($con);
	if (mysqli_num_rows($result) > 0) 
	{
		 while ($row = mysqli_fetch_assoc($result)) 
		 {
		     $notifications[] = $row;
		 }
	}
	
	if(!empty($notifications))
	{
		if( $notificationsCount !="")
		return array("notifications"=>$notifications ,"notificationsCount"=>$notificationsCount ) ;
		return $notifications;
	}
	else return '';
  
	 
 }
 
   /*:-------------------------------method start here changeLanguage ------------------------------*/
static function changeLanguage($params)
 {
	
	$con =$params['dbconnection'];	
	 $query= "UPDATE `authkeys` SET 
	`language`='{$params['langset']}' 
	WHERE `auth_key`='{$params['Authorization']}'"	;
	mysqli_query($con,$query) ;
	if (mysqli_error($con) != '')
	return  "mysql_Error:-".mysqli_error($con);
	/*if(mysqli_affected_rows($con)==0)
	return "";*/
	return 'updated';
  
	 
 }

/* 4:------------------------------method start here logout ------------------------------*/
static function logout($params) 
{
	DbMethods::removeAuthKey($params);
	return "logout";
}
/* 1:------------------------------method start here removeAuthKey ------------------------------*/
static function removeAuthKey($params) 
{
	$con =$params['dbconnection'];	
	$queryd = "DELETE  FROM  `authkeys`  WHERE `auth_key`='{$params['Authorization']}'";
	mysqli_query($con,$queryd) ;
	return "removed";
}
/* 1:------------------------------method start here checkImageSrc ------------------------------*/
static function checkImageSrc($img) 
{
	//echo (__DIR__).'/../../uploads/'.$img;
	if(file_exists((__DIR__).'/../../uploads/'.$img) && !empty($img))
	return 1;
	else
	return null;
}
/* 1:------------------------------method start here post_async ------------------------------*/
static function post_async($url, $params) 
{
	/*echo $url;
	echo "<pre>";
	print_r($params);*/
	
    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
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

/* END-----------------------------END END END END------------------------------END*/
}


?>