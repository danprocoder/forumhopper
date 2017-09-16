<?php
require('../config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if (USER_IS_LOGGED_IN)
{
	header('Location: ' . $config['http_host']);
	die();
}

if (isset($_POST['login']) && isset($_POST['pass']))
{
	$login = trim($_POST['login']);
	$pass = trim($_POST['pass']);
	if (($salt = $user_db->get_salt($login)) !== NULL)
	{
		require($config['includes_path'] . '/password.php');
	
		$pass = hash_password($salt, $pass);
		$user_id = $user_db->get_user_id_by_login($login, $pass);
		if ($user_id == NULL)
		{
			$error->add('login', 'login', 'Incorrect password');
			$error->add_field_value('login', 'login', $login);
			$error_key = $error->save();
			header('Location: ' . $config['http_host'] . '?e_k=' . $error_key);
		}
		else
		{
			$sess->start_new_sess($user_id, isset($_POST['remember']));
			
			$redirect_url = $config['http_host'];
			if (isset($_POST['ref']))
			{
				$redirect_url .= '/'.urldecode(trim($_POST['ref']));
			}
			header('Location: ' . $redirect_url);
		}
	}
	else
	{
		$error->add('login', 'login', 'Incorrect username/e-mail');
		$error->add_field_value('login', 'login', $login);
		$error_key = $error->save();
		header('Location: ' . $config['http_host'] . '?e_k=' . $error_key);
	}
}
else
{
	header('Location: ' . $config['http_host'] . '/index.php');
}
