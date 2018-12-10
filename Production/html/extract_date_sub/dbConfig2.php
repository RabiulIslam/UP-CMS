<?php
    date_default_timezone_set('Asia/Qatar');
	define('SERVER', 'updater.cvbaznhpajws.eu-central-1.rds.amazonaws.com');
	define('USERNAME', 'up_qatar2018');
	define('PASSWORDd', 'up!2018&bymayar?h');
	define('DATABASE', 'up_qatar');
	
	
	/*define('SERVER', 'localhost');
	define('USERNAME', 'root');
	define('PASSWORDd', '');
	define('DATABASE', 'up_qatar');*/



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
