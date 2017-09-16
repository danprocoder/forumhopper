<?php
if (version_compare(PHP_VERSION, '5.3.0', '<'))
{
	die('PHP 5.3.0 or above is required');
}

define('CONFIG_DIR', dirname(dirname(dirname(__FILE__))).'/config');
if (file_exists(CONFIG_DIR.'/config.php'))
{
	require(CONFIG_DIR.'/config.php');
	
	header('Location: '.$config['http_host']);
	die();
}
else
{
	if (file_exists(CONFIG_DIR.'/config_template.php'))
	{
		require(CONFIG_DIR.'/config_template.php');
	}
	else
	{
		die('Error: config_template.php not found.');
	}
}

