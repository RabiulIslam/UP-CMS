<?php
class dbConfigMethods 
{
    /* 1:------------------------------method start here dbConnection 1------------------------------*/
	static function dbConnection()
	 {
			
            			
           static $condb;
			if(!$condb)
			{
				date_default_timezone_set('UTC');
				/*define('SERVER', 'andpercent.cpecinchdkud.ap-south-1.rds.amazonaws.com');
				define('USERNAME', 'andpercent');
				define('PASSWORDd', 'andpercent!123');
				define('DATABASE', 'up_qatar');*/
				
				
				define('SERVER', 'updater.cvbaznhpajws.eu-central-1.rds.amazonaws.com');
				define('USERNAME', 'up_qatar2018');
				define('PASSWORDd', 'up!2018&bymayar?h');
				define('DATABASE', 'up_qatar');
				
				/*define('SERVER', 'localhost');
				define('USERNAME', 'root');
				define('PASSWORDd', '');
				define('DATABASE', 'up_qatar');*/
				
				$condb=mysqli_connect(SERVER, USERNAME, PASSWORDd,DATABASE);
				if (!$condb) {
				   die("Connection failed: " . mysqli_connect_error());
				}
				mysqli_set_charset($condb,"utf8");
				
			}
			return $condb;
			//mysqli_close($con);
	 }
	 
	 
	
}
?>
