<?php
    date_default_timezone_set('Asia/Qatar');
	define('SERVER', 'updbbyandpercent.cykhtx3lt0cv.us-east-2.rds.amazonaws.com');
	define('USERNAME', 'andpercentUp');
	define('PASSWORDd', 'magent$ma!1463');
	//define('PASSWORDd', '');
	define('DATABASE', 'urban_point_apis');



	$con=mysqli_connect(SERVER, USERNAME, PASSWORDd,DATABASE);
	if (!$con) {
	die("Connection failed: " . mysqli_connect_error());
	}
	
	
	
	$now = new DateTime();
	
	$mins = $now->getOffset() / 60;
	$sgn = ($mins < 0 ? -1 : 1);
	$mins = abs($mins);
	$hrs = floor($mins / 60);
	$mins -= $hrs * 60;
	
	$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
	
	$query=("SET @session.time_zone='".$offset."'; SET @@session.time_zone='".$offset."';");
	$query=("SET @global.time_zone='".$offset."'; SET @@global.time_zone='".$offset."';");
	mysqli_query($con,$query) ;
	
	
	mysqli_set_charset($con,"utf8");
?>
