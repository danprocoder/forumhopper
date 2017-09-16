<?php require_once('database.php');

class User_session_database extends Database {
    
    function __construct()
    {
        parent::__construct();
    }
    
    function add_sess_info($session_id, $user_id, $user_agent, $ip)
	{
		$stmt = $this->db->prepare("INSERT INTO {$this->tables->users_sessions}(session_id, user_id, user_agent, ip_addr, time_created)VALUES(?,?,?,?,?)") or die($this->db->error);
		$sess_created = time();
		$stmt->bind_param('sissi', $session_id, $user_id, $user_agent, $ip, $sess_created);
		$stmt->execute();
	}
	
	function get_sess_info($session_id)
	{
		$stmt = $this->db->prepare("SELECT user_id, user_agent, ip_addr FROM {$this->tables->users_sessions} WHERE session_id=?") or die($this->db->error);
		$stmt->bind_param('s', $session_id);
		$stmt->execute();
		
		return $this->select_one_row($stmt);
	}
	
	function get_last_active($user_id)
	{
		$stmt = $this->db->prepare("SELECT last_active FROM {$this->tables->users} WHERE user_id=?") or die($this->db->error);
		$stmt->bind_param('i', $user_id);
		$stmt->execute();
		
		return $this->select_one_row($stmt)['last_active'];
	}
	
	function set_last_active($sec_last_active, $user_id)
	{
		$stmt = $this->db->prepare("UPDATE {$this->tables->users} SET last_active=? WHERE user_id=?") or die($this->db->error);
		$stmt->bind_param('ii', $sec_last_active, $user_id);
		$stmt->execute();
	}
	
	function delete_sess_record($session_id)
	{
		$stmt = $this->db->prepare("DELETE FROM {$this->tables->users_sessions} WHERE session_id=?") or die($this->db->error);
		$stmt->bind_param('s', $session_id);
		$stmt->execute();
	}
}
