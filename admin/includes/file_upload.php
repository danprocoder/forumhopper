<?php
function validate_uploaded_file($file, $rules, &$error_msg, $name)
{
	require($GLOBALS['config']['__'] . '/file.php');

	if (is_string($rules['extensions']))
	{
		$rules['extensions'] = array($rules['extensions']);
	}
	
	// If a file was uploaded
	if ($file['error'] == 4)
	{
		$error_msg = 'Please select a '.$name;
		return False;
	}
	
	// If max file upload is exceeded.
	if ($file['error'] == 1 || $file['error'] == 2)
	{
		$error_msg = ucfirst($name).' exceeds the max filesize which is '.convert_bytes($rules['max_upload_size']);
		return False;
	}
	
	// If no error
	if ($file['error'] == 0)
	{
		if ($file['size'] > $rules['max_upload_size'])
		{
			$error_msg = ucfirst($name).' exceeds the max filesize which is '.convert_bytes($rules['max_upload_size']);
			return False;
		}
		elseif ( ! in_array('.'.pathinfo($file['name'], PATHINFO_EXTENSION), $rules['extensions']))
		{
			$error_msg  = 'Only ' . implode(', ', $rules['extensions']) . ' files allowed';
			return False;
		}
	}
	
	return True;
}

function random_logo_filename()
{
	$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-';
	$filename = '';
	for ($i = 0; $i < 20; $i++)
	{
		$filename .= $charset[ rand(0, strlen($charset) - 1) ];
	}
	return $filename;
}
