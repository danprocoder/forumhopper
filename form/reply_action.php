<?php
require('../config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if (!USER_IS_LOGGED_IN)
{
	header('Location: ' . $config['http_host']);
	die();
}

if (isset($_GET['id']))
{
	require($config['database_path'] . '/threads_replies_database.php');
	$reply_db = new Threads_replies_database();

	$reply = $reply_db->get_reply_by_id($_GET['id']);
	
	// If reply does not exists.
	if ($reply === NULL)
	{
		header('Location: ' . $config['http_host']);
		die();
	}
	
	if (isset($_GET['a']) && in_array(strtolower($_GET['a']), array('edit', 'delete')))
	{	
		switch (strtolower($_GET['a']))
		{
		case 'edit':
			if (!isset($_POST['reply']))
			{
				header('Location: ' . $config['http_host'] . '/view.php?id=' . $reply['thread_id']);
				die();
			}
			
			// If the logged in user is not the user that posted the reply. To avoid editing 
			// someone else's reply.
			if ($reply['user_id'] !== $sess_info['user_id'])
			{
				header('Location: ' . $config['http_host'] . '/view.php?id=' . $reply['thread_id']);
				die();
			}
			
			if (trim($_POST['reply']) === '')
			{
				$error->add('edit_reply', 'reply', 'Reply cannot be empty');
				$error_key = $error->save();
				header('Location: ' . $config['http_host'] . '/edit_reply.php?id=' . $_GET['id'] . '&e_k=' . $error_key);
			}
			else
			{
				$reply_db->edit_reply($_GET['id'], trim($_POST['reply']));
				header('Location: ' . $config['http_host'] . '/view.php?id=' . $reply['thread_id']);
			}
			
			break;
		case 'delete':
			$base_cat_id = $cat->get_base_cat($reply['cat_id']);
			$is_moderator = $user_db->is_moderator($sess_info['user_id'], $base_cat_id);
			if (USER_IS_ADMIN || $is_moderator)
			{
				$reply_db->delete_reply($_GET['id']);
				// Delete attachments
				require($config['__'] . '/file.php');
				$attachment_path = $config['attachment_path'].'/thread'.$reply['thread_id'].'/reply'.$_GET['id'];
				delete_folder($attachment_path);
			}
			header('Location: ' . $config['http_host'] . '/view.php?id=' . $reply['thread_id']);
			
			break;
		}
	}
	else
	{
		header('Location: ' . $config['http_host'] . '/view.php?id=' . $reply['thread_id']);
	}
}
else
{
	header('Location: ' . $config['http_host']);
}
