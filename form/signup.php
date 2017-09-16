<?php
require('../config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if (USER_IS_LOGGED_IN)
{
	header($config['http_host']);
	die();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (isset($_POST['user']) && isset($_POST['email']) && isset($_POST['pass']) && isset($_POST['re_pass']))
	{
		$username = trim($_POST['user'], ' ');
		$email = trim($_POST['email'], ' ');
		$password = trim($_POST['pass'], ' ');
		$re_password = trim($_POST['re_pass'], ' ');
		
		if (validate($username, $email, $password, $re_password))
		{
			// Save user info to database.
			require($config['includes_path'] . '/password.php');
			$salt = generate_user_salt();
			$password = hash_password($salt, $password);
			$user_id = $user_db->add_user($username, $email, $salt, $password);
			
			// Start user session.
			// $sess declared in constants.php
			$sess->start_new_sess($user_id, True);
			
			header('Location: ' . $config['http_host'] . '/index.php');
		}
		else
		{
			$error->add_field_value('signup', 'username', $username);
			$error->add_field_value('signup', 'email', $email);
			$error->add_field_value('signup', 'password', $password);
			$error->add_field_value('signup', 're_password', $re_password);
			$error_key = $error->save();
			header('Location: ' . $config['http_host'] . '/signup.php?e_k=' . $error_key);
		}
	}
}

function validate($username, $email, $password, $re_password)
{
	global $error, $user_db;
	
	// Validate username.
	if (strlen($username) < 2 || strlen($username) > 12)
	{
		$error->add('signup', 'username', 'Username must be between 2 - 12 characters long.');
	}
	elseif (!preg_match('/^[a-zA-Z][a-zA-Z_0-9]+$/', $username))
	{
		$error->add('signup', 'username', 'Username must begin with a letter, followed by either a letter, a number or _.');
	}
	elseif ($user_db->user_exists('username', $username))
	{
		$error->add('signup', 'username', 'Username already used by another user.');
	}
	
	// Validate password.
	if (strlen($password) < 8)
	{
		$error->add('signup', 'password', 'Password must be at least 8 characters long.');
	}
	elseif (strtolower($password) == strtolower($username))
	{
		$error->add('signup', 'password', 'You cannot use your username as password.');
	}
	
	if ($password !== $re_password)
	{
		$error->add('signup', 're_password', 'Password does not match the one provided above.');
	}
	
	// Validate email address.
	if ($email == '')
	{
		$error->add('signup', 'email', 'Email address cannot be empty.');
	}
	elseif ( ! filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		$error->add('signup', 'email', 'Email address not valid.');
	}
	elseif ($user_db->user_exists('email', $email))
	{
		$error->add('signup', 'email', 'Email address already used by another user.');
	}
	
	if (!isset($_POST['terms_accepted']))
	{
		$error->add('signup', 'terms_acceptance', 'You must accept the terms and conditions.');
	}
	
	return !$error->exists_group('signup');
}
