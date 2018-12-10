<?php
    include (__DIR__).'/../../../common/defaults.php';
    include_once 'dbMethods.php';
    include_once 'premierUser.php';
    
    class nonUsersMethods{
        /* 1:------------------------------method start here runNonRCronJob ------------------------------*/
        static function runNonRCronJob($params){
            /* $date = date("d M Y H:i:s");
             $text = "call runNonRCronJob ".$date." \n" . $query ." \n";
             $filename = "/var/www/html/up_qatar/api/v1/subscription/newfile.txt";
             $fh = fopen($filename, "a");
             fwrite($fh, $text);
             fclose($fh);
             return "completed";
             exit;*/
            $con = $params['dbconnection'];
            $query = "SELECT DISTINCT(ncs.`phone`) as phone  ,TIMESTAMPDIFF(HOUR,ncs.`expiry_datetime`,NOW()) as timediffer,cs.`id`
	   FROM `non_registered_users` as ncu
	   INNER JOIN `nonregisteredusers_sub` as ncs ON(ncu.`phone`=ncs.`phone`)
	   LEFT OUTER JOIN  `subscriptions` cs ON(cs.`phone` =ncs.`phone`) 
	   WHERE  ncs.`status`='1' AND ncu.`premier_user` !='1' AND (ncs.`expiry_datetime` < NOW() OR ncs.`start_datetime` IS NULL) AND ncs.`phone` !=''";
            $result = mysqli_query($con, $query);
            if(mysqli_num_rows($result) > 0){
                while($row1 = mysqli_fetch_assoc($result)){
                    if($row1['id'] != null){
                        $params['phone'] = $row1['phone'];
                        nonUsersMethods::deleteNonUser($params);
                    }
                    else{
                        $unsubcron = '';
                        if($row1['timediffer'] >= 48){
                            $unsubcron = 'unsub';
                        }
                        $params['phone'] = $row1['phone'];
                        $params['unsubcron'] = $unsubcron;
                        $params['renewed'] = 'yes';
                        nonUsersMethods:: doChargeMonthly($params);
                    }
                }
            }
            return "completed";
        }
        /* 1:------------------------------method start here addNonUserSubscription ------------------------------*/
        static function addNonUserSubscription($params){
            $con = $params['dbconnection'];
            $query = "INSERT `non_registered_users` SET `phone`='{$params['phone']}'";
            mysqli_query($con, $query);
            $query = "INSERT `nonregisteredusers_sub` SET `phone`='{$params['phone']}', `status`='0'";
            mysqli_query($con, $query);
            /*$params['start_datetime'] = "";
            $params['expiry_datetime'] = "";
            $params['type'] = 'sms_sub';
            DbMethods:: subscriptionslog($params);
            nonUsersMethods::doChargeMonthly($params);*/
            return "added";
        }
        /* 1:------------------------------method start here updateNonUserSubscription ------------------------------*/
        static function updateNonUserSubscription($params){
            $con = $params['dbconnection'];
            $start_datetime = "";
            $expiry_datetime = "";
            $text = "";
            $premier_user = "";
            $nonuser_decline = false;
            if($params['status'] == '0'){
                $status = "`status`='0' ,";
            }
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
                $params['mobile'] = 'nonuser_docharge';
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
                $params['mobile'] = 'nonuser_docharge';
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
                $params['mobile'] = 'nonuser_docharge';
            }
            else if($params['type'] == 'subscribe'){
                $text = subscribeMsgO;
                if($params['docharge'] == '0'){
                    $status = "`status`='1' ,";
                }
                else{
                    $status = "`status`='0' ,";
                }
                $params['mobile'] = 'sms_sub';
            }
            else if($params['type'] == 'unsubscribe' || $params['type'] == 'decline'){
                if($params['type'] == 'unsubscribe'){
                    $text = unsubscribeMsgO;
                    $params['type'] = 'nonuser_unsub';
                }
                else if($params['type'] == 'decline'){
                    $text = declineMsgO;
                    $nonuser_decline = true;
                }
                $status = "`status`='0' ,";
                $params['docharge'] = '0';
                nonUsersMethods::unsubscribe($params);
            }
            if($params['premier_user'] == '1'){
                $text = premierUserText;
                if($params['language'] != "ar-QA"){
                    $params['language'] = "en";
                }
                $language = "`language`='{$params['language']}',";
                $premier_user = "`premier_user`='1' ,";
                $status = "`status`='0' ,";
                $start_datetime = "";
                $expiry_datetime = "";
                $query = "UPDATE `non_registered_users` SET ".$language." `premier_user`='1' WHERE `phone`='{$params['phone']}'";
                mysqli_query($con, $query);
                $query = "DELETE FROM `nonregisteredusers_sub` WHERE `phone`='{$params['phone']}'";
                mysqli_query($con, $query);
                $params['type'] = 'add_premier_nonUser';
            }
            //--//
            $query = "UPDATE `nonregisteredusers_sub` SET ".$start_datetime." ".$expiry_datetime." ".$status." `id`=`id` WHERE `phone`='{$params['phone']}'";
            mysqli_query($con, $query);
            //--//
            $params['start_datetime'] = $start_datetime;
            $params['expiry_datetime'] = $expiry_datetime;
            DbMethods:: subscriptionslog($params);
            //--//
            if($nonuser_decline){
                $type = $params['type'];
                $mobile = $params['mobile'];
                //--//
                $params['type'] = 'nonuser_decline';
                $params['mobile'] = 'nonuser_decline';
                DbMethods:: subscriptionslog($params);
                //--//
                $params['type'] = $type;
                $params['mobile'] = $mobile;
            }
            //--//
            $params['unsubcron'] = 'decline';
            if($params['docharge'] == '1'){
                nonUsersMethods::doChargeMonthly($params);
            }
            else{
                $dir = $params['apiBasePath']."sendMT";
                if($text != ''){
                    nonUsersMethods:: post_async($dir, ['phone' => $params['phone'], 'Text' => $text]);
                }
            }
            return "added";
        }
        /* 1:------------------------------method start here getNonUserSubscription ------------------------------*/
        static function getNonUserSubscription($params){
            $con = $params['dbconnection'];
            if($params['phone'] != ""){
                $query = "SELECT
			nru.`phone`,
			nru.`premier_user`,
			nru.`language`,
			nrus.`start_datetime`,
			nrus.`expiry_datetime`,
			nrus.`status`,
			NOW() as cuurentdate,
			TIMESTAMPDIFF(DAY,`expiry_datetime`,NOW()) as timediffer
			FROM `non_registered_users` as nru LEFT OUTER JOIN `nonregisteredusers_sub` as nrus ON(nru.`phone`=nrus.`phone`)
			WHERE nru.`phone`='{$params['phone']}'";
                $result = mysqli_query($con, $query);
                if(mysqli_error($con) != ''){
                    return "mysql_Error:-".mysqli_error($con);
                }
                if(mysqli_num_rows($result) > 0){
                    $row = mysqli_fetch_assoc($result);
                    return $row;
                }
            }
            return null;
        }
        /* 1:------------------------------method start here deleteNonUser ------------------------------*/
        static function deleteNonUser($params){
            $con = $params['dbconnection'];
            $query = "DELETE FROM `non_registered_users` WHERE `phone`='{$params['phone']}'";
            mysqli_query($con, $query);
            $query = "DELETE FROM `nonregisteredusers_sub` WHERE `phone`='{$params['phone']}'";
            mysqli_query($con, $query);
            return "deleted";
        }
        /* 1:------------------------------method start here doChargeMonthly ------------------------------*/
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
                if($data){
                    $params['docharge'] = '0';
                    $params['network'] = 'ooredoo';
                    $params['price_point'] = '15';
                    $params['response_code'] = $data;
                    $params['mobile'] = 'nonuser_docharge';
                    $regarr = explode(",", $data);
                    // --- start
                    /*
                    $params['renew'] = "No";
                    $params['type'] = 'renew_30';
                    $params['price_point'] = '15';
                    $params['response_code'] = $data;
                    $params['mobile'] = 'nonuser_docharge';
                    if(isset($regarr[1]) && $regarr[1] != '1'){
                        DbMethods:: subscriptionslog($params);
                    }
                    */
                    // --- end
                    if(isset($regarr[1]) && $regarr[1] == '1'){
                        $params['type'] = 'renew_30';
                        $params['response_code'] = $regarr[1];
                        $params['renew'] = $params['renewed'];
                        nonUsersMethods:: updateNonUserSubscription($params);
                        return ["code" => 0, "data" => 'Success'];
                    }
                    else if(isset($regarr[1]) && $regarr[1] == '1122'){
                        return nonUsersMethods:: doChargeWeekly($params);
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
                            nonUsersMethods:: updateNonUserSubscription($params);
                        }
                        return ["code" => 1, "data" => 'Insufficient balance.'];
                    }
                }
            }
            catch(Exception  $e){
                return ["code" => 1, "data" => $e->getMessage()];
            }
        }
        /* 1:------------------------------method start here docharge7 ------------------------------*/
        static function doChargeWeekly($params){
            $con = $params['dbconnection'];
            set_time_limit(0);
            $PricePointId = "5132866";//3.75 QAR 1 week
            // $PricePointId=PricePointId;
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
                    $params['price_point'] = '3.75';
                    $params['response_code'] = $data;
                    $params['mobile'] = 'nonuser_docharge';
                    $regarr = explode(",", $data);
                    // --- start
                    /*
                    $params['renew'] = "No";
                    $params['type'] = 'renew_7';
                    $params['price_point'] = '3.75';
                    $params['response_code'] = $data;
                    $params['mobile'] = 'nonuser_docharge';
                    if(isset($regarr[1]) && $regarr[1] != '1'){
                        DbMethods:: subscriptionslog($params);
                    }
                    */
                    // --- end
                    if(isset($regarr[1]) && $regarr[1] == '1'){
                        $params['type'] = 'renew_7';
                        $params['response_code'] = $regarr[1];
                        $params['renew'] = $params['renewed'];
                        nonUsersMethods:: updateNonUserSubscription($params);
                        return ["code" => 0, "data" => 'Success'];
                    }
                    else if(isset($regarr[1]) && $regarr[1] == '1122'){
                        return nonUsersMethods:: doChargeDaily($params);
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
                            nonUsersMethods:: updateNonUserSubscription($params);
                        }
                        return ["code" => 1, "data" => 'Insufficient balance.'];
                    }
                }
            }
            catch(Exception  $e){
                return ["code" => 1, "data" => $e->getMessage()];
            }
        }
        /* 1:------------------------------method start here doChargeDaily ------------------------------*/
        static function doChargeDaily($params){
            $con = $params['dbconnection'];
            set_time_limit(0);
            $PricePointId = "5132876";//.5 QAR 1 day
            // $PricePointId=PricePointId;
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
                    $params['price_point'] = '.5';
                    $params['response_code'] = $data;
                    $params['mobile'] = 'nonuser_docharge';
                    $regarr = explode(",", $data);
                    // --- start
                    /*
                    $params['renew'] = "No";
                    $params['type'] = 'renew_1';
                    $params['price_point'] = '.5';
                    $params['response_code'] = $data;
                    $params['mobile'] = 'nonuser_docharge';
                    if(isset($regarr[1]) && $regarr[1] != '1'){
                        DbMethods:: subscriptionslog($params);
                    }
                    */
                    // --- end
                    if(isset($regarr[1]) && $regarr[1] == '1'){
                        $params['type'] = 'renew_1';
                        $params['response_code'] = $regarr[1];
                        $params['renew'] = $params['renewed'];
                        nonUsersMethods:: updateNonUserSubscription($params);
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
                            nonUsersMethods:: updateNonUserSubscription($params);
                        }
                        return ["code" => 1, "data" => 'Insufficient balance.'];
                    }
                }
            }
            catch(Exception  $e){
                return ["code" => 1, "data" => $e->getMessage()];
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
                //exit;
                if($data){
                    $data = json_decode(json_encode(simplexml_load_string($data)), true);
                    if($data['response']['responsestatus']['code'] == '1' || $data['response']['responsestatus']['code'] == '-77'){
                        nonUsersMethods::deleteNonUser($params);
                        return $data;
                    }
                    else{
                        return $data;
                    }
                }
            }
            catch(Exception  $e){
                return $e->getMessage();
            }
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
