<?php
require('../config/config_inc.php');
require($config['includes_path'].'/constants.php');

if (!USER_IS_LOGGED_IN)
{
	header('Location: ' . $config['http_host']);
	die();
}

if (isset($_GET['cat_id']) && isset($_POST['topic']) && isset($_POST['post']))
{
	// If category does not exist.
	if ($cat->get_cat_by_id($_GET['cat_id']) === NULL)
	{
		header('Location: ' . $config['http_host']);
		die();
	}
	
	if (strlen(trim($_POST['topic'])) < $config['min_thread_topic_length'])
	{
		$error->add('thread', 'topic', 'Topic should be at least ' . $config['min_thread_topic_length'] . ' characters long');
	}
	elseif (strlen(trim($_POST['topic'])) > 150)
	{
		$error->add('thread', 'topic', 'Topic should not exceed 150 characters.');
	}
	
	if (trim($_POST['post']) === '')
	{
		$error->add('thread', 'body', 'Body cannot be empty');
	}
	
	require($config['__'] . '/attachments.php');
	if (!validate_attachments())
	{
		$error->add('thread', 'attachment', $attachment_error);
	}
	
	if ($error->exists_group('thread'))
	{
		$error->add_field_value('thread', 'topic', trim($_POST['topic']));
		$error->add_field_value('thread', 'body', trim($_POST['post']));
		$error_key = $error->save();
		header('Location: ' . $config['http_host'] . '/create_thread.php?cat_id=' . $_GET['cat_id'] . '&e_k=' . $error_key);
	}
	else
	{	
		require($config['database_path'] . '/threads_database.php');
		require($config['database_path'] . '/threads_replies_database.php');
		
		$threads_db = new Threads_Database();
		$thread_id = $threads_db->add_new_thread($sess_info['user_id'], $_GET['cat_id'], trim($_POST['topic']));
		
		$threads_replies_db = new Threads_replies_database();
		$reply_id = $threads_replies_db->add_reply($thread_id, $sess_info['user_id'], trim($_POST['post']), true);
		
		save_attachments('thread'.$thread_id.'/reply'.$reply_id);
		
		header('Location: ' . $config['http_host'] . '/view.php?id=' . $thread_id);
	}
}
else
{
	header('Location: ' . $config['http_host']);
}
