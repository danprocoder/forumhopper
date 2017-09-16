<?php
require('../config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if (USER_IS_LOGGED_IN)
{
	if (isset($_GET['id']))
	{
		require($config['database_path'] . '/threads_database.php');
		
		$threads_db = new Threads_database();
		$thread = $threads_db->get_thread_meta($_GET['id']);
		if ($thread === NULL)
		{
			header('Location: ' . $config['http_host']);
		}
		else
		{
			$base_cat_id = $cat->get_base_cat($thread['cat_id']);
			$is_moderator = $user_db->is_moderator($sess_info['user_id'], $base_cat_id);
			if (USER_IS_ADMIN || $is_moderator)
			{
				$threads_db->delete_thread($_GET['id']);
				
				// Delete attachments
				require($config['__'] . '/file.php');
				$attachment_path = $config['attachment_path'] . '/thread'.$_GET['id'];
				delete_folder($attachment_path);
			}
			
			$redirect_url = $config['http_host'];
			if (isset($_GET['ref']))
			{
				$redirect_url .= '/' . $_GET['ref'];
			}
			else
			{
				$redirect_url .= '/threads.php?cat_id=' . $thread['cat_id'];
			}
			header("Location: $redirect_url");
		}
	}
	else
	{
		header('Location: ' . $config['http_host']);
	}
}
else
{
	header('Location: ' . $config['http_host']);
}

