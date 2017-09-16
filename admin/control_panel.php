<?php
require('../config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if ( ! USER_IS_LOGGED_IN || (USER_IS_LOGGED_IN && ! USER_IS_ADMIN))
{
	header("Location: $config[http_host]");
	die();
}

$cp_sections = array(
	'change_site_logo',
	'categories',
	'moderators',
	'users',
	'install_theme',
	'my_themes',
);

$current = 'change_site_name';
if (isset($_GET['s']) && in_array(strtolower($_GET['s']), $cp_sections))
{
	$current = strtolower($_GET['s']);
}

$css_paths[] = 'admin/css/control_panel.css';

$theme_css = array( 'side_nav.css', 'side_sections_right.css', 'button.css', );

switch($current)
{
case 'my_themes':
	$css_paths[] = 'admin/css/control_panel_themes.css';
	$theme_css[] = 'control_panel_themes.css';
	break;
case 'categories':
	$css_paths[] = 'admin/css/control_panel_categories.css';
	$css_paths[] = 'admin/css/control_panel_form.css';
	
	$theme_css[] = 'control_panel_categories.css';
	break;
case 'users':
	require($config['includes_path'] . '/functions/time.php');
	
	$css_paths[] = 'admin/css/control_panel_users.css';
	$theme_css[] = 'control_panel_users.css';
	break;
case 'moderators':
	require($config['includes_path'] . '/functions/time.php');
	
	$css_paths[] = 'admin/css/control_panel_mod.css';
	$css_paths[] = 'admin/css/control_panel_users.css';
	$theme_css[] = 'control_panel_users.css';
default:
	$css_paths[] = 'admin/css/control_panel_form.css';
}

$title = 'Control Panel &ndash; '.$config['site_name'];

include( $config['includes_path'].'/header.php' );

$form_action = $config['http_host'].'/admin/form/control_panel.php?a='.$current;

function error($key)
{
	global $error;
	return $error->exists('control_panel', $key) ? $error->get_message('control_panel', $key) : '';
}

function get($key)
{
	return isset($_GET[$key]) ? strtolower(trim($_GET[$key])) : null;
}

function post_clean($key)
{
	return isset($_POST[$key]) ? trim($_POST[$key]) : null;
}

function get_sort_by()
{
	$sort_by = get('sort_by');
	if ($sort_by == null || !in_array($sort_by, array('az', 'za', 'time')))
	{
		$sort_by = 'time';
	}
	return $sort_by;
}

if (isset($_GET['q']) && get('q') != '')
{
	$_POST['q'] = $_GET['q'];
}
?>
<div id="content">
	<div id="left">
		<h2>Control Panel</h2>
		<div id="inner">
			<?php
			function cp_link($s, $params=array())
			{
				$url = ADMIN_URL . '/control_panel.php?s=' . $s;
				if (!empty($params))
				{
					$arr = array();
					foreach ($params as $k => $v)
					{
						$arr[] = $k.'='.urlencode($v);
					}
					$url .= '&'.implode('&', $arr);
				}
				return $url;
			}
			
			$side_nav = array(
				'My Site' => array(
					'Change Site Name' => cp_link('change_site_name'),
					'Change Site Logo' => cp_link('change_site_logo'),
				),
				'Forum' => array(
					'Categories' => cp_link('categories'),
					'Moderators' => cp_link('moderators'),
					'Users'      => cp_link('users'),
				),
				'Themes' => array(
					'Install Theme' => cp_link('install_theme'),
					'My Themes'     => cp_link('my_themes'),
				),
			);
			
			$side_nav_active = '';
			switch ($current) {
			case 'change_site_name':
				$side_nav_active = 'Change Site Name';
				break;
			case 'change_site_logo':
				$side_nav_active = 'Change Site Logo';
				break;
			case 'install_theme':
				$side_nav_active = 'Install Theme';
				break;
			case 'categories':
				$side_nav_active = 'Categories';
				break;
			case 'moderators':
				$side_nav_active = 'Moderators';
				break;
			case 'users':
				$side_nav_active = 'Users';
				break;
			case 'my_themes':
				$side_nav_active = 'My Themes';
				break;
			}
			
			$theme->side_nav($side_nav, $side_nav_active);
			?>
			<div id="main_content">
			<?php if ($current == 'change_site_name') { ?>
				<form id="cp_form" action="<?php echo $form_action; ?>" method="POST">
					<div>
						<label class="bold">Enter Site Name<?php
						if ($error->exists('control_panel', 'site_name')) {
							echo $error->get_message('control_panel', 'site_name');
						}
						?></label><br/>
						<input type="text" class="textfield" name="sitename" value="<?php
						if ($error->field_value_exists('control_panel', 'site_name'))
						{
							echo $error->get_field_value('control_panel', 'site_name');
						}
						else
						{
							echo $config['site_name'];
						}
						?>" />
					</div>
					<div class="note">Site name can only contain the following characters: a-z, A-Z, 0-9, -, _, :, (, ) or a whitespace.</div>
					<div>
						<input type="submit" class="button" value="Change Site Name" />
					</div>
				</form>
			<?php } elseif ($current == 'change_site_logo') { ?>
				<form id="cp_form" action="<?php echo $form_action; ?>" method="POST" enctype="multipart/form-data">
					<div>
						<label for="logo-field" class="bold">Select Site Logo<?php
						if ($error->exists('control_panel', 'site_logo'))
						{
							echo $error->get_message('control_panel', 'site_logo');
						}
						?></label><br/>
						<input type="file" class="file_field" name="logo" id="logo-field" />
					</div>
					<div class="note">
						<p>Max resolution for site logo is <?php echo "<b>$config[site_logo_max_width]</b>x<b>$config[site_logo_max_height]</b> (pixels)"; ?></p>
						<p>Filesize should not exceed <b><?php
						require($config['__'] . '/file.php');
						echo convert_bytes($config['site_logo_max_filesize']);
						?></b></p>
					</div>
					<div><input type="submit" class="button" value="Change Site Logo" /></div>
				</form>
			<?php } elseif ($current == 'categories') { ?>
				<form id="cp_form" action="<?php echo $form_action; ?>" method="POST" enctype="multipart/form-data" onsubmit="addToForm(event)">
					<div id="error_div"><?php
					if ($error->exists('control_panel', 'categories'))
					{
						$msg = $error->get_message('control_panel', 'categories');
						$msg = str_replace('<span', '<span id="error"', $msg);
						echo $msg;
					}
					else
					{
						echo '<span id="error" class="error"></span>';
					}
					?></div>
					<div id="table_wrapper">
						<div id="category_table"><div id="theader">
								<div class="col col1">Category</div>
								<div class="col col2">Description</div>
								<div class="clearfix"></div>
							</div><div id="body"></div>
						</div>
						<div id="info">Click on a row above to edit.</div>
					</div>
					<div><h4 id="add_cat_h4">Add Category</h4></div>
					<div>
						<label for="name" class="bold">Category<span class="error error_name"></span></label><br/>
						<input type="text" name="name" id="name" class="textfield" />
					</div>
					<div>
						<label for="description" class="bold">Description<span class="error error_description"></span></label><br/>
						<textarea type="text" name="description" id="description" class="textfield"></textarea>
					</div>
					<div>
						<input type="button" value="Add" name="addBtn" onclick="addCat()" />
						<input type="submit" id="finish_btn" class="button" value="Save" />
						<div class="clearfix"></div>
					</div>
					<script src="<?php echo ADMIN_URL; ?>/js/setup_4.js"></script>
					<script>addForumCats([<?php		
					$cats = $cat->get_categories(CATEGORY_NO_PARENT);
					
					$objs = '';
					foreach ($cats as $c)
					{
						$objs .= "new Category('$c[cat_id]','$c[category]','$c[description]'),";
					}
					echo rtrim($objs, ',');
					?>]);</script>
				</form>
			<?php }
			elseif ($current == 'moderators')
			{
				$username = null;
				if (isset($_GET['add']))
				{
					$username = get('add');
				}
				elseif (isset($_GET['edit']))
				{
					$username = get('edit');
				}
				
				if ($username != null)
				{
					$usermeta = $user_db->get_user_meta($username);
					if ($usermeta != null)
					{
						$form_action = '';
						$h4 = '';
						$submit_btn_value = '';
						if ($user_db->is_moderator($usermeta['user_id']))
						{
							$form_action = 'edit_mod';
							$h4 = "Edit $username";
							$submit_btn_value = 'Edit Mod';
						}
						else
						{
							$form_action = 'add_mod';
							$h4 = "Add $username to moderators";
							$submit_btn_value = 'Add Mod';
						}
			?>
			<form id="cp_form" action="<?php echo ADMIN_URL . '/form/control_panel.php?a='.$form_action; ?>" method="POST" accept-charset="UTF-8">
				<input type="hidden" name="user" value="<?php echo $username; ?>" />
				<div><a href="<?php echo cp_link('moderators'); ?>">View all moderators &rsaquo;</a></div>
				<div><h4><?php echo $h4; ?></h4></div>
				<div><?php echo error('mod'); ?></div>
				<div id="user_info">
				<?php
				echo '<img src="'.$config['http_host'].'/file.php?u='.$username.'" alt="'.$username.'" />';
				echo '<div id="beside_img">';
				echo '<p><a href="'.$config['http_host'].'/profile.php?u='.$username.'" id="username">'.$username.'</a></p>';
				echo '<div><p><b>Joined:</b> '.date('d M, Y', $usermeta['joined']).'</p>';
				echo '<p><b>Last Online:</b> 1 minute ago</p></div>';
				echo '</div>';
				?>
				<div class="clearfix"></div>
				</div>
				<div id="categories"><h4>Select Categories</h4><?php
				$cats = $cat->get_categories(CATEGORY_NO_PARENT);
				echo '<ul>';
				$i = 1;
				
				$mod_cat_ids = $user_db->get_moderator_cat_ids($usermeta['user_id']);
				foreach ($cats as $c)
				{
					echo '<li><input type="checkbox" name="cat'.($i++).'" id="'.$c['category'].'" value="'.$c['cat_id'].'"';
					if ($mod_cat_ids != null && in_array($c['cat_id'], $mod_cat_ids))
					{
						echo ' checked="checked"';
					}
					echo ' /> ';
					echo '<label for="'.$c['category'].'">'.$c['category'].'</label></li>';
				}
				echo '</ul>';
				?></div>
				<div><input type="submit" value="<?php echo $submit_btn_value; ?>" class="button" /></div>
			</form>
			<?php   }
					else 
					{
						echo '<div>'.$username.' does not exists.</div>';
					}
				}
				else
				{
					$sort_by = get_sort_by();
					
					include(ADMIN_DIR.'/includes/control_panel_users_toolbar.php');
					
					// Show all moderators here.
					$mod_rows = $user_db->get_moderators($sort_by, post_clean('q'));
					if ($mod_rows != null)
					{
						$mods = array();
						foreach ($mod_rows as $m)
						{
							if (!array_key_exists($m->username, $mods))
							{
								$mods[ $m->username ] = array(
									'joined' => $m->joined,
									'sections' => array($m->cat_id => $m->cat)
								);
							}
							else
							{
								$mods[ $m->username ]['sections'][ $m->cat_id ] = $m->cat;
							}
						}
						
						echo '<div id="users">';
						foreach ($mods as $username => $mod_info)
						{
							$mod['username'] = $username;
							$mod['picture_url'] = $config['http_host'].'/file.php?u='.$username;
							$mod['profile_url'] = $config['http_host'].'/profile.php?u='.$username;
							$mod['options'] = array(
								'<a href="'.ADMIN_URL.'/form/control_panel.php?a=remove_mod&user='.$username.'" title="Remove from moderators">Remove</a>',
								'<a href="'.cp_link('moderators', array('edit' => $username)).'">Edit moderator</a>'
							);
							$mod['info'] = array(
								'joined' => readable_time($mod_info['joined']),
								'sections' => array(),
							);
							foreach ($mod_info['sections'] as $cat_id => $catname)
							{
								array_push($mod['info']['sections'], '<a href="'.$config['http_host'].'/threads.php?cat_id='.$cat_id.'">'.$catname.'</a>');
							}
							$mod['info']['sections'] = implode(',', $mod['info']['sections']);
							
							$theme->cp_user($mod);
						}
						echo '</div>';
					}
					else
					{
						$q = post_clean('q');
						if ($q != null && $q != '')
						{
							echo '<div id="error">There was no user found with the search criteria you entered.</div>';
						}
						else
						{
							echo '<div id="error">There are no moderators to show.</div>';
						}
					}
				}
			}
			elseif ($current == 'users')
			{
				$sort_by = get_sort_by();
				
				include(ADMIN_DIR.'/includes/control_panel_users_toolbar.php');
				
				if ($error->exists('control_panel', 'user'))
				{
					echo '<div id="error">';
					echo $error->get_message('control_panel', 'user');
					echo '</div>';
				}
				
				$users = $user_db->get_all_users($sess_info['user_id'], $sort_by, post_clean('q'));
				if ($users != null)
				{
					echo '<div id="users">';
					foreach ($users as $u)
					{
						$u = (array) $u;
						$u['profile_url'] = $config['http_host'].'/profile.php?u='.$u['username'];
						$u['picture_url'] = $config['http_host'].'/file.php?u='.$u['username'];
						
						$u['info'] = array(
							'Joined'      => readable_time($u['joined']),
							'Last Online' => readable_time($u['last_active']),
						);
						
						$u['options'] = array();
						$ban_url_ref = 'admin/control_panel.php?s=users';
						if (isset($_POST['q']) && post_clean('q') != '')
						{
							$ban_url_ref .= '&q='.post_clean('q');
						}
						$ban_url = $config['http_host'].'/form/user_ban.php?u='.$u['username'].'&ref='.urlencode($ban_url_ref);
						if ($u['is_banned'])
						{
							$u['options'][] = '<a href="'.$ban_url.'">Unban User</a>';
						}
						else
						{	
							$u['options'][] = '<a href="'.$ban_url.'">Ban User</a>';
						}
						$u['options'][] = '<a href="'.ADMIN_URL.'/form/control_panel.php?a=rm_user&user='.$u['username'].'">Delete User</a>';
						if (!$user_db->is_moderator($u['user_id']))
						{
							$u['options'][] = '<a href="'.cp_link('moderators', array('add' => $u['username'])).'">Add to Mod</a>';
						}
						
						unset($u['user_id']);
						
						$theme->cp_user($u);
					}
					echo '</div>';
				}
				else
				{
					$q = post_clean('q');
					if ($q != null && $q != '')
					{
						echo '<div id="error">There was no user found with the search criteria you entered.</div>';
					}
					else
					{
						echo '<div id="error">There are no users.</div>';
					}
				}
			}
			elseif ($current == 'install_theme')
			{ ?>
				<form id="cp_form" action="<?php echo $form_action; ?>" method="POST" enctype="multipart/form-data">
					<?php
					if ($error->exists('control_panel', 'site_theme'))
					{
						echo '<div>'.$error->get_message('control_panel', 'site_theme').'</div>';
					}
					?>
					<div>
						<label class="bold">Select Theme</label><br/>
						<input type="file" name="theme" class="file_field" />
					</div>
					<div><input type="checkbox" id="use" name="use" class="checkbox" /> <label for="use">Set as current theme.</label></div>
					<div>
						<input type="submit" class="button" value="Install Theme" />
					</div>
				</form>
			<?php
			} elseif ($current == 'my_themes')
			{
				if ($error->exists('control_panel', 'site_theme'))
				{
					echo '<div id="theme_error">';
					echo $error->get_message('control_panel', 'site_theme');
					echo '</div>';
				}
			
				$themes = Theme_manager::get_themes();
				
				$current_theme = $themes[$config['site_theme']];
				
				echo '<div id="current_theme">';
				echo '<div><b>'.$current_theme->name.'</b> (v'.$current_theme->version.') <span id="current_theme_span">&ndash; Current Theme</span></div>';
				echo '<img src="'.$current_theme->screenshot_url.'" />';
				echo '</div>';
				
				unset($themes[$config['site_theme']]);
				
				echo '<div id="themes">';
				echo '<h5>Installed Themes</h5>';
				echo '<div id="grid">';
				foreach ($themes as $thm => $meta)
				{
					$dir = $config['themes_path'].'/'.$thm;
					
					echo '<div class="theme">';
					
					echo '<div class="name">'.($meta !== null ? $meta->name : $thm).'</div>';
					
					echo '<div class="meta">';
					if ($meta !== null && file_exists($meta->screenshot_path))
					{
						echo '<div class="screenshot">';
						echo '<div><img src="'.$meta->screenshot_url.'" /></div>';
						echo '<p class="view_full">(<a href="'.$meta->screenshot_url.'">View screenshot fullsize</a>)</p>';
						echo '</div>';
					}
					else
					{
						echo '<div class="no_screenshot">No screenshot found</div>';
					}
					
					// Theme options
					echo '<div class="theme_right">';
					echo '<ul class="options">';
					
					echo '<li><b>Version:</b> '.$meta->version.'</li>';
					
					$is_current_theme = $thm == $config['site_theme'];
					if ( ! $is_current_theme)
					{
						echo '<li><a href="'.$config['http_host'].'/admin/form/control_panel.php?a=set_theme&th='.$thm.'">Set as Site Theme</a></li>';
					}
					
					if (count($themes) > 1 && !$is_current_theme)
					{
						echo '<li><a href="'.$config['http_host'].'/admin/form/control_panel.php?a=delete_theme&th='.$thm.'">Delete Theme</a></li>';
					}
					
					echo '</div>';
					
					echo '<div class="clearfix"></div>';
					
					echo '</div>';
					
					echo '</div>';
				}
				
				echo '<div class="clearfix"></div>';
				echo '</div>';
				echo '</div>';
			}
			?>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<?php require($config['includes_path'].'/right-sections.php'); ?>
	<div class="clearfix"></div>
</div>
<?php require($config['includes_path'].'/footer.php'); ?>