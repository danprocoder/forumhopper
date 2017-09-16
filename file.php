<?php
require('./config/config_inc.php');
require($config['__'] . '/file.php');

$image = null;

if (isset($_GET['p']))
{
	$filepath = $config['attachment_path'] . '/' . $_GET['p'];
	if (file_exists($filepath))
	{
		$image = array(
			'type' => get_mime(pathinfo($filepath, PATHINFO_EXTENSION)),
			'name' => pathinfo($filepath, PATHINFO_BASENAME),
			'bin' => file_get_contents($filepath)
		);
	}
}
elseif (isset($_GET['u']))
{
	$filepath = '';

	require($config['database_path'] . '/user_database.php');
	$user_db =  new User_database();
	$meta = $user_db->get_user_meta($_GET['u']);
	
	if ($meta['picture_filename'] != null)
	{
		$filepath = $config['user_picture_path'] . '/' . $meta['picture_filename'];
	}
	else
	{
		switch ($meta['sex'])
		{
		case 'male':
			$filepath = IMAGES_DIR . '/default_avatar.png';
			break;
		case 'female':
			$filepath = IMAGES_DIR . '/default_avatar.png';
			break;
		default:
			$filepath = IMAGES_DIR . '/default_avatar.png';
			break;
		}
	}
	
	$image = array(
		'name' => pathinfo($filepath, PATHINFO_BASENAME),
		'type' => get_mime(pathinfo($filepath, PATHINFO_EXTENSION))
	);
	if ($meta['is_banned'])
	{
		$image['bin'] = add_ban_sticker($filepath, $image['type']);
	}
	else
	{
		$image['bin'] = file_get_contents($filepath);
	}
}

if ($image != null)
{
	if (isset($_GET['download']))
	{
		header('Content-Disposition: attachment; filename='.$image['name']);
	}
	
	header('Content-Type: ' . $image['type']);
	
	echo $image['bin'];
}
else
{
	echo 'File does not exists.';
}

function add_ban_sticker($path, $type)
{
	$image = null;
	switch($type)
	{
	case 'image/jpeg':
		$image = imagecreatefromjpeg($path);
		break;
	case 'image/png':
		$image = imagecreatefrompng($path);
		imagealphablending($image, false);
		imagesavealpha($image, true);
		break;
	case 'image/gif':
		$image = imagecreatefromgif($path);
		break;
	}
	
	$w = imagesx($image);
	$h = imagesy($image);
	
	$font = $GLOBALS['config']['includes_path'] . '/fonts/arial.ttf';
	$text = 'BANNED!';
	$bbox = imagettfbbox(40, 0, $font, $text);
	
	$text_width = abs($bbox[4]) - abs($bbox[0]);
	$text_height = abs($bbox[5]) - abs($bbox[1]);
	
	// Draw blue rectangle
	$rect_w = $text_width + 40;
	$rect_h = $text_height + 40;
	$rect = imagecreatetruecolor($rect_w, $rect_h);
	
	$red = imagecolorallocate($rect, 255, 0, 0);
	imagefill($rect, 0, 0, $red);
	
	// Draw text
	$white = imagecolorallocate($image, 255, 255, 255);
	imagettftext($rect, 40, 0, 20, $rect_h - 20, $white, $font, $text);
	
	// Resize sticker
	$new_sticker_w = 0.8 * $w;
	$new_sticker_h = $new_sticker_w / ($rect_w / $rect_h);
	$resized = imagecreatetruecolor($new_sticker_w, $new_sticker_h);
	imagecopyresampled($resized, $rect, 0, 0, 0, 0, $new_sticker_w, $new_sticker_h, $rect_w, $rect_h);
	
	// Rotate sticker
	$rect = imagerotate($resized, 15, imagecolorallocatealpha($rect, 0, 0, 0, 127));
	imagealphablending($rect, false);
	imagesavealpha($rect, true);
	
	// Add sticker.
	imagecopy($image, $rect, ($w - imagesx($rect)) / 2, ($h - imagesy($rect)) / 2,
			  0, 0, imagesx($rect), imagesy($rect));
	
	ob_start();
	switch($type)
	{
	case 'image/jpeg':
		imagejpeg($image);
		break;
	case 'image/png':
		imagepng($image);
		break;
	case 'image/gif':
		imagegif($image);
		break;
	}
	$data = ob_get_contents();
	ob_end_clean();
	imagedestroy($image);
	
	return $data;
}
