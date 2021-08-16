<?php
	/************************************
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	class DB
	{
		private $servername;
		private $username;
		private $password;
		private $dbname;

		public $conn;
		public $db_error;

		function __construct()
		{
			// DB INFO
			$this->servername = "localhost";
			$this->dbname = "rt_rt";
			// $this->username = "rt_rt";
			// $this->password = "QQE6myLYwgyX8aLEwvu3";
			$this->username = "root";
			$this->password = "";

			// Create connection
			$this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
			$this->conn->set_charset("utf8");
			// Check connection
			if ($this->conn->connect_error)
			{
				$this->db_error = "Connection failed: " . $this->conn->connect_error;
			}
			else
			{
				$this->db_error = '';
			}
		}
	}
?>