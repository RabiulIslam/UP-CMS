<?php
    $filetype = "dbMethods";
    include (__DIR__).'/../../../common/defaults.php';
    include_once 'premierUser.php';
    include_once 'nonRegisteredUserSub.php';
    
    class DbMethods{
        /* 1:------------------------------method start here validatemsisdn ------------------------------*/
        static function validatemsisdn($params){
            $con = $params['dbconnection'];
            $url = "http://mb.timwe.com/neo-mb-sub-facade/qao/webapi/validatemsisdn?Role=".PartnerRoleId."&Password=".Password."&Opid=".OpId."&Country=".CountryId."&Dest=".$params['phone']."";
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
                /*print_r($data);
                exit;*/
                if($data){
                    $data = json_decode(json_encode(simplexml_load_string($data)), true);
                    if($data['isMsisdnValid'] == 'true'){
                        $params['Text'] = substr(mt_rand(), 3, 4).substr(mt_rand(), 5, 2);
                        $dir = $params['apiBasePath']."sendMT";
                        DbMethods:: post_async($dir, [
                            'phone'         => $params['phone'],
                            'Text'          => $params['Text'],
                            'Authorization' => $params['Authorization']
                        ]);
                        return ["code" => 1, "data" => $params['Text']];
                    }
                    else{
                        return ["code" => 0, "data" => $data['req_res']];
                    }
                }
                else{
                    return "";
                }
            }
            catch(Exception  $e){
                $error = $e->getMessage();
            }
        }
        /* 1:------------------------------method start here validateCode ------------------------------*/
        static function validateCode($params){
            $con = $params['dbconnection'];
            $query = "SELECT `id`,`phone`, `sms`
			FROM `send_sms` 
			WHERE `phone`='{$params['phone']}' AND CHAR_LENGTH (`sms`)='6'  AND `sms`='{$params['code']}'
			ORDER BY `id` DESC LIMIT 0,4";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                return "match";
            }
            else{
                return "notmatch";
            }
        }
        /* 1:------------------------------method start here sendMT ------------------------------*/
        static function sendMT($params){
            $con = $params['dbconnection'];
            if(!isset($params['Text'])){
                $params['Text'] = substr(mt_rand(), 3, 4).substr(mt_rand(), 5, 2);
            }//six
            $params['ExtTxId'] = substr(md5(microtime(true)), -16);
            $url = "http://mb.timwe.com/qao/sendMT?PartnerRoleId=".PartnerRoleId."&Password=".Password."&ProductId=".ProductId."&PricePointId=".PricePointId."&SenderId=".SenderId."&OpId=".OpId."&Text=".urlencode($params['Text'])."&ExtTxId=".$params['ExtTxId']."&Destination=".$params['phone']."";
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
                    $query = "INSERT INTO `send_sms` SET
						`sms`='{$params['Text']}',
						`phone`='{$params['phone']}',
						`response`='{$data}'";
                    mysqli_query($con, $query);
                    return $params['Text'];
                }
                else{
                    return "";
                }
            }
            catch(Exception  $e){
                $error = $e->getMessage();
            }
        }
        /* 1:------------------------------method start here checksubstatus ------------------------------*/
        static function checksubstatus($params){
            $con = $params['dbconnection'];
            $url = "http://mb.timwe.com/neo-mb-me-ma-subapi/qao/checksubstatus?PartnerRoleId=".PartnerRoleId."&Password=".Password."&ProductId=".ProductId."&Msisdn=".$params['phone']."&CountryId=".CountryId."&OpId=".OpId."";
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
                    return [
                        "code" => $data['response']['responsestatus']['code'],
                        "data" => $data['response']['responsestatus']['description']
                    ];
                }
            }
            catch(Exception  $e){
                return ["code" => 1, "data" => $error = $e->getMessage()];
            }
        }
        /* 1:------------------------------method start here subscribe ------------------------------*/
        static function subscribe($params){
            $con = $params['dbconnection'];
            $userSubscriptions = [];
            $nonUserSubscription = [];
            $params['premier_user'] = "";
            $params['renewed'] = "No";
            $user_id = "";
            $params['userSubscriptions'] = "";
            $params['nonUserSubscription'] = "";
            //check user
            if($params['user_id'] != ''){
                $query = "SELECT * FROM  `users` WHERE  `id` ='{$params['user_id']}'";
                $result0 = mysqli_query($con, $query);
                if(mysqli_num_rows($result0) == 0){
                    return ["code" => 1, "data" => 'This customer is not registered.'];
                }
                else{
                    $user_id = " OR `user_id`='{$params['user_id']}'";
                }
            }
            //check subscriptions
            if($params['phone'] != ''){
                $query = "SELECT *, NOW() as today FROM `subscriptions` WHERE `phone`='{$params['phone']}' ".$user_id."";
                $result1 = mysqli_query($con, $query);
                if(mysqli_num_rows($result1) > 0){
                    if(mysqli_num_rows($result1) != 1){
                        return ["code" => 1, "data" => 'This mobile number is already registered.'];
                    }
                    $userSubscriptions = mysqli_fetch_assoc($result1);
                    if($params['user_id'] != ''){
                        if(($params['user_id'] != $userSubscriptions['user_id'])){
                            return ["code" => 1, "data" => 'This mobile number is already registered.'];
                        }
                        else{
                            $params['user_id'] = $userSubscriptions['user_id'];
                        }
                    }
                    else{
                        $params['user_id'] = $userSubscriptions['user_id'];
                    }
                    $params['userSubscriptions'] = $userSubscriptions;
                }
            }
            //check non_registered_users_and_sub
            $query = "SELECT
                nru.`id` as nonru_id,
                nru.`phone`,
                nru.`premier_user`,
                nru.`language`,
                nrus.`id` as nonrus_id,
                nrus.`start_datetime`,
                nrus.`expiry_datetime`,
                nrus.`status`,
                NOW() as today,
                NOW() as cuurentdate,
                TIMESTAMPDIFF(DAY,NOW(),`expiry_datetime`) as timediffer
                FROM  `non_registered_users` as nru
                LEFT OUTER JOIN `nonregisteredusers_sub` as nrus ON(nru.`phone` = nrus.`phone`)
                WHERE nru.`phone`='{$params['phone']}'";
            $result2 = mysqli_query($con, $query);
            if(mysqli_num_rows($result2) > 0){
                $nonUserSubscription = mysqli_fetch_assoc($result2);
                $params['premier_user'] = $nonUserSubscription['premier_user'];
                $params['nonUserSubscription'] = $nonUserSubscription;
            }
            if($params['user_id'] == '' && $params['phone'] != '')//for sms subscription
            {
                $params['type'] = 'subscribe';
                $ooredoSubscribe = DbMethods::ooredoSubscribe($params);
                if($ooredoSubscribe['code'] == '1' || $ooredoSubscribe['code'] == '-72'){
                    if($nonUserSubscription['nonrus_id'] == ''){
                        //add in both tables
                        $params['docharge'] = '1';
                        $result = nonUsersMethods::addNonUserSubscription($params);
                        $params['docharge'] = '1';
                        $result = nonUsersMethods::updateNonUserSubscription($params);
                        return "added";
                    }
                    else{
                        if($nonUserSubscription['premier_user'] == '1'){
                            return ["code" => 1, "data" => 'Subscription already exist.'];
                        }
                        else if(($nonUserSubscription['expiry_datetime'] > $nonUserSubscription['today']) && ($nonUserSubscription['status'] == '1')){
                            return ["code" => 1, "data" => 'Subscription already exist.'];
                        }
                        else if(($nonUserSubscription['expiry_datetime'] > $nonUserSubscription['today']) && ($nonUserSubscription['status'] == '0')){
                            $params['docharge'] = '0';
                            $result = nonUsersMethods::updateNonUserSubscription($params);
                            return "added";
                        }
                        else{
                            $params['docharge'] = '1';
                            $result = nonUsersMethods::updateNonUserSubscription($params);
                            return "added";
                        }
                    }
                }
                else{
                    return ["code" => 1, "data" => $ooredoSubscribe['description']];
                }
            }
            else{
                $ooredoSubscribe = DbMethods::ooredoSubscribe($params);
                if($userSubscriptions['id'] != ''){
                    if($ooredoSubscribe['code'] == '1' || $ooredoSubscribe['code'] == '-72'){
                        $params['type'] = 'subscribe';
                        if(($userSubscriptions['expiry_datetime'] > $userSubscriptions['today']) && $userSubscriptions['premier_user'] == '1'){
                            return ["code" => 1, "data" => 'Subscription already exist.'];
                        }
                        if(($userSubscriptions['expiry_datetime'] > $userSubscriptions['today']) && ($userSubscriptions['status'] == '1')){
                            return ["code" => 1, "data" => 'Subscription already exist.'];
                        }
                        else if(($userSubscriptions['expiry_datetime'] > $userSubscriptions['today']) && ($userSubscriptions['status'] == '0')){
                            $params['response_code'] = $ooredoSubscribe['code'];
                            $params['docharge'] = '0';
                            $result = DbMethods::updateSubscription($params);
                            return "added";
                        }
                        else{
                            $params['response_code'] = $ooredoSubscribe['code'];
                            $params['docharge'] = '1';
                            $result = DbMethods::updateSubscription($params);
                            return "added";
                        }
                    }
                    else{
                        return $ooredoSubscribe['description'];
                    }
                }
                else{
                    if($ooredoSubscribe['code'] == '1' || $ooredoSubscribe['code'] == '-72'){
                        $params['docharge'] = '1';
                        $params['response_code'] = $ooredoSubscribe['code'];
                        DbMethods::addsubscription($params);
                        return "added";
                    }
                    else{
                        return $ooredoSubscribe['description'];
                    }
                }
            }
        }
        /* 1:------------------------------method start here unsubscribe ------------------------------*/
        static function unsubscribe($params){
            $con = $params['dbconnection'];
            $url = "http://mb.timwe.com/neo-mb-me-ma-subapi/qao/unsubscribe?PartnerRoleId=".PartnerRoleId."&Password=".Password."&ProductId=".ProductId."&Msisdn=".$params['phone']."&OpId=".OpId."&CountryId=".CountryId."&Keyword=stop";
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
                        if($params['type'] == 'unsubscribe' || $params['type'] == 'decline'){
                            return $data['response']['responsestatus']['code'];
                        }
                        else{
                            $params['type'] = 'manuallyUnsubscribe';
                            $params['docharge'] = '0';
                            $params['network'] = 'ooredoo';
                            $params['response_code'] = $data['response']['responsestatus']['code'];
                            //print_r($params);
                            DbMethods::updateSubscription($params);
                        }
                        nonUsersMethods::deleteNonUser($params);
                        return $data;
                    }
                    else{
                        return $data['response']['responsestatus']['code'];
                    }
                }
            }
            catch(Exception  $e){
                return $e->getMessage();
            }
        }
        /* 1:------------------------------method start here ooredoSubscribe ------------------------------*/
        static function ooredoSubscribe($params){
            $con = $params['dbconnection'];
            $url = "http://mb.timwe.com/neo-mb-me-ma-subapi/qao/subscribe?PartnerRoleId=".PartnerRoleId."&Password=".Password."&Msisdn=".$params['phone']."&OpId=".OpId."&BuyChannel=SMS&ProductId=".ProductId."&CountryId=".CountryId."";
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
                if($data){
                    $data = json_decode(json_encode(simplexml_load_string($data)), true);
                    return [
                        "code" => $data['response']['responsestatus']['code'],
                        "data" => $data['response']['responsestatus']['description']
                    ];
                }
            }
            catch(Exception  $e){
                return ["code" => 1, "data" => $error = $e->getMessage()];
            }
        }
        /* 1:------------------------------method start here addsubscription ------------------------------*/
        static function addSubscription($params){
            $con = $params['dbconnection'];
            $start_datetime = "`start_datetime`=NULL,";
            $expiry_datetime = "`expiry_datetime`=NULL,";
            $status = "`status`='0' ,";
            $premier_user = "";
            $language = "";
            $params['type'] = 'subscribe';
            $nonUserSubscription = $params['nonUserSubscription'];
            $text = subscribeMsgO;
            if($nonUserSubscription != null){
                if($nonUserSubscription['premier_user'] == '1'){
                    $text = premierUserText;
                    if($nonUserSubscription['language'] != "ar-QA"){
                        $params['language'] = "en";
                    }
                    else{
                        $params['language'] = "ar-QA";
                    }
                    $language = "`language`='{$params['language']}',";
                    $premier_user = "`premier_user`='1',";
                    $start_datetime = "`start_datetime`=NOW(),";
                    $expiry_datetime = "`expiry_datetime`=now() + interval 30 day,";
                    $params['docharge'] = '0';
                    $params['type'] = 'premier_user_sub';
                }
                else if($nonUserSubscription['status'] == '1' && $nonUserSubscription['expiry_datetime'] > $nonUserSubscription['cuurentdate']){
                    $status = "`status`='1' ,";
                    $start_datetime = "`start_datetime`='{$nonUserSubscription['start_datetime']}',";
                    $expiry_datetime = "`expiry_datetime`='{$nonUserSubscription['expiry_datetime']}',";
                    $params['docharge'] = '0';
                    $params['type'] = 'subscribe';
                }
            }
            $query = "INSERT INTO `subscriptions` SET
		`user_id`='{$params['user_id']}',
		`phone`='{$params['phone']}',
		".$premier_user."
		".$language."
		".$start_datetime."
		".$expiry_datetime."
		".$status."
		`network`='ooredoo'";
            mysqli_query($con, $query);
            $params['start_datetime'] = $start_datetime;
            $params['expiry_datetime'] = $expiry_datetime;
            DbMethods:: subscriptionslog($params);
            if($nonUserSubscription != null){
                nonUsersMethods::deleteNonUser($params);
            }
            $params['unsubcron'] = 'decline';
            if($params['docharge'] == '1'){
                DbMethods::doChargeMonthly($params);
            }
            else{
                $dir = $params['apiBasePath']."sendMT";
                DbMethods:: post_async($dir, [
                    'phone'         => $params['phone'],
                    'Text'          => $text,
                    'Authorization' => $params['Authorization']
                ]);
            }
            return "add";
        }
        /* 1:------------------------------method start here updateSubscription ------------------------------*/
        static function updateSubscription($params){
            $con = $params['dbconnection'];
            $start_datetime = "";
            $expiry_datetime = "";
            $text = "";
            $user_id = "";
            $premier_user = "";
            $language = "";
            $where = "";
            $userSubscriptions = $params['userSubscriptions'];
            $nonUserSubscription = $params['nonUserSubscription'];
            if($params['type'] == 'renew_1'){
                if($params['renew'] == 'yes'){
                    $text = dochargedaily;
                }
                else{
                    $text = new_sub_with_QR_0_5_charge;
                }
                $start_datetime = "`start_datetime`=NOW(),";
                $expiry_datetime = "`expiry_datetime`=now() + interval 1 day,";
                $status = "`status`='1' ,";
                $params['price_point'] = '.5';
            }
            else if($params['type'] == 'renew_7'){
                if($params['renew'] == 'yes'){
                    $text = dochargeweek;
                }
                else{
                    $text = new_sub_with_QR_3_75_charge;
                }
                $start_datetime = "`start_datetime`=NOW(),";
                $expiry_datetime = "`expiry_datetime`=now() + interval 7 day,";
                $status = "`status`='1' ,";
                $params['price_point'] = '3.75';
            }
            else if($params['type'] == 'renew_30'){
                if($params['renew'] == 'yes'){
                    $text = docharge;
                }
                else{
                    $text = new_sub_with_QR_15_charge;
                }
                $start_datetime = "`start_datetime`=NOW(),";
                $expiry_datetime = "`expiry_datetime`=now() + interval 30 day,";
                $status = "`status`='1' ,";
                $params['price_point'] = '15';
            }
            else if($params['type'] == 'subscribe'){
                $text = subscribeMsgO;
                if($params['docharge'] == '0'){
                    $status = "`status`='1' ,";
                }
                else{
                    $status = "`status`='0' ,";
                }
                if($nonUserSubscription != null){
                    if($nonUserSubscription['premier_user'] == '1'){
                        $text = premierUserText;
                        if($nonUserSubscription['language'] != "ar-QA"){
                            $params['language'] = "en";
                        }
                        else{
                            $params['language'] = "ar-QA";
                        }
                        $language = "`language`='{$params['language']}',";
                        $premier_user = "`premier_user`='1',";
                        $start_datetime = "`start_datetime`=NOW(),";
                        if($userSubscriptions['expiry_datetime'] != "" && ($userSubscriptions['expiry_datetime'] > $nonUserSubscription['cuurentdate'])){
                            $days = 30;
                            $start_datetime = "";
                            $expiry_datetime = date('Y-m-d H:i:s', $userSubscriptions['expiry_datetime']." + ".(int)$days." days");
                            $expiry_datetime = "`expiry_datetime`='{$expiry_datetime}' ,";
                        }
                        else{
                            $expiry_datetime = "`expiry_datetime`=now() + interval 30 day,";
                        }
                        $params['docharge'] = '0';
                        $status = "`status`='0' ,";
                        $params['type'] = 'premier_user_sub';
                    }
                    else if($nonUserSubscription['status'] == '1' && ($nonUserSubscription['expiry_datetime'] > $nonUserSubscription['cuurentdate'])){
                        $status = "`status`='1' ,";
                        $params['docharge'] = '0';
                        if($userSubscriptions['expiry_datetime'] != "" && ($userSubscriptions['expiry_datetime'] > $nonUserSubscription['cuurentdate'])){
                            $nonUserSubscription['timediffer'] = abs($nonUserSubscription['timediffer']);
                            $expiry_datetime = "".$userSubscriptions['expiry_datetime']." +".(int)$nonUserSubscription['timediffer']." days";
                            $expiry_datetime = date("Y-m-d H:i:s", strtotime($expiry_datetime));
                            $expiry_datetime = "`expiry_datetime`='{$expiry_datetime}' ,";
                        }
                        else{
                            $start_datetime = "`start_datetime`='{$nonUserSubscription['start_datetime']}' ,";
                            $expiry_datetime = "`expiry_datetime`='{$nonUserSubscription['expiry_datetime']}' ,";
                        }
                    }
                }
            }
            else if($params['type'] == 'unsubscribe' || $params['type'] == 'decline'){
                if($params['type'] == 'unsubscribe'){
                    $text = unsubscribeMsgO;
                }
                else if($params['type'] == 'decline'){
                    $text = declineMsgO;
                }
                $status = "`status`='0' ,";
                $params['docharge'] = '0';
                DbMethods::unsubscribe($params);
            }
            else if($params['type'] == 'manuallyUnsubscribe'){
                $text = unsubscribeMsgO;
                $status = "`status`='0' ,";
                $params['docharge'] = '0';
            }
            if($params['user_id'] != ""){
                $where = "`user_id`='{$params['user_id']}'";
            }
            else{
                $where = "`phone`='{$params['phone']}'";
            }
            $query = "UPDATE `subscriptions` SET
		".$premier_user."
		".$language."
		".$start_datetime."
		".$expiry_datetime."
		".$status."
		`network`='ooredoo' ,
		`phone`='{$params['phone']}'
		WHERE ".$where."";
            mysqli_query($con, $query);
            $params['start_datetime'] = $start_datetime;
            $params['expiry_datetime'] = $expiry_datetime;
            DbMethods:: subscriptionslog($params);
            if($nonUserSubscription != null){
                nonUsersMethods::deleteNonUser($params);
            }
            $params['unsubcron'] = 'decline';
            if($params['docharge'] == '1'){
                DbMethods::doChargeMonthly($params);
            }
            else{
                $dir = $params['apiBasePath']."sendMT";
                if($text != ''){
                    DbMethods:: post_async($dir, [
                        'phone'         => $params['phone'],
                        'Text'          => $text,
                        'Authorization' => $params['Authorization']
                    ]);
                }
            }
            return "added";
        }
        /* 1:------------------------------method start here subscriptionslog ------------------------------*/
        static function subscriptionslog($params){
            $con = $params['dbconnection'];
            $params['network'] = 'ooredoo';
            if($params['price_point'] != ''){
                $price_point = " `price_point`='{$params['price_point']}' ,";
            }
            else{
                $price_point = "";
            }
            if($params['response_code'] != ''){
                $response_code = " `response_code`='{$params['response_code']}' ,";
            }
            else{
                $response_code = "";
            }
            if($params['type'] == "renew_1" || $params['type'] == "renew_7" || $params['type'] == "renew_30"){
                if($params['renew'] == 'yes'){
                    $params['type'] = "renewal";
                }
                else{
                    $params['type'] = "docharge";
                }
            }
            if(isset($params['mobile']) && ($params['mobile'] == 'sms_sub' || $params['mobile'] == 'sms_unsub' || $params['mobile'] == 'nonuser_docharge' || $params['mobile'] == 'nonuser_renewal' || $params['mobile'] == 'nonuser_unsub' || $params['mobile'] == 'nonuser_decline')){
                $params['type'] = $params['mobile'];
                if($params['mobile'] == 'nonuser_docharge'){
                    if($params['renew'] == 'yes'){
                        $params['type'] = "nonuser_renewal";
                    }
                    else{
                        $params['type'] = "nonuser_docharge";
                    }
                }
            }
            $language = "";
            if($params['language'] != "ar-QA"){
                $params['language'] = "en";
            }
            $language = "`language`='{$params['language']}',";
            $user_id = "";
            if($params['user_id'] != ""){
                $user_id = "`user_id`='{$params['user_id']}',";
            }
            $query_log = "INSERT INTO `subscriptions_log` SET ".$user_id." `phone`='{$params['phone']}', `network`='{$params['network']}', ".$params['start_datetime']." ".$params['expiry_datetime']." ".$price_point." ".$language." ".$response_code." `type`='{$params['type']}'";
            mysqli_query($con, $query_log);
            return "added";
        }
        /* 1:------------------------------method start here docharge ------------------------------*/
        static function doChargeMonthly($params){
            $con = $params['dbconnection'];
            set_time_limit(0);
            $PricePointId = "5132856";//15 QAR 1 month
            //$PricePointId="5132876";//.5 QAR 1 day
            $params['ExtTxId'] = substr(md5(microtime(true)), -16);
            $url = "http://mb.timwe.com/neo-billing-plugins-qao-facade/docharge?PartnerRoleId=".PartnerRoleId."&Password=".Password."&ProductId=".ProductId."&PricePointId=".$PricePointId."&Destination=".$params['phone']."&OpId=".OpId."&ExtTxId=".$params['ExtTxId']."";
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
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120); //timeout in seconds
                $data = curl_exec($ch);
                curl_close($ch);
                /* print_r($data);
                 echo "_";
                 exit;*/
                if($data){
                    $params['docharge'] = '0';
                    $params['network'] = 'ooredoo';
                    $params['type'] = 'renew_30';
                    $params['renew'] = $params['renewed'];
                    $params['response_code'] = $data;
                    $params['price_point'] = '15';
                    $regarr = explode(",", $data);
                    if(isset($regarr[1]) && $regarr[1] != '1'){
                        DbMethods:: subscriptionslog($params);
                    }
                    if(isset($regarr[1]) && $regarr[1] == '1'){
                        DbMethods::updateSubscription($params);
                        return ["code" => 0, "data" => 'Success'];
                    }
                    else if(isset($regarr[1]) && $regarr[1] == '1122'){
                        return DbMethods:: doChargeWeekly($params);
                    }
                    else{
                        if(isset($regarr[0]) && $regarr[0] == '-81'){
                            $params['unsubcron'] = 'unsub';
                        }
                        if($params['unsubcron'] == 'unsub' || $params['unsubcron'] == 'decline'){
                            if($params['unsubcron'] == 'decline'){
                                $params['type'] = 'decline';
                            }
                            else{
                                $params['type'] = 'unsubscribe';
                            }
                            $params['response_code'] = $data;
                            DbMethods::updateSubscription($params);
                        }
                        return ["code" => 1, "data" => 'Insufficient balance.'];
                    }
                }
            }
            catch(Exception  $e){
                return ["code" => 1, "data" => $e->getMessage()];
            }
        }
        /* 1:------------------------------method start here dochargeweek ------------------------------*/
        static function doChargeWeekly($params){
            $con = $params['dbconnection'];
            set_time_limit(0);
            $PricePointId = "5132866";//3.75 QAR 1 week
            //$PricePointId="5132876";//.5 QAR 1 day
            $params['ExtTxId'] = substr(md5(microtime(true).rand(0, 10)), -16);
            $url = "http://mb.timwe.com/neo-billing-plugins-qao-facade/docharge?PartnerRoleId=".PartnerRoleId."&Password=".Password."&ProductId=".ProductId."&PricePointId=".$PricePointId."&Destination=".$params['phone']."&OpId=".OpId."&ExtTxId=".$params['ExtTxId']."";
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
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120); //timeout in seconds
                $data = curl_exec($ch);
                curl_close($ch);
                //print_r($data);
                if($data){
                    $params['docharge'] = '0';
                    $params['network'] = 'ooredoo';
                    $params['type'] = 'renew_7';
                    $params['renew'] = $params['renewed'];
                    $params['response_code'] = $data;
                    $params['price_point'] = '3.75';
                    $regarr = explode(",", $data);
                    if(isset($regarr[1]) && $regarr[1] != '1'){
                        DbMethods:: subscriptionslog($params);
                    }
                    if(isset($regarr[1]) && $regarr[1] == '1'){
                        DbMethods::updateSubscription($params);
                        return ["code" => 0, "data" => 'Success'];
                    }
                    else if(isset($regarr[1]) && $regarr[1] == '1122'){
                        return DbMethods:: doChargeDaily($params);
                    }
                    else{
                        if(isset($regarr[0]) && $regarr[0] == '-81'){
                            $params['unsubcron'] = 'unsub';
                        }
                        if($params['unsubcron'] == 'unsub' || $params['unsubcron'] == 'decline'){
                            if($params['unsubcron'] == 'decline'){
                                $params['type'] = 'decline';
                            }
                            else{
                                $params['type'] = 'unsubscribe';
                            }
                            $params['response_code'] = $data;
                            DbMethods::updateSubscription($params);
                        }
                        return ["code" => 1, "data" => 'Insufficient balance.'];
                    }
                }
            }
            catch(Exception  $e){
                return ["code" => 1, "data" => $e->getMessage()];
            }
        }
        /* 1:------------------------------method start here dochargedaily ------------------------------*/
        static function doChargeDaily($params){
            $con = $params['dbconnection'];
            set_time_limit(0);
            $PricePointId = "5132876";//.5 QAR 1 day
            $params['ExtTxId'] = substr(md5(microtime(true).rand(0, 10)), -16);
            $url = "http://mb.timwe.com/neo-billing-plugins-qao-facade/docharge?PartnerRoleId=".PartnerRoleId."&Password=".Password."&ProductId=".ProductId."&PricePointId=".$PricePointId."&Destination=".$params['phone']."&OpId=".OpId."&ExtTxId=".$params['ExtTxId']."";
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
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120); //timeout in seconds
                $data = curl_exec($ch);
                curl_close($ch);
                //print_r($data);
                if($data){
                    $params['docharge'] = '0';
                    $params['network'] = 'ooredoo';
                    $params['type'] = 'renew_1';
                    $params['renew'] = $params['renewed'];
                    $params['response_code'] = $data;
                    $params['price_point'] = '.5';
                    $regarr = explode(",", $data);
                    if(isset($regarr[1]) && $regarr[1] != '1'){
                        DbMethods:: subscriptionslog($params);
                    }
                    if(isset($regarr[1]) && $regarr[1] == '1'){
                        DbMethods::updateSubscription($params);
                        return ["code" => 0, "data" => 'Success'];
                    }
                    else{
                        if(isset($regarr[0]) && $regarr[0] == '-81'){
                            $params['unsubcron'] = 'unsub';
                        }
                        if($params['unsubcron'] == 'unsub' || $params['unsubcron'] == 'decline'){
                            if($params['unsubcron'] == 'decline'){
                                $params['type'] = 'decline';
                            }
                            else{
                                $params['type'] = 'unsubscribe';
                            }
                            $params['response_code'] = $data;
                            DbMethods::updateSubscription($params);
                        }
                        return ["code" => 1, "data" => 'Insufficient balance.'];
                    }
                }
            }
            catch(Exception  $e){
                return ["code" => 1, "data" => $e->getMessage()];
            }
        }
        /* 1:------------------------------method start here cronjob ------------------------------*/
        static function cronjob($params){
            /*$date = date("d M Y H:i:s");
            $text = "call cronjob ".$date." \n"  . $query ." \n";
            $filename = "/var/www/html/up_qatar/api/v1/subscription/newfile.txt";
            $fh = fopen($filename, "a");
            fwrite($fh, $text);
            fclose($fh);
            exit;*/
            $con = $params['dbconnection'];
            $query = "SELECT DISTINCT(`phone`) as phone ,`user_id` ,TIMESTAMPDIFF(HOUR,`expiry_datetime`,NOW()) as timediffer
		FROM  `subscriptions` 
		WHERE `network`='ooredoo' AND `status`='1' AND `premier_user` !='1' AND (`expiry_datetime` < NOW() OR `start_datetime` IS NULL) AND `phone` !=''";
            $result = mysqli_query($con, $query);
            if(mysqli_num_rows($result) > 0){
                while($row1 = mysqli_fetch_assoc($result)){
                    $unsubcron = '';
                    if($row1['timediffer'] >= 48){
                        $unsubcron = 'unsub';
                    }
                    $dir = $params['apiBasePath']."doChargeMonthly";
                    DbMethods:: post_async($dir, [
                        'phone'     => $row1['phone'],
                        'user_id'   => $row1['user_id'],
                        'renewed'   => 'yes',
                        'unsubcron' => $unsubcron
                    ]);
                    usleep(1000000);
                    /*$params['phone']=$row1['phone'];
                    $params['user_id']=$row1['user_id'];
                    $params['renewed']='yes';
                    $params['unsubcron']=$unsubcron;
                    echo DbMethods::doChargeMonthly($params);
                    echo "<br>";*/
                }
            }
            $dir2 = $params['apiBasePath']."runNonRCronJob";
            DbMethods:: post_async($dir2, ['method' => 'runNonRCronJob']);
            return "completed";
        }
        /* 1:------------------------------method start here cronjob ------------------------------*/
        static function changeOperator($params){
            $con = $params['dbconnection'];
            $query = "SELECT
		`user_id`
		FROM `subscriptions` 
		WHERE `user_id`='{$params['user_id']}' ";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                return "subexist";
            }
            $query = "UPDATE `users` SET
		`network`='{$params['network']}' 
		WHERE `user_id`='{$params['user_id']}' ";
            mysqli_query($con, $query);
            return "updated";
        }
        /* 1:------------------------------method start here post_async ------------------------------*/
        static function post_async($url, $params){
            /*echo $url;
            echo "==========";
            print_r($params);*/
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
        /* END-----------------------------TPAY------------------------------END*/
        /* 1:------------------------------method start here AddSubscriptionContractRequest ------------------------------*/
        static function AddSubscriptionContractRequest($params){
            $con = $params['dbconnection'];
            $subscribe = false;
            $query2 = "SELECT `user_id` FROM  `subscriptions`
			WHERE  `phone`='{$params['msisdn']}'  AND `user_id` !='{$params['user_id']}'";
            $resul2 = mysqli_query($con, $query2);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($resul2) > 0){
                return ["code" => 1, "data" => 'This mobile number is already registered.'];
            }
            $query = "SELECT `id`,`user_id`,`msisdn`,`start_datetime`,`expiry_datetime`,`status`,`network`,NOW() as today ,
			TIMESTAMPDIFF(MINUTE, NOW(), `expiry_datetime`) as diff
			FROM  `subscriptions`  
			WHERE  `user_id`='{$params['user_id']}'";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                $subscribe = true;
                if($row['expiry_datetime'] > $row['today']){
                    return ["code" => 1, "data" => 'Subscription already exist.'];
                }
            }
            $params2['customerAccountNumber'] = $params['user_id'];
            //$params2['msisdn']=substr($params['msisdn'], 3);
            $params2['msisdn'] = $params['msisdn'];
            $params2['operatorCode'] = operatorCode;
            $params2['subscriptionPlanId'] = subscriptionPlanId;
            $params2['initialPaymentproductId'] = initialPaymentproductId;
            $params2['initialPaymentDate'] = contractStartDate;
            $params2['executeInitialPaymentNow'] = executeInitialPaymentNow;
            $params2['recurringPaymentproductId'] = recurringPaymentproductId;
            $params2['productCatalogName'] = productCatalogName;
            $params2['executeRecurringPaymentNow'] = executeRecurringPaymentNow;
            $params2['contractStartDate'] = contractStartDate;
            $params2['contractEndDate'] = contractEndDate;
            $params2['autoRenewContract'] = autoRenewContract;
            $params2['language'] = language;
            $params2['sendVerificationSMS'] = sendVerificationSMS;
            $msgtotalp = implode("", $params2);
            $hash = hash_hmac('sha256', $msgtotalp, PrivateKey);
            $signature = Publickey.":".$hash;
            $params2['signature'] = $signature;
            // print_r(json_encode($params2));
            $url = "http://".tpaybaseurl.".TPAY.me/api/TPAYSubscription.svc/Json/AddSubscriptionContractRequest";
            try{
                static $ch;
                if(empty($ch)){
                    $ch = curl_init();
                }
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params2));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $headers = ["Content-Type:application/json"];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $data = curl_exec($ch);
                curl_close($ch);
                //print_r($data);
                if($data = json_decode($data)){
                    if($data->errorMessage == null){
                        $querytd = "DELETE FROM `temp_subscriptions`
								WHERE `user_id`='{$params['user_id']}'";
                        mysqli_query($con, $querytd);
                        $query_s = "INSERT INTO `temp_subscriptions` SET
								`user_id`='{$params['user_id']}',
								`subscriptionContractId`='{$data->subscriptionContractId}',
								`phone`='{$params['phone']}',
								`network`='vodafone'";
                        $result_no = mysqli_query($con, $query_s);
                        if(mysqli_error($con) != ''){
                            return "mysql_Error:-".mysqli_error($con);
                        }
                        return ["code" => 0, "data" => $data->subscriptionContractId];
                        /*$params['subscriptionContractId']=$data->subscriptionContractId;
                        return DbMethods:: SendSubscriptionContractVerificationSMS($params) ;*/
                    }
                    else{
                        return ["code" => 1, "data" => $data->errorMessage];
                    }
                }
                else{
                    return ["code" => 1, "data" => 'error'];
                }
            }
            catch(Exception  $e){
                $error = $e->getMessage();
            }
        }
        /* 1:------------------------------method start here SendSubscriptionContractVerificationSMS ------------------------------*/
        static function SendSubscriptionContractVerificationSMS($params){
            $con = $params['dbconnection'];
            $params2 = [];
            $params2['subscriptionContractId'] = $params['subscriptionContractId'];
            $msgtotalp = $params2['subscriptionContractId'];
            $hash = hash_hmac('sha256', $msgtotalp, PrivateKey);
            $signature = Publickey.":".$hash;
            $params2['signature'] = $signature;
            //print_r(json_encode($params2));
            $url = "http://".tpaybaseurl.".TPAY.me/api/TPAYSubscription.svc/Json/SendSubscriptionContractVerificationSMS";
            try{
                static $ch;
                if(empty($ch)){
                    $ch = curl_init();
                }
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params2));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $headers = ["Content-Type:application/json"];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $data = curl_exec($ch);
                curl_close($ch);
                // print_r($data);
                if($data = json_decode($data)){
                    if($data->errorMessage == null){
                        return ["code" => 0, "data" => $data->subscriptionContractId];
                    }
                    else{
                        return ["code" => 1, "data" => $data->errorMessage];
                    }
                }
                else{
                    return ["code" => 1, "data" => 'error'];
                }
            }
            catch(Exception  $e){
                $error = $e->getMessage();
            }
        }
        /* 1:------------------------------method start here VerifySubscriptionContract ------------------------------*/
        static function VerifySubscriptionContract($params){
            $con = $params['dbconnection'];
            $params2 = [];
            $params2['subscriptionContractId'] = $params['subscriptionContractId'];
            $params2['pinCode'] = $params['pinCode'];
            $msgtotalp = $params2['subscriptionContractId'].$params2['pinCode'];
            $hash = hash_hmac('sha256', $msgtotalp, PrivateKey);
            $signature = Publickey.":".$hash;
            $params2['signature'] = $signature;
            //print_r(json_encode($params2));
            $url = "http://".tpaybaseurl.".TPAY.me/api/TPAYSubscription.svc/Json/VerifySubscriptionContract";
            try{
                static $ch;
                if(empty($ch)){
                    $ch = curl_init();
                }
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params2));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $headers = ["Content-Type:application/json"];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $data = curl_exec($ch);
                curl_close($ch);
                //print_r($data);
                if($data = json_decode($data)){
                    if($data->errorMessage == null){
                        $queryf = "SELECT *,
							(SELECT `user_id` FROM `subscriptions`
							 WHERE `user_id`=`temp_subscriptions`.`user_id` ORDER BY `id` LIMIT 0,1 ) as customer_iddd
							FROM  `temp_subscriptions`  
							WHERE `subscriptionContractId`='{$params['subscriptionContractId']}' ";
                        $resultf = mysqli_query($con, $queryf);
                        if(mysqli_error($con) != ''){
                            return "mysql_Error:-".mysqli_error($con);
                        }
                        if(mysqli_num_rows($resultf) > 0){
                            $rowf = mysqli_fetch_assoc($resultf);
                            if($rowf['customer_iddd']){
                                $subqueryvr = "`expiry_datetime`=now() + interval 1 day ,";
                                $querych = "SELECT `id`,`user_id`,`phone`,`start_datetime`,`expiry_datetime`,`status`,`network`,NOW() as today ,
									TIMESTAMPDIFF(MINUTE, NOW(), `expiry_datetime`) as diff
									FROM  `subscriptions`  
									WHERE `user_id`='{$rowf['customer_iddd']}' ";
                                $resultch = mysqli_query($con, $querych);
                                if(mysqli_num_rows($resultch) > 0){
                                    $rowch = mysqli_fetch_assoc($resultch);
                                    if($rowch['expiry_datetime'] > $rowch['today']){
                                        $subqueryvr = "`expiry_datetime`='{$rowch['expiry_datetime']}' ,";
                                    }
                                }
                                $query_s = "UPDATE `subscriptions` SET
									`start_datetime`=NOW(),
									 ".$subqueryvr."
									`subscriptionContractId`='{$rowf['subscriptionContractId']}' ,
									`phone`='{$rowf['phone']}' ,
									`network`='vodafone',
									`status`='1'  
									WHERE `user_id`='{$rowf['customer_iddd']}' ";
                                $result_no = mysqli_query($con, $query_s);
                                if(mysqli_error($con) != ''){
                                    return "mysql_Error:-".mysqli_error($con);
                                }
                            }
                            else{
                                $query_s = "INSERT INTO `subscriptions` SET
									`start_datetime`=NOW(),
									`expiry_datetime`=now() + interval 1 day ,
									`subscriptionContractId`='{$rowf['subscriptionContractId']}' ,
									`phone`='{$rowf['phone']}' ,
									`user_id`='{$rowf['user_id']}' ,
									`network`='vodafone',
									`status`='1' ";
                                $result_no = mysqli_query($con, $query_s);
                                if(mysqli_error($con) != ''){
                                    return "mysql_Error:-".mysqli_error($con);
                                }
                            }
                            $dir = $params['apiBasePath']."SendFreeMTMessage";
                            DbMethods:: post_async($dir, [
                                'msisdn'        => $rowf['phone'],
                                'messageBody'   => subscribeMsg,
                                'Authorization' => $params['Authorization']
                            ]);
                            return ["code" => 0, "data" => $data->subscriptionContractId];
                        }
                    }
                    else{
                        return ["code" => 1, "data" => $data->errorMessage];
                    }
                }
                else{
                    return ["code" => 1, "data" => 'error'];
                }
            }
            catch(Exception  $e){
                $error = $e->getMessage();
            }
        }
        /* 1:------------------------------method start here CancelSubscriptionContractRequest ------------------------------*/
        static function CancelSubscriptionContractRequest($params){
            $con = $params['dbconnection'];
            $params2 = [];
            $subscribe = false;
            $query = "SELECT `user_id`,`subscriptionContractId`,`phone`
			FROM  `subscriptions`  
			WHERE  `user_id`='{$params['user_id']}'";
            $result = mysqli_query($con, $query);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                $params['subscriptionContractId'] = $row['subscriptionContractId'];
                $params['msisdn'] = $row['phone'];
            }
            else{
                return ["code" => 1, "data" => 'Subscription is not exist.'];
            }
            $params2['subscriptionContractId'] = $params['subscriptionContractId'];
            $msgtotalp = $params2['subscriptionContractId'];
            $hash = hash_hmac('sha256', $msgtotalp, PrivateKey);
            $signature = Publickey.":".$hash;
            $params2['signature'] = $signature;
            // print_r(json_encode($params2));
            $url = "http://".tpaybaseurl.".TPAY.me/api/TPAYSubscription.svc/Json/CancelSubscriptionContractRequest";
            try{
                static $ch;
                if(empty($ch)){
                    $ch = curl_init();
                }
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params2));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $headers = ["Content-Type:application/json"];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $data = curl_exec($ch);
                curl_close($ch);
                //print_r($data);
                if($data = json_decode($data)){
                    if($data->errorMessage == null || $data->errorMessage == 'Contract Is Already Canceled'){
                        $dir = $params['apiBasePath']."SendFreeMTMessage";
                        DbMethods:: post_async($dir, [
                            'msisdn'        => $params['msisdn'],
                            'messageBody'   => unsubscribeMsg,
                            'Authorization' => $params['Authorization']
                        ]);
                        $query_s = "UPDATE `subscriptions` SET
							`status`='0'
							WHERE `subscriptionContractId`='{$params['subscriptionContractId']}' AND `network`='vodafone' ";
                        $result_no = mysqli_query($con, $query_s);
                        if(mysqli_error($con) != ''){
                            return "mysql_Error:-".mysqli_error($con);
                        }
                        return ["code" => 0, "data" => $params2['subscriptionContractId']];
                    }
                    else{
                        return ["code" => 1, "data" => $data->errorMessage];
                    }
                }
                else{
                    return ["code" => 1, "data" => 'error'];
                }
            }
            catch(Exception  $e){
                $error = $e->getMessage();
            }
        }
        /* 1:------------------------------method start here SendFreeMTMessage ------------------------------*/
        static function SendFreeMTMessage($params){
            $con = $params['dbconnection'];
            $params2['messageBody'] = $params['messageBody'];
            $params2['msisdn'] = $params['msisdn'];
            $params2['operatorCode'] = operatorCode;
            $msgtotalp = implode("", $params2);
            $hash = hash_hmac('sha256', $msgtotalp, PrivateKey);
            $signature = Publickey.":".$hash;
            $params2['signature'] = $signature;
            // print_r(json_encode($params2));
            $url = "http://".tpaybaseurl.".TPAY.me/api/TPAY.svc/json/SendFreeMTMessage";
            try{
                static $ch;
                if(empty($ch)){
                    $ch = curl_init();
                }
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params2));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $headers = ["Content-Type:application/json"];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $data = curl_exec($ch);
                curl_close($ch);
                // print_r($data);
                if($data = json_decode($data)){
                    if($data->errorMessage == null){
                        return ["code" => 0, "data" => 'sent'];
                    }
                    else{
                        return ["code" => 1, "data" => $data->errorMessage];
                    }
                }
                else{
                    return ["code" => 1, "data" => 'error'];
                }
            }
            catch(Exception  $e){
                $error = $e->getMessage();
            }
        }
        /* 1:------------------------------method start here CancelSubscriptionContractRequest ------------------------------*/
        static function tpayCallback($params){
            $con = $params['dbconnection'];
            $expiry_datetime = "";
            $now_datetime = "";
            $newstr = json_encode($params);
            $query_s = "
	INSERT INTO `tpay` SET  
	`query`='{$newstr}' ";
            $result_no = mysqli_query($con, $query_s);
            if(mysqli_error($con) != ''){
                return "mysql_Error:-".mysqli_error($con);
            }
            if($params['msisdn']){
                $querych = "SELECT `id`,`user_id`,`phone`,`start_datetime`,`expiry_datetime`,`status`,`network`,NOW() as today ,
		TIMESTAMPDIFF(MINUTE, NOW(), `expiry_datetime`) as diff
		FROM  `subscriptions`  
		WHERE  `phone`='{$params['msisdn']}'";
                $resultch = mysqli_query($con, $querych);
                if(mysqli_num_rows($resultch) > 0){
                    $rowch = mysqli_fetch_assoc($resultch);
                    if($rowch['expiry_datetime'] > $rowch['today']){
                        $now_datetime = $rowch['today'];
                        $expiry_datetime = $rowch['expiry_datetime'];
                    }
                }
            }
            if($params['action'] == 'SubscriptionChargingNotification' && $params['paymentTransactionStatusCode'] == 'PaymentCompletedSuccessfully'){
                $params['nextPaymentDate'] = date('Y-m-d H:i:s', strtotime($params['nextPaymentDate']));
                if($expiry_datetime != "" && $now_datetime != ""){
                    $datetime1 = date_create($now_datetime);
                    $datetime2 = date_create($expiry_datetime);
                    $interval = date_diff($datetime1, $datetime2);
                    $params['nextPaymentDate'] = date('Y-m-d H:i:s', strtotime($params['nextPaymentDate'].' + '.$interval->days.' days'));
                }
                $query_s = "
			UPDATE `subscriptions` SET  
			`status`='1' ,
			`expiry_datetime`='{$params['nextPaymentDate']}'
			WHERE `user_id`='{$params['customerAccountNumber']}' 
			AND `subscriptionContractId`='{$params['subscriptionContractId']}'  
			AND  `network`='vodafone'  ";
                $result_no = mysqli_query($con, $query_s);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
            }
            if($params['action'] == 'SubscriptionChargingNotification' && $params['paymentTransactionStatusCode'] != 'PaymentCompletedSuccessfully'){
                $subquery = "";
                $subquery = "`expiry_datetime`=NOW() - INTERVAL 8 HOUR";
                if($expiry_datetime != "" && ($expiry_datetime > date('Y-m-d H:i:s', strtotime($now_datetime.' 1 days')))){
                    $subquery = "`expiry_datetime`='{$expiry_datetime}'";
                }
                $query_s = "
			UPDATE `subscriptions` SET  
			`status`='1' ,
			".$subquery."
			WHERE `user_id`='{$params['customerAccountNumber']}' 
			AND `subscriptionContractId`='{$params['subscriptionContractId']}'  
			AND  `network`='vodafone'  ";
                $result_no = mysqli_query($con, $query_s);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
            }
        }
        /* END-----------------------------END END END END------------------------------END*/
    }
