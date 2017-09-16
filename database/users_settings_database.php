<?php
class Users_settings_database extends Database {
    
    function __construct($user_id)
    {
        parent::__construct();
		
		$this->user_id = $user_id;
    }
	
	function set_num_replies_per_page($value)
	{
		$this->_change_setting('num_replies_per_page', $value);
	}
	
	function set_num_threads_per_page($value)
	{
		$this->_change_setting('num_threads_per_page', $value);
	}
	
	function get_num_replies_per_page()
	{
		return $this->_get_setting('num_replies_per_page');
	}
	
	function get_num_threads_per_page()
	{
		return $this->_get_setting('num_threads_per_page');
	}
    
    function _change_setting($setting, $value)
    {
        $stmt = $this->db->prepare("UPDATE {$this->tables->users_settings} SET $setting=? WHERE user_id=?") or die($this->db->error);
		
		$type = 'i';
		if (is_int($value))
		{
			$type .= 'i';
		}
		elseif (is_string($value))
		{
			$type .= 's';
		}
		$stmt->bind_param($type, $value, $this->user_id);
		$stmt->execute();
    }
	
	function _get_setting($setting)
	{
		$stmt = $this->db->prepare("SELECT `$setting` FROM {$this->tables->users_settings} WHERE user_id=?") or die($this->db->error);
		$stmt->bind_param('i', $this->user_id);
		$stmt->execute();
		
		return $this->select_one_row($stmt)[$setting];
	}
}
