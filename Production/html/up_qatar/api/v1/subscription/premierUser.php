<?php
    include (__DIR__).'/../../../common/defaults.php';
    include_once 'dbMethods.php';
    include_once 'nonRegisteredUserSub.php';
    
    class premierUserMethods{
        /* 1:------------------------------method start here addPremierUser ------------------------------*/
        static function addPremierUser($params){
            $con = $params['dbconnection'];
            $user_id = "";
            if($params['language'] != "ar-QA"){
                $params['language'] = "en";
            }
            $text = "";
            /*$premierUserSub=premierUserMethods::ooredoSubscribe($params);
            if($premierUserSub==NULL)
            return NULL;*/
            $docharged = "";
            $docharged = premierUserMethods::docharged($params);
            if(isset($docharged)){
                $params['response_code'] = $docharged;
                /*$regarr=explode(",",$docharged);
                if(isset($regarr[1]) && $regarr[1] == '1')
                {
                }*/
            }
            /*if($docharged !="1")
            return NULL;*/
            $subscribe = false;
            $user = false;
            $paidsubscription = false;
            $query = "SELECT
		u.`id`,
		cs.`id` as cs_id,
		cs.`start_datetime`,
		cs.`expiry_datetime`,
		cs.`premier_user`,
		cs.`phone`,
		cs.`status`,
		NOW() as cuurentdate
		FROM `users` as u left JOIN `subscriptions` as cs ON(u.`id`=cs.`user_id`) 
		WHERE  (cs.`phone`='{$params['phone']}'  OR  u.`phone`='{$params['phone']}') ";
            $result = mysqli_query($con, $query);
            if((mysqli_num_rows($result) > 0) || ($params['user_id'] != null)){
                $row = mysqli_fetch_assoc($result);
                $user_id = " `user_id`= '{$row['id']}' ,";
                $user = true;
                if($row['cs_id'] == null){
                    $text = premierUserText;
                }
                else{
                    $text = becomePremierUserText;
                    $subscribe = true;
                }
            }
            else{
                $text = premierUserTextwithnoac;
            }
            if($user == false){
                $query = "SELECT `id`
			FROM `non_registered_users` 
			WHERE `phone`='{$params['phone']}' AND `premier_user`='1'";
                $result = mysqli_query($con, $query);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
                if(mysqli_num_rows($result) > 0){
                    $text = "";
                }
                else{
                    $query = "INSERT INTO `non_registered_users` SET
				`premier_user`='1', 
				`language`='{$params['language']}', 
				`phone`='{$params['phone']}'";
                    mysqli_query($con, $query);
                }
                $query = "DELETE FROM `nonregisteredusers_sub`
			 WHERE `phone`='{$params['phone']}'";
                mysqli_query($con, $query);
                // for log
                $params['user_id'] = null;
                $params['start_datetime'] = "";
                $params['expiry_datetime'] = "";
                $params['type'] = 'add_premier_nonUser';
                DbMethods:: subscriptionslog($params);
            }
            else{
                if($subscribe == true){
                    $days = 30;
                    if(strtotime($row['expiry_datetime']) > strtotime($row['cuurentdate'])){
                        $days = "".$row['expiry_datetime']." +".(int)$days." days";
                    }
                    else{
                        $days = "".$row['cuurentdate']." +".(int)$days." days";
                    }
                    $params['start_datetime'] = date('Y-m-d H:i:s', strtotime($row['cuurentdate']));
                    $params['expiry_datetime'] = date('Y-m-d H:i:s', strtotime($days));
                    if($row['premier_user'] == "1" && $row['expiry_datetime'] > $row['cuurentdate']){
                        $text = "";
                    }
                    else{
                        $byuser_id = "";;
                        if($row['phone'] == ""){
                            $byuser_id = " OR `user_id`= '{$row['id']}'";
                        }
                        $query = "UPDATE `subscriptions` SET
					`phone`='{$params['phone']}',
					`network`='ooredoo',
					`start_datetime`=NOW(),
					`expiry_datetime`='{$params['expiry_datetime']}',
					`premier_user`='1',
					`language`='{$params['language']}', 
					`status`='0'
					 WHERE  (`phone`='{$params['phone']}' ".$byuser_id." ) AND `premier_user` !='1'";
                        mysqli_query($con, $query);
                        if($row['status'] == "1"){
                            $paidsubscription = true;
                        }
                    }
                    if($row['premier_user'] == "1" && $row['expiry_datetime'] > $row['cuurentdate']){
                        $params['start_datetime'] = "";
                        $params['expiry_datetime'] = "";
                    }
                    else{
                        $params['start_datetime'] = "`start_datetime`=NOW(),";
                        $params['expiry_datetime'] = "`expiry_datetime`='{$params['expiry_datetime']}',";
                    }
                    // for log
                    $params['user_id'] = $row['id'];
                    $params['type'] = 'premier_user_sub';
                    DbMethods:: subscriptionslog($params);
                    if($paidsubscription && $params['phone'] != ""){
                        $params['type'] = 'unsubscribe';
                        DbMethods:: unsubscribe($params);
                        $params['user_id'] = $row['id'];
                        $params['start_datetime'] = "";
                        $params['expiry_datetime'] = "";
                        $params['type'] = 'unsubscribe';
                        DbMethods:: subscriptionslog($params);
                    }
                }
                else{
                    $query = "INSERT INTO `subscriptions` SET
				 ".$user_id."
				`phone`='{$params['phone']}',
				`network`='ooredoo',
				`start_datetime`=NOW(),
				`expiry_datetime`=now() + interval 30 day,
				`premier_user`='1',
				`language`='{$params['language']}', 
				`status`='0'";
                    mysqli_query($con, $query);
                    $paidsubscription = true;
                    // for log
                    $params['user_id'] = $row['id'];
                    $params['start_datetime'] = "`start_datetime`=NOW(),";
                    $params['expiry_datetime'] = "`expiry_datetime`=now() + interval 30 day,";
                    $params['type'] = 'premier_user_sub';
                    DbMethods:: subscriptionslog($params);
                }
                nonUsersMethods::deleteNonUser($params);
            }
            $dir = $params['apiBasePath']."sendMT";
            if($text != ''){
                premierUserMethods:: post_async($dir, ['phone' => $params['phone'], 'Text' => $text]);
            }
            return "added";
        }
        /* 1:------------------------------method start here unsubPremierUser ------------------------------*/
        static function unsubPremierUser($params){
            $con = $params['dbconnection'];
            $query = "UPDATE `nonregisteredusers_sub` SET
		`start_datetime`=NULL,
		`expiry_datetime`=NULL,
		`premier_user`='0',
		`status`='0'
		WHERE `phone`='{$params['phone']}'";
            mysqli_query($con, $query);
            $query = "UPDATE `subscriptions` SET
		`start_datetime`=NULL,
		`expiry_datetime`=NULL,
		`premier_user`='0',
		`status`='0'
		WHERE `phone`='{$params['phone']}'";
            mysqli_query($con, $query);
            $params['type'] = 'premier_user_unsub';
            $params['start_datetime'] = '';
            $params['expiry_datetime'] = '';
            DbMethods:: subscriptionslog($params);
            $dir = $params['apiBasePath']."sendMT";
            premierUserMethods:: post_async($dir, ['phone' => $params['phone'], 'Text' => unsubscribeMsgO]);
            return "updated";
        }
        /* 1:------------------------------method start here premierUserSub ------------------------------*/
        static function ooredoSubscribe($params){
            $url = "http://mb.timwe.com/neo-mb-me-ma-subapi/qao/subscribe?PartnerRoleId=".PartnerRoleId."&Password=".Password."&Msisdn=".$params['phone']."&OpId=".OpId."&BuyChannel=SMS&ProductId=7207&CountryId=".CountryId."";
            try{
                static $ch;
                if(empty($ch)){
                    $ch = curl_init();
                }
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_ENCODING, "gzip");
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_VERBOSE, 0);
                $data = curl_exec($ch);
                curl_close($ch);
                //print_r($data);
                if($data){
                    $data = json_decode(json_encode(simplexml_load_string($data)), true);
                    if($data['response']['responsestatus']['code'] == '1' || $data['response']['responsestatus']['code'] == '-72'){
                        $docharged = premierUserMethods::docharged($params);
                        if($docharged != "1"){
                            return null;
                        }
                        else{
                            return $data['response']['responsestatus']['code'];
                        }
                    }
                    else{
                        return null;
                    }
                }
                else{
                    return null;
                }
            }
            catch(Exception  $e){
                return null;
            }
        }
        /* 1:------------------------------method start here unsubscribe ------------------------------*/
        static function unsubscribe($params){
            $con = $params['dbconnection'];
            $params['user_id'] = null;
            $params['start_datetime'] = "";
            $params['expiry_datetime'] = "";
            $params['type'] = 'premier_user_unsub';
            DbMethods:: subscriptionslog($params);
            return "unsub";
            /*  $con =$params['dbconnection'];
              $url="http://mb.timwe.com/neo-mb-me-ma-subapi/qao/unsubscribe?PartnerRoleId=".PartnerRoleId."&Password=".Password."&ProductId=7207&Msisdn=".$params['phone']."&OpId=".OpId."&CountryId=".CountryId."&Keyword=stop";
                try{
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
                        //print_r($data);
                        if($data)
                        {
                               $data = json_decode(json_encode(simplexml_load_string($data)), true);
                              // print_r($data);
                               if($data['response']['responsestatus']['code']=='1' ||  $data['response']['responsestatus']['code']=='-77')
                               {
                                    premierUserMethods:: unsubPremierUser($params);
                                    return $data['response']['responsestatus']['code'];
                                }
                                else
                                return $data;
                         }
                    }catch(Exception  $e)
                    {
                        return $e->getMessage();
                    }
                    
                    
                    */
        }
        /* 1:------------------------------method start here dochargedaily ------------------------------*/
        static function docharged($params){
            $con = $params['dbconnection'];
            $PricePointId = "5613146";
            $params['ExtTxId'] = substr(md5(microtime(true).rand(0, 10)), -16);
            $url = "http://mb.timwe.com/neo-billing-plugins-qao-facade/docharge?PartnerRoleId=".PartnerRoleId."&Password=".Password."&ProductId=7207&PricePointId=".$PricePointId."&Destination=".$params['phone']."&OpId=".OpId."&ExtTxId=".$params['ExtTxId']."";
            try{
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_ENCODING, "gzip");
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_VERBOSE, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120); //timeout in seconds
                $data = curl_exec($ch);
                curl_close($ch);
                //print_r($data);
                if($data){
                    //eror messages
                    /*321089,1 charged successfully.
                     321090, 1122 customer had no balance
                    0 – General Error
                      1 – Success
                    -1 – Invalid Destination
                    -2 – Invalid Operator
                    -3 – Invalid Credentials
                    -4 – Invalid Price Point
                    -6 – Invalid Partner Product
                    -10 –Duplicate ExtTxId
                    -12 –Blacklisted Destination
                    -81– User not subscribed for your service/club.
                    1101 –Charging operation failed, the charge was not applied
                    1101 –Charging operation failed, the charge was not applied
                    1102–Charging operation failed, the charge was not applied
                    1103–Charging operation failed, the charge was not applied
                    1123–The %1 operator charging limit for this user has been exceeded
                    1124–The charge happened too soon after the previous one
                    1125– Unknown error
                    1122–Charging operation failed, Insufficient Credit*/
                    return $data;
                }
            }
            catch(Exception  $e){
                return ["code" => 1, "data" => $e->getMessage()];
            }
        }
        /* 1:------------------------------method start here runPremierUserCronJob ------------------------------*/
        static function runPremierUserCronJob($params){
            /* $date = date("d M Y H:i:s");
             $text = "call runPremierUserCronJob ".$date . $query ." \n";
             $filename = "/var/www/html/up_qatar/api/v1/subscription/newfile.txt";
             $fh = fopen($filename, "a");
             fwrite($fh, $text);
             fclose($fh);
             return "completed";
             exit;*/
            $con = $params['dbconnection'];
            $query = "SELECT DISTINCT(`phone`) as phone,`language` ,`user_id` ,TIMESTAMPDIFF(HOUR,`expiry_datetime`,NOW()) as timediffer FROM  `subscriptions`
	    WHERE `network`='ooredoo' AND `premier_user`='1' AND (`expiry_datetime` < NOW() OR `start_datetime` IS NULL) AND `phone` !='' AND `phone` IS NOT NULL";
            $result = mysqli_query($con, $query);
            if(mysqli_num_rows($result) > 0){
                while($row1 = mysqli_fetch_assoc($result)){
                    $params['user_id'] = $row1['user_id'];
                    $params['phone'] = $row1['phone'];
                    $response = premierUserMethods::eligibilitychecker($params);
                    $text = '';
                    if($response == 'ELIGIBLE'){
                        $query_s = "UPDATE `subscriptions` SET `expiry_datetime`=now() + interval 30 day WHERE `phone`='{$params['phone']}'  ";
                        mysqli_query($con, $query_s);
                        if($row1['language'] == 'en'){
                            $text = premierUpgrade_en;
                        }
                        else{
                            $text = premierUpgrade_ar;
                        }
                        //for log
                        $params['type'] = 'premier_renewal_up';
                        $params['start_datetime'] = "`start_datetime`=NOW(),";
                        $params['expiry_datetime'] = "`expiry_datetime`=now() + interval 30 day,";
                        DbMethods:: subscriptionslog($params);
                    }
                    else /*if($response == 'NOT ELIGIBLE')*/{
                        $query_s = "UPDATE `subscriptions` SET `status`='0', `premier_user`='0' WHERE `phone`='{$params['phone']}'  ";
                        mysqli_query($con, $query_s);
                        if($row1['language'] == 'en'){
                            $text = premierDowngrade_en;
                        }
                        else{
                            $text = premierDowngrade_ar;
                        }
                        //for log
                        $params['type'] = 'premier_renewal_down';
                        $params['start_datetime'] = '';
                        $params['expiry_datetime'] = '';
                        DbMethods:: subscriptionslog($params);
                    }
                    //--//
                    if($text != ''){
                        $dir = $params['apiBasePath']."sendMT";
                        premierUserMethods:: post_async($dir, ['phone' => $params['phone'], 'Text' => $text]);
                    }
                }
            }
            return "completed";
        }
        /* 1:------------------------------method start here eligibilitychecker ------------------------------*/
        static function eligibilitychecker($params){
            $con = $params['dbconnection'];
            try{
                $curl = curl_init();
                $eligibility_array = (object)["productId" => "7207", "msisdn" => $params["phone"]];
                curl_setopt_array($curl, [
                    CURLOPT_URL            => "https://qao-ma.timwe.com/webapp-ma-qao-bundling-api/eligibilitychecker/v1/",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING       => "",
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_TIMEOUT        => 30,
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST  => "POST",
                    CURLOPT_POSTFIELDS     => json_encode($eligibility_array),
                    CURLOPT_HTTPHEADER     => [
                        "authorization: Basic b29yZWRvbzpvb3JlZG9vQDEyMw==",
                        "content-type: application/json"
                    ],
                ]);
                $data = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if($err){
                    return $err;
                }
                else{
                    $data = (object)json_decode($data);
                    $params['response'] = $data->status;
                    premierUserMethods:: eligibilitychecker_log($params);
                    return $data->status;
                }
            }
            catch(Exception  $e){
                return $e->getMessage();
            }
        }
        /* 1:------------------------------method start here checkPremier ------------------------------*/
        static function checkPremier($params){
            $con = $params['dbconnection'];
            $premier_user = 0;
            $premier_subscription = 0;
            $query = "SELECT `phone` FROM  `non_registered_users` WHERE  `phone`='{$params['phone']}' AND `premier_user`='1'";
            $result = mysqli_query($con, $query);
            if((mysqli_num_rows($result) > 0)){
                $row = mysqli_fetch_assoc($result);
                $premier_user = 1;
            }
            $query1 = "SELECT `phone` FROM  `subscriptions` WHERE `network`='ooredoo'AND `phone`='{$params['phone']}' AND `premier_user`='1' ";
            $result1 = mysqli_query($con, $query1);
            if((mysqli_num_rows($result1) > 0)){
                $row1 = mysqli_fetch_assoc($result1);
                $premier_subscription = 1;
            }
            return [
                "eligibility"          => 'ELIGIBLE',
                "premier_subscription" => $premier_subscription,
                "premier_user"         => $premier_user
            ];
        }
        /* 1:------------------------------method start here eligibilitychecker_log ------------------------------*/
        static function eligibilitychecker_log($params){
            $con = $params['dbconnection'];
            if($params['user_id'] != ""){
                $user_id = "`user_id`='{$params['user_id']}',";
            }
            else{
                $user_id = "";
            }
            if($params['phone'] != ""){
                $phone = "`phone`='{$params['phone']}',";
            }
            else{
                $phone = "";
            }
            $query_log = "INSERT INTO `eligibilitychecker_log` SET ".$user_id." ".$phone." `response`='{$params['response']}'";
            mysqli_query($con, $query_log);
            return "added";
        }
        /* 1:------------------------------method start here addprimeir ------------------------------*/
        static function addprimeir($params){
            $con = $params['dbconnection'];
            $offers = [];
            $query = "SELECT `phone`
	    FROM  `premier_user` WHERE `type`='Active'";
            $result = mysqli_query($con, $query);
            if(mysqli_num_rows($result) > 0){
                while($row1 = mysqli_fetch_assoc($result)){
                    $dir = $params['apiBasePath']."addPremierUser";
                    premierUserMethods:: post_async($dir, ['msisdn' => $row1['phone']]);
                    usleep(1000000);
                    $offers[] = $row1;
                }
                return $offers;
            }
        }
        /* 1:------------------------------method start here sendMTtoremierNonUser ------------------------------*/
        static function sendMTtoremierNonUser($params){
            $con = $params['dbconnection'];
            $text = "Download Urban Point App today to avail your FREE SUBSCRIPTION from Ooredoo! Urban Point gives over QR 50,000 in monthly savings through thousands of offers that renew every month! Save more and Live better!
Download Now!
iOS: https://itunes.apple.com/qa/app/urban-point/id1074590743
Android: https://play.google.com/store/apps/details?id=com.urbanpoint.UrbanPoint";
            $resultfinal = [];
            $query = "SELECT DISTINCT(`phone`) as phone
	    FROM  `non_registered_users` 
	    WHERE  `premier_user`='1' ";
            $result = mysqli_query($con, $query);
            if(mysqli_num_rows($result) > 0){
                while($row1 = mysqli_fetch_assoc($result)){
                    $resultfinal[] = $row1;
                    $dir = $params['apiBasePath']."sendMT";
                    if($text != ''){
                        premierUserMethods:: post_async($dir, ['phone' => $row1['phone'], 'Text' => $text]);
                    }
                    usleep(1000000);
                }
            }
            return $resultfinal;
            return "completed";
        }
        /* 1:------------------------------method start here post_async ------------------------------*/
        static function post_async($url, $params){
            if($params['Authorization'] == ''){
                $params['Authorization'] = 'UP!and$';
            }
            foreach($params as $key => &$val){
                if(is_array($val)){
                    $val = implode(',', $val);
                }
                $post_params[] = $key.'='.urlencode($val);
            }
            $post_string = implode('&', $post_params);
            $parts = parse_url($url);
            $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
            $out = "POST ".$parts['path']." HTTP/1.1\r\n";
            $out .= "Host: ".$parts['host']."\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "Content-Length: ".strlen($post_string)."\r\n";
            $out .= "Connection: Close\r\n\r\n";
            if(isset($post_string)){
                $out .= $post_string;
            }
            fwrite($fp, $out);
            fclose($fp);
        }
        /* END-----------------------------END END END END------------------------------END*/
    }
