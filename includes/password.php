<?php

function generate_user_salt()
{
	$salt = '';
	
	$charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*-_=+:;?~`.';
	for ($i = 0; $i < 50; $i++)
	{
		$salt .= $charset[rand(0, strlen($charset) - 1)];
	}
	return $salt;
}

function hash_password($salt, $password)
{
    return hash('sha256', $salt.$password);
}
