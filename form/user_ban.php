<?php
require('../config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if (!USER_IS_LOGGED_IN
	|| (!USER_IS_ADMIN && !$user_db->is_moderator($sess_info['user_id'])))
{
	redirect();
}

if (isset($_GET['u']))
{
	$meta = $user_db->get_user_meta($_GET['u']);
	if ($meta !== null)
	{
		// The admin cannot be banned.
		// User cannot ban himself.
		if ( ! $user_db->is_admin($meta['user_id']) && $meta['user_id'] != $sess_info['user_id'])
		{
			$ban_status = ! $meta['is_banned'];
			$user_db->set_ban($meta['user_id'], $ban_status);
		}
	}
	
	redirect();
}
else
{
	redirect();
}

function redirect()
{
	global $config;
	
	if (isset($_GET['ref']))
	{
		header("Location: $config[http_host]/$_GET[ref]");
	}
	elseif (isset($_GET['u']))
	{
		header("Location: $config[http_host]/profile.php?u=".$_GET['u']);
	}
	else
	{
		header("Location: $config[http_host]");
	}
	die();
}
