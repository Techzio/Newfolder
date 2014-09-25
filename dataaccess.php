<?php
	require_once('Configuration.php');
	class DataAccess {
		
		private $_host, $_userId, $_password, $_db, $_port;
		private $_connection;
		public $_error;
		private $_sql, $_config;
		
		public function OpenDefault() {
			$this->_config = new IniConfiguration();
			$this->_config->load();
			$c = $this->_config->getActiveDbConfiguration();
			$this->Open($c->host, $c->userId, $c->password, $c->db, $c->port);
		}
		
		public function Open($host, $userid, $pwd, $db, $port=3306) {
			$this->_connection = new mysqli($host, $userid, $pwd, $db, $port);
			if ($this->_connection->connect_error) {
				die('Could not connect to database: '.$this->_connection->connect_error);
			}
			$this->_host = $host;
			$this->_userId = $userid;
			$this->_password = $pwd;
			$this->_db = $db;
			$this->_port = $port;
		}
		
		public function RetrieveData($sql) {
			try {
				$this->_sql = $sql;
				$this->_connection->multi_query($sql);
				if (mysqli_errno($this->_connection)) {
					$msg = mysqli_error($this->_connection)." executing SQL: ".$sql;
					throw new Exception($msg, mysqli_errno($this->_connection));
				}
		
				$data = array();
				$qindex = 0;
				do {
					if ($result = $this->_connection->use_result()) {
						$resultset = array();
						while($row = $result->fetch_object()) {
							array_push($resultset, $row);
						}
						$result->close();
						++$qindex;
						$data[$qindex] = $resultset;
					}
				} while ($this->_connection->next_result());
		
				if ($qindex > 1) {
					return $data;
				} else {
					if (count($data) == 1) {
						return $data[1];
					} else {
						return array();
					}
				}
			} catch (Exception $ex) {
				$this->_error = $ex;
				throw $ex;
			}
		}
		
		public function LastSql() {
			$g = $this->_config->getValue("general", "debug");
			if ($g == "1") {
				return $this->_sql;
			} else {
				return "";
			}
		}
		
		public function setSql($sql) {
			$this->_sql = $sql;
		}
		
		public function escapeString($str) {
			return $this->_connection->real_escape_string($str);
		}
		
	}
?>
