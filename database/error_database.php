<?php require_once('database.php');

class Error_database extends Database {
    
    function __construct()
    {
        parent::__construct();
    }
    
    function get($error_key)
	{
		$stmt = $this->db->prepare("SELECT message FROM {$this->tables->errors} WHERE error_key=?") or die($this->db->error);
		$stmt->bind_param('s', $error_key);
		$stmt->execute();
		
		return $this->select_one_row($stmt)['message'];
	}
	
	function save($error_key, $message)
	{
		$stmt = $this->db->prepare("INSERT INTO {$this->tables->errors}(error_key, message)VALUES(?, ?)") or die($this->db->error);
		$stmt->bind_param('ss', $error_key, $message);
		$stmt->execute();
	}
	
	function delete($error_key)
	{
		$stmt = $this->db->prepare("DELETE FROM {$this->tables->errors} WHERE error_key=?") or die($this->db->error);
		$stmt->bind_param('s', $error_key);
		$stmt->execute();
	}
}
