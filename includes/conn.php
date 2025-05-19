<?php
$stime = time();
date_default_timezone_set("Africa/Nairobi");
//include './includes/security.php';
// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', 3600);
ini_set('max_execution_time', 3600);
// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(3600);
//*
class Database
{


	private $server = "mysql:host=srv1140.hstgr.io;dbname=u854855859_bsnverif";
	private $username = "u854855859_bsnverif";
	private $password = "3:+H#hMa@W";
	private $options  = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,);
	protected $conn;

	public function open()
	{
		try {
			$this->conn = new PDO($this->server, $this->username, $this->password, $this->options);
			return $this->conn;
		} catch (PDOException $e) {
			echo "There is some problem in connection: " . $e->getMessage();
		}
	}

	public function close()
	{
		$this->conn = null;
	}
}

$pdo = new Database();

$conn = $pdo->open();
//*/

