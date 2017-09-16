<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo $title; ?></title>
		
		<link rel="stylesheet" type="text/css" href="<?php echo $config['http_host']; ?>/css/main.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo $config['http_host']; ?>/css/footer.css" />
		<?php
		if (isset($css_paths))
		{
			foreach ($css_paths as $css)
			{
				echo '<link rel="stylesheet" type="text/css" href="' . $config['http_host'] . '/' . $css . '" />';
			}
		}
		
		$theme_css_dir = $config['themes_path'] . '/' . $config['site_theme'] . '/css';
		$theme_css_http_dir = $config['http_host'] . '/themes/' . $config['site_theme'] . '/css';
		
		$def = array('main.css', 'nav_menu.css', 'nav_search.css', );
		foreach ($def as $f)
		{
			if (file_exists($theme_css_dir.'/'.$f))
			{
				echo '<link rel="stylesheet" type="text/css" href="' . $theme_css_http_dir.'/'.$f . '" />';
			}
		}
		
		if (isset($theme_css))
		{
			foreach ($theme_css as $filename)
			{
				if (file_exists($theme_css_dir.'/'.$filename))
				{
					echo '<link rel="stylesheet" type="text/css" href="' . $theme_css_http_dir.'/'.$filename . '" />';
				}
			}
		}
		
		if (isset($js_paths))
			foreach ($js_paths as $js)
				echo '<script src="'.$js.'"></script>';
		
		$theme_js_dir = $config['themes_path'] . '/' . $config['site_theme'] . '/js';
		if (file_exists($theme_js_dir))
		{
			$theme_js_url = $config['themes_url'] . '/' . $config['site_theme'] . '/js';
			foreach (array_slice(scandir($theme_js_dir), 2) as $js)
			{
				echo '<script src="'.$theme_js_url.'/'.$js.'"></script>';
			}
		}
		?>
    </head>
    <body>
        <div id="nav">
			<div id="nav_left">
				<?php
				// Site banner
				if (isset($config['site_logo_url']) && $config['site_logo_url'] !== null && file_exists(IMAGES_DIR.'/'.array_pop(explode('/', $config['site_logo_url']))))
				{
					echo '<div id="site_banner">';
					echo '<a href="' . $config['http_host'] . '">';
					echo '<img src="' . $config['site_logo_url'] . '" alt="' . $config['site_name'] . '" />';
					echo '</a>';
					echo '</div>';
				}
				else
				{
					if (strlen($config['site_name']) > 23)
					{
						echo '<h3 id="site_banner">';
						echo '<a href="'.$config['http_host'].'">' . $config['site_name'] . '</a>';
						echo '</h3>';
					}
					else
					{
						echo '<h1 id="site_banner">';
						echo '<a href="'.$config['http_host'].'">' . $config['site_name'] . '</a>';
						echo '</h1>';
					}
				}
				
				$script = basename($_SERVER['SCRIPT_FILENAME']);
				
				// Navigation menu
				$menus = array();
				if ( ! USER_IS_LOGGED_IN)
				{
					$menus['Log In']  = $config['http_host'];
					$menus['Sign Up'] = $config['http_host'] . '/signup.php';
				}
				elseif (USER_IS_LOGGED_IN && ! USER_IS_BANNED)
				{
					$menus['Forum'] = $config['http_host'];
					$menus['My Profile'] = $config['http_host'] . '/profile.php';
					if (USER_IS_ADMIN)
					{
						$menus['Admin CP'] = $config['http_host'] . '/admin/control_panel.php';
					}
					$menus['Settings'] = $config['http_host'] . '/settings.php';
				}
				
				if ( ! USER_IS_LOGGED_IN || (USER_IS_LOGGED_IN && ! USER_IS_BANNED))
				{					
					$active = '';
					switch ($script)
					{
					case 'profile.php':
						if (isset($_GET['u']) && strtolower(trim($_GET['u'])) != USER_NICK)
						{
							break;
						}
					case 'edit_profile.php':
						$active = 'My Profile';
						break;
					case 'settings.php':
						$active = 'Settings';
						break;
					case 'control_panel.php':
						$active = 'Admin CP';
						break;
					case 'signup.php':
						$active = 'Sign Up';
						break;
					case 'index.php':
					case 'threads.php':
					case 'view.php':
					case 'search.php':
					case 'create_thread.php':
					case 'edit_reply.php':
					case 'create_subcategory.php':
					case 'edit_subcategory.php':
						$active = 'Forum';
						break;
					}
					
					$theme->nav_menu($menus, $active);
				}
				?>
				<div class="clearfix"></div>
			</div>
			<?php
			if ( ! USER_IS_LOGGED_IN || (USER_IS_LOGGED_IN && ! USER_IS_BANNED))
			{
				$query = '';
				if ($script == 'search.php' && isset($_GET['q']))
				{
					$query = htmlspecialchars($_GET['q']);
				}
				
				$radios = array('topics', 'posts');
				
				$active_radio = 'topics';
				if ($script == 'search.php' && isset($_GET['w']) && $_GET['w'] == 'posts')
				{
					$active_radio = 'posts';
				}
				
				$theme->nav_search($config['http_host'].'/search.php', $query, $radios, $active_radio);
			}
			?>
			<div class="clearfix"></div>
		</div>