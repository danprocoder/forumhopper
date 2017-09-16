<?php
function user_profile_url($username)
{
	$url = $GLOBALS['config']['http_host'] . '/profile.php';
	if ((USER_IS_LOGGED_IN && $username != USER_NICK) || ! USER_IS_LOGGED_IN)
	{
		$url .= '?u=' . $username;
	}
	return $url;
}
