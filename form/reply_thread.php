<?php
require('../config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if (!USER_IS_LOGGED_IN)
{
	header('Location: ' . $config['http_host']);
	die();
}

if (isset($_GET['id']) && isset($_POST['reply']))
{
	$redirect_url = $config['http_host'] . '/view.php?id=' . $_GET['id'];

	require($config['database_path'] . '/threads_database.php');
	// If thread exist
	if ((new Threads_database())->get_thread_meta($_GET['id']) !== NULL)
	{
		if (trim($_POST['reply']) === '')
		{
			$error->add('reply_thread', 'reply', 'Reply cannot be empty');
		}
		
		require($config['__'] . '/attachments.php');
		if (!validate_attachments())
		{
			$error->add('reply_thread', 'attachments', $attachment_error);
		}
		
		if (!$error->exists_group('reply_thread'))
		{
			require($config['database_path'] . '/threads_replies_database.php');
			
			$replies_db = new Threads_replies_database();
			$reply_id = $replies_db->add_reply($_GET['id'], $sess_info['user_id'], trim($_POST['reply']), false);
			
			save_attachments('thread'.$_GET['id'].'/reply'.$reply_id);
			
			$redirect_url .= '&page=l#r' . $reply_id;
		}
		else
		{
			$error->add_field_value('reply_thread', 'reply', $_POST['reply']);
			$error_key = $error->save();
			$redirect_url .= '&e_k=' . $error_key;
		}
	}
	
	header('Location: ' . $redirect_url);
}
else
{
	header('Location: ' . $config['http_host']);
}
