<?php
require('../includes/setup_inc.php');

define('CONFIG_TEMPLATE_PATH', CONFIG_DIR.'/config_template.php');

define('SETUP_TOTAL_STEPS', 4);

if (!file_exists(TMP_DIR))
{
	mkdir(TMP_DIR);
}

if (!(isset($_COOKIE['setup']) && file_exists(TMP_DIR.'/'.$_COOKIE['setup'].'.txt')))
{
	header('Location: ' . ADMIN_URL . '/setup.php');
}
else
{
	// Add to config file.
	require(ADMIN_DIR . '/includes/config.php');
	
	$setup = unserialize(file_get_contents(TMP_DIR . '/' . $_COOKIE['setup'] . '.txt'));
	define('CURRENT_STEP', $setup['current_step']);
	
	if (CURRENT_STEP == 1) // Database setup.
	{
		if (isset($_POST['host']) && isset($_POST['user']) && isset($_POST['pass']) && isset($_POST['name']))
		{
			$host = trim($_POST['host']);
			$user = trim($_POST['user']);
			$pass = trim($_POST['pass']);
			$name = trim($_POST['name']);
			
			if ($host == '')
			{
				$setup['error']['host'] = 'Database host is required';
			}
			
			if ($user == '')
			{
				$setup['error']['user'] = 'Database username is required';
			}
			
			if ($name == '')
			{
				$setup['error']['name'] = 'Database name is required';
			}
			
			if ( ! isset($setup['error']))
			{
				// Perform setup magic here
				// Test connection info.
				$con = @mysqli_connect($host, $user, $pass, $name);
				if ( ! $con)
				{
					switch (mysqli_connect_errno())
					{
					case 1042:
					case 1043:
					case 2002:
						$setup['error']['database'] = 'Unable to connect to database on host &lsquo;'.htmlspecialchars($host).'&rsquo;';
						break;
					case 1044:
					case 1045:
						$setup['error']['database'] = 'Invalid username/password';
						break;
					case 1049:
						$setup['error']['database'] = 'Database &lsquo;'.htmlspecialchars($name).'&rsquo; does not exists.';
						break;
					default:
						$setup['error']['database'] = mysqli_connect_errno() . ': ' . mysqli_connect_error();
					}
				}
				else
				{
					modify_config_setting(CONFIG_TEMPLATE_PATH, 'db_host', $host);
					modify_config_setting(CONFIG_TEMPLATE_PATH, 'db_user', $user);
					modify_config_setting(CONFIG_TEMPLATE_PATH, 'db_pass', $pass);
					modify_config_setting(CONFIG_TEMPLATE_PATH, 'db_name', $name);
					
					// Create database tables.
					$tables = array(
						'CREATE TABLE IF NOT EXISTS categories (
							cat_id INT NOT NULL AUTO_INCREMENT,
							parent INT DEFAULT \'1\',
							cat VARCHAR(50) NOT NULL,
							description VARCHAR(1024) NOT NULL,
							PRIMARY KEY(cat_id),
							
							FOREIGN KEY (parent)
							  REFERENCES categories(cat_id)
							  ON DELETE CASCADE
						);',
						
						'INSERT INTO categories(parent, cat, description)VALUES(NULL, \'base\', \'Base category\')',
						
						'CREATE TABLE IF NOT EXISTS users (
							user_id INT NOT NULL AUTO_INCREMENT,
							username VARCHAR(200) NOT NULL,
							email VARCHAR(200) NOT NULL,
							salt VARCHAR(200) NOT NULL,
							password VARCHAR(257) NOT NULL,
							joined INT NOT NULL DEFAULT \'0\',
							is_admin TINYINT(1) NOT NULL DEFAULT \'0\',
							is_banned TINYINT(1) NOT NULL DEFAULT \'0\',
							last_active INT NOT NULL DEFAULT \'0\',
							PRIMARY KEY(user_id)
						) Engine=InnoDB;',
						
						'CREATE TABLE IF NOT EXISTS usermeta (
							meta_id INT NOT NULL AUTO_INCREMENT,
							user_id INT NOT NULL,
							fullname VARCHAR(200),
							location VARCHAR(100),
							picture_filename VARCHAR(200),
							birthdate DATE,
							sex VARCHAR(7),
							PRIMARY KEY(meta_id),
							
							FOREIGN KEY (user_id)
							  REFERENCES users(user_id)
							  ON DELETE CASCADE
						) Engine=InnoDB;',

						'CREATE TABLE IF NOT EXISTS users_sessions (
							session_id VARCHAR(40) NOT NULL,
							user_id INT NOT NULL,
							user_agent VARCHAR(40) NOT NULL,
							ip_addr VARCHAR(16) NOT NULL,
							time_created INT NOT NULL,
							PRIMARY KEY(session_id),
							
							FOREIGN KEY (user_id)
							  REFERENCES users(user_id)
							  ON DELETE CASCADE
						) Engine=InnoDB;',

						'CREATE TABLE IF NOT EXISTS threads (
							thread_id INT NOT NULL AUTO_INCREMENT,
							user_id INT NOT NULL,
							cat_id INT NOT NULL DEFAULT \'0\',
							topic VARCHAR(200) NOT NULL,
							time_created INT NOT NULL,
							PRIMARY KEY(thread_id),
							
							FOREIGN KEY (cat_id)
							  REFERENCES categories(cat_id)
							  ON DELETE CASCADE,
							  
							FOREIGN KEY (user_id)
							  REFERENCES users(user_id)
							  ON DELETE CASCADE
						) Engine=InnoDB;',

						'CREATE TABLE IF NOT EXISTS threads_replies (
							reply_id INT NOT NULL AUTO_INCREMENT,
							thread_id INT NOT NULL,
							user_id INT NOT NULL,
							reply VARCHAR(10000) NOT NULL,
							time_replied INT NOT NULL,
							first_post TINYINT(1) NOT NULL DEFAULT \'0\',
							PRIMARY KEY (reply_id),
							
							FOREIGN KEY (thread_id)
							REFERENCES threads(thread_id)
							ON DELETE CASCADE,
							  
							FOREIGN KEY (user_id)
							  REFERENCES users(user_id)
							  ON DELETE CASCADE
						) Engine=InnoDB;',

						'CREATE TABLE IF NOT EXISTS errors (
							error_key VARCHAR(33) NOT NULL,
							message text NOT NULL,
							PRIMARY KEY (error_key)
						);',

						'CREATE TABLE IF NOT EXISTS threads_views (
							view_id INT NOT NULL AUTO_INCREMENT,
							user_id INT NOT NULL,
							thread_id INT NOT NULL,
							times_viewed INT NOT NULL DEFAULT \'1\',
							last_viewed INT NOT NULL,
							ip VARCHAR(16) NOT NULL,
							
							PRIMARY KEY (view_id),
							
							FOREIGN KEY (thread_id)
							  REFERENCES threads(thread_id)
							  ON DELETE CASCADE
						)Engine=InnoDB;',

						'CREATE TABLE IF NOT EXISTS users_settings (
							settings_id INT NOT NULL AUTO_INCREMENT,
							user_id INT NOT NULL,
							num_threads_per_page INT NOT NULL,
							num_replies_per_page INT NOT NULL,
							
							PRIMARY KEY (settings_id),
							
							FOREIGN KEY (user_id)
							  REFERENCES users(user_id)
							  ON DELETE CASCADE
						)Engine=InnoDB',
						
						'CREATE TABLE moderators (
							id INT NOT NULL AUTO_INCREMENT,
							mod_id INT NOT NULL,
							cat_id INT NOT NULL,
							
							PRIMARY KEY (id),
							
							FOREIGN KEY (mod_id)
							  REFERENCES users(user_id)
							  ON DELETE CASCADE
						)Engine=InnoDB',
					);
					foreach ($tables as $sql)
					{
						mysqli_query($con, $sql) or die(mysqli_error($con));
					}
					
					mysqli_close($con);
				}
			}
			
			if (isset($setup['error']))
			{
				$setup['form_data'] = array(
					'host' => htmlspecialchars($host),
					'user' => htmlspecialchars($user),
					'pass' => htmlspecialchars($pass),
					'name' => htmlspecialchars($name),
				);
			}
		}
		else
		{
			$setup['error'] = null; // Add 'error', to redirect back to same page.
		}
	}
	elseif (CURRENT_STEP == 2) // Admin account creation.
	{
		if (isset($_POST['username']) && isset($_POST['pass']) && isset($_POST['re_pass']) && isset($_POST['email']))
		{
			$username = trim($_POST['username']);
			$pass = trim($_POST['pass']);
			$re_pass = trim($_POST['re_pass']);
			$email = trim($_POST['email']);
			
			// Validate username
			if ($username == '')
			{
				$setup['error']['user'] = 'Field is required';
			}
			elseif (strlen($username) < 2 || strlen($username) > 12)
			{
				$setup['error']['user'] = 'Must be between 2 - 12 characters long.';
			}
			elseif ( ! preg_match('/^[a-zA-Z][a-zA-Z_0-9]+$/', $username))
			{
				$setup['error']['user'] = 'Must begin with a letter, followed by either a letter, a number or _';
			}
			
			// Validate password
			if ($pass == '')
			{
				$setup['error']['pass'] = 'Field is required.';
			}
			elseif (strlen($pass) < 8)
			{
				$setup['error']['pass'] = 'Must be at least 8 characters.';
			}
			elseif (strtolower($pass) == strtolower($username))
			{
				$setup['error']['pass'] = 'Must be different from your username.';
			}
			
			// Validate re-entered password
			if ($re_pass == '')
			{
				$setup['error']['re_pass'] = 'Field is required';
			}
			elseif ($re_pass != $pass)
			{
				$setup['error']['re_pass'] = 'Does not match password you provided above';
			}
			
			// Validate email.
			if ($email == '')
			{
				$setup['error']['email'] = 'Field is required';
			}
			elseif ( ! filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$setup['error']['email'] = 'Email address is not valid';
			}
			
			if (isset($setup['error']))
			{
				$setup['form_data'] = array(
					'user' => htmlspecialchars($username),
					'email' => htmlspecialchars($email),
				);
			}
			else
			{
				// Hash password.
				require($config['includes_path'] . '/password.php');
				$salt = generate_user_salt();
				$pass = hash_password($salt, $pass);
				
				// Add to database.
				require($config['database_path'] . '/user_database.php');
				$user_db = new User_database();
				$user_db->add_admin($username, $email, $salt, $pass);
			}
		}
		else
		{
			$setup['error'] = null; // Add 'error' to redirect user to the same page.
		}
	}
	elseif (CURRENT_STEP == 3)
	{
		if (isset($_POST['site_name']) && isset($_FILES['site_logo']) && isset($_POST['site_theme']))
		{
			$name = trim($_POST['site_name']);
			$logo = $_FILES['site_logo'];
			$theme = trim(strtolower($_POST['site_theme']));
			
			// Validate site name.
			if ($name == '')
			{
				$setup['error']['site_name'] = 'Field is required.';
			}
			elseif (strlen($name) > 59)
			{
				$setup['error']['site_name'] = 'Must not exceed 59 characters.';
			}
			elseif (preg_match('~[^A-Za-z0-9_\-\(\):\\\' ]~', $name))
			{
				$setup['error']['site_name'] = 'Can only contain the following characters: <i>A-Z, a-z, 0-9, _, -, :, &apos;, (, ) or a space.</i>';
			}
			
			// Validate site_logo
			require(ADMIN_DIR . '/includes/file_upload.php');
			$rules = array(
				'extensions' => array('.jpg', '.jpeg', '.png', '.gif'),
				'max_upload_size' => $config['site_logo_max_filesize'],
			);
			if ( ! validate_uploaded_file($_FILES['site_logo'], $rules, $logo_error, 'logo'))
			{
				$setup['error']['site_logo'] = $logo_error;
			}
			
			// Validate theme
			require($config['__'] . '/theme_manager.php');
			$themes = Theme_manager::get_themes();
			if ( ! array_key_exists($theme, $themes))
			{
				$setup['error']['site_theme'] = 'Please select your site theme.';
			}
			
			if (isset($setup['error']))
			{
				$setup['form_data'] = array(
					'site_name' => htmlspecialchars($name),
					'site_theme' => htmlspecialchars($theme),
				);
			}
			else
			{
				// Set site name.
				modify_config_setting(CONFIG_TEMPLATE_PATH, 'site_name', str_replace('\'', "'.APOSTROPHE.'", $name));
				
				// Set site banner.
				require(ADMIN_DIR . '/includes/site_logo.php');
				$filename = random_logo_filename().'.'.pathinfo($logo['name'], PATHINFO_EXTENSION);
				$logo_path = IMAGES_DIR.'/'.$filename;
				
				$logo_img = load_logo($logo['tmp_name'], $logo['type']);
				if ($logo_img != null)
				{
					resize_logo($logo_img, $logo['type'], $logo_path);
					modify_config_setting(CONFIG_TEMPLATE_PATH, 'site_logo_url', 'IMAGES_URL.\'/'.$filename.'\'');
				}
				
				// Set site theme.
				modify_config_setting(CONFIG_TEMPLATE_PATH, 'site_theme', $theme);
			}
		}
		else
		{
			$setup['error'] = null; // Add 'error' to redirect user to the same page.
		}
	}
	elseif (CURRENT_STEP == 4)
	{
		if (isset($_POST['cats']))
		{
			$cats = @json_decode($_POST['cats']);
			if ($cats != null)
			{
				if (empty($cats))
				{
					$setup['error']['category'] = 'Please add a category';
				}
				else
				{
					// Create the main categories.
					require($config['database_path'] . '/thread_category.php');
					$cats_db = new Thread_category();
					
					foreach ($cats as $c)
					{
						if ( ! isset($c->name) || ! isset($c->description))
						{
							continue;
						}
					
						$c->name = trim($c->name);
						$c->description = trim($c->description);
					
						// Skip if 'name' and 'description' are empty.
						if ($c->name != '' && $c->description != '')
						{
							$cats_db->add_category(CATEGORY_NO_PARENT, $c->name, $c->description);
						}
					}
				}
			}
			else
			{
				$setup['error']['category'] = 'Please add a category';
			}
		}
		else
		{
			$setup['error']['category'] = 'Please add a category.';
		}
	}
	
	if (CURRENT_STEP == SETUP_TOTAL_STEPS && ! array_key_exists('error', $setup))
	{
		unlink(TMP_DIR.'/'.$_COOKIE['setup'].'.txt');
		setcookie('setup', '', time() - 3600);
		
		// Create config file.
		$config_file_src = file_get_contents(CONFIG_TEMPLATE_PATH);
		file_put_contents(CONFIG_DIR.'/config.php', $config_file_src);
		
		header("Location: $config[http_host]");
	}
	else
	{
		// If no error, take user to the next step.
		if ( ! array_key_exists('error', $setup))
		{
			$setup['current_step']++;
		}
		file_put_contents(TMP_DIR.'/'.$_COOKIE['setup'].'.txt', serialize($setup));
		
		header("Location: $config[http_host]/admin/setup.php");
	}
}
