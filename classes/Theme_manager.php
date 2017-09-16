<?php
/**
 * @since 1.0
 */
class Theme_manager {
	
    static function load_theme($theme_name)
    {
		$theme = null;
		if (in_array($theme_name, array_keys(self::get_themes())))
		{
			require($GLOBALS['config']['__'].'/Theme.php');
			require($GLOBALS['config']['themes_path']."/$theme_name/$theme_name.php");
			
			$theme = new $theme_name;
		}
		return $theme;
    }
	
	static function get_themes()
	{
		$themes = array();
		foreach (array_slice(scandir($GLOBALS['config']['themes_path']), 2) as $th)
		{
			$themes[$th] = json_decode(file_get_contents($GLOBALS['config']['themes_path'] . "/$th/meta.json"));
			if (isset($themes[$th]->screenshot_path))
			{
				$themes[ $th ]->screenshot_url = $GLOBALS['config']['themes_url']."/$th/".$themes[ $th ]->screenshot_path;
				$themes[ $th ]->screenshot_path = $GLOBALS['config']['themes_path'] . "/$th/" . $themes[ $th ]->screenshot_path;
			}
		}
		return $themes;
	}
    
	/**
	 * Installs a new theme.
	 *
	 * @param string Path to zip file.
	 */
    static function install_theme($theme_path, $theme_zip_filename, &$error)
	{
		$theme_name = strtolower(explode('.', $theme_zip_filename)[0]);
		
		$theme_zip = zip_open($theme_path);
		if ($theme_zip)
		{
			// Fetch all entries in zip file.
			$entries = array();
			while ($theme_zip_entry = zip_read($theme_zip))
			{
				$entry_name = strtolower(zip_entry_name($theme_zip_entry));
				$entries['zip_entry_res'][] = $theme_zip_entry;
				$entries['files'][] = $entry_name;
			}
			
			// Look for required files.
			$required = array(
				// CSS files.
				"$theme_name/css/",
				"$theme_name/css/button.css",
				"$theme_name/css/currently_viewing.css",
				"$theme_name/css/main.css",
				"$theme_name/css/nav_menu.css",
				"$theme_name/css/nav_search.css",
				"$theme_name/css/side_nav.css",
				"$theme_name/css/side_sections_right.css",
				"$theme_name/css/side_topics.css",
				"$theme_name/css/tab.css",
				"$theme_name/css/thread_item.css",
				"$theme_name/css/thread_reply.css",
				"$theme_name/css/threads_toolbar.css",
				// Theme class file.
				"$theme_name/$theme_name.php",
				// JSON file.
				"$theme_name/meta.json",
			);
			foreach ($required as $rq)
			{
				if (!in_array($rq, $entries['files']))
				{
					$error =  "&apos;$theme_zip_filename/$rq&apos; not found";
					return false;
				}
			}
			
			$meta = null;
			// Check meta file.
			foreach ($entries['files'] as $i => $f)
			{
				if ($f == "$theme_name/meta.json")
				{
					$entry_res_id = $entries['zip_entry_res'][$i];
					zip_entry_open($theme_zip, $entry_res_id);
					
					$meta = @json_decode(zip_entry_read($entry_res_id, zip_entry_filesize($entry_res_id)));
					if ($meta !== null)
					{
						$meta = (array) $meta;
						// Check for required keys
						foreach (array('name', 'screenshot_path', 'version') as $k)
						{
							if (!in_array($k, array_keys($meta)))
							{
								$error = "&lsquo;$k&rsquo; not found in &lsquo;$theme_zip_filename/$f&rsquo;";
								return false;
							}
						}
						
						// Validate required keys.
						foreach ($meta as $k => $v)
						{
							$meta[$k] = trim($v);
						}
						
						// Validate $meta['name'].
						if (strlen($meta['name']) == 0)
						{
							$error = "&lsquo;name&rsquo; in &lsquo;meta.json&rsquo; cannot be empty";
							return false;
						}
						elseif (preg_match('~[^0-9a-zA-Z\-_ ]~', $meta['name']))
						{
							$error = "&lsquo;name&rsquo; in &lsquo;meta.json&rsquo; can only contain the following characters: 0-9, A-Z, a-z, -, _ and a white space";
							return false;
						}
						
						// Validate $meta['version']
						if (strlen($meta['version']) == 0)
						{
							$error = "&lsquo;version&rsquo; in &lsquo;meta.json&rsquo; cannot be empty";
							return false;
						}
						elseif (!preg_match('~^\d+\.\d+$~', $meta['version']))
						{
							$error = "&lsquo;version&rsquo; in &lsquo;meta.json&rsquo; must be in the format &lsquo;a.b&rsquo;. Example: 1.0, 1.1";
							return false;
						}
					}
					else
					{
						$error = "Invalid &lsquo;meta.json&rsquo; file";
						return false;
					}
					
					break;
				}
			}
			
			// Delete theme if theme is installed.
			$installed_themes = self::get_themes();
			if (in_array($theme_name, array_keys($installed_themes)))
			{
				self::delete_theme($theme_name);
			}
			
			// Install theme.
			require_once($GLOBALS['config']['__'] . '/file.php');
			for ($i = 0; $i < count($entries['zip_entry_res']); $i++)
			{
				$name = $entries['files'][$i];
				if ($name[strlen($name) - 1] == '/')
				{
					create_path($GLOBALS['config']['themes_path'] . '/' . rtrim($name, '/'));
				}
				else
				{
					$entry_resid = $entries['zip_entry_res'][$i];
					
					// ^^ Meta file has been read before.
					if ($name == "$theme_name/meta.json")
					{
						$content = json_encode($meta);
					}
					else
					{
						zip_entry_open($theme_zip, $entries['zip_entry_res'][$i]);
						$content = zip_entry_read($entry_resid, zip_entry_filesize($entry_resid));
					}
					
					$fp = fopen($GLOBALS['config']['themes_path'] . '/' . $name, 'w+');
					fwrite($fp, $content);
					fclose($fp);
					
					zip_entry_close($entry_resid);
				}
			}
			
			return true;
		}
	}
	
	static function delete_theme($theme_name)
	{
		require_once($GLOBALS['config']['__'] . '/file.php');
		delete_folder($GLOBALS['config']['themes_path'].'/'.$theme_name);
	}
}
