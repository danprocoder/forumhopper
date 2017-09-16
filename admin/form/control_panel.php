<?php
define('CONFIG_PATH', '../../config/config_inc.php');

require(CONFIG_PATH);
require($config['includes_path'] . '/constants.php');

if ( ! USER_IS_LOGGED_IN || (USER_IS_LOGGED_IN && ! USER_IS_ADMIN))
{
	header("Location: $config[http_host]");
	die();
}

function post_clean($key)
{
	return trim($_POST[$key]);
}

function get_clean($key)
{
	return trim($_GET[$key]);
}

$cp_actions = array(
	'change_site_name',
	'change_site_logo',
	'categories',
	'install_theme',
	'set_theme',
	'delete_theme',
	'add_mod',
	'edit_mod',
	'remove_mod',
	'rm_user',
);

if (isset($_GET['a']) && in_array(strtolower($_GET['a']), $cp_actions)) {
	require(ADMIN_DIR . '/includes/config.php');
	
	switch (strtolower($_GET['a']))
	{
	case 'change_site_name':
		if (isset($_POST['sitename']))
		{
			$sitename = post_clean('sitename');
			if (strlen($sitename) == 0)
			{
				$error->add('control_panel', 'site_name', 'Cannot be empty');
			}
			elseif (strlen($sitename) > 59)
			{
				$error->add('control_panel', 'site_name', 'Sitename cannot be more than 59 characters.');
			}
			elseif (preg_match('~[^A-Za-z0-9_\-\(\):\\\' ]~', $sitename))
			{
				$error->add('control_panel', 'site_name', 'Sitename can only contain the following characters: <i>A-Z, a-z, 0-9, _, -, :, &apos;, (, ) or a space.</i>');
			}
			else
			{
				modify_config_setting(CONFIG_DIR.'/config.php', 'site_name', str_replace('\'', "'.APOSTROPHE.'", $sitename));
			}
		}
		else
		{
			$error->add('control_panel', 'site_name', 'Please enter your sitename');
		}
		break;
	case 'change_site_logo':
		if (isset($_FILES['logo']))
		{
			require(ADMIN_DIR . '/includes/file_upload.php');
			require(ADMIN_DIR . '/includes/site_logo.php');
			
			$rules = array(
				'extensions' => array('.jpg', '.jpeg', '.png', '.gif'),
				'max_upload_size' => $config['site_logo_max_filesize'],
			);
			
			if (validate_uploaded_file($_FILES['logo'], $rules, $error_str, 'logo'))
			{
				$filename = random_logo_filename().'.'.pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
				$logo_path = IMAGES_DIR.'/'.$filename;
				
				$logo = load_logo($_FILES['logo']['tmp_name'], $_FILES['logo']['type']);
				if ($logo != null)
				{
					resize_logo($logo, $_FILES['logo']['type'], $logo_path);
					
					// Delete previous logo
					$previous_logo_path = IMAGES_DIR.'/'.basename($config['site_logo_url']);
					if (file_exists($previous_logo_path))
					{
						unlink($previous_logo_path);
					}
					
					modify_config_setting(CONFIG_DIR.'/config.php', 'site_logo_url', 'IMAGES_URL.\'/'.$filename.'\'');
				}
			}
			else
			{
				$error->add('control_panel', 'site_logo', $error_str);
			}
		}
		else
		{
			$error->add('control_panel', 'site_logo', 'Please select a logo');
		}
		
		break;
	case 'categories':
		if (isset($_POST['cats']))
		{
			$cats = @json_decode($_POST['cats']);
			if ($cats != null)
			{
				if (empty($cats))
				{
					$error->add('control_panel', 'categories', 'Please add a category');
				}
				else
				{
					$cat_ids = array();
					
					foreach ($cats as $c)
					{
						if ( ! isset($c->name) || ! isset($c->description))
						{
							continue;
						}
						
						$c->name = trim($c->name);
						$c->description = trim($c->description);
						
						if ($c->name == '' || $c->description == '')
						{
							continue;
						}
						
						if (isset($c->id))
						{
							// Edit category.
							$cat->edit_category($c->id, $c->name, $c->description);
							
							$cat_ids[] = $c->id;
						}
						else
						{
							$cat_id = 0;
							
							// If category name exists, edit the category to avoid creating 
							// multiple category with the same name.
							$meta = $cat->get_cat_by_name($c->name);
							if ($meta != null)
							{
								// Edit category.
								$cat_id = $cat->edit_category($meta['id'], $c->name, $c->description);
							}
							else
							{
								// Add category.
								$cat_id = $cat->add_category(CATEGORY_NO_PARENT, $c->name, $c->description);
							}
							
							$cat_ids[] = $cat_id;
						}
					}
					
					if ( ! empty($cat_ids))
					{
						// Delete all cats who's id is not found in $cat_ids
						$cat->delete_all_except($cat_ids, CATEGORY_NO_PARENT);
					}
				}
			}
			else
			{
				$error->add('control_panel', 'categories', 'Please add a category.');
			}
		}
		else
		{
			$error->add('control_panel', 'categories', 'Please add a category.');
		}
		break;
	case 'install_theme':
		if (isset($_FILES['theme']))
		{
			$rules = array(
				'max_upload_size' => $config['site_theme_max_filesize'],
				'extensions' => '.zip',
			);
			if (validate_uploaded_file($_FILES['theme'], $rules, $error_msg, 'theme'))
			{
				if (!Theme_manager::install_theme($_FILES['theme']['tmp_name'], $_FILES['theme']['name'], $install_error))
				{
					$error->add('control_panel', 'site_theme', "Unable to install theme: $install_error");
				}
				else
				{
					if (isset($_POST['use']))
					{
						$theme_name = explode('.', $_FILES['theme']['name'])[0];
						modify_config_setting(CONFIG_DIR.'/config.php', 'site_theme', $theme_name);
					}
				}
			}
			else
			{
				$error->add('control_panel', 'site_theme', $error_msg);
			}
		}
		else
		{
			$error->add('control_panel', 'site_theme', 'Please select a theme.');
		}
	
		break;
	case 'set_theme':
		$theme_name = get_theme_name();
		if ($theme_name != null)
		{
			$themes = Theme_manager::get_themes();
			if (in_array($theme_name, array_keys($themes)))
			{
				modify_config_setting(CONFIG_DIR.'/config.php', 'site_theme', $theme_name);
			}
			else
			{
				$error->add('control_panel', 'site_theme', 'Unable to set theme: Theme not found');
			}
		}
		break;
	case 'delete_theme':
		$theme_name = get_theme_name();
		if ($theme_name != null)
		{
			$themes = Theme_manager::get_themes();
			if (in_array($theme_name, array_keys($themes)))
			{
				// Users can't delete their current theme.
				if ($theme_name !== $config['site_theme'])
				{
					Theme_manager::delete_theme($theme_name);
				}
				else
				{
					$error->add('control_panel', 'site_theme', 'Unable to delete theme: Theme currently in use');
				}
			}
			else
			{
				$error->add('control_panel', 'site_theme', 'Unable to delete theme: Theme not found.');
			}
		}
		else
		{
			$error->add('control_panel', 'site_theme', 'Unable to delete theme: Theme not found.');
		}
		
		break;
	case 'add_mod':
	case 'edit_mod':
		$forum_cats = array();
		foreach ($cat->get_categories(CATEGORY_NO_PARENT) as $c)
		{
			array_push($forum_cats, $c['cat_id']);
		}
		
		$username = null;
		$mod_cats = array();
		foreach (array_keys($_POST) as $k)
		{
			$val = post_clean($k);
			if ($k == 'user')
			{
				$username = $val;
			}
			elseif (preg_match('~^cat\d+$~', $k) && preg_match('/^\d+$/', $val))
			{
				if (!in_array($val, $forum_cats) || in_array($val, $mod_cats))
				{
					continue;
				}
				$mod_cats[] = $_POST[$k];
			}
		}
		
		if ($username != null && ($usermeta = $user_db->get_user_meta($username)) != null)
		{
			if (!empty($mod_cats))
			{
				$user_db->remove_moderator($usermeta['user_id']);
				
				foreach ($mod_cats as $cat_id)
				{
					$user_db->add_moderator($usermeta['user_id'], $cat_id);
				}
			}
			else
			{
				$error->add('control_panel', 'mod', 'Please choose a category for the moderator.');
			}
		}
		
		break;
	case 'remove_mod':
		if (isset($_GET['user']))
		{
			$usermeta = $user_db->get_user_meta(get_clean('user'));
			if ($usermeta != null)
			{
				$user_db->remove_moderator($usermeta['user_id']);
			}
			else
			{
				$error->add('control_panel', 'mod', '\''.get_clean('user').'\' does not exists.');
			}
		}
		break;
	case 'rm_user':
		if (isset($_GET['user']))
		{
			$usermeta = $user_db->get_user_meta(get_clean('user'));
			if ($usermeta != null)
			{
				require $config['database_path'].'/threads_database.php';
				require $config['database_path'].'/threads_replies_database.php';
				
				require( $config['__'] . '/file.php' );
				
				// Delete all attachments for threads user has created.
				$thread_db = new Threads_database();
				$threads = $thread_db->get_threads_by_user_id($usermeta['user_id'], 0, -1);
				foreach ($threads as $t)
				{
					$path = $config['attachment_path'].'/thread'.$t->thread_id;
					if (file_exists($path))
					{
						delete_folder($path);
					}
				}
				
				// Delete all attachments for user's replies to threads.
				$reply_db = new Threads_replies_database();
				$replies = $reply_db->get_replies_by_user_id($usermeta['user_id']);
				foreach ($replies as $r)
				{
					$path = $config['attachment_path'].'/thread'.$r->thread_id.'/reply'.$r->reply_id;
					if (file_exists($path))
					{
						delete_folder($path);
					}
				}
				
				// Delete user's profile picture.
				$picture_path = $usermeta['picture_filename'];
				if ($picture_path != null && file_exists("$config[user_picture_path]/$picture_path"))
				{
					unlink("$config[user_picture_path]/$picture_path");
				}
				
				$user_db->remove_user($usermeta['user_id']);
			}
			else
			{
				$error->add('control_panel', 'user', 'User \''.get_clean('user').'\' does not exists.');
			}
		}
		break;
	}
	
	$redirect_url = "$config[http_host]/admin/control_panel.php?s=";
	if (in_array($_GET['a'], array('set_theme', 'delete_theme'))
		|| ($_GET['a'] == 'install_theme' && ! $error->exists('control_panel', 'site_theme')))
	{
		$redirect_url .= 'my_themes';
	}
	elseif (in_array($_GET['a'], array('add_mod', 'edit_mod', 'remove_mod')))
	{
		$redirect_url .= 'moderators';
		if (isset($_POST['user']) && ($_GET['a'] == 'add_mod' || $_GET['a'] == 'edit_mod'))
		{
			$redirect_url .= '&' . explode('_', $_GET['a'])[0] . '=' . post_clean('user');
		}
	}
	elseif ($_GET['a'] == 'rm_user')
	{
		$redirect_url .= 'users';
	}
	else
	{
		$redirect_url .= $_GET['a'];
	}
	
	if ($error->exists_group('control_panel'))
	{
		if ($_GET['a'] == 'change_site_name' && isset($_POST['sitename']))
		{
			$error->add_field_value('control_panel', 'site_name', trim($_POST['sitename']));
		}
		$redirect_url .= '&e_k='.$error->save();
	}
	header("Location: $redirect_url");
}
else
{
	header("Location: $config[http_host]/admin/control_panel.php");
	die();
}

function get_theme_name()
{
	$theme_name = null;
	if (isset($GLOBALS['_GET']['th']))
	{
		$theme_name = strtolower(trim($GLOBALS['_GET']['th']));
	}
	return $theme_name;
}
