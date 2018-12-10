<?php

$dir ="http://18.185.217.28/up_qatar/api/v1/subscription/cronjob"; 
echo post_async($dir ,array('Msisdn'=>'1122'));

function post_async($url, $params) 
{
	$Authorization='UP!and$';
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
	$out.= "Authorization: ".$Authorization."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;
    fwrite($fp, $out);
    fclose($fp);
	
	
	$date = date("d M Y H:i:s");
	$text = "Cats chase mice mayar  ".$date." \n";
	$filename = "/var/www/html/iosweb/up_qatar/api/v1/subscription/newfile.txt";
	$fh = fopen($filename, "a");
	fwrite($fh, $text);
	fclose($fh);
	
}


echo "done";

?>