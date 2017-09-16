<?php require_once('database.php');

class User_Database extends Database {
	
    function __construct()
    {
        parent::__construct();
    }
    
	/**
	 * Add new user into the database.
	 *
	 * @param string The user's username.
	 * @param string The user's email address.
	 * @param string The user's password salt.
	 * @param string The user's hashed password.
	 */
    function add_user($username, $email, $salt, $password)
	{
		return $this->_add_user($username, $email, $salt, $password, False);
	}
	
	function add_admin($username, $email, $salt, $password)
	{
		return $this->_add_user($username, $email, $salt, $password, True);
	}
	
	private function _add_user($user, $email, $salt, $password, $is_admin)
	{
		$stmt = $this->db->prepare("INSERT INTO {$this->tables->users}(username, email, salt, password, joined, is_admin)VALUES(?,?,?,?,?,?)");
		$sec_joined = time();
		$is_admin = $is_admin ? 1 : 0;
		$stmt->bind_param('ssssii', $user, $email, $salt, $password, $sec_joined, $is_admin);
		$stmt->execute();
		
		$user_id = $this->last_insert_id();
		
		$meta_stmt = $this->db->prepare("INSERT INTO {$this->tables->usermeta}(user_id)VALUES(?)") or die($this->db->error);
		$meta_stmt->bind_param('i', $user_id);
		$meta_stmt->execute();
		
		// Create user settings.
		$setting_stmt = $this->db->prepare("INSERT INTO {$this->tables->users_settings}(user_id,num_threads_per_page,num_replies_per_page)VALUES(?,?,?)") or die($this->db->error);
		$setting_stmt->bind_param('iii', $user_id, $GLOBALS['config']['default_num_threads_per_page'], $GLOBALS['config']['default_num_replies_per_page']);
		$setting_stmt->execute();
		
		return $user_id;
	}
	
	function get_salt($login)
	{
		$stmt = $this->db->prepare("SELECT salt FROM {$this->tables->users} WHERE username=? OR email=?");
		if ( ! $stmt) die($this->db->error);
		$stmt->bind_param('ss', $login, $login);
		$stmt->execute();
		
		$result = $this->select_one_row($stmt);
		return $result !== NULL ? $result['salt'] : NULL;
	}
	
	/**
	 * Fetches the user's database row id using the user's username or email and password.
	 *
	 * @param string The user's username or email
	 * @param string The user's password
	 */
	function get_user_id_by_login($login, $pass)
	{
		$stmt = $this->db->prepare("SELECT user_id FROM {$this->tables->users} WHERE (username=? OR email=?) AND password=?");
		if ( ! $stmt) die( $this->db->error );
		$stmt->bind_param('sss', $login, $login, $pass);
		$stmt->execute();
		
		$result = $this->select_one_row($stmt);
		return $result !== NULL ? $result['user_id'] : NULL;
	}
	
	function get_user_by_id($user_id)
	{
		$stmt = $this->db->prepare("SELECT username, email FROM {$this->tables->users} WHERE user_id=?") or die($this->db->error);
		$stmt->bind_param('i', $user_id);
		$stmt->execute();
		
		return $this->select_one_row($stmt);
	}
	
	function get_user_meta($username)
	{
		$stmt = $this->db->prepare("
		SELECT
			u.user_id,
			u.username,
			m.fullname,
			m.birthdate,
			m.picture_filename,
			m.sex,
			m.location,
			u.joined,
			u.is_banned,
			u.last_active
		FROM {$this->tables->users} AS u
		
		INNER JOIN {$this->tables->usermeta} AS m
		ON m.user_id=u.user_id
		
		WHERE u.username=?") or die($this->db->error);
		$stmt->bind_param('s', $username);
		$stmt->execute();
		
		return $this->select_one_row($stmt);
	}
	
	function user_exists($field, $value)
	{
		$stmt = $this->db->prepare("SELECT user_id FROM {$this->tables->users} WHERE {$field}=?") or die($this->db->error);
		$stmt->bind_param('s', $value);
		$stmt->execute();
		return $this->select_one_row($stmt) !== NULL;
	}
	
	function change_email($user_id, $email)
	{
		$this->_update($user_id, 'email', $email);
	}
	
	function change_password($user_id, $password)
	{
		$this->_update($user_id, 'password', $password);
	}
	
	function _update($user_id, $field, $value)
	{
		$stmt = $this->db->prepare("UPDATE {$this->tables->users} SET `$field`=? WHERE `user_id`=?") or die($this->db->error);
		$stmt->bind_param('si', $value, $user_id);
		$stmt->execute();
	}
	
	function update_meta($user_id, $array)
	{
		$fields = '';
		foreach (array_keys($array) as $k)
		{
			$fields .= "`$k`=?,";
		}
		$fields = rtrim($fields, ',');
		
		$stmt = $this->db->prepare("UPDATE `{$this->tables->usermeta}` SET $fields WHERE `user_id`=?") or die($this->db->error);
		
		$type = '';
		for ($i = 0; $i < count($array); $i++)
		{
			$type .= 's';
		}
		$type .= 'i'; // For user_id
		
		$values = array($type);
		$values = array_merge($values, array_values($array));
		$values[] = $user_id;
		for ($i = 0; $i < count($values); $i++)
		{
			$values[ $i ] =& $values[ $i ];
		}
		call_user_func_array(array($stmt, 'bind_param'), $values);
		
		$stmt->execute();
	}
	
	function set_ban($user_id, $ban) {
		$stmt = $this->db->prepare("UPDATE {$this->tables->users} SET is_banned=? WHERE user_id=?") or die($this->db->error);
		$ban = ($ban == true ? 1 : 0);
		$stmt->bind_param('ii', $ban, $user_id);
		$stmt->execute();
	}
	
	function is_admin($user_id) {
		$stmt = $this->db->prepare("SELECT is_admin FROM `{$this->tables->users}` WHERE user_id=?") or die($this->db->error);
		$stmt->bind_param('i', $user_id);
		$stmt->execute();
		
		return $this->select_one_row($stmt)['is_admin'] === 1;
	}
	
	function is_banned($user_id) {
		$stmt = $this->db->prepare("SELECT is_banned FROM `{$this->tables->users}` WHERE user_id=?") or die($this->db->error);
		$stmt->bind_param('i', $user_id);
		$stmt->execute();
		
		return $this->select_one_row($stmt)['is_banned'] == 1;
	}
	
	function get_moderators($sort_by, $username_like='')
	{
		$sql = "
			SELECT
				u.username,
				m.cat_id,
				c.cat,
				u.joined
			FROM {$this->tables->moderators} AS m
			INNER JOIN {$this->tables->users} AS u
			ON u.user_id=m.mod_id
			INNER JOIN {$this->tables->categories} AS c
			ON c.cat_id=m.cat_id
		";
		
		
		if ($username_like != null && $username_like != '')
		{
			$sql .= ' AND u.username LIKE ?';
		}
		
		if ($sort_by == 'az')
		{
			$sql .= ' ORDER BY u.username ASC';
		}
		elseif ($sort_by == 'za')
		{
			$sql .= ' ORDER BY u.username DESC';
		}
		elseif ($sort_by == 'time')
		{
			$sql .= ' ORDER BY u.joined ASC';
		}
		
		$stmt = $this->db->prepare($sql) or $this->log_error(__FILE__, __LINE__);
		if ($username_like != null && $username_like != '')
		{
			$username_like .= '%';
			$stmt->bind_param('s', $username_like);
		}
		$stmt->execute();
		
		return $this->select_rows($stmt);
	}
	
	function is_moderator($user_id, $cat_id=0)
	{
		$sql = "SELECT id FROM {$this->tables->moderators} WHERE mod_id=?";
		if ($cat_id != 0)
		{
			$sql .= ' AND cat_id=?';
		}
		$stmt = $this->db->prepare($sql) or $this->log_error(__FILE__, __LINE__);
		if ($cat_id != 0)
		{
			$stmt->bind_param('ii', $user_id, $cat_id);
		}
		else
		{
			$stmt->bind_param('i', $user_id);
		}
		$stmt->execute();
		
		return $this->select_one_row($stmt) !== null;
	}
	
	/**
	 * Adds a moderator.
	 *
	 * @since 1.0
	 */
	function add_moderator($user_id, $cat_id)
	{
		$stmt = $this->db->prepare("INSERT INTO {$this->tables->moderators}(mod_id,cat_id)VALUES(?,?)") or $this->log_error(__FILE__, __LINE__);
		$stmt->bind_param('ii', $user_id, $cat_id);
		$stmt->execute();
	}
	
	/**
	 * Removes a moderator from a category.
	 *
	 * @since 1.0
	 *
	 * @param 
	 * @param 
	 */
	function remove_moderator($user_id, $cat_id=0)
	{
		$sql = "DELETE FROM {$this->tables->moderators} WHERE mod_id=?";
		if ($cat_id != 0)
		{
			$sql .= " AND cat_id=?";
		}
		$stmt = $this->db->prepare($sql) or $this->log_error(__FILE__, __LINE__);
		if ($cat_id == 0)
		{
			$stmt->bind_param('i', $user_id);
		}
		else
		{
			$stmt->bind_param('ii', $user_id, $cat_id);
		}
		$stmt->execute();
	}
	
	function get_moderator_cat_ids($user_id)
	{
		$stmt = $this->db->prepare("SELECT cat_id FROM {$this->tables->moderators} WHERE mod_id=?") or die();
		$stmt->bind_param('i', $user_id);
		$stmt->execute();
		
		$cat_ids = array();
		$rows = $this->select_rows($stmt);
		if ($rows != null)
		{
			foreach ($rows as $r)
			{
				$cat_ids[] = $r->cat_id;
			}
		}
		return $cat_ids;
	}
	
	function get_all_users($admin_id, $sort_by, $username_like)
	{
		$sql = "SELECT last_active, user_id, username, joined, is_banned
			FROM {$this->tables->users}
			WHERE NOT user_id=?";
		
		if ($username_like != null && $username_like != '')
		{
			$sql .= ' AND username LIKE ?';
		}
			
		switch ($sort_by)
		{
		case 'az':
			$sql .= ' ORDER BY username ASC';
			break;
		case 'za':
			$sql .= ' ORDER BY username DESC';
			break;
		case 'time':
			$sql .= ' ORDER BY joined ASC';
			break;
		}
		$stmt = $this->db->prepare($sql) or $this->log_error(__FILE__, __LINE__);
		if ($username_like != '' && $username_like != null)
		{
			$username_like .= '%';
			$stmt->bind_param('is', $admin_id, $username_like);
		}
		else
		{
			$stmt->bind_param('i', $admin_id);
		}
		$stmt->execute();
		return $this->select_rows($stmt);
	}
	
	function remove_user($user_id)
	{
		$stmt = $this->db->prepare("DELETE FROM {$this->tables->users} WHERE user_id=?") or die($this->db->error);
		$stmt->bind_param('i', $user_id);
		$stmt->execute();
	}
}
