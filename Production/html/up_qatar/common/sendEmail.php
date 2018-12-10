<?php
include_once '../api/v1/mobile/outh.php';
outh::checkIp($_POST['con']);
require_once('PHPMailer-master/class.phpmailer.php');
$header = apache_request_headers();
if($header['Authorization']!="UP!and$")
exit; 


if(isset ($_POST['to'] , $_POST['subject'], $_POST['body'], $_POST['con'])) 
	{
		$params['to']=$_POST['to'];
		$params['subject']=$_POST['subject'];
		$params['body']=$_POST['body'];
	    HelpingMethods::sendEmail($params);
	}

class HelpingMethods {
/* 1:------------------------------method start here validatemsisdn 1------------------------------*/
	 static function sendEmail($params) 
	   {
			//print_r($params);
			
			$email_array = explode(',', $params['to']);
			//print_r($email_array);
			$subject=$params['subject']; 
			$body=$params['body'];
			$mail = new PHPMailer(); // create a new object
			$mail->IsSMTP(); // enable SMTP
			$mail->SMTPDebug = 0; // debugging: 1 = errors and messages, 2 = messages only
			$mail->SMTPAuth = true; // authentication enabled
			$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
			$mail->Host = "ssl://smtp.gmail.com";
			$mail->Port = 465; // or 587
			$mail->IsHTML(true);
			
			/*$mail->Username = "man8oosha.jo@gmail.com";
			$mail->Password = "M@n8oosha";
			$mail->SetFrom("man8oosha.jo@gmail.com");*/
			
			$mail->Username = "support@urbanpoint.com";
			$mail->Password = "urbanpoint.com";
			$mail->SetFrom("support@urbanpoint.com");
			$mail->Subject = $subject;
			$mail->Body = $body;
			$mail->CharSet = 'UTF-8';
			//$mail->AddAddress($to);
			for($i = 0; $i < count($email_array); $i++) {
			 $mail->AddAddress($email_array[$i]);
			}
			$mail->Send();
			return "sent";
			exit;
			
			/*if(!$mail->Send()) {
			echo "Mailer Error: " . $mail->ErrorInfo;
			} else {
			echo "Message has been sent";
			}*/
	   }
		 
/* 1:------------------------------end end end end------------------------------*/	

}



?>