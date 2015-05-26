<?php
class db {
	
	public $dbconn; 
			
	//Construct
	public function __construct() {
		
      $dsn = 'mysql:dbname=routefolio;host=127.0.0.1';
      $user = 'root';
      $pass = '';
	  
      try {
        $this->dbconn = new PDO($dsn, $user, $pass);
      }
	  
	  catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
      }
	
	  
	}
	
	//blank2Null
	public function blank2Null($v) {
		if($v == ""){
			$v = "NULL";
		}
		return $v;
	}
}
?>