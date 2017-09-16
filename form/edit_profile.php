<?php
require('../config/config_inc.php');
require($config['includes_path'] . '/constants.php');

function redirect_to($page)
{
	header('Location: ' . $GLOBALS['config']['http_host'] . '/' . $page);
	die();
}

function generate_filename() {
	$charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
	$filename = $charset[ rand(0, 51) ]; // Filename starts with a letter
	for ($i = 0; $i < 14; $i++)
	{
		$filename .= $charset[ rand(0, strlen($charset) - 1) ];
	}
	return $filename;
}

function crop_image($ext, $path) {
	$image = null;
	
	switch (strtolower($ext))
	{
	case 'jpeg':
	case 'jpg':
		$image = imagecreatefromjpeg($path);
		break;
	case 'png';
		$image = imagecreatefrompng($path);
		break;
	case 'gif';
		$image = imagecreatefromgif($path);
		break;
	}
	
	$s = 0;
	
	$w = imagesx($image);
	$h = imagesy($image);
	if ($w > $h) {
		$s = $h;
	} else {
		$s = $w;
	}
	
	$cropped = imagecreatetruecolor($s, $s);
	if ($w > $h)
	{
		$src_x = ($w / 2) - ($s / 2);
		imagecopyresampled($cropped, $image, 0, 0, $src_x, 0, $s, $s, $s, $s);
	}
	else
	{
		$src_y = ($h / 2) - ($s / 2);
		imagecopyresampled($cropped, $image, 0, 0, 0, $src_y, $s, $s, $s, $s);
	}
	
	$filename = generate_filename() . '.jpg';
	imagejpeg($cropped, $GLOBALS['config']['user_picture_path'] . '/' . $filename, 100);
	return $filename;
}

if (USER_IS_LOGGED_IN)
{
	$edit = array();
	
	foreach ($_POST as $k => $v)
	{
		$_POST[$k] = trim($v);
	}
	
	// Validate sex
	if (isset($_POST['sex']))
	{
		if (in_array(strtolower($_POST['sex']), array('female', 'male')))
		{
			$edit['sex'] = $_POST['sex'];
		}
	}
	
	// Validate birthdate
	if (isset($_POST['birthday']) && isset($_POST['birthmonth']) && isset($_POST['birthyear']))
	{
		$day = 0;
		$month = 0;
		$year = 0;
		
		if (preg_match('~^\d+$~', $_POST['birthday']) && $_POST['birthday'] > 0 && $_POST['birthday'] <= 31)
		{
			$day = $_POST['birthday'];
		}
		
		if (preg_match('~^\d+$~', $_POST['birthmonth']) && $_POST['birthmonth'] > 0 && $_POST['birthmonth'] <= 12)
		{
			$month = $_POST['birthmonth'];
		}
		
		if (preg_match('~^\d{4}$~', $_POST['birthyear']) && $_POST['birthyear'] >= 1940 && $_POST['birthyear'] <= date('Y'))
		{
			$year = $_POST['birthyear'];
		}
		
		if ($day !== 0 && $month !== 0 && $year !== 0)
		{
			// Check if day has exceeded numbers of days for the month
			$months_max_days = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
			if ($day <= $months_max_days[$month - 1])
			{
				$edit['birthdate'] = "$year:$month:$day";
			}
			else
			{
				$months = array('January', 'February', 'March',
								'April', 'May', 'June',
								'July', 'August', 'September',
								'October', 'November', 'December');
				$error->add('edit_profile', 'birthdate', 'Day is more than the number of days in ' . $months[ $month - 1 ]);
			}
		}
	}
	
	// Validate fullname
	if (isset($_POST['fullname']) && $_POST['fullname'] !== '')
	{
		if (preg_match('/^[a-zA-Z]+(\s+[a-zA-Z]+)*$/', $_POST['fullname']))
		{
			$edit['fullname'] = preg_replace('/\s+/', ' ', $_POST['fullname']);
		}
		else
		{
			$error->add('edit_profile', 'fullname', 'Invalid fullname!');
		}
	}
	
	// Validate location
	if (isset($_POST['location']) && $_POST['location'] !== '')
	{
		$edit['location'] = $_POST['location'];
	}
	
	// Validate profile picture
	if (isset($_FILES['profile_pic']))
	{
		require($config['__'] . '/file.php');
		
		if ($_FILES['profile_pic']['error'] !== 4)
		{
			if ($_FILES['profile_pic']['error'] === 0)
			{
				$ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
				
				if ( ! in_array(strtolower($ext), $config['user_photo_extensions']))
				{
					$error->add('edit_profile', 'profile_pic', 'Only jpeg, png and gif images allowed.');
				}
				elseif ($_FILES['profile_pic']['size'] > $config['user_photo_max_size'])
				{
					$error->add('edit_profile', 'profile_pic', 'Photo should not exceed ' . convert_bytes($config['user_photo_max_size']));
				}
				else
				{
					if ( ! file_exists($config['user_picture_path']))
					{
						create_path($config['user_picture_path']);
					}
					
					// Delete user's current profile picture
					$current_pic = $user_db->get_user_meta(USER_NICK)['picture_filename'];
					if ($current_pic !== null && file_exists($config['user_picture_path'].'/'.$current_pic)) {
						unlink($config['user_picture_path'] . '/' . $current_pic);
					}
					
					// Crop and save newly uploaded photo
					$filename = crop_image($ext, $_FILES['profile_pic']['tmp_name']);
					
					$edit['picture_filename'] = $filename;
				}
			} elseif ($_FILES['profile_pic']['error'] === 1) {
				$error->add('edit_profile', 'profile_pic', 'Photo should not exceed ' . convert_bytes($config['user_photo_max_size']));
			}
		}
	}
	
	if ($error->exists_group('edit_profile')) {
		$fields = array('fullname', 'location');
		foreach ($fields as $f) {
			if (isset($_POST[$f])) {
				$error->add_field_value('edit_profile', $f, $_POST[$f]);
			}
		}
		
		$error_key = $error->save();
		redirect_to('edit_profile.php?e_k='.$error_key);
	} else {
		$user_db->update_meta($sess_info['user_id'], $edit);
		redirect_to('profile.php');
	}
}
else
{
	redirect_to('profile.php');
}
