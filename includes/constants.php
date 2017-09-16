<?php
require_once($config['database_path'] . '/database.php');
require($config['__'] . '/user_session.php');

$sess = new User_Session();
$sess_info = $sess->is_logged_in();

define('USER_IS_LOGGED_IN', ($sess_info !== False));

require($config['database_path'] . '/user_database.php');
$user_db = new User_Database();
	
if (USER_IS_LOGGED_IN)
{
	// Update session last active
	$sess->update_last_online($sess_info['user_id']);
	
	$user = $user_db->get_user_by_id($sess_info['user_id']);
	define('USER_NICK', $user['username']);
	define('USER_EMAIL', $user['email']);
	
	define('USER_IS_ADMIN', $user_db->is_admin($sess_info['user_id']));
	
	// Admin cannot be banned.
	if ($user_db->is_banned($sess_info['user_id']) && ! USER_IS_ADMIN)
	{
		define('USER_IS_BANNED', true);
	}
	else
	{
		define('USER_IS_BANNED', false);
	}
}

// If user is banned, redirect the user to 'banned.php'.
if (USER_IS_LOGGED_IN && USER_IS_BANNED && pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_BASENAME) !== 'banned.php')
{
	header('Location: '.$config['http_host'].'/banned.php');
	die();
}

define('IP_ADDRESS', $_SERVER['REMOTE_ADDR']);

require($config['database_path'] . '/thread_category.php');
$cat = new Thread_Category();

require($config['__'] . '/error_message.php');
$error = new Error_message();

// Clear current_thread cookie so if user leaves a thread to another page and comes back to it,
// it counts as another view.
if (pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_BASENAME) !== 'view.php')
{
	setcookie('current_thread', '', time() - 3600);
}

require($config['includes_path'].'/functions/user.php');

require($config['__'] . '/Theme_manager.php');
$theme = Theme_manager::load_theme($config['site_theme']);
if ($theme == null)
{
	die('Unable to load theme');
}
