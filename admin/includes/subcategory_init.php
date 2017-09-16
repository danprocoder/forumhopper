<?php
require('../config/config_inc.php');
require($config['includes_path'] . '/constants.php');

function redirect_to_index()
{
	header('Location: ' . $GLOBALS['config']['http_host']);
	die();
}

if (!USER_IS_LOGGED_IN)
{
	redirect_to_index();
}

if (isset($_GET['cat_id']))
{
	$meta = $cat->get_cat_by_id($_GET['cat_id']);
	if ($meta != null)
	{
		define('CURRENT_CATEGORY_ID', $meta['id']);
		define('CURRENT_CATEGORY_NAME', $meta['name']);
		define('CURRENT_CATEGORY_DESCRIPTION', $meta['description']);
		
		$base_cid = $cat->get_base_cat(CURRENT_CATEGORY_ID);
		define('USER_IS_MODERATOR', $user_db->is_moderator($sess_info['user_id'], $base_cid));
		
		if (!USER_IS_ADMIN && !USER_IS_MODERATOR)
		{
			redirect_to_index();
		}
	}
	else
	{
		redirect_to_index();
	}
}
else
{
	redirect_to_index();
}
