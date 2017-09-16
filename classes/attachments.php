<?php
$attachment_error = '';

require_once($config['__'] . '/file.php');

function validate_attachments()
{
	global $config, $error, $attachment_error;
	
	for ($i = 0; $i < count($_FILES['attachment']['name']); $i++) {
		if ($i > $config['attachment_max'] - 1) {
			break;
		}
	
		$filename = $_FILES['attachment']['name'][$i];
		$error_code = $_FILES['attachment']['error'][$i];
		if ($error_code !== 0)
		{
			if ($error_code == 1 || $error_code == 2)
			{
				$attachment_error = $filename . ' exceeds the max size limit which is ' . convert_bytes($config['attachment_max_size']);
				return false;
			}
			elseif ($error_code === 4)
			{
				continue;
			}
		}
		else
		{
			if (!in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $config['attachment_supported_extensions']))
			{
				$attachment_error = 'Only .'.implode(', .', $config['attachment_supported_extensions']).' file(s) allowed';
				return false;
			}
			elseif ($_FILES['attachment']['size'][$i] > $config['attachment_max_size'])
			{
				$attachment_error = $filename . ' exceeds the max size limit which is ' . convert_bytes($config['attachment_max_size']);
				return false;
			}
		}
	}
	
	return true;
}

function save_attachments($relpath)
{
	global $config;
	
	if (count($_FILES['attachment']['name']) === 1 && $_FILES['attachment']['error'][0] === 4)
	{
		return;
	}
	
	if ( ! file_exists($config['attachment_path']))
	{
		create_path($config['attachment_path']);
	}
	
	$tmp = '';
	foreach (explode('/', $relpath) as $p)
	{
		$tmp .= '/' . $p;
		if ( ! file_exists($config['attachment_path'] . $tmp))
		{
			mkdir($config['attachment_path'] . $tmp);
		}
	}
	
	for ($i = 0; $i < count($_FILES['attachment']['name']); $i++)
	{
		if ($i > $config['attachment_max'] - 1)
		{
			break;
		}
		
		$filename = $_FILES['attachment']['name'][$i];
		
		move_uploaded_file($_FILES['attachment']['tmp_name'][$i], $config['attachment_path'] . '/' . $relpath . '/' . $filename);
	}
}

function generate_random_filename()
{
	$valid_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
	$name = '';
	for ($i = 0; $i < 30; $i++)
	{
		$name .= $valid_chars[rand(0, strlen($valid_chars) - 1)];
	}
	return $name;
}
