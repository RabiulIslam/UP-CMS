<?php
    $filetype = "dbMethods";
    
    class DbMethods{
        /* 1:------------------------------method start here signIn ------------------------------*/
        static function signIn($params){
            $con = $params['dbconnection'];
            $query = "SELECT
		`id`,
		`name`,
		`email`,
		`phone`,
		`password`
		FROM `admin`
		WHERE `email`='{$params['email']}' ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                if(strtolower($row['email']) == strtolower(TRIM($params['email'])) && strtolower($row['password']) == strtolower(TRIM(sha1($params['password'])))){
                    $params['admin_id'] = $row['id'];
                    $auth_key = DbMethods::addAuthKey($params);
                    $row['Authorization'] = $auth_key;
                    unset($row['password']);
                    $row['id'] = (int)$row['id'];
                    return $row;
                }
                else{
                    return 'not_valid_credential_pass';
                }
            }
            else{
                return 'not_valid_credential_email';
            }
        }
        /* 1:------------------------------method start here getProfile ------------------------------*/
        static function getProfile($params){
            $con = $params['dbconnection'];
            $query = "SELECT
		`id`,
		`name`,
		`email`,
		`phone`,
		`password`
		FROM `admin`
		WHERE `id`='{$params['admin_id']}'";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                return $row;
            }
        }
        /* 1:------------------------------method start here forgotPassword ------------------------------*/
        static function forgotPassword($params){
            $con = $params['dbconnection'];
            if($params['email'] != ''){
                if(DbMethods::checkEmail($params) != 'emailexist'){
                    return 'emailnotexist';
                }
            }
            $password_reset_token = sha1($params['email']);
            $query = "UPDATE `admin` SET
		`password_reset_token`='{$password_reset_token}'
		WHERE `email`='{$params['email']}' ";
            mysqli_query($con, $query);
            $baseurl = "http://18.185.217.28/up_qatar/cms/#/auth/reset-password";
            $resetLink = "?token=".$password_reset_token;
            $resetLink = $baseurl.$resetLink."&type=admin";
            $angertag = "  <a href='".$resetLink."' style='background: #b64645 none repeat scroll 0 0; border-radius: 2px; color: #fff; font-size: 14px; font-weight: 400; padding: 4px 28px; display: block; max-width: 160px; font-size: 16px; font-weight: 600; text-decoration: none; margin: 10px auto 8px; padding: 15px 25px;' target='_blank'>Reset Password</a>";
            $params['to'] = $params['email'];
            $params['subject'] = "Password Recovery";
            $params['body'] = "<html>
		<head>
		<title>Password Recovery</title>
		</head>
		<body>
		<h3>Dear user,</h3>
		<p>Click on Link to reset Password: <b>".$angertag." </b></p>
		<p></p>
		<p>Regards, </p>
		<p>UP</p>
		</body>
		</html>";
            //HelpingMethods::sendEmail($params);//send mail
            $dir = $params['apiBasePath']."../../../common/sendEmail.php";
            DbMethods:: post_async($dir, [
                'to'      => $params['to'],
                'subject' => $params['subject'],
                'body'    => $params['body'],
                'con'     => $params['dbconnection']
            ]);
            return "sended";
        }
        /* 1:------------------------------method start here changePassword ------------------------------*/
        static function changePassword($params){
            $con = $params['dbconnection'];
            if($params['type'] == 'user'){
                $tablename = "`users`";
            }
            else{
                $tablename = "`admin`";
                $params['password'] = sha1($params['password']);
            }
            $query = "SELECT `id` FROM ".$tablename."
		WHERE `password_reset_token`='{$params['password_reset_token']}' ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) == 0){
                return "tokeninvalid";
            }
            $query = "UPDATE ".$tablename." SET
		`password`='{$params['password']}',
		`password_reset_token`=''
		WHERE `password_reset_token`='{$params['password_reset_token']}' ";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            return 'updated';
        }
        /* 1:------------------------------method start here updateProfile ------------------------------*/
        static function updateProfile($params){
            $con = $params['dbconnection'];
            if($params['email'] != ''){
                if(DbMethods::checkEmail($params) == 'emailexist'){
                    return 'emailexist';
                }
            }
            if($params['phone'] != ''){
                if(DbMethods::checkPhone($params) == 'phoneexist'){
                    return 'phoneexist';
                }
            }
            $name = '';
            $email = '';
            $phone = '';
            $password = '';
            $subquery = "";
            if($params['name'] != ""){
                $name = " `name`='{$params['name']}' ,";
            }
            if($params['email'] != ""){
                $email = " `email`='{$params['email']}' ,";
            }
            if($params['phone'] != ""){
                $phone = " `phone`='{$params['phone']}' ,";
            }
            if($params['password'] != ""){
                $password = " `password`='{$params['password']}' ,";
            }
            if($params['password'] != ''){
                $checkPassword = DbMethods::checkPassword($params);
                if($checkPassword != null){
                    return $checkPassword;
                }
            }
            $query = "UPDATE `admin` SET
	".$name."
	".$email."
	".$phone."
	".$password."
	`id`='{$params['admin_id']}'
	WHERE `id`='{$params['admin_id']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            return 'updated';
        }
        /* 1:------------------------------method start here checkEmail ---------------------------1*/
        static function checkPassword($params){
            $con = $params['dbconnection'];
            if($params['old_password'] == $params['password']){
                return "same";
            }
            $query = "SELECT `id` FROM `admin`
	WHERE `id`='{$params['user_id']}' AND `password`='{$params['old_password']}'";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) == 0){
                return "wrong";
            }
            else{
                return null;
            }
        }
        /* 1:------------------------------method start here checkEmail ---------------------------1*/
        static function checkEmail($params){
            $con = $params['dbconnection'];
            $querysub = "";
            if($params['admin_id'] != ''){
                $querysub = "  AND 	`id` !='{$params['admin_id']}' ";
            }
            if($params['id'] != ''){
                $querysub = "  AND 	`id` !='{$params['id']}' ";
            }
            $query = "SELECT `email` FROM  `admin` WHERE `email` ='{$params['email']}' ".$querysub."  ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                return 'emailexist';
            }
            else{
                return ['email' => $params['email']];
            }
        }
        /* 1:------------------------------method start here checkPhone ---------------------------1*/
        static function checkPhone($params){
            $con = $params['dbconnection'];
            $querysub = "";
            if($params['admin_id'] != ''){
                $querysub = "  AND 	`id` !='{$params['admin_id']}' ";
            }
            if($params['id'] != ''){
                $querysub = "  AND 	`id` !='{$params['id']}' ";
            }
            $query = "SELECT `phone` FROM  `admin` WHERE `phone` ='{$params['phone']}' ".$querysub."  ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                return 'phoneexist';
            }
            else{
                return ['phone' => $params['phone']];
            }
        }
        /* 1:------------------------------method start here addAuthKey ------------------------------*/
        static function addAuthKey($params){
            $con = $params['dbconnection'];
            $admin_id = '';
            $auth_key = md5($params['admin_id'].microtime());
            $admin_id = "`admin_id` ='{$params['admin_id']}'";
            $queryt_t = "INSERT INTO  `authkeys`
	SET `auth_key`='{$auth_key}',
	`token`='{$params['token']}',
	 ".$admin_id."";
            mysqli_query($con, $queryt_t);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            return $auth_key;
        }
        /* 1:------------------------------method start here addAdmin ------------------------------*/
        static function addAdmin($params){
            $con = $params['dbconnection'];
            if($emailexist = DbMethods::checkEmail($params) == 'emailexist'){
                return 'emailexist';
            }
            if($phoneexist = DbMethods::checkPhone($params) == 'phoneexist'){
                return 'phoneexist';
            }
            $params['password'] = sha1($params['password']);
            $query = "INSERT INTO `admin` SET
		`name`='{$params['name']}',
		`phone`='{$params['phone']}',
		`email`='{$params['email']}',
		`password`='{$params['password']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            $id = mysqli_insert_id($con);
            $params['id'] = $id;
            $params['table_name'] = 'admin';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'add';
            DbMethods::logs($params);
            return ["id" => (int)$id];
        }
        /* 1:------------------------------method start here updateAdmin ------------------------------*/
        static function updateAdmin($params){
            $con = $params['dbconnection'];
            if($params['email'] != ''){
                if(DbMethods::checkEmail($params) == 'emailexist'){
                    return 'emailexist';
                }
            }
            if($params['phone'] != ''){
                if(DbMethods::checkPhone($params) == 'phoneexist'){
                    return 'phoneexist';
                }
            }
            $name = '';
            $email = '';
            $phone = '';
            $password = '';
            if($params['name'] != ""){
                $name = " `name`='{$params['name']}' ,";
            }
            if($params['email'] != ""){
                $email = " `email`='{$params['email']}' ,";
            }
            if($params['phone'] != ""){
                $phone = " `phone`='{$params['phone']}' ,";
            }
            if($params['password'] != ""){
                /*$checkPassword=DbMethods::checkPassword($params);
			if($checkPassword!=NULL)
			return $checkPassword;*/
                $params['password'] = sha1($params['password']);
                $password = " `password`='{$params['password']}' ,";
            }
            $query = "UPDATE `admin` SET
		".$name."
		".$email."
		".$phone."
		".$password."
		`id`='{$params['id']}'
		WHERE `id`='{$params['id']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            $params['table_name'] = 'admin';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'update';
            DbMethods::logs($params);
            return 'updated';
        }
        /* 1:------------------------------method start here deleteAdmin ------------------------------*/
        static function deleteAdmin($params){
            $con = $params['dbconnection'];
            $queryd = "DELETE  FROM  `admin` WHERE `id`='{$params['id']}'";
            mysqli_query($con, $queryd);
            $params['table_name'] = 'admin';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'delete';
            DbMethods::logs($params);
            return "removed";
        }
        /* 1:------------------------------method start here getAdmins ------------------------------*/
        static function getAdmins($params){
            $con = $params['dbconnection'];
            $admin = [];
            $where = "";
            if($params['search'] != ''){
                $where = "WHERE  (`name` LIKE '%".$params['search']."%' OR  `email` LIKE '%".$params['search']."%'  OR  `phone` = '".$params['search']."') ";
            }
            $adminsCount = "";
            if($adminsCount = $con->query("SELECT `id` FROM  `admin` ".$where."")){
                $adminsCount = $adminsCount->num_rows;
            }
            $query = "SELECT * FROM  `admin` ".$where."
		ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    unset($row['password']);
                    $admin[] = $row;
                }
            }
            if($adminsCount != ''){
                return ["admin" => $admin, "adminsCount" => $adminsCount];
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here removeAuthKey ------------------------------*/
        static function removeAuthKey($params){
            $con = $params['dbconnection'];
            $queryd = "DELETE  FROM  `authkeys`  WHERE `auth_key`='{$params['Authorization']}'";
            mysqli_query($con, $queryd);
            return "removed";
        }
        /* 21:------------------------------method start here addCategory 21------------------------------*/
        static function addCategory($params){
            /*$con =$params['dbconnection'];
		if($categoryexist=DbMethods::checkCategory($params)=='categoryexist') return  'categoryexist';
		$query = "INSERT INTO `category` SET
		`name`='{$params['name']}',
		`image`='{$params['image']}'" ;
		mysqli_query($con,$query) ;
		if (mysqli_error($con) != '')
		return  "mysql_Error:-".mysqli_error($con);
		$id=mysqli_insert_id($con);
		
		$params['table_name']='category';
		$params['id']=$id;
		$params['table_id']=$id;
		$params['admin_logstype']='add';
		DbMethods::logs($params);
		
		return array("id"=>(int)$id) ;*/
            return "added";
        }
        /* 21:------------------------------method start here updateCategory 21------------------------------*/
        static function updateCategory($params){
            $con = $params['dbconnection'];
            $name = '';
            $image = '';
            if($params['name'] != ""){
                if($categoryexist = DbMethods::checkCategory($params) == 'categoryexist'){
                    return 'categoryexist';
                }
                $name = " `name`='{$params['name']}' ,";
            }
            if($params['image'] != ""){
                $image = " `image`='{$params['image']}' ,";
            }
            $query = "UPDATE `category` SET
	".$name."
	".$image."
	`id`='{$params['id']}'
	WHERE `id`='{$params['id']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            $params['table_name'] = 'category';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'update';
            DbMethods::logs($params);
            return 'updated';
        }
        /* 21:------------------------------method start here deleteCategory 21------------------------------*/
        static function deleteCategory($params){
            /*$con =$params['dbconnection'];
	$queryd = "DELETE  FROM  `category`  WHERE `id`='{$params['id']}'";
	mysqli_query($con,$queryd) ;
	
	$params['table_name']='category';
	$params['table_id']=$params['id'];
	$params['admin_logstype']='delete';
	DbMethods::logs($params);*/
            return "removed";
        }
        /* 21:------------------------------method start here getCategories 21------------------------------*/
        static function getCategories($params){
            $con = $params['dbconnection'];
            $categories = [];
            $categoriesCount = "";
            if($categoriesCount = $con->query("SELECT `id` FROM  `category` ")){
                $categoriesCount = $categoriesCount->num_rows;
            }
            $query = "SELECT * FROM  `category`
	ORDER BY `id` ASC LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $categories[] = $row;
                }
            }
            if($categoriesCount != ''){
                return ["categories" => $categories, "categoriesCount" => $categoriesCount];
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here checkCategory ---------------------------1*/
        static function checkCategory($params){
            $con = $params['dbconnection'];
            $querysub = "";
            if($params['id'] != ''){
                $querysub = "  AND 	`id` !='{$params['id']}' ";
            }
            $query = "SELECT `name` FROM  `category` WHERE `name` ='{$params['name']}' ".$querysub."  ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                return 'categoryexist';
            }
            else{
                return ['name' => $params['name']];
            }
        }
        /* 1:------------------------------method start here addMerchant 1------------------------------*/
        static function addMerchant($params){
            $con = $params['dbconnection'];
            //if(DbMethods::checkMerchantEmail($params)=='emailexist')return  'emailexist';
            $name = '';
            $title = '';
            $phone = '';
            $email = '';
            $gender = '';
            $point_of_contact = '';
            $up_account_manager = '';
            $contract_start_date = '';
            $contract_expiry_date = '';
            $notes = '';
            $TAC_accepted = '';
            if($params['contract_start_date'] != ""){
                $params['contract_start_date'] = date_format(date_create($params['contract_start_date']), "Y-m-d");
            }
            if($params['contract_expiry_date'] != ""){
                $params['contract_expiry_date'] = date_format(date_create($params['contract_expiry_date']), "Y-m-d");
            }
            if($params['name'] != ""){
                $name = " `name`='{$params['name']}' ,";
            }
            if($params['title'] != ""){
                $title = " `title`='{$params['title']}' ,";
            }
            if($params['phone'] != ""){
                $phone = " `phone`='{$params['phone']}' ,";
            }
            if($params['email'] != ""){
                $email = " `email`='{$params['email']}' ,";
            }
            if($params['gender'] != ""){
                $gender = " `gender`='{$params['gender']}' ,";
            }
            if($params['point_of_contact'] != ""){
                $point_of_contact = " `point_of_contact`='{$params['point_of_contact']}' ,";
            }
            if($params['up_account_manager'] != ""){
                $up_account_manager = " `up_account_manager`='{$params['up_account_manager']}' ,";
            }
            if($params['contract_start_date'] != ""){
                $contract_start_date = " `contract_start_date`='{$params['contract_start_date']}' ,";
            }
            if($params['contract_expiry_date'] != ""){
                $contract_expiry_date = " `contract_expiry_date`='{$params['contract_expiry_date']}' ,";
            }
            if($params['notes'] != ""){
                $notes = " `notes`='{$params['notes']}' ,";
            }
            if(isset($params['TAC_accepted']) && ($params['TAC_accepted'] == '0' || $params['TAC_accepted'] == '1')){
                $TAC_accepted = " `TAC_accepted`='{$params['TAC_accepted']}' ,";
            }
            $query = "INSERT INTO `merchants` SET
	".$name."
	".$title."
	".$phone."
	".$email."
	".$gender."
	".$point_of_contact."
	".$up_account_manager."
	".$contract_start_date."
	".$contract_expiry_date."
	".$notes."
	".$TAC_accepted."
	`id`=`id` ";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            $id = mysqli_insert_id($con);
            $params['id'] = $id;
            DbMethods::createPin($params);
            $params['table_name'] = 'merchants';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'add';
            DbMethods::logs($params);
            return ["id" => (int)$id];
        }
        /* 1:------------------------------method start here addMerchants 1------------------------------*/
        static function addMerchants($params){
            $con = $params['dbconnection'];
            $merchants = stripslashes($params['merchants']);
            $merchants = json_decode($merchants, true);
            $outletObj = [];
            for($i = 0; $i < count($merchants); $i++){
                //if($outletexist=DbMethods::checkOutlet($params)=='outletexist') return  'outletexist';
                $params['name'] = addslashes($merchants[$i]['name']);
                if(isset($merchants[$i]['title'])){
                    $params['title'] = addslashes($merchants[$i]['title']);
                }
                if(isset($merchants[$i]['phone'])){
                    $params['phone'] = $merchants[$i]['phone'];
                }
                if(isset($merchants[$i]['email'])){
                    $params['email'] = addslashes($merchants[$i]['email']);
                }
                if(isset($merchants[$i]['gender'])){
                    $params['gender'] = $merchants[$i]['gender'];
                }
                if(isset($merchants[$i]['point_of_contact'])){
                    $params['point_of_contact'] = $merchants[$i]['point_of_contact'];
                }
                if(isset($merchants[$i]['up_account_manager'])){
                    $params['up_account_manager'] = addslashes($merchants[$i]['up_account_manager']);
                }
                if(isset($merchants[$i]['contract_start_date'])){
                    $params['contract_start_date'] = addslashes($merchants[$i]['contract_start_date']);
                }
                if(isset($merchants[$i]['contract_expiry_date'])){
                    $params['contract_expiry_date'] = addslashes($merchants[$i]['contract_expiry_date']);
                }
                if(isset($merchants[$i]['notes'])){
                    $params['notes'] = addslashes($merchants[$i]['notes']);
                }
                if(isset($merchants[$i]['TAC_accepted'])){
                    $params['TAC_accepted'] = $merchants[$i]['TAC_accepted'];
                }
                DbMethods::addMerchant($params);
            }
            return "added";
        }
        /* 1:------------------------------method start here updateMerchant 1------------------------------*/
        static function updateMerchant($params){
            $con = $params['dbconnection'];
            $name = '';
            $title = '';
            $phone = '';
            $email = '';
            $gender = '';
            $point_of_contact = '';
            $up_account_manager = '';
            $contract_start_date = '';
            $contract_expiry_date = '';
            $notes = '';
            $TAC_accepted = '';
            if($params['contract_start_date'] != ""){
                $params['contract_start_date'] = date_format(date_create($params['contract_start_date']), "Y-m-d");
            }
            if($params['contract_expiry_date'] != ""){
                $params['contract_expiry_date'] = date_format(date_create($params['contract_expiry_date']), "Y-m-d");
            }
            if($params['name'] != ""){
                $name = " `name`='{$params['name']}' ,";
            }
            if($params['title'] != ""){
                $title = " `title`='{$params['title']}' ,";
            }
            if($params['phone'] != ""){
                $phone = " `phone`='{$params['phone']}' ,";
            }
            if($params['email'] != ""){
                $email = " `email`='{$params['email']}' ,";
            }
            if($params['gender'] != ""){
                $gender = " `gender`='{$params['gender']}' ,";
            }
            if($params['point_of_contact'] != ""){
                $point_of_contact = " `point_of_contact`='{$params['point_of_contact']}' ,";
            }
            if($params['up_account_manager'] != ""){
                $up_account_manager = " `up_account_manager`='{$params['up_account_manager']}' ,";
            }
            if($params['contract_start_date'] != ""){
                $contract_start_date = " `contract_start_date`='{$params['contract_start_date']}' ,";
            }
            if($params['contract_expiry_date'] != ""){
                $contract_expiry_date = " `contract_expiry_date`='{$params['contract_expiry_date']}' ,";
            }
            if($params['notes'] != ""){
                $notes = " `notes`='{$params['notes']}' ,";
            }
            if($params['TAC_accepted'] != ""){
                $TAC_accepted = " `TAC_accepted`='{$params['TAC_accepted']}' ,";
            }
            /*if($params['email'] !='')
	if(DbMethods::checkMerchantEmail($params)=='emailexist')return  'emailexist';*/
            $query = "UPDATE `merchants` SET
	".$name."
	".$title."
	".$phone."
	".$email."
	".$gender."
	".$point_of_contact."
	".$up_account_manager."
	".$contract_start_date."
	".$contract_expiry_date."
	".$notes."
	".$TAC_accepted."
	`id`='{$params['id']}'
	WHERE `id`='{$params['id']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            $params['table_name'] = 'merchants';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'update';
            DbMethods::logs($params);
            return 'updated';
        }
        /* 1:------------------------------method start here checkEmail ---------------------------1*/
        static function checkMerchantEmail($params){
            if($params['email'] != ""){
                $con = $params['dbconnection'];
                $querysub = "";
                if($params['id'] != ''){
                    $querysub = "  AND 	`id` !='{$params['id']}' ";
                }
                $query = "SELECT `email` FROM  `merchants` WHERE `email` ='{$params['email']}' ".$querysub."  ";
                $result = mysqli_query($con, $query);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
                if(mysqli_num_rows($result) > 0){
                    return 'emailexist';
                }
                else{
                    return ['email' => $params['email']];
                }
            }
            return null;
        }
        /* 1:------------------------------method start here checkEmail ---------------------------1*/
        static function createPin($params){
            $con = $params['dbconnection'];
            $querysub = "";
            $pin = rand(1, 9999);
            if(strlen($pin) == 1){
                $pin = '000'.$pin;
            }
            else if(strlen($pin) == 2){
                $pin = '00'.$pin;
            }
            else if(strlen($pin) == 3){
                $pin = '0'.$pin;
            }
            $query = "SELECT `id` FROM  `merchants` WHERE `pin` ='{$pin}' ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                DbMethods::createPin($params);
            }
            else{
                mysqli_query($con, "UPDATE `merchants` SET `pin`='{$pin}' WHERE `id`='{$params['id']}'");
            }
            /*$params['to']=$params['email'];
	$params['subject']="PIN";
	$params['body']= "<html>
	<head>
	<title>PIN</title>
	</head>
	<body>
	<h3>Dear user,</h3>
	<p>PIN: <b>".$pin." </b></p>
	<p></p>
	<p>Regards, </p>
	<p>wafr</p>
	</body>
	</html>";
	//HelpingMethods::sendEmail($params);//send mail
	
	$dir=$params['apiBasePath']."../../../common/sendEmail.php";
	DbMethods:: post_async($dir ,array('to'=>$params['to'],'subject'=>$params['subject']
	,'body'=>$params['body'],'con'=>$params['dbconnection']));*/
            return "updated";
        }
        /* 1:------------------------------method start here deleteMerchant 1------------------------------*/
        static function deleteMerchant($params){
            $con = $params['dbconnection'];
            $queryd = "DELETE  FROM  `merchants`  WHERE `id`='{$params['id']}'";
            mysqli_query($con, $queryd);
            $params['table_name'] = 'merchants';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'delete';
            DbMethods::logs($params);
            return "removed";
        }
        /* 1:------------------------------method start here ADMerchant 1------------------------------*/
        static function ADMerchant($params){
            $con = $params['dbconnection'];
            try{
                $con->begin_transaction();
                $query = "SELECT
		m.`id`
		FROM  `merchants` as m
		LEFT JOIN `outlets` as o ON(m.`id`=o.`merchant_id`)
		WHERE m.`id`='{$params['id']}' GROUP BY o.`id`";
                $result = mysqli_query($con, $query);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
                if(mysqli_num_rows($result) > 0){
                    $row = mysqli_fetch_assoc($result);
                    $query0 = "UPDATE `merchants`
			SET `active`='{$params['active']}'
			WHERE `id`='{$row['id']}'";
                    mysqli_query($con, $query0);
                    if(mysqli_error($con) != ''){
                        return "mysql_Error:-".mysqli_error($con);
                    }
                    $query1 = "UPDATE `outlets`
			SET `active`='{$params['active']}'
			WHERE `merchant_id`='{$row['id']}'";
                    mysqli_query($con, $query1);
                    if(mysqli_error($con) != ''){
                        return "mysql_Error:-".mysqli_error($con);
                    }
                    $query2 = "UPDATE `offers`
			SET `active`='{$params['active']}'
			WHERE `outlet_id`='{$row['outlet_id']}'";
                    mysqli_query($con, $query2);
                    if(mysqli_error($con) != ''){
                        return "mysql_Error:-".mysqli_error($con);
                    }
                }
                $con->commit();
                $params['table_name'] = 'merchants';
                $params['table_id'] = $params['id'];
                $params['admin_logstype'] = 'update';
                DbMethods::logs($params);
                return "updated";
            }
            catch(\Exception $e){
                $con->rollBack();
                throw $e;
            }
            catch(\Throwable $e){
                $con->rollBack();
                throw $e;
            }
        }
        /* 1:------------------------------method start here getMerchants 1------------------------------*/
        static function getMerchants($params){
            $con = $params['dbconnection'];
            $merchants = [];
            $search = "";
            $datarange = "";
            if($params['search'] != ''){
                $search = " AND (`name` LIKE '%{$params['search']}%' OR
	`email` LIKE '%{$params['search']}%' OR
	`point_of_contact` LIKE '%{$params['search']}%' OR
	`phone` ='{$params['search']}' )";
            }
            if($params['start_date'] != "" && $params['end_date']){
                $datarange = " AND  `created_at` > '{$params['start_date']}' AND `created_at` < '{$params['end_date']}'";
            }
            $sortby = "`id` DESC";
            if($params['sortby'] != '' && $params['orderby'] != ''){
                $sortby = "`{$params['sortby']}` ".$params['orderby'];
            }
            $merchantsCount = "";
            if($merchantsCount = $con->query("SELECT `id` FROM  `merchants` WHERE `id`=`id` ".$search."  ".$datarange." ")){
                $merchantsCount = $merchantsCount->num_rows;
            }
            $query = "SELECT `id`,`name`,`title`,`phone`,
	`email`,`gender`,`pin`,`point_of_contact`,`up_account_manager`,`contract_start_date`,
	`contract_expiry_date`,`notes`,`TAC_accepted`,`active`,`created_at`,`updated_at`
	FROM  `merchants` WHERE `id`=`id` ".$search." ".$datarange."
	ORDER BY ".$sortby." LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $merchants[] = $row;
                }
            }
            if($merchantsCount != ''){
                return ["merchants" => $merchants, "merchantsCount" => $merchantsCount];
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here getAllMerchants 1------------------------------*/
        static function getAllMerchants($params){
            $con = $params['dbconnection'];
            $allmerchants = [];
            $query = "SELECT
	`id`,
	`name`
	FROM  `merchants`
	ORDER BY `id` DESC LIMIT 0,500";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $allmerchants[] = $row;
                }
            }
            if(!empty($allmerchants)){
                return $allmerchants;
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here addOutlet 1------------------------------*/
        static function addOutlet($params){
            $con = $params['dbconnection'];
            try{
                $con->begin_transaction();
                //if($outletexist=DbMethods::checkOutlet($params)=='outletexist') return  'outletexist';
                $query = "INSERT INTO `outlets` SET
			`merchant_id`='{$params['merchant_id']}',
			`name`='{$params['name']}',
			`emails`='{$params['emails']}',
			`search_tags`='{$params['search_tags']}',
			`phone`='{$params['phone']}',
			`SKU`='{$params['SKU']}',
			`pin`='{$params['pin']}',
			`address`='{$params['address']}',
			`description`='{$params['description']}',
			`timings`='{$params['timings']}',
			`latitude`='{$params['latitude']}',
			`longitude`='{$params['longitude']}',
			`logo`='{$params['logo']}',
			`image`='{$params['image']}',
			`special`='{$params['special']}',
			`type`='{$params['type']}'";
                mysqli_query($con, $query);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
                $id = mysqli_insert_id($con);
                $params['id'] = $id;
                /*mysqli_query($con,"INSERT INTO `outlet_timing`
			SET `outlet_id`='{$params['id']}' ,`day`='Everyday', `start_time`='{$params['start_timing']}', `end_time`='{$params['end_timing']}'") ;*/
                $category_ids = explode(',', $params['category_ids']);
                $multiplequery = "INSERT INTO `outlet_category`(`category_id`,`outlet_id`) VALUES ";
                $multiplequerycol = "";
                foreach($category_ids as $x => $value){
                    $multiplequerycol .= "('{$value}','{$params['id']}'),";
                }
                $multiplequerycol = substr($multiplequerycol, 0, -1);
                $query = $multiplequery.$multiplequerycol;
                mysqli_query($con, $query);
                $params['table_name'] = 'outlets';
                $params['table_id'] = $params['id'];
                $params['admin_logstype'] = 'add';
                DbMethods::logs($params);
                $con->commit();
                return ["id" => (int)$id];
            }
            catch(\Exception $e){
                $con->rollBack();
                throw $e;
            }
            catch(\Throwable $e){
                $con->rollBack();
                throw $e;
            }
        }
        /* 1:------------------------------method start here addOutlets 1------------------------------*/
        static function addOutlets($params){
            $con = $params['dbconnection'];
            $outlets = stripslashes($params['outlets']);
            $outlets = json_decode($outlets, true);
            $outletObj = [];
            $logo = "";
            $images = "";
            //echo print_r($outlets);
            for($i = 0; $i < count($outlets); $i++){
                //if($outletexist=DbMethods::checkOutlet($params)=='outletexist') return  'outletexist';
                $params['merchant_id'] = $outlets[$i]['merchant_id'];
                if($outlets[$i]['category_ids'] == '' || !preg_match('/^[1-9,]+$/', $outlets[$i]['category_ids'])){
                    break;
                }
                else{
                    $params['category_ids'] = $outlets[$i]['category_ids'];
                }
                $params['name'] = addslashes($outlets[$i]['name']);
                $params['emails'] = addslashes($outlets[$i]['emails']);
                $params['search_tags'] = addslashes($outlets[$i]['search_tags']);
                $params['logo'] = addslashes($outlets[$i]['logo']);
                $params['image'] = addslashes($outlets[$i]['image']);
                $params['phone'] = addslashes($outlets[$i]['phone']);
                $params['SKU'] = addslashes($outlets[$i]['SKU']);
                $pin = preg_match('/[^0-9]/', $outlets[$i]['pin']);
                if($outlets[$i]['pin'] == '' || (strlen($outlets[$i]['pin']) > 4) || ($pin > 0)){
                    break;
                }
                else{
                    $params['pin'] = $outlets[$i]['pin'];
                }
                $params['address'] = addslashes($outlets[$i]['address']);
                $params['description'] = addslashes($outlets[$i]['description']);
                $params['timings'] = $outlets[$i]['timings'];
                $params['latitude'] = $outlets[$i]['latitude'];
                $params['longitude'] = $outlets[$i]['longitude'];
                if(isset($outlets[$i]['special']) && ($outlets[$i]['special'] == '0' || $outlets[$i]['special'] == '1')){
                    $params['special'] = $outlets[$i]['special'];
                }
                if(isset($outlets[$i]['type']) && ($outlets[$i]['type'] == '0' || $outlets[$i]['type'] == '1' || $outlets[$i]['type'] == '2')){
                    $params['type'] = $outlets[$i]['type'];
                }
                DbMethods::addOutlet($params);
            }
            return "added";
        }
        /* 1:------------------------------method start here updateOutlet 1------------------------------*/
        static function updateOutlet($params){
            $con = $params['dbconnection'];
            $merchant_id = "";
            $name = "";
            $emails = "";
            $search_tags = '';
            $phone = "";
            $SKU = "";
            $pin = "";
            $address = "";
            $description = "";
            $timings = "";
            $start_timing = "";
            $end_timing = "";
            $term_and_conditions = "";
            $latitude = "";
            $longitude = "";
            $logo = "";
            $image = "";
            $type = "";
            $special = "";
            //if($outletexist=DbMethods::checkOutlet($params)=='outletexist') return  'outletexist';
            if($params['merchant_id'] != ""){
                $merchant_id = " `merchant_id`='{$params['merchant_id']}' ,";
            }
            if($params['name'] != ""){
                $name = " `name`='{$params['name']}' ,";
            }
            if($params['emails'] != ""){
                $emails = " `emails`='{$params['emails']}' ,";
            }
            if($params['search_tags'] != ""){
                $search_tags = " `search_tags`='{$params['search_tags']}' ,";
            }
            if($params['phone'] != ""){
                $phone = " `phone`='{$params['phone']}' ,";
            }
            if($params['SKU'] != ""){
                $SKU = " `SKU`='{$params['SKU']}' ,";
            }
            if($params['pin'] != ""){
                $pin = " `pin`='{$params['pin']}' ,";
            }
            if($params['address'] != ""){
                $address = " `address`='{$params['address']}' ,";
            }
            if($params['description'] != ""){
                $description = " `description`='{$params['description']}' ,";
            }
            if($params['timings'] != ""){
                $timings = " `timings`='{$params['timings']}' ,";
            }
            if($params['latitude'] != ""){
                $latitude = " `latitude`='{$params['latitude']}' ,";
            }
            if($params['longitude'] != ""){
                $longitude = " `longitude`='{$params['longitude']}' ,";
            }
            if($params['logo'] != ""){
                $logo = " `logo`='{$params['logo']}' ,";
            }
            if($params['image'] != ""){
                $image = " `image`='{$params['image']}' ,";
            }
            if($params['type'] != ""){
                $type = " `type`='{$params['type']}' ,";
            }
            if($params['special'] != ""){
                $special = " `special`='{$params['special']}' ,";
            }
            $query = "UPDATE `outlets` SET
	".$merchant_id."
	".$name."
	".$emails."
	".$search_tags."
	".$phone."
	".$SKU."
	".$pin."
	".$address."
	".$description."
	".$timings."
	".$term_and_conditions."
	".$latitude."
	".$longitude."
	".$logo."
	".$image."
	".$type."
	".$special."
	`id`='{$params['id']}'
	WHERE `id`='{$params['id']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if($params['category_ids'] != ""){
                $queryd = "DELETE  FROM  `outlet_category` WHERE `outlet_id`='{$params['id']}'";
                mysqli_query($con, $queryd);
                $category_ids = explode(',', $params['category_ids']);
                $multiplequery = "INSERT INTO `outlet_category`(`category_id`,`outlet_id`) VALUES ";
                $multiplequerycol = "";
                foreach($category_ids as $x => $value){
                    $multiplequerycol .= "('{$value}','{$params['id']}'),";
                }
                $multiplequerycol = substr($multiplequerycol, 0, -1);
                $query = $multiplequery.$multiplequerycol;
                mysqli_query($con, $query);
            }
            $params['table_name'] = 'outlets';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'update';
            DbMethods::logs($params);
            return 'updated';
        }
        /* 1:------------------------------method start here checkOutlet ------------------------------*/
        static function checkOutlet($params){
            $con = $params['dbconnection'];
            $id = '';
            if($params['id'] != ''){
                $id = "  AND 	`id` !='{$params['id']}' ";
            }
            $query = "SELECT `id` FROM `outlets` WHERE `name`='{$params['name']}' ".$id."";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                return "outletexist";
            }
            else{
                return null;
            }
        }
        /* 1:------------------------------method start here deleteOutlet 1------------------------------*/
        static function deleteOutlet($params){
            $con = $params['dbconnection'];
            $queryd = "DELETE  FROM  `outlets` WHERE `id`='{$params['id']}'";
            mysqli_query($con, $queryd);
            $params['table_name'] = 'outlets';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'delete';
            DbMethods::logs($params);
            return "removed";
        }
        /* 1:------------------------------method start here ADOutlet 1------------------------------*/
        static function ADOutlet($params){
            $con = $params['dbconnection'];
            try{
                $con->begin_transaction();
                $query = "SELECT
			`id`
			FROM  `outlets`
			WHERE `id`='{$params['id']}' ";
                $result = mysqli_query($con, $query);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
                if(mysqli_num_rows($result) > 0){
                    $row = mysqli_fetch_assoc($result);
                    $query0 = "UPDATE `outlets`
				SET `active`='{$params['active']}'
				WHERE `id`='{$row['id']}'";
                    mysqli_query($con, $query0);
                    if(mysqli_error($con) != ''){
                        return "mysql_Error:-".mysqli_error($con);
                    }
                    $query1 = "UPDATE `offers`
				SET `active`='{$params['active']}'
				WHERE `outlet_id`='{$row['id']}'";
                    mysqli_query($con, $query1);
                    if(mysqli_error($con) != ''){
                        return "mysql_Error:-".mysqli_error($con);
                    }
                    $con->commit();
                    $params['table_name'] = 'outlets';
                    $params['table_id'] = $params['id'];
                    $params['admin_logstype'] = 'update';
                    DbMethods::logs($params);
                    return "updated";
                }
            }
            catch(\Exception $e){
                $con->rollBack();
                throw $e;
            }
            catch(\Throwable $e){
                $con->rollBack();
                throw $e;
            }
        }
        /* 21:------------------------------method start here getOutlets 21------------------------------*/
        static function getOutlets($params){
            $con = $params['dbconnection'];
            $where = '';
            $datarange = '';
            $outlet_category = "";
            $merchant_id = "";
            if($params['merchant_id']){
                $where = " AND ou.`merchant_id`='{$params['merchant_id']}' ";
            }
            if($params['category_id']){
                $outlet_category = "INNER JOIN `outlet_category` as oc ON(oc.`outlet_id`=ou.`id` AND oc.`category_id`='{$params['category_id']}') ";
            }
            if($params['start_date'] != "" && $params['end_date']){
                $datarange = " AND  ou.`created_at` > '{$params['start_date']}' AND ou.`created_at` < '{$params['end_date']}'";
            }
            $search = "";
            if($params['search'] != ''){
                $search = " AND (m.`name` LIKE '%{$params['search']}%' OR
	m.`email` LIKE '%{$params['search']}%' OR
	ou.`name` LIKE '%{$params['search']}%' OR
	ou.`SKU` LIKE '%{$params['search']}%' OR
	ou.`search_tags` LIKE '%{$params['search']}%')";
            }
            $sortby = "ou.`id` DESC";
            if($params['sortby'] != '' && $params['orderby'] != ''){
                if($params['sortby'] == 'merchant_name'){
                    $sortby = "m.`name` ".$params['orderby'];
                }
                else{
                    $sortby = "ou.`{$params['sortby']}` ".$params['orderby'];
                }
            }
            $outletsCount = "";
            if($outletsCount = $con->query("SELECT ou.`id`
	FROM  `outlets` as ou
	INNER JOIN `merchants` as m ON(ou.`merchant_id`=m.`id`)
	".$outlet_category."
	WHERE ou.`id`=ou.`id` ".$where." ".$search." ".$merchant_id."  ".$datarange."")
            ){
                $outletsCount = $outletsCount->num_rows;
            }
            $outlets = [];
            $query = "SELECT
	ou.*,
	m.`name` as merchant_name,
	(SELECT GROUP_CONCAT(`category_id` SEPARATOR ',') AS category_ids FROM `outlet_category` WHERE `outlet_id`=ou.`id` ) as category_ids
	FROM  `outlets` as ou
	INNER JOIN `merchants` as m ON(ou.`merchant_id`=m.`id`)
	".$outlet_category."
	WHERE ou.`id`=ou.`id` ".$where." ".$search." ".$merchant_id." ".$datarange."
	ORDER BY  ".$sortby." LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $params['outlet_id'] = $row['id'];
                    //$row['category_ids']=  DbMethods::outletCategories ($params);
                    $row['image_name'] = $row['image'];
                    $row['logo_name'] = $row['logo'];
                    if(empty(DbMethods::checkImageSrc($row['logo']))){
                        $row['logo'] = '';
                    }
                    if(empty(DbMethods::checkImageSrc($row['image']))){
                        $row['image'] = '';
                    }
                    if($row['special'] == null){
                        $row['special'] = '0';
                    }
                    $cids = explode(',', $row['category_ids']);
                    if(in_array(64, $cids)){
                        if($row['type'] == null){
                            $row['type'] = '0';
                        }
                    }
                    else{
                        $row['type'] = null;
                    }
                    $outlets[] = $row;
                }
            }
            if($outletsCount != ''){
                return ["outlets" => $outlets, "outletsCount" => $outletsCount];
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here addOffer 1------------------------------*/
        static function outletCategories($params){
            $con = $params['dbconnection'];
            $query = "SELECT
	GROUP_CONCAT(`category_id` SEPARATOR ',') as category_ids
	FROM  `outlet_category`
	WHERE  `outlet_id`='{$params['outlet_id']}'
	ORDER BY `id` DESC LIMIT 0,10";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                return $row['category_ids'];
            }
        }
        /* 1:------------------------------method start here getAllOutlets 1------------------------------*/
        static function getAllOutlets($params){
            $con = $params['dbconnection'];
            $alloutlets = [];
            $merchant_id = "";
            if($params['merchant_id']){
                $merchant_id = " AND `merchant_id`='{$params['merchant_id']}' ";
            }
            $query = "SELECT
	`id`,
	`name`
	FROM  `outlets`
	WHERE `id`=`id` ".$merchant_id."
	ORDER BY `id` DESC LIMIT 0,1000";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $alloutlets[] = $row;
                }
            }
            if(!empty($alloutlets)){
                return $alloutlets;
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here addOffer 1------------------------------*/
        static function addOffer($params){
            $con = $params['dbconnection'];
            $redemptions = "";
            $per_user = "";
            $valid_for = "";
            $special_type = "";
            $special = "";
            $renew = "";
            $price = "";
            $special_price = "";
            if($special_price = DbMethods::checkOffer($params) == 'offertexist'){
                return 'offertexist';
            }
            if($params['redemptions'] != ""){
                $redemptions = " `redemptions`='{$params['redemptions']}' ,";
            }
            if($params['per_user'] != ""){
                $per_user = " `per_user`='{$params['per_user']}' ,";
            }
            if($params['special'] == '1'){
                $special_type = "`special_type`='{$params['special_type']}' ,";
            }
            if($params['special'] == '1'){
                $special = "`special`='{$params['special']}' ,";
            }
            if($params['renew'] == '1'){
                $renew = "`renew`='{$params['renew']}' ,";
            }
            if($params['price'] != ""){
                $price = " `price`='{$params['price']}' ,";
            }
            if($params['special_price'] != ""){
                $special_price = " `special_price`='{$params['special_price']}' ,";
            }
            if($params['start_datetime'] != ""){
                $params['start_datetime'] = date_format(date_create($params['start_datetime']), "Y-m-d H:i:s");
            }
            else{
                $params['start_datetime'] = null;
            }
            if($params['end_datetime'] != ""){
                $params['end_datetime'] = date_format(date_create($params['end_datetime']), "Y-m-d H:i:s");
            }
            else{
                $params['end_datetime'] = null;
            }
            $query = "INSERT INTO `offers` SET
	`outlet_id`='{$params['outlet_id']}',
	`title`='{$params['title']}',
	`SKU`='{$params['SKU']}',
	`search_tags`='{$params['search_tags']}',
	`image`='{$params['image']}',
	`approx_saving`='{$params['approx_saving']}',
	".$price."
	".$special_price."
	`valid_for`='{$params['valid_for']}' ,
	".$special."
	".$renew."
	".$special_type."
	".$redemptions."
	".$per_user."
	`start_datetime`='{$params['start_datetime']}',
	`end_datetime`='{$params['end_datetime']}',
	`description`='{$params['description']}',
	`rules_of_purchase`='{$params['rules_of_purchase']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            $id = mysqli_insert_id($con);
            $params['id'] = $id;
            $params['table_name'] = 'offers';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'add';
            DbMethods::logs($params);
            return ["id" => (int)$id];
        }
        /* 1:------------------------------method start here addOffers 1------------------------------*/
        static function addOffers($params){
            $con = $params['dbconnection'];
            $offers = stripslashes($params['offers']);
            $offers = json_decode($offers, true);
            $outletObj = [];
            $titles = "";
            for($i = 0; $i < count($offers); $i++){
                $outletObj['outlet_id'] = $offers[$i]['outlet_id'];
                $outletObj['title'] = addslashes($offers[$i]['title']);
                if($offertexist = DbMethods::checkOffer($outletObj) == 'offertexist'){
                    $titles .= $offers[$i]['title'].",";
                }
                unset($outletObj);
            }
            if($titles != ''){
                $titles = substr($titles, 0, -1);
                return ["msg" => 'exists', "title" => $titles];
            }
            for($i = 0; $i < count($offers); $i++){
                $outletObj['dbconnection'] = $params['dbconnection'];
                $outletObj['outlet_id'] = addslashes($offers[$i]['outlet_id']);
                $outletObj['title'] = addslashes($offers[$i]['title']);
                $outletObj['SKU'] = addslashes($offers[$i]['SKU']);
                $outletObj['search_tags'] = addslashes($offers[$i]['search_tags']);
                $outletObj['valid_for'] = addslashes($offers[$i]['valid_for']);
                $outletObj['image'] = addslashes($offers[$i]['image']);
                $outletObj['approx_saving'] = addslashes($offers[$i]['approx_saving']);
                $outletObj['price'] = addslashes($offers[$i]['price']);
                $outletObj['special_price'] = addslashes($offers[$i]['special_price']);
                $outletObj['start_datetime'] = addslashes($offers[$i]['start_datetime']);
                $outletObj['end_datetime'] = addslashes($offers[$i]['end_datetime']);
                $outletObj['special'] = addslashes($offers[$i]['special']);
                if($outletObj['special'] == '1'){
                    $outletObj['special_type'] = addslashes($offers[$i]['special_type']);
                }
                else{
                    $outletObj['special_type'] = "";
                }
                $outletObj['renew'] = addslashes($offers[$i]['renew']);
                $outletObj['redemptions'] = addslashes($offers[$i]['redemptions']);
                $outletObj['per_user'] = addslashes($offers[$i]['per_user']);
                $outletObj['description'] = addslashes($offers[$i]['description']);
                $outletObj['rules_of_purchase'] = addslashes($offers[$i]['rules_of_purchase']);
                DbMethods::addOffer($outletObj);
                unset($outletObj);
            }
            return "added";
        }
        /* 1:------------------------------method start here updateOffer 1------------------------------*/
        static function updateOffer($params){
            $con = $params['dbconnection'];
            $outlet_id = '';
            $title = '';
            $SKU = '';
            $search_tags = '';
            $description = '';
            $rules_of_purchase = '';
            $price = '';
            $special_price = '';
            $approx_saving = '';
            $image = '';
            $start_datetime = '';
            $end_datetime = '';
            $valid_for = '';
            $special = '';
            $special_type = '';
            $renew = '';
            $redemptions = '';
            if($offertexist = DbMethods::checkOffer($params) == 'offertexist'){
                return 'offertexist';
            }
            if($params['start_datetime'] != ""){
                $params['start_datetime'] = date_format(date_create($params['start_datetime']), "Y-m-d H:i:s");
            }
            if($params['end_datetime'] != ""){
                $params['end_datetime'] = date_format(date_create($params['end_datetime']), "Y-m-d H:i:s");
            }
            if($params['outlet_id'] != ""){
                $outlet_id = " `outlet_id`='{$params['outlet_id']}' ,";
            }
            if($params['title'] != ""){
                $title = " `title`='{$params['title']}' ,";
            }
            if($params['SKU'] != ""){
                $SKU = " `SKU`='{$params['SKU']}' ,";
            }
            if($params['search_tags'] != ""){
                $search_tags = " `search_tags`='{$params['search_tags']}' ,";
            }
            if($params['image'] != ""){
                $image = " `image`='{$params['image']}' ,";
            }
            if($params['start_datetime'] != ""){
                $start_datetime = " `start_datetime`='{$params['start_datetime']}' ,";
            }
            if($params['end_datetime'] != ""){
                $end_datetime = " `end_datetime`='{$params['end_datetime']}' ,";
            }
            if($params['price'] != ""){
                $price = " `price`='{$params['price']}' ,";
            }
            if($params['special_price'] != ""){
                $special_price = " `special_price`='{$params['special_price']}' ,";
            }
            if($params['approx_saving'] != ""){
                $approx_saving = " `approx_saving`='{$params['approx_saving']}' ,";
            }
            if($params['valid_for'] != ""){
                $valid_for = " `valid_for`='{$params['valid_for']}' ,";
            }
            if($params['special'] != ""){
                $special = " `special`='{$params['special']}' ,";
            }
            if($params['special_type'] != ""){
                $special_type = " `special_type`='{$params['special_type']}' ,";
            }
            if($params['renew'] != ""){
                $renew = " `renew`='{$params['renew']}' ,";
            }
            if($params['redemptions'] != ""){
                $redemptions = " `redemptions`='{$params['redemptions']}' ,";
            }
            if($params['description'] != ""){
                $description = " `description`='{$params['description']}' ,";
            }
            if($params['rules_of_purchase'] != ""){
                $rules_of_purchase = " `rules_of_purchase`='{$params['rules_of_purchase']}' ,";
            }
            $query = "UPDATE `offers` SET
		".$outlet_id."
		".$title."
		".$SKU."
		".$search_tags."
		".$description."
		".$rules_of_purchase."
		".$image."
		".$start_datetime."
		".$end_datetime."
		".$price."
		".$special_price."
		".$approx_saving."
		".$valid_for."
		".$special."
		".$special_type."
		".$renew."
		".$redemptions."
		`id`='{$params['id']}'
		WHERE `id`='{$params['id']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            $params['table_name'] = 'offers';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'update';
            DbMethods::logs($params);
            return 'updated';
        }
        /* 1:------------------------------method start here updateOffer 1------------------------------*/
        static function updateMultipleOffer($params){
            $con = $params['dbconnection'];
            $con = $params['dbconnection'];
            $start_datetime = '';
            $end_datetime = '';
            $active = '';
            if($params['start_datetime'] != ""){
                $start_datetime = " `start_datetime`='{$params['start_datetime']}' ,";
            }
            if($params['end_datetime'] != ""){
                $end_datetime = " `end_datetime`='{$params['end_datetime']}' ,";
            }
            if($params['active'] != ""){
                $active = " `active`='{$params['active']}' ,";
            }
            $idss = explode(",", $params['ids']);
            foreach($idss as $id){
                $query = "UPDATE `offers` SET
			".$start_datetime."
			".$end_datetime."
			".$active."
			`id`='{$id}'
			WHERE `id`='{$id}'";
                mysqli_query($con, $query);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
                $params['table_name'] = 'offers';
                $params['table_id'] = $id;
                $params['admin_logstype'] = 'update';
                DbMethods::logs($params);
            }
            return "updated";
        }
        /* 1:------------------------------method start here checkOutlet ------------------------------*/
        static function checkOffer($params){
            $con = $params['dbconnection'];
            $id = '';
            if($params['id'] != ''){
                $id = "  AND 	`id` !='{$params['id']}' ";
            }
            $query = "SELECT `id` FROM `offers` WHERE `SKU`='{$params['SKU']}' AND `outlet_id` ='{$params['outlet_id']}'  ".$id."";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                return "offertexist";
            }
            else{
                return null;
            }
        }
        /* 1:------------------------------method start here deleteOffer 1------------------------------*/
        static function deleteOffer($params){
            $con = $params['dbconnection'];
            $queryd = "DELETE  FROM  `offers`  WHERE `id`='{$params['id']}'";
            mysqli_query($con, $queryd);
            $params['table_name'] = 'offers';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'delete';
            DbMethods::logs($params);
            return "removed";
        }
        /* 1:------------------------------method start here ADOffer 1------------------------------*/
        static function ADOffer($params){
            $con = $params['dbconnection'];
            $con->begin_transaction();
            try{
                $query = "SELECT
			`id`
			FROM  `offers`
			WHERE `id`='{$params['id']}' ";
                $result = mysqli_query($con, $query);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
                if(mysqli_num_rows($result) > 0){
                    $row = mysqli_fetch_assoc($result);
                    $query0 = "UPDATE `offers`
				SET `active`='{$params['active']}'
				WHERE `id`='{$row['id']}'";
                    mysqli_query($con, $query0);
                    if(mysqli_error($con) != ''){
                        return "mysql_Error:-".mysqli_error($con);
                    }
                    $con->commit();
                    $params['table_name'] = 'offers';
                    $params['table_id'] = $params['id'];
                    $params['admin_logstype'] = 'update';
                    DbMethods::logs($params);
                    return "updated";
                }
            }
            catch(\Exception $e){
                $con->rollBack();
                throw $e;
            }
            catch(\Throwable $e){
                $con->rollBack();
                throw $e;
            }
        }
        /* 21:------------------------------method start here getOffers 21------------------------------*/
        static function getOffers($params){
            $con = $params['dbconnection'];
            $offers = [];
            $outlet_id = "";
            $datarange = "";
            $search = "";
            if($params['search'] != ''){
                $search = " AND (ou.`name` LIKE '%{$params['search']}%' OR
	of.`search_tags` LIKE '%{$params['search']}%' OR
	of.`SKU` LIKE '%{$params['search']}%' OR
	of.`title` LIKE '%{$params['search']}%')";
            }
            if($params['outlet_id'] != ''){
                $outlet_id = " AND of.`outlet_id`='{$params['outlet_id']}'";
            }
            if($params['start_date'] != "" && $params['end_date']){
                $datarange = " AND  of.`created_at` > '{$params['start_date']}' AND of.`created_at` < '{$params['end_date']}'";
            }
            $sortby = "of.`id` DESC";
            if($params['sortby'] != '' && $params['orderby'] != ''){
                if($params['sortby'] == 'outlet_name'){
                    $sortby = "ou.`name` ".$params['orderby'];
                }
                else if($params['sortby'] == 'live'){
                    $sortby = "`remaining_days` DESC";
                }
                else if($params['sortby'] == 'expired'){
                    $sortby = "`remaining_days` ASC";
                }
                else{
                    $sortby = "ou.`{$params['sortby']}` ".$params['orderby'];
                }
            }
            $offersCount = "";
            if($offersCount = $con->query("SELECT
	of.`id`
	FROM  `offers` as of
	INNER JOIN `outlets` as ou ON(of.`outlet_id`=ou.`id`)
	WHERE of.`id`=of.`id` ".$search." ".$outlet_id." ".$datarange."")
            ){
                $offersCount = $offersCount->num_rows;
            }
            $query = "SELECT
	of.*,
	datediff(`end_datetime`, NOW()) as remaining_days,
	ou.`name` as outlet_name
	FROM  `offers` as of
	INNER JOIN `outlets` as ou ON(of.`outlet_id`=ou.`id`)
	WHERE of.`id`=of.`id` ".$search." ".$outlet_id." ".$datarange."
	ORDER BY ".$sortby." LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $row['image_name'] = $row['image'];
                    if(empty(DbMethods::checkImageSrc($row['image']))){
                        $row['image'] = '';
                    }
                    if($row['special'] == null){
                        $row['special'] = '0';
                    }
                    $offers[] = $row;
                }
            }
            if($offersCount != ''){
                return ["offers" => $offers, "offersCount" => $offersCount];
            }
            else{
                return '';
            }
        }
        /* 21:------------------------------method start here getOfferDetail 21------------------------------*/
        static function getOfferDetail($params){
            $con = $params['dbconnection'];
            $offersDetail = [];
            $query = "SELECT
	of.*,
	ou.`merchant_id`,
	ou.`category_id`,
	f.`id` as favourite_id,
	ou.`name`,
	ou.`phone`,
	ou.`address`,
	ou.`latitude`,
	ou.`longitude`,
	ou.`logo`
	FROM `outlets` as ou INNER JOIN `offers` as of ON(ou.`id`=of.`outlet_id`)
	LEFT OUTER JOIN `favourite` as f ON(f.`offer_id` =of.`id` AND f.`user_id`='{$params['user_id']}')
	WHERE ".offerFilterJoin." AND of.`id`='{$params['id']}'";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                $row['image_name'] = $row['image'];
                $row['logo_name'] = $row['logo'];
                if(empty(DbMethods::checkImageSrc($row['image']))){
                    $row['image'] = '';
                }
                if(empty(DbMethods::checkImageSrc($row['logo']))){
                    $row['logo'] = '';
                }
                if($row['favourite_id'] == null){
                    $row['favourite_id'] = '0';
                }
                else{
                    $row['favourite_id'] = '1';
                }
                $offersDetail[] = $row;
            }
            if(!empty($offersDetail)){
                return $offersDetail;
            }
            return "";
        }
        /* 1:------------------------------method start here addNotification 1------------------------------*/
        static function addNotification($params){
            $con = $params['dbconnection'];
            $dates = '';
            $greater_than = '';
            $less_than = '';
            $archive = "";
            if($params['audience'] == 'specificUsers' || $params['audience'] == 'specificusers'){
                if($params['spType'] != '1'){
                    $spusers = stripslashes($params['specificUsers']);
                    $spusers = json_decode($spusers, true);
                    for($i = 0; $i < count($spusers); $i++){
                        $specificUsers .= $spusers[$i]['email'].",";
                    }
                    $params['specificUsers'] = substr($specificUsers, 0, -1);
                }
                $params['specificUsers2'] = $params['specificUsers'];
                if($params['specificUsers'] != ""){
                    $params['specificUsers'] = " `specificUsers`='{$params['specificUsers']}' ,";
                }
            }
            else if($params['audience'] == 'userCreatedDate'){
                if($params['dates'] != ""){
                    $dates = " `dates`='{$params['dates']}' ,";
                }
                if($params['dates'] == "Both"){
                    if($params['greater_than'] != ""){
                        $greater_than = " `greater_than`='{$params['greater_than']}' ,";
                    }
                    if($params['less_than'] != ""){
                        $less_than = " `less_than`='{$params['less_than']}' ,";
                    }
                }
                else if($params['dates'] == "Greater"){
                    if($params['greater_than'] != ""){
                        $greater_than = " `greater_than`='{$params['greater_than']}' ,";
                    }
                }
                else if($params['dates'] == "Greater"){
                    if($params['greater_than'] != ""){
                        $greater_than = " `greater_than`='{$params['greater_than']}' ,";
                    }
                }
            }
            if($params['archive'] == '0' || $params['archive'] == '1'){
                $archive = " `archive`='{$params['archive']}' ,";
            }
            $query = "INSERT INTO `notifications` SET
	`admin_id`='{$params['admin_id']}',
	`title`='{$params['title']}',
	`message`='{$params['message']}',
	`audience`='{$params['audience']}',
	`platform`='{$params['platform']}',
	`operator`='{$params['operator']}',
	".$archive."
	".$dates."
	".$greater_than."
	".$less_than."
	".$params['specificUsers']."
	`push`='{$params['push']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            $id = mysqli_insert_id($con);
            $params['id'] = $id;
            if($params['push'] == '1' && $params['archive'] != '1'){
                DbMethods::sendNotification($params);
            }
            $params['table_name'] = 'notifications';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'add';
            DbMethods::logs($params);
            return ["id" => (int)$id];
        }
        /* 1:------------------------------method start here reSendNotification 1------------------------------*/
        static function reSendNotification($params){
            $con = $params['dbconnection'];
            $query = "SELECT
	*
	FROM `notifications`
	WHERE `id`='{$params['notification_id']}' ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                $params['title'] = $row['title'];
                $params['push'] = $row['push'];
                $params['audience'] = $row['audience'];
                $params['platform'] = $row['platform'];
                $params['operator'] = $row['operator'];
                $params['dates'] = $row['dates'];
                $params['greater_than'] = $row['greater_than'];
                $params['less_than'] = $row['less_than'];
                $params['specificUsers'] = $row['specificUsers'];
                $params['message'] = $row['message'];
                $params['spType'] = '1';
                return DbMethods::addNotification($params);
            }
        }
        /* 1:------------------------------method start here sendNotification ------------------------------*/
        static function sendNotification($params){
            $con = $params['dbconnection'];
            $tokens_ios = [];
            $tokens_android = [];
            $tokens_all = [];
            $created_at = "";
            $specificUsers = "";
            $platform = "";
            $operator = "";
            //print_r($params);
            if($params['audience'] == 'userCreatedDate'){
                if($params['dates'] == "Both"){
                    $created_at = " AND (u.`created_at` > '{$params['greater_than']}' AND u.`created_at` < '{$params['less_than']}')";
                }
                else if($params['dates'] == "Greater"){
                    $created_at = " AND u.`created_at` > '{$params['greater_than']}'";
                }
                else if($params['dates'] == "Greater"){
                    $created_at = " AND u.`created_at` < '{$params['greater_than']}'";
                }
            }
            else if($params['audience'] == 'specificUsers' || $params['audience'] == 'specificusers'){
                $specificUsers = "  AND FIND_IN_SET(u.`email`, '{$params['specificUsers2']}')";
            }
            if($params['platform'] == "android"){
                $platform = " AND `deviceType`='android'";
            }
            else if($params['platform'] == "ios"){
                $platform = " AND `deviceType`='ios'";
            }
            if($params['operator'] == "ooredoo"){
                $operator = " AND u.`network`='ooredoo'";
            }
            else if($params['operator'] == "vodafone"){
                $operator = " AND u.`network`='vodafone'";
            }
            $language = "";
            $date = "";
            $query = "SELECT
	DISTINCT(au.`token`) as token,
    au.`deviceType`,
	NOW() as created_at
	FROM `users` as u INNER JOIN `authkeys` as au ON(u.`id`=au.`user_id`)
	WHERE  au.`token` !='' ".$specificUsers." ".$created_at."  ".$created_at."  ".$platform."  ".$operator."
	ORDER BY au.`id` DESC ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $date = $row['created_at'];
                    if($row['deviceType'] == 'ios'){
                        $tokens_ios[] = $row['token'];
                    }
                    else if($row['deviceType'] == 'android'){
                        $tokens_android[] = $row['token'];
                    }
                }
            }
            $dir = str_replace("api/v1/cms/", "common/", $params['apiBasePath']."pushNotification.php");
            $params['Authorization'] = "push!UP!Qatar>m";
            /*print_r($tokens_ios);
	echo "========";
	print_r($tokens_android);
	exit;*/
            if(!empty($tokens_ios) && count($tokens_ios) > 0){
                $tokens_ios = array_chunk($tokens_ios, 1000);
                foreach($tokens_ios as $iostokens){
                    $params['token'] = $iostokens;
                    $params['deviceType'] = 'ios';
                    DbMethods:: post_async($dir, [
                        'token'         => $params['token'],
                        'title'         => $params['title'],
                        'message'       => $params['message'],
                        'id'            => $params['id'],
                        'deviceType'    => $params['deviceType'],
                        'date'          => $date,
                        'Authorization' => $params['Authorization']
                    ]);
                }
            }
            if(!empty($tokens_android) && count($tokens_android) > 0){
                $tokens_android = array_chunk($tokens_android, 1000);
                foreach($tokens_android as $androidtokens){
                    $params['token'] = $androidtokens;
                    $params['deviceType'] = 'android';
                    DbMethods:: post_async($dir, [
                        'token'         => $params['token'],
                        'title'         => $params['title'],
                        'message'       => $params['message'],
                        'id'            => $params['id'],
                        'deviceType'    => $params['deviceType'],
                        'date'          => $date,
                        'Authorization' => $params['Authorization']
                    ]);
                }
            }
            return "sended";
        }
        /* 1:------------------------------method start here updateNotification 1------------------------------*/
        static function updateNotification($params){
            $con = $params['dbconnection'];
            $dates = '';
            $greater_than = '';
            $less_than = '';
            $archive = "";
            if($params['audience'] == 'specificUsers' || $params['audience'] == 'specificusers'){
                if($params['spType'] != '1'){
                    $spusers = stripslashes($params['specificUsers']);
                    $spusers = json_decode($spusers, true);
                    for($i = 0; $i < count($spusers); $i++){
                        $specificUsers .= $spusers[$i]['email'].",";
                    }
                    $params['specificUsers'] = substr($specificUsers, 0, -1);
                }
                $params['specificUsers2'] = $params['specificUsers'];
                if($params['specificUsers'] != ""){
                    $params['specificUsers'] = " `specificUsers`='{$params['specificUsers']}' ,";
                }
            }
            else if($params['audience'] == 'userCreatedDate'){
                if($params['dates'] != ""){
                    $dates = " `dates`='{$params['dates']}' ,";
                }
                if($params['dates'] == "Both"){
                    if($params['greater_than'] != ""){
                        $greater_than = " `greater_than`='{$params['greater_than']}' ,";
                    }
                    if($params['less_than'] != ""){
                        $less_than = " `less_than`='{$params['less_than']}' ,";
                    }
                }
                else if($params['dates'] == "Greater"){
                    if($params['greater_than'] != ""){
                        $greater_than = " `greater_than`='{$params['greater_than']}' ,";
                    }
                }
                else if($params['dates'] == "Greater"){
                    if($params['greater_than'] != ""){
                        $greater_than = " `greater_than`='{$params['greater_than']}' ,";
                    }
                }
            }
            if($params['archive'] == '0' || $params['archive'] == '1'){
                $archive = " `archive`='{$params['archive']}' ,";
            }
            $query = "UPDATE `notifications` SET
	`title`='{$params['title']}',
	`message`='{$params['message']}',
	`audience`='{$params['audience']}',
	`platform`='{$params['platform']}',
	`operator`='{$params['operator']}',
	".$archive."
	".$dates."
	".$greater_than."
	".$less_than."
	".$params['specificUsers']."
	`push`='{$params['push']}'
	WHERE `id`='{$params['id']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            return 'updated';
        }
        /* 1:------------------------------method start here deleteNotification 1------------------------------*/
        static function deleteNotification($params){
            $con = $params['dbconnection'];
            $queryd = "DELETE  FROM  `notifications` WHERE `id`='{$params['id']}'";
            mysqli_query($con, $queryd);
            $params['table_name'] = 'notifications';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'delete';
            DbMethods::logs($params);
            return "removed";
        }
        /* 21:------------------------------method start here getNotifications 21------------------------------*/
        static function getNotifications($params){
            $con = $params['dbconnection'];
            $notifications = [];
            $search = "";
            if($params['search'] != ''){
                $search = " AND (`title` LIKE '%{$params['search']}%' OR
	`message` LIKE '%{$params['search']}%')";
            }
            $notificationsCount = "";
            if($notificationsCount = $con->query("SELECT `id` FROM  `notifications` WHERE `id`=`id` ".$search."")){
                $notificationsCount = $notificationsCount->num_rows;
            }
            $query = "SELECT
	*
	FROM  `notifications`
	WHERE `id`=`id`  ".$search."
	ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $notifications[] = $row;
                }
            }
            if($notificationsCount != ''){
                return ["notifications" => $notifications, "notificationsCount" => $notificationsCount];
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here addUser ------------------------------*/
        public function createCode($i){
            try{
                $datetime = date('Y-m-d H:i:s');
                $string_1 = "";
                $chars = "AB1CD2EF3GH4IJ5KL6MN7OP8QR9ST0UVWXYZabcdefghijklmnopqrstuvwxyz";
                for($k = 0; $k < 10; $k++){
                    $string_1 .= substr($chars, rand(0, strlen($chars)), 1);
                }
                $string_f = substr($string_1, -1).substr(md5($datetime.$string_1.substr($string_1, 1)), -5);
                return $string_f;
            }
            catch(\Exception $e){
                throw $e;
            }
        }
        /* 1:------------------------------method start here addAccessCode 1------------------------------*/
        static function addAccessCode($params){
            $con = $params['dbconnection'];
            for($i = 0; $i < $params['number']; $i++){
                if($params['code'] == ''){
                    $code = dbMethods::createCode($i);
                }
                else{
                    if(DbMethods::checkCode($params) == 'codeexist'){
                        return 'codeexist';
                    }
                    $code = $params['code'];
                }
                $parent_id = "";
                if($params['parent_id'] != ""){
                    $parent_id = "`parent_id`='{$params['parent_id']}',";
                }
                $multiple = "";
                if($params['number'] != '1'){
                    $multiple = "`multiple`='1',";
                }
                $query = "INSERT INTO `accesscodes` SET
		".$parent_id."
		".$multiple."
		`title`='{$params['title']}',
		`code`='{$code}',
		`redemptions`='{$params['redemptions']}',
		`expiry_datetime`='{$params['expiry_datetime']}',
		`days`='{$params['days']}'";
                mysqli_query($con, $query);
                if(mysqli_error($con) != ''){
                    $params['number']++;
                    continue;
                }
                $id = mysqli_insert_id($con);
                $params['id'] = $id;
                if($params['number'] > 1 && $parent_id == ""){
                    mysqli_query($con, "UPDATE `accesscodes` SET `parent_id`='{$params['id']}' WHERE `id`='{$params['id']}'");
                }
                if($params['parent_id'] == ''){
                    $params['parent_id'] = $id;
                }
                $params['table_name'] = 'accesscodes';
                $params['table_id'] = $params['id'];
                $params['admin_logstype'] = 'add';
                DbMethods::logs($params);
            }
            return ["id" => (int)$id];
        }
        /* 1:------------------------------method start here checkCode ---------------------------1*/
        static function checkCode($params){
            $con = $params['dbconnection'];
            $id = "";
            if($params['id'] != ""){
                $id = " AND `id` !='{$params['id']}' ";
            }
            $query = "SELECT `code` FROM  `accesscodes` WHERE `code`='{$params['code']}' ".$id." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                return 'codeexist';
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here updateAccessCode 1------------------------------*/
        static function updateAccessCode($params){
            $con = $params['dbconnection'];
            $title = '';
            $code = '';
            $redemptions = '';
            $redeemed = '';
            $expiry_datetime = '';
            $days = '';
            $multiple = '';
            $status = '';
            if($params['code'] != "" && DbMethods::checkCode($params) == 'codeexist'){
                return 'codeexist';
            }
            $subquery = "";
            if($params['title'] != ""){
                $title = " `title`='{$params['title']}' ,";
            }
            if($params['code'] != ""){
                $code = " `code`='{$params['code']}' ,";
            }
            if($params['redemptions'] != ""){
                $redemptions = " `redemptions`='{$params['redemptions']}' ,";
            }
            if($params['redeemed'] != ""){
                $redeemed = " `redeemed`='{$params['redeemed']}' ,";
            }
            if($params['expiry_datetime'] != ""){
                $expiry_datetime = " `expiry_datetime`='{$params['expiry_datetime']}' ,";
            }
            if($params['days'] != ""){
                $days = " `days`='{$params['days']}' ,";
            }
            if($params['multiple'] != ""){
                $multiple = " `multiple`='{$params['multiple']}' ,";
            }
            if($params['status'] != ""){
                $status = " `status`='{$params['status']}' ,";
            }
            $query = "UPDATE `accesscodes` SET
	".$title."
	".$code."
	".$redemptions."
	".$redeemed."
	".$expiry_datetime."
	".$days."
	".$multiple."
	".$status."
	`id`='{$params['id']}'
	WHERE `id`='{$params['id']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            $params['table_name'] = 'accesscodes';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'update';
            DbMethods::logs($params);
            return 'updated';
        }
        /* 1:------------------------------method start here deleteAccessCode 1------------------------------*/
        static function deleteAccessCode($params){
            $con = $params['dbconnection'];
            $queryd = "DELETE  FROM  `accesscodes`  WHERE `id`='{$params['id']}'";
            mysqli_query($con, $queryd);
            $params['table_name'] = 'accesscodes';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'delete';
            DbMethods::logs($params);
            return "removed";
        }
        /* 1:------------------------------method start here getAccessCodes 1------------------------------*/
        static function getAccessCodes($params){
            $con = $params['dbconnection'];
            $accesscodes = [];
            $search = "";
            if($params['search'] != ''){
                $search = " AND (ac.`title` LIKE '%{$params['search']}%')";
            }
            $innerjoinapp_id = "";
            if(isset($params['user_app_id']) && $params['user_app_id'] != ""){
                $innerjoinapp_id = "INNER JOIN `app_access` as appa
	ON(ac.`id`=appa.`accesscode_id` AND appa.`app_id`='{$params['user_app_id']}')";
            }
            $accesscodesCount = "";
            if($accesscodesCount = $con->query("SELECT ac.`id` FROM  `accesscodes` as ac ".$innerjoinapp_id."
	 WHERE ac.`id`=ac.`id`  AND (ac.`parent_id` IS NULL OR  ac.`id` =ac.`parent_id`)  ".$search."")
            ){
                $accesscodesCount = $accesscodesCount->num_rows;
            }
            $query = "SELECT ac.* FROM  `accesscodes` as ac ".$innerjoinapp_id."
	WHERE ac.`id`=ac.`id` AND (ac.`parent_id` IS NULL OR  ac.`id` =ac.`parent_id`)  ".$search."
	ORDER BY ac.`id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    unset($row['parent_id']);
                    $accesscodes[] = $row;
                }
            }
            if($accesscodesCount != ''){
                return ["accesscodes" => $accesscodes, "accesscodesCount" => $accesscodesCount];
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here getMultipleAccessCode 1------------------------------*/
        static function getMultipleAccessCode($params){
            $con = $params['dbconnection'];
            $accesscodes = [];
            $search = "";
            if($params['search'] != ''){
                $search = " AND (ac.`title` LIKE '%{$params['search']}%')";
            }
            $innerjoinapp_id = "";
            if(isset($params['user_app_id']) && $params['user_app_id'] != ""){
                $innerjoinapp_id = "INNER JOIN `app_access` as appa
	ON(ac.`id`=appa.`accesscode_id` AND appa.`app_id`='{$params['user_app_id']}')";
            }
            $accesscodesCount = "";
            if($accesscodesCount = $con->query("SELECT ac.`id` FROM  `accesscodes` as ac  ".$innerjoinapp_id."
	WHERE ac.`id`=ac.`id`  AND (ac.`id`='{$params['id']}' OR  ac.`parent_id`='{$params['id']}') ".$search."")
            ){
                $accesscodesCount = $accesscodesCount->num_rows;
            }
            $query = "SELECT ac.* FROM  `accesscodes` as ac ".$innerjoinapp_id."
	WHERE ac.`id`=ac.`id` AND (ac.`id`='{$params['id']}' OR  ac.`parent_id`='{$params['id']}')   ".$search."
	ORDER BY ac.`id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    unset($row['parent_id']);
                    unset($row['multiple']);
                    $accesscodes[] = $row;
                }
            }
            if($accesscodesCount != ''){
                return ["accesscodes" => $accesscodes, "accesscodesCount" => $accesscodesCount];
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here getOrdersWithouReview 1------------------------------*/
        static function totalSaving($params){
            $con = $params['dbconnection'];
            $query = "SELECT
		SUM(of.`approx_saving`) as totalsaving
		FROM  `offers` as of INNER JOIN `orders` as o ON(of.`id`=o.`offer_id`  )
	    INNER JOIN `outlets` as ou ON(of.`outlet_id`=ou.`id`)
		WHERE  o.`user_id`='{$params['user_id']}'";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                return $row['totalsaving'];
            }
            else{
                return 0;
            }
        }
        /* 1:------------------------------method start here totalDealsUsed 1------------------------------*/
        static function totalDealsUsed($params){
            $con = $params['dbconnection'];
            if($orderCount = $con->query("SELECT
		o.`id`
		FROM  `offers` as of INNER JOIN `orders` as o ON(of.`id`=o.`offer_id`  )
		INNER JOIN `outlets` as ou ON(of.`outlet_id`=ou.`id`)
		WHERE  o.`user_id`='{$params['user_id']}'")
            ){
                $orderCount = $orderCount->num_rows;
            }
            if($orderCount != ""){
                return $orderCount;
            }
            else{
                return 0;
            }
        }
        /* 21:------------------------------method start here getUsers 21------------------------------*/
        static function getUsers($params){
            $con = $params['dbconnection'];
            $users = [];
            $where = "";
            if($params['search'] != ''){
                $where .= " AND (u.`name` LIKE '%{$params['search']}%'  OR
	u.`email` LIKE '%{$params['search']}%' OR
	u.`phone` ='{$params['search']}')";
            }
            if($params['greater_than'] != "" && $params['less_than'] != ""){
                $where .= " AND  u.`created_at` > '{$params['greater_than']}' AND u.`created_at` < '{$params['less_than']}'";
                $params['index'] = 0;
                $params['index2'] = 100000;
            }
            if($params['start_date'] != "" && $params['end_date'] != ""){
                $where .= " AND  u.`created_at` > '{$params['start_date']}' AND u.`created_at` < '{$params['end_date']}'";
            }
            if($params['network'] != ''){
                $where .= " AND  u.`network` ='{$params['network']}'";
            }
            $sortby = "u.`id` DESC";
            $sortjoin = "";
            $sortselect = "";
            if($params['sortby'] != '' && $params['orderby'] != ''){
                if($params['sortby'] == 'subscription_status' || $params['sortby'] == 'network'){
                    if($params['sortby'] == 'subscription_status'){
                        $sortby = "s.`expiry_datetime` ".$params['orderby'];
                    }
                    else{
                        $sortby = "s.`network` ".$params['orderby'];
                    }
                    $sortjoin = "INNER JOIN `subscriptions` as s ON(u.`id` =s.`user_id`)";
                    $sortselect = ",s.`phone`,s.`start_datetime`,s.`expiry_datetime`,s.`status`,s.`network`,s.`premier_user`";
                }
                else if($params['sortby'] == 'last_timestamp'){
                    $sortby = "au.`updated_at` ".$params['orderby'];
                }
                else{
                    $sortby = "u.`{$params['sortby']}` ".$params['orderby'];
                }
            }
            $usersCount = "";
            if($usersCount = $con->query("SELECT u.`id` FROM  `users` u
	LEFT JOIN `authkeys` as au on au.id = (select id from `authkeys` where user_id = u.id order by `id` desc limit 1)
	".$sortjoin."
	WHERE u.`id`=u.`id`  ".$where."")
            ){
                $usersCount = $usersCount->num_rows;
            }
            $query = "SELECT u.*,au.`updated_at` as last_timestamp,au.`deviceType`,au.`device_info` ".$sortselect."
	FROM  `users` as u LEFT JOIN `authkeys` as au on au.id = (select id from `authkeys` where user_id = u.id order by `id` desc limit 1)
	".$sortjoin."
	WHERE u.`id`=u.`id`  ".$where."
	ORDER BY  ".$sortby."  LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    // unset($row['password']);
                    $params['user_id'] = $row['id'];
                    $row['totalsaving'] = DbMethods::totalSaving($params);
                    $row['totalDealsUsed'] = DbMethods::totalDealsUsed($params);
                    $users[] = $row;
                }
            }
            if($usersCount != ''){
                return ["users" => $users, "usersCount" => $usersCount];
            }
            else{
                return '';
            }
        }
        /* 21:------------------------------method start here getNonUsers 21------------------------------*/
        static function getNonUsers($params){
            $con = $params['dbconnection'];
            $users = [];
            $where = "";
            if($params['search'] != ''){
                $where .= " AND (`phone` ='{$params['search']}')";
            }
            if($params['greater_than'] != "" && $params['less_than']){
                $where .= " AND  `created_at` > '{$params['greater_than']}' AND `created_at` < '{$params['less_than']}'";
                $params['index'] = 0;
                $params['index2'] = 100000;
            }
            if($params['start_date'] != "" && $params['end_date']){
                $where .= " AND  `created_at` > '{$params['start_date']}' AND `created_at` < '{$params['end_date']}'";
            }
            if(isset($params['filterby'])){
                if($params['filterby'] == "premier"){
                    $where .= " AND `premier_user`='1'";
                }
            }
            $usersCount = "";
            if($usersCount = $con->query("SELECT `id` FROM  `non_registered_users`
	WHERE `id`=`id`  ".$where."")
            ){
                $usersCount = $usersCount->num_rows;
            }
            $query = "SELECT * FROM  `non_registered_users`
	WHERE `id`=`id`  ".$where."
	ORDER BY  `id` DESC  LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $users[] = $row;
                }
            }
            if($usersCount != ''){
                return ["users" => $users, "usersCount" => $usersCount];
            }
            else{
                return '';
            }
        }
        /* 21:------------------------------method start here updateUser 21------------------------------*/
        static function updateUser($params){
            $con = $params['dbconnection'];
            if($params['email'] != ''){
                if(DbMethods::checkUserEmail($params) == 'emailexist'){
                    return 'emailexist';
                }
            }
            if($params['phone'] != ''){
                if(DbMethods::checkUserPhone($params) == 'phoneexist'){
                    return 'phoneexist';
                }
            }
            $name = '';
            $email = '';
            $phone = '';
            $password = '';
            $network = '';
            $gender = '';
            $DOB = '';
            $nationality = "";
            $subquery = "";
            if($params['name'] != ""){
                $name = " `name`='{$params['name']}' ,";
            }
            if($params['email'] != ""){
                $email = " `email`='{$params['email']}' ,";
            }
            if($params['phone'] != ""){
                $phone = " `phone`='{$params['phone']}' ,";
            }
            if($params['network'] != ""){
                $network = " `network`='{$params['network']}' ,";
            }
            if($params['gender'] != ""){
                $gender = " `gender`='{$params['gender']}' ,";
            }
            if($params['DOB'] != ""){
                $DOB = " `DOB`='{$params['DOB']}' ,";
            }
            if($params['nationality'] != ""){
                $nationality = " `nationality`='{$params['nationality']}' ,";
            }
            if($params['password'] != ""){
                $password = " `password`='{$params['password']}' ,";
            }
            /*if($params['password'] !='')
	{
		$checkPassword=DbMethods::checkUserPassword($params);
		if($checkPassword!=NULL)
		return $checkPassword;
	}*/
            $query = "UPDATE `users` SET
	".$name."
	".$email."
	".$phone."
	".$password."
	".$network."
	".$gender."
	".$DOB."
	".$nationality."
	`id`='{$params['id']}'
	WHERE `id`='{$params['id']}'";
            mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            return 'updated';
        }
        /* 1:------------------------------method start here checkEmail ---------------------------1*/
        static function checkUserEmail($params){
            $con = $params['dbconnection'];
            $query = "SELECT `email` FROM  `users` WHERE `email` ='{$params['email']}'  AND 	`id` !='{$params['id']}'  ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                return 'emailexist';
            }
            else{
                return ['email' => $params['email']];
            }
        }
        /* 1:------------------------------method start here checkUserPhone ---------------------------1*/
        static function checkUserPhone($params){
            $con = $params['dbconnection'];
            $query = "SELECT `phone` FROM  `users` WHERE `phone` ='{$params['phone']}' AND 	`id` !='{$params['id']}'  ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                return 'phoneexist';
            }
            else{
                return ['phone' => $params['phone']];
            }
        }
        /* 1:------------------------------method start here checkEmail ---------------------------1*/
        static function checkUserPassword($params){
            $con = $params['dbconnection'];
            if($params['old_password'] == $params['password']){
                return "same";
            }
            $query = "SELECT `id` FROM `users`
		WHERE `id`='{$params['id']}' AND `password`='{$params['old_password']}' ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) == 0){
                return "wrong";
            }
            else{
                return null;
            }
        }
        /* 21:------------------------------method start here getSubscriptions 21------------------------------*/
        static function getSubscriptions($params){
            $con = $params['dbconnection'];
            $subscriptions = [];
            $where = "";
            $search = "";
            if($params['search'] != ''){
                $where .= " AND (`u`.`name` LIKE '%{$params['search']}%'  OR `u`.`email` LIKE '%{$params['search']}%' OR `s`.`phone` ='{$params['search']}')";
            }
            if(isset($params['filterby'])){
                if($params['filterby'] == "premier"){
                    $where .= " AND `premier_user`='1'";
                }
                else if($params['filterby'] == "ooredoo" || $params['filterby'] == "vodafone" || $params['filterby'] == "code" || $params['filterby'] == "card"){
                    $where .= " AND `network`='{$params['filterby']}'";
                }
            }
            if($params['user_id'] != ''){
                $where .= " AND `s`.`user_id` ='{$params['user_id']}'";
            }
            if($params['start_date'] != "" && $params['end_date'] != ""){
                $where .= " AND  `s`.`updated_at` > '{$params['start_date']}' AND `s`.`updated_at` < '{$params['end_date']}'";
            }
            $subscriptionsCount = "";
            if($subscriptionsCount = $con->query("SELECT `u`.`id` FROM  `users` AS `u` INNER JOIN `subscriptions` AS `s` ON(`u`.`id` = `s`.`user_id`) WHERE `u`.`id` = `u`.`id` {$where}")){
                $subscriptionsCount = $subscriptionsCount->num_rows;
            }
            $query = "SELECT
                    `u`.`id` AS user_id,
                    `u`.`name`,
                    `u`.`email`,
                    `u`.`created_at` as account_creation,
                    `u`.`updated_at` as account_updation,
                    `s`.`id`,
                    `s`.`phone`,
                    `s`.`network`,
                    `s`.`start_datetime`,
                    `s`.`expiry_datetime`,
                    `s`.`status`,
                    `s`.`accesscode_id`,
                    `s`.`premier_user`,
                    `s`.`subscriptionContractId`,
                    `s`.`strip_charged_id`,
                    `s`.`language`,
                    `s`.`created_at`,
                    `s`.`updated_at`
                    FROM `users` AS `u` INNER JOIN `subscriptions` AS `s` ON(`u`.`id` = `s`.`user_id`)
                    WHERE `u`.`id` = `u`.`id` ".$where."
                    ORDER BY `s`.`id` DESC
                    LIMIT ".$params['index'].", ".$params['index2']."";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $params['user_id'] = $row['user_id'];
                    $row['totalDealsUsed'] = DbMethods::totalDealsUsed($params);
                    unset($row['password']);
                    $accesscodes = [];
                    $query1 = "SELECT * FROM `accesscodes` WHERE `id`='{$row['accesscode_id']}'";
                    $result1 = mysqli_query($con, $query1);
                    if(mysqli_error($con) != ''){
                        return "mysql_Error:-".mysqli_error($con);
                    }
                    if(mysqli_num_rows($result1) > 0){
                        $accesscodes = mysqli_fetch_assoc($result1);
                    }
                    $row['accesscodes'] = $accesscodes;
                    //---bilal---adding user approx savings---start---//
                    $row['approx_saving'] = 0;
                    $savings_query = "SELECT `user_id`, SUM(`approx_saving`) AS `user_approx_saving` FROM `orders` WHERE `user_id` = '{$row['user_id']}'";
                    $savings_resource = mysqli_query($con, $savings_query);
                    if(mysqli_error($con) != ''){
                        return "mysql_Error:-".mysqli_error($con);
                    }
                    if(mysqli_num_rows($savings_resource) > 0){
                        $savings_result = mysqli_fetch_assoc($savings_resource);
                        $row['approx_saving'] = round((float)$savings_result["user_approx_saving"], 3);
                    }
                    //---bilal---adding user approx savings---end---//
                    $subscriptions[] = $row;
                }
            }
            if($subscriptionsCount != ''){
                return [
                    "subscriptions"      => $subscriptions,
                    "subscriptionsCount" => $subscriptionsCount
                ];
            }
            else{
                return '';
            }
        }
        /* 21:------------------------------method start here getSubscriptionLogs 21------------------------------*/
        static function getSubscriptionLogs($params){
            $con = $params['dbconnection'];
            $subscriptions = [];
            $where = "";
            if($params['search'] != ''){
                $where = "WHERE  (u.`name` LIKE '%".$params['search']."%' OR u.`email` LIKE '%".$params['search']."%' )";
            }
            if($params['user_id'] != ''){
                if($where == ""){
                    $where = "WHERE  s.`user_id` ='{$params['user_id']}'";
                }
                else{
                    $where .= " AND   s.`user_id` ='{$params['user_id']}'";
                }
            }
            $subscriptionsCount = "";
            if($subscriptionsCount = $con->query("SELECT u.`id` FROM  `users` as u INNER JOIN `subscriptions_log` as s ON(u.`id`=s.`user_id`) ".$where."")){
                $subscriptionsCount = $subscriptionsCount->num_rows;
            }
            $query = "SELECT
	u.`id` as user_id,
	u.`name`,
	u.`email`,
	s.`id`,
	s.`phone`,
	s.`network`,
	s.`start_datetime`,
	s.`expiry_datetime`,
	s.`price_point`,
	s.`response_code`,
	s.`type`,
	s.`language`,
	s.`created_at`
	
	FROM  `users` as u INNER JOIN `subscriptions_log` as s ON(u.`id`=s.`user_id`)
	".$where."
	ORDER BY s.`id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    unset($row['password']);
                    $subscriptions[] = $row;
                }
            }
            if($subscriptionsCount != ''){
                return ["subscriptions" => $subscriptions, "subscriptionsCount" => $subscriptionsCount];
            }
            else{
                return '';
            }
        }
        /* 21:------------------------------method start here unsubscribe 21------------------------------*/
        static function unsubscribe($params){
            $con = $params['dbconnection'];
            $query = "SELECT
	au.`auth_key`,
	s.`id`,
	s.`user_id`,
	s.`phone`,
	s.`network`
	FROM  `subscriptions` as s INNER JOIN `authkeys` as au ON(au.`user_id`=s.`user_id`)
	WHERE s.`user_id`='{$params['user_id']}' AND s.`status`='1'";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                $dir = str_replace('cms', 'subscription', $params['apiBasePath']);
                if($row['network'] == 'ooredoo'){
                    $dir = $dir."unsubscribe";
                }
                else{
                    $dir = $dir."CancelSubscriptionContractRequest";
                }
                DbMethods:: post_async($dir, [
                    'user_id'       => $row['user_id'],
                    'phone'         => $row['phone'],
                    'Authorization' => $row['auth_key']
                ]);
                return "unsubscribed";
            }
        }
        /* 21:------------------------------method start here getFavouriteOffers 21------------------------------*/
        static function getFavouriteOffers($params){
            $con = $params['dbconnection'];
            $usersfavouriteoffers = [];
            $usersfavouriteoffersCount = "";
            if($usersfavouriteoffersCount = $con->query("SELECT
		u.`id`
		FROM  `users` as u
		INNER JOIN `favourite` as f ON(u.`id`=f.`user_id`)
		INNER JOIN `offers` as of ON(of.`id`=f.`offer_id`)
		WHERE   f.`user_id` ='{$params['user_id']}'
		ORDER BY f.`id`")
            ){
                $usersfavouriteoffersCount = $usersfavouriteoffersCount->num_rows;
            }
            $query = "SELECT
		u.*,
		f.`id` as favourite_id,
		of.`start_datetime`,
		of.`end_datetime`,
		of.`approx_saving`,
		of.`valid_for`,
		of.`special`
		FROM  `users` as u
		INNER JOIN `favourite` as f ON(u.`id`=f.`user_id`)
		INNER JOIN `offers` as of ON(of.`id`=f.`offer_id`)
		WHERE   f.`user_id` ='{$params['user_id']}'
		ORDER BY f.`id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    unset($row['password']);
                    $usersfavouriteoffers[] = $row;
                }
            }
            if($usersfavouriteoffersCount != ''){
                return [
                    "usersfavouriteoffers"      => $usersfavouriteoffers,
                    "usersfavouriteoffersCount" => $usersfavouriteoffersCount
                ];
            }
            else{
                return '';
            }
        }
        /* 21:------------------------------method start here getOrders 21------------------------------*/
        static function getOrders($params){
            $con = $params['dbconnection'];
            $where = "";
            if($params['search'] != ''){
                $where = " AND (of.`title` LIKE '%{$params['search']}%'  OR
		ou.`name` LIKE '%{$params['search']}%' OR
		o.`id` ='{$params['search']}' OR
		o.`user_id` ='{$params['search']}')";
            }
            if($params['user_id'] != ''){
                $where .= " AND  o.`user_id` ='{$params['user_id']}'";
            }
            if($params['start_date'] != "" && $params['end_date'] != ""){
                $where .= " AND  CAST(o.`created_at` AS DATE) >= '{$params['start_date']}' AND o.`created_at` <= '{$params['end_date']}'";
            }
            $allOrders = [];
            $allOrdersCount = "";
            if($allOrdersCount = $con->query("SELECT
		of.`id`
		FROM  `offers` as of
		INNER JOIN `outlets` as ou ON(of.`outlet_id`=ou.`id`)
		INNER JOIN `orders` as o ON(of.`id`=o.`offer_id`  )
		INNER JOIN `users` as u ON(u.`id`=o.`user_id`  )
		LEFT OUTER JOIN `reviews`  r ON(r.`order_id`=o.`id`)
		WHERE of.`id` =of.`id` ".$where."")
            ){
                $allOrdersCount = $allOrdersCount->num_rows;
            }
            $query = "SELECT
		of.`id`,
		of.`outlet_id`,
		of.`title`,
		of.`image`,
		of.`approx_saving`,
		o.`id` as order_id,
		o.`user_id`,
		u.`name` as user_name,
		ou.`name` as outlet_name,
		ou.`address` as outlet_address,
		o.`created_at` as order_created_at,
		r.id as review
		FROM  `offers` as of
		INNER JOIN `outlets` as ou ON(of.`outlet_id`=ou.`id`)
		INNER JOIN `orders` as o ON(of.`id`=o.`offer_id`  )
		INNER JOIN `users` as u ON(u.`id`=o.`user_id`  )
		LEFT OUTER JOIN `reviews`  r ON(r.`order_id`=o.`id`)
		WHERE of.`id` =of.`id` ".$where."
		ORDER BY o.`id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    if($row['review'] == null){
                        $row['review'] = 'No';
                    }
                    else{
                        $row['review'] = 'Yes';
                    }
                    $row['totalsaving'] = DbMethods::totalSaving($params);
                    $allOrders[] = $row;
                }
            }
            if($allOrdersCount != ''){
                return ["allOrders" => $allOrders, "allOrdersCount" => $allOrdersCount];
            }
            else{
                return '';
            }
        }
        /* 21:------------------------------method start here getOrderReviews 21------------------------------*/
        static function getOrderReviews($params){
            $con = $params['dbconnection'];
            $allReviews = [];
            $allReviewsCount = "";
            if($allReviewsCount = $con->query("SELECT `id` FROM  `reviews`
		WHERE `order_id`='{$params['order_id']}'")
            ){
                $allReviewsCount = $allReviewsCount->num_rows;
            }
            $query = "SELECT
		*
		FROM  `reviews`
		WHERE `order_id`='{$params['order_id']}'
		ORDER BY `id` DESC LIMIT ".$params['index'].",".$params['index2']." ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $allReviews[] = $row;
                }
            }
            if($allReviewsCount != ''){
                return ["allReviews" => $allReviews, "allReviewsCount" => $allReviewsCount];
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here getVersion 1------------------------------*/
        static function getVersion($params){
            $con = $params['dbconnection'];
            $query = "SELECT
		*
		FROM `version`
		ORDER BY `id` LIMIT 1";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                return $row;
            }
        }
        /* 1:------------------------------method start here updateVersion 1------------------------------*/
        static function updateVersion($params){
            $con = $params['dbconnection'];
            $version_ios = "";
            $forcefully_updated_ios = "";
            $version_android = "";
            $forcefully_updated_android = "";
            $upDown = '-';
            if($params['upDown'] == '1'){
                $upDown = '+';
            }
            if($params['type'] == 'ios'){
                $version_ios = "`version_ios` = `version_ios` ".$upDown." .01,";
                if($params['forcefully_updated'] != ''){
                    $forcefully_updated_ios = "`forcefully_updated_ios` = '{$params['forcefully_updated']}',";
                }
            }
            else if($params['type'] == 'android'){
                $version_android = "`version_android` = `version_android` ".$upDown." .01,";
                if($params['forcefully_updated'] != ''){
                    $forcefully_updated_android = "`forcefully_updated_android` = '{$params['forcefully_updated']}',";
                }
            }
            $query = "UPDATE `version`
		SET
		".$version_ios."
		".$forcefully_updated_ios."
		".$version_android."
		".$forcefully_updated_android."
		`id`=`id`
		";
            mysqli_query($con, $query);
            $params['id'] = mysqli_insert_id($con);
            $params['table_name'] = 'version';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'add';
            DbMethods::logs($params);
            return DbMethods::getVersion($params);
        }
        /* 1:------------------------------method start here getDefaults 1------------------------------*/
        static function getDefaults($params){
            $con = $params['dbconnection'];
            $query = "SELECT
		`id`,
		`text` as home_page_text,
		`uber`,
		`created_at`,
		`updated_at`
		FROM `defaults`
		WHERE (`type`='home-page')
		ORDER BY `id` LIMIT 1";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                $subText = [];
                $query = "SELECT
				`id`,
				`text`,
				`paragraph`,
				`created_at`,
				`updated_at`
				FROM `defaults`
				WHERE `type`='subscription'
				ORDER BY `paragraph`,`id` ASC LIMIT 0,20";
                $result = mysqli_query($con, $query);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
                if(mysqli_num_rows($result) > 0){
                    while($row2 = mysqli_fetch_assoc($result)){
                        $subText[] = $row2;
                    }
                }
                $row['subText'] = $subText;
                $query = "SELECT
				`uber`
				FROM `defaults`
				WHERE (`type`='uber')
				ORDER BY `id` LIMIT 1";
                $result = mysqli_query($con, $query);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
                if(mysqli_num_rows($result) > 0){
                    $row1 = mysqli_fetch_assoc($result);
                    $row['uber'] = $row1['uber'];
                }
                return $row;
            }
        }
        /* 1:------------------------------method start here addUpdateDefault 1------------------------------*/
        static function addUpdateDefault($params){
            $con = $params['dbconnection'];
            $type = '';
            $text = '';
            $uber = '';
            $paragraph = '';
            if($params['type'] != ''){
                $type = " `type`='{$params['type']}' ,";
            }
            if($params['text'] != ''){
                $text = " `text`='{$params['text']}' ,";
            }
            if($params['uber'] != ''){
                $uber = " `uber`='{$params['uber']}' ,";
            }
            if($params['paragraph'] != ''){
                $paragraph = " `paragraph`='{$params['paragraph']}' ,";
            }
            if($params['type'] == 'home-page'){
                $uber = '';
                $paragraph = '';
            }
            else if($params['type'] == 'uber'){
                $text = '';
                $paragraph = '';
            }
            else if($params['type'] == 'subscription'){
                $uber = '';
            }
            $query = "SELECT
		*
		FROM `defaults`
		WHERE (`type`='home-page' OR `type`='uber') AND `type`='{$params['type']}'";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                $query = "UPDATE `defaults`
				SET
				".$text."
				".$uber."
				".$paragraph."
				`id`=`id`
				WHERE (`type`='home-page' OR `type`='uber') AND `type`='{$params['type']}'";
                mysqli_query($con, $query);
                $params['id'] = $row['id'];
            }
            else if($params['id'] != '' && $params['type'] == 'subscription'){
                $query = "UPDATE `defaults`
			SET
			".$type."
			".$text."
			".$uber."
			".$paragraph."
			`id`=`id`
			WHERE `id`='{$params['id']}'";
                mysqli_query($con, $query);
            }
            else{
                $query = "INSERT INTO `defaults`
			SET
			".$type."
			".$text."
			".$uber."
			".$paragraph."
			`id`=`id`";
                mysqli_query($con, $query);
                $params['id'] = mysqli_insert_id($con);
            }
            $params['table_name'] = 'defaults';
            $params['table_id'] = $params['id'];
            $params['admin_logstype'] = 'add';
            DbMethods::logs($params);
            return DbMethods::getDefaults($params);
        }
        /* 4:------------------------------method start here getCreditcardPackages ------------------------------*/
        static function getCreditcardPackages($params){
            $con = $params['dbconnection'];
            $creditcardPackages = [];
            $query = "SELECT
	*
	FROM `creditcard_packages`
	ORDER BY `id` ASC LIMIT 0,20";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $creditcardPackages[] = $row;
                }
            }
            return $creditcardPackages;
        }
        /* 4:------------------------------method start here updateCreditcardPackages ------------------------------*/
        static function updateCreditcardPackages($params){
            $con = $params['dbconnection'];
            $name = "";
            $type = "";
            $qatar_value = "";
            $doller_value = "";
            $savings = "";
            if($params['name'] != ''){
                $name = " `name`='{$params['name']}' ,";
            }
            if($params['qatar_value'] != ''){
                $qatar_value = " `qatar_value`='{$params['qatar_value']}' ,";
            }
            if($params['doller_value'] != ''){
                $doller_value = " `doller_value`='{$params['doller_value']}' ,";
            }
            if($params['savings'] != ''){
                $savings = " `savings`='{$params['savings']}' ,";
            }
            $query = "UPDATE `creditcard_packages`
	SET
	".$name."
	".$qatar_value."
	".$doller_value."
	".$savings."
	`id`=`id`
	WHERE `id`='{$params['id']}'";
            mysqli_query($con, $query);
            return "updated";
        }
        /* 4:------------------------------method start here logs ------------------------------*/
        static function logs($params){
            if($params['admin_logstype'] == 'add'){
                $params = array_reverse($params);
            }
            $con = $params['dbconnection'];
            $excludeitems = [
                "apiBasePath",
                "langset",
                "method",
                "Authorization",
                "url",
                "index",
                "index2",
                "user_deviceType",
                "admin_logstype",
                "table_id",
                "table_name",
                "admin_id",
                "dbconnection"
            ];
            $number = 0;
            $query = "SELECT
	`number`
	FROM `admin_logs`
	WHERE  `table_id`='{$params['table_id']}' AND  `table_name`='{$params['table_name']}'";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                $number = ($row['number'] + 1);
            }
            $multiplequery = "INSERT INTO `admin_logs`(`admin_id`,`table_id`,`column_name`,`value`,`number`,`type`,`table_name`) VALUES ";
            $multiplequerycol = "";
            foreach($params as $x => $value){
                if(!in_array($x, $excludeitems)){
                    $multiplequerycol .= "('{$params['admin_id']}','{$params['table_id']}','{$x}','{$value}','{$number}','{$params['admin_logstype']}'
			,'{$params['table_name']}'),";
                }
            }
            $multiplequerycol = substr($multiplequerycol, 0, -1);
            $query = $multiplequery.$multiplequerycol;
            mysqli_query($con, $query);
            if($params['admin_logstype'] == 'add'){
                $subid = "";
                if($params['table_name'] == 'merchants'){
                    $subid = "`merchant_id`='{$params['table_id']}'";
                }
                else if($params['table_name'] == 'outlets'){
                    $subid = "`outlet_id`='{$params['table_id']}'";
                }
                else if($params['table_name'] == 'offers'){
                    $subid = "`offer_id`='{$params['table_id']}'";
                }
                else if($params['table_name'] == 'accesscodes'){
                    $subid = "`accesscode_id`='{$params['table_id']}'";
                }
                else if($params['table_name'] == 'notifications'){
                    $subid = "`notification_id`='{$params['table_id']}'";
                }
                else if($params['table_name'] == 'version'){
                    $subid = "`version_id`='{$params['table_id']}'";
                }
                $queryt_t = "INSERT INTO  `app_access`
		SET `app_id`='{$params['user_app_id']}',
		".$subid."
		";
                mysqli_query($con, $queryt_t);
            }
            return "add";
        }
        /* 4:------------------------------method start here logout ------------------------------*/
        static function logout($params){
            DbMethods::removeAuthKey($params);
            return "logout";
        }
        /* 1:------------------------------method start here checkImageSrc ------------------------------*/
        static function checkImageSrc($img){
            //echo (__DIR__).'/../../uploads/'.$img;
            if(file_exists((__DIR__).'/../../../uploads/'.$img)){
                return 1;
            }
            else{
                return null;
            }
        }
        /* 1:------------------------------method start here post_async ------------------------------*/
        static function post_async($url, $params){
            //echo $url;print_r($params);
            foreach($params as $key => &$val){
                if(is_array($val)){
                    $val = implode(',', $val);
                }
                $post_params[] = $key.'='.urlencode($val);
            }
            if($params['Authorization'] == ''){
                $params['Authorization'] = 'UP!and$';
            }
            $post_string = implode('&', $post_params);
            $parts = parse_url($url);
            $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
            $out = "POST ".$parts['path']." HTTP/1.1\r\n";
            $out .= "Host: ".$parts['host']."\r\n";
            $out .= "Authorization: ".$params['Authorization']."\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "Content-Length: ".strlen($post_string)."\r\n";
            $out .= "Connection: Close\r\n\r\n";
            if(isset($post_string)){
                $out .= $post_string;
            }
            fwrite($fp, $out);
            fclose($fp);
        }
        /* 1:------------------------------method start here distQuery ------------------------------*/
        static function distQuery($params){
            if($params['latitude'] && $params['longitude']){
                return $distance = ",( 6371 * acos( cos( radians('{$params['latitude']}') ) * cos( radians(`latitude`) ) * cos( radians(`longitude` ) - radians('{$params['longitude']}') ) + sin( radians('{$params['latitude']}') ) * sin( radians(`latitude`) ) ) ) AS distance ";
            }
            else{
                return '';
            }
        }
        /* 1:------------------------------method start here "`active`='0'  offerFilter------------------------------*/
        static function offerFilter($params){
            return "`active`='1' AND ( NOW() < `end_date`) AND `start_date` IS NOT NULL AND `end_date` IS NOT NULL";
        }
        /* 1:------------------------------method start here offerFilterJoin ------------------------------*/
        static function offerFilterJoin($params){
            return "of.`active`='1' AND ou.`active`='1' AND ( NOW() < of.`end_date`) AND of.`start_date` IS NOT NULL AND of.`end_date` IS NOT NULL";
        }
        /* 1:------------------------------method start here orderBy ------------------------------*/
        static function orderBy($params){
            if($params['sortby'] == 'location'){
                return $orderby = "`distance` ASC";
            }
            else{
                return $orderby = " `title` ASC";
            }
        }
        /* 1:------------------------------method start here orderBy ------------------------------*/
        static function orderByOutlet($params){
            if($params['sortby'] == 'location'){
                return $orderby = "`distance` ASC";
            }
            else{
                return $orderby = " `name` ASC";
            }
        }
        /* END-----------------------------END END END END------------------------------END*/
    }
