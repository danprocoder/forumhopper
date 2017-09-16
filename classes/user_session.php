<?php

class User_session {
	
    function __construct()
    {
		require($GLOBALS['config']['database_path'] . '/user_session_database.php');
        $this->session_db = new User_Session_Database();
		
		define('SESS_COOKIE_NAME', $GLOBALS['config']['session_cookie_name']);
		
		ini_set('session.name', SESS_COOKIE_NAME);
		ini_set('session.cookie_httponly', '1');
		ini_set('session.cookie_path', '/');
    }
	
	function start_new_sess($user_id, $keep_user_logged_in)
	{	
		if (!session_id())
		{
			if ($keep_user_logged_in)
			{
				ini_set('session.cookie_lifetime', $GLOBALS['config']['session_cookie_lifetime']);
			}
			else
			{
				ini_set('session.cookie_lifetime', '0');
			}
			session_start();
			$this->session_db->add_sess_info(session_id(), $user_id, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
			$this->update_last_online($user_id);
		}
		
		return session_id();
	}
	
	function end_current_session()
	{
		if (isset($_COOKIE[SESS_COOKIE_NAME]))
		{
			$this->session_db->delete_sess_record($_COOKIE[SESS_COOKIE_NAME]);
			
			$params = session_get_cookie_params();
			setcookie(SESS_COOKIE_NAME, '', time() - 3600, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
		}
	}
	
	function is_logged_in()
	{
		if (isset($_COOKIE[SESS_COOKIE_NAME]))
		{
			$sess_info = $this->session_db->get_sess_info($_COOKIE[SESS_COOKIE_NAME]);
			if ($sess_info !== NULL)
			{
				return $sess_info;
			}
			else
			{
				$params = session_get_cookie_params();
				setcookie(SESS_COOKIE_NAME, '', time() - 3600, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}
	
	function get_last_online($user_id)
	{
		return $this->session_db->get_last_active($user_id);
	}
	
	function update_last_online($user_id)
	{
		$this->session_db->set_last_active(time(), $user_id);
	}
}
