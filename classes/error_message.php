<?php
class Error_message {
    
	var $data = array(
		'error' => array(),
		'value' => array()
	);
	
	var $error_db = NULL;
	
    function __construct()
    {
		require($GLOBALS['config']['database_path'] . '/error_database.php');
		$this->error_db = new Error_database();
		
		if (isset($_GET['e_k'])) {
			$errors = $this->error_db->get($_GET['e_k']);
			if ($errors !== null) {
				$this->data = unserialize($errors);
				$this->error_db->delete($_GET['e_k']);
			}
		}
    }
    
    function add($group, $key, $message)
    {
		$this->data['error'][$group][$key] = $message;
    }
	
	function get_message($group, $key)
	{
		return ' <span class="error">' . $this->data['error'][$group][$key] . '</span>';
	}
    
    function exists($group, $key)
    {
		if ($this->exists_group($group))
		{
			return array_key_exists($key, $this->data['error'][$group]);
		}
		else
		{
			return False;
		}
    }
	
	function exists_group($group)
	{
		return array_key_exists($group, $this->data['error']);
	}
    
    function add_field_value($group, $key, $value)
    {
        $this->data['value'][$group][$key] = $value;
    }
	
	function field_value_exists($group, $key)
	{
		if (array_key_exists($group, $this->data['value']))
		{
			return array_key_exists($key, $this->data['value'][$group]);
		}
		else
		{
			return False;
		}
	}
	
	function get_field_value($group, $key)
	{
		return $this->data['value'][$group][$key];
	}
	
	function save()
	{
		$serialized = serialize($this->data);
		$error_key = hash('md5', uniqid(). $serialized);
		$this->error_db->save($error_key, $serialized);
		return $error_key;
	}
	
	function clear($group)
	{
		//unset($_SESSION['error'][$group]);
		//unset($_SESSION['value'][$group]);
	}
}
