<?php
require('../config/config_inc.php');
require($config['includes_path'] . '/constants.php');

function validate_password($salt, $password)
{
	global $sess_info, $user_db, $config;
	
	require($config['includes_path'] . '/password.php');
	$password = hash_password($salt, $password);
	return $user_db->get_user_id_by_login(USER_EMAIL, $password) === $sess_info['user_id'];
}

$redirect_url = '';

if ( ! USER_IS_LOGGED_IN)
{
    $redirect_url = $config['http_host'];
}
else
{
    if (isset($_GET['a']))
    {
        if ($_GET['a'] === 'ch_email')
        {
			if (isset($_POST['current_email']) && isset($_POST['new_email']) && isset($_POST['pass']))
			{
				$current_email = trim($_POST['current_email']);
				$new_email = trim($_POST['new_email']);
				
				// Validate user's current email.
				if ($current_email === '')
				{
					$error->add('ch_email', 'current_email', 'Cannot be empty');
				}
				elseif ($current_email !== USER_EMAIL)
				{
					$error->add('ch_email', 'current_email', 'Email address incorrect');
				}
				
				// Validate user's new email.
				if ($new_email === '')
				{
					$error->add('ch_email', 'new_email', 'Cannot be empty');
				}
				elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL))
				{
					$error->add('ch_email', 'new_email', 'Email address is not valid.');
				}
				elseif ($new_email === USER_EMAIL)
				{
					$error->add('ch_email', 'new_email', 'Cannot be the same as your current email.');
				}
				elseif ($user_db->user_exists('email', $new_email))
				{
					$error->add('ch_email', 'new_email', 'Email address already used by another user.');
				}
				
				// Validate password.
				if ($_POST['pass'] !== '')
				{
					$salt = $user_db->get_salt(USER_EMAIL);
					
					if (!validate_password($salt, $_POST['pass'])) {
						$error->add('ch_email', 'pass', 'Password incorrect');
					}
				}
				else
				{
					$error->add('ch_email', 'pass', 'Cannot be empty');
				}
				
				if ($error->exists_group('ch_email'))
				{
					$error->add_field_value('ch_email', 'current_email', $current_email);
					$error->add_field_value('ch_email', 'new_email', $new_email);
					
					$error_key = $error->save();
					$redirect_url = $config['http_host'] . '/settings.php?a=ch_email&e_k=' . $error_key;
				}
				else
				{
					$user_db->change_email($sess_info['user_id'], $new_email);
					
					$redirect_url = $config['http_host'] . '/settings.php?a=ch_email';
				}
			}
			else
			{
				$redirect_url = $config['http_host'] . '/settings.php?a=ch_email';
			}
        }
		elseif ($_GET['a'] === 'ch_password')
		{
			$salt = $user_db->get_salt(USER_EMAIL);
		
			$current_password = $_POST['pass'];
			$new_password     = $_POST['new_pass'];
			$re_new_password  = $_POST['re_new_pass'];
			
			// Validate user's current password
			if ($current_password === '')
			{
				$error->add('ch_password', 'current_password', 'Cannot be empty');
			}
			elseif (!validate_password($salt, $current_password))
			{
				$error->add('ch_password', 'current_password', 'Incorrect password');
			}
			
			// Validate user's new password
			if ($new_password === '')
			{
				$error->add('ch_password', 'new_password', 'Cannot be empty');
			}
			elseif (strlen($new_password) < 8)
			{
				$error->add('ch_password', 'new_password', 'Must be at least 8 characters');
			}
			elseif (strtolower(trim($new_password)) === USER_NICK)
			{
				$error->add('ch_password', 'new_password', 'Cannot be the same as your username');
			}
			
			// Validate re-entered new password
			if ($re_new_password === '')
			{
				$error->add('ch_password', 're_new_password', 'Cannot be empty');
			}
			elseif ($re_new_password !== $new_password)
			{
				$error->add('ch_password', 're_new_password', 'Does not match password provided above.');
			}
			
			if ($error->exists_group('ch_password'))
			{
				$error_key = $error->save();
				$redirect_url = $config['http_host'] . '/settings.php?a=ch_password&e_k=' . $error_key;
			}
			else
			{
				$new_password = hash('sha1', $salt.$new_password);
				$user_db->change_password($sess_info['user_id'], $new_password);
				
				// User have to log in again with their new password after their
				// password has been changed.
				$sess->end_current_session();
				
				$redirect_url = $config['http_host'];
			}
		}
        elseif ($_GET['a'] === 'thr_pagination')
        {
			require($config['database_path'] . '/users_settings_database.php');
			$user_setting_db = new Users_settings_database($sess_info['user_id']);
			
			if (isset($_POST['threads'])
				&& preg_match('~^[0-9]+$~', $_POST['threads'])
				&& $_POST['threads'] >= 1 && $_POST['threads'] <= 14)
			{
				$user_setting_db->set_num_threads_per_page($_POST['threads']);
			}
			
			if (isset($_POST['replies'])
				&& preg_match('~^[0-9]+$~', $_POST['replies'])
				&& $_POST['replies'] >= 1 && $_POST['replies'] <= 14)
			{
				$user_setting_db->set_num_replies_per_page($_POST['replies']);
			}
			
			$redirect_url = $config['http_host'] . '/settings.php?a=thr_pagination';
        }
        else
		{
			$redirect_url = $config['http_host'] . '/settings.php?a=' . urlencode($_GET['a']);
		}
    }
    else
    {
        
    }
}

header('Location: ' . $redirect_url);
