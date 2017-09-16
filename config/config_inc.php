<?php
if (version_compare(PHP_VERSION, '5.3.0', '<'))
{
	die('PHP 5.3.0 or above is required.');
}

define('CONFIG_DIR', dirname(__FILE__));

if (file_exists(CONFIG_DIR.'/config.php'))
{
	require(CONFIG_DIR.'/config.php');
}
else
{
	if (CONFIG_DIR.'/config_template.php')
	{
		require(CONFIG_DIR.'/config_template.php');
		
		header('Location: '.ADMIN_URL.'/setup.php');
		die();
	}
	else
	{
		die('config_template.php is missing.');
	}
}
