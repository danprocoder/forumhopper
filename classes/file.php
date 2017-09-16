<?php
function delete_folder($folder)
{
	if (file_exists($folder))
	{
		$files = array_slice(scandir($folder), 2);
		foreach ($files as $f)
		{
			$filepath = $folder.'/'.$f;
			if (is_dir($filepath))
			{
				delete_folder($filepath);
			}
			else
			{
				unlink($filepath);
			}
		}
		rmdir($folder);
	}
}

function get_mime($extension)
{
	switch ($extension)
	{
		case 'jpg':
		case 'jpeg':
			return 'image/jpeg';
		case 'png':
			return 'image/png';
		case 'gif':
			return 'image/gif';
	}
}

function convert_bytes($bytes)
{
	if ($bytes >= 1024*1024*1024) {
		return round($bytes / (1024*1024*1024), 2) . 'GB';
	} elseif ($bytes >= 1024 * 1024) {
		return round($bytes / (1024*1024), 2) . 'MB';
	} elseif ($bytes >= 1024) {
		return round($bytes / 1024, 2) . 'KB';
	}
	
	return $bytes.'B';
}

function create_path($path)
{
	$basepath = dirname($path);
	
	if (!file_exists($basepath))
	{
		create_path($basepath);
	}
	
	mkdir($path);
}
