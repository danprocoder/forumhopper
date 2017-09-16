<?php
class Database {

    function __construct()
	{
		if ( ! isset($GLOBALS['db']))
		{
			$this->db = new mysqli(
				$GLOBALS['config']['db_host'],
				$GLOBALS['config']['db_user'],
				$GLOBALS['config']['db_pass'],
				$GLOBALS['config']['db_name']);
			$GLOBALS['db'] = $this->db;
		}
		else
		{
			$this->db = $GLOBALS['db'];
		}
		
		$this->tables = (object) array(
			'users'           => 'users',
			'usermeta'        => 'usermeta',
			'users_settings'  => 'users_settings',
			'categories'      => 'categories',
			'users_sessions'  => 'users_sessions',
			'threads'         => 'threads',
			'threads_replies' => 'threads_replies',
			'threads_views'   => 'threads_views',
			'errors'          => 'errors',
			'moderators'      => 'moderators',
		);
    }
	
	/**
	 * Selects the first row from the database. Execute the statement first then pass the statement as argument.
	 *
	 * @param object The statement
	 */
	function select_one_row($stmt)
	{
		$stmt->store_result();
		
		if ($stmt->num_rows > 0)
		{
			$meta = $stmt->result_metadata();
			$fields = $meta->fetch_fields();
			
			$row = array();
			foreach ($fields as $f)
			{
				$row[$f->name] = '';
				$row[$f->name] =& $row[$f->name];
			}
			
			call_user_func_array(array($stmt, 'bind_result'), $row);
			$stmt->fetch();
			
			return $row;
		}	
		else
		{
			return NULL;
		}
	}
	
	function select_rows($stmt)
	{
		$stmt->store_result();
		
		if ($stmt->num_rows > 0)
		{
			$results = array();
			
			$meta = $stmt->result_metadata();
			$fields = $meta->fetch_fields();
			
			while (True)
			{
				$row = $this->_get_result_holder($fields);
				call_user_func_array(array($stmt, 'bind_result'), $row);
				
				if ($stmt->fetch() === NULL)
				{
					break;
				}
				
				array_push($results, (object) $row);
			}
		
			return $results;
		}
		else
		{
			return NULL;
		}
	}
	
	function _get_result_holder($fields)
	{
		$row = array();
		foreach ($fields as $f)
		{
			$row[$f->name] = '';
			$row[$f->name] =& $row[$f->name];
		}
		return $row;
	}
	
	function last_insert_id()
	{
		return $this->db->insert_id;
	}
	
	function log_error($file, $line)
	{
		$error = $this->db->error;
		
		$stmt = $this->db->prepare("INSERT INTO error_log(file,line,message)VALUES(?,?,?)");
		$file = substr($file, strlen(FORUM_ROOT) + 1);
		$stmt->bind_param('sis', $file, $line, $error);
		$stmt->execute();
		
		die($error);
	}
}
