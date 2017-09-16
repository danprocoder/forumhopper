<?php
function load_logo($logo_path, $filetype)
{
	$image = null;
	// create image
	switch ($filetype)
	{
	case 'image/jpeg':
		$image = imagecreatefromjpeg($logo_path);
		
		break;
	case 'image/png':
		$image = imagecreatefrompng($logo_path);
		
		break;
	case 'image/gif':
		$image = imagecreatefromgif($logo_path);
		
		break;
	}
	return $image;
}

function resize_logo($image, $filetype, $savepath)
{
	// Resize logo
	$w = imagesx($image);
	$h = imagesy($image);
	
	list($logo_w, $logo_h) = calc_logo_size($w, $h);
	
	$resized = imagecreatetruecolor($logo_w, $logo_h);
	if ($filetype == 'image/png')
	{
		imagealphablending($resized, false);
		imagesavealpha($resized, true);
		$color = imagecolorallocatealpha($resized, 0, 0, 0, 127);
		imagefill($resized, 0, 0, $color);
	}
	
	imagecopyresampled($resized, $image, 0, 0, 0, 0, $logo_w, $logo_h, $w, $h);
	
	// Save
	switch ($filetype)
	{
	case 'image/jpeg':
		imagejpeg($resized, $savepath, 100);
		break;
	case 'image/png':
		imagepng($resized, $savepath, 9);
		break;
	case 'image/gif':
		imagegif($resized, $savepath, 100);
		break;
	}
}

function calc_logo_size($w, $h)
{
	global $config;
	
	$aspect_ratio = $w/$h;
	
	$logo_w = 0;
	$logo_h = 0;
	
	if ($w > $config['site_logo_max_width'])
	{
		list($logo_w, $logo_h) = calc_size_by_width($config['site_logo_max_width'], $aspect_ratio);
	}
	else
	{
		$logo_w = $w; $logo_h = $h;
	}
	
	if ($logo_h > $config['site_logo_max_height'])
	{
		list($logo_w, $logo_h) = calc_size_by_height($config['site_logo_max_height'], $aspect_ratio);
	}
	
	return array($logo_w, $logo_h);
}

function calc_size_by_height($height, $aspect_ratio)
{
	return array($height * $aspect_ratio, $height);
}

function calc_size_by_width($width, $aspect_ratio)
{
	return array($width, $width / $aspect_ratio);
}
