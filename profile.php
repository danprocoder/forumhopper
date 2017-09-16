<?php
require('./config/config_inc.php');
require($config['includes_path'] . '/constants.php');

$user_details = null;

if (isset($_GET['u']))
{
	$user_details = $user_db->get_user_meta($_GET['u']);
}
else
{
	if (USER_IS_LOGGED_IN)
	{
		$user_details = $user_db->get_user_meta(USER_NICK);
	}
}

$title = ' &ndash; '.$config['site_name'];

$theme_css = array('side_sections_right.css');
if ($user_details !== null)
{
	$title = $user_details['username'].$title;
	$css_paths[] = 'css/profile_layout.css';
	$css_paths[] = 'css/profile.css';
	
	$theme_css[] = 'thread_item.css';
	$theme_css[] = 'profile.css';
	$theme_css[] = 'tab.css';
} else {
	$title = 'Page not found'.$title;
}
include($config['includes_path'] . '/header.php');

require $config['includes_path'].'/functions/time.php';
?>
<div id="content">
	<div id="left">
		<?php if ($user_details !== null) : ?>
		<h3><?php echo $user_details['username'];
		if ($user_details['fullname'] !== null)
		{
			echo ' <span>(' . $user_details['fullname'] . ')</span>';
		}
		?></h3>
		<?php if ($user_details['user_id'] === $sess_info['user_id']) : ?>
		<div id="edit_link"><a href="<?php echo $config['http_host'] . '/edit_profile.php'; ?>">Edit Profile</a></div>
		<?php endif; ?>
		<div id="user_content">
			<div id="left_small">
				<div id="profile_pic_wrapper"><img <?php echo 'src="' . $config['http_host'] . '/file.php?u=' . $user_details['username'] . '" alt="'.$user_details['username'].'"'; ?> /></div>
				<div>
					<?php
					echo '<ul>';
					
					if ($user_details['birthdate'] !== null)
					{
						$date = explode('-', $user_details['birthdate']);
						
						$age = date('Y') - $date[0];
						if (date('m') < $date[1] || (date('m') == $date[1] && date('d') < $date[2]))
						{
							$age--;
						}
						echo '<li><span>Age</span> '.$age.'</li>';
					}
					
					if ($user_details['sex'] !== null)
					{
						echo '<li><span>Sex</span> '.$user_details['sex'].'</li>';
					}
					
					if ($user_details['location'] !== null)
					{
						echo '<li><span>Location</span><div>' . $user_details['location'] . '</div></li>';
					}
					
					echo '<li><span>Joined</span><div>'.readable_time($user_details['joined']).'</div></li>';
					
					// Last online
					$sec_diff = time() - $user_details['last_active'];
					// Show active if user is active at least 1 minute ago.
					if ($sec_diff <= 60)
					{
						echo '<li class="online"><b>&bull;</b> Online</li>';
					}
					else
					{
						echo '<li><span>Last Online</span><div>'.readable_time($user_details['last_active']).'</div></li>';
					}
					
					echo '</ul>';
					?>
				</div>
				<?php if ($user_details['username'] != USER_NICK
						  && (USER_IS_ADMIN || $user_db->is_moderator($sess_info['user_id']))
						  && !$user_db->is_admin($user_details['user_id'])) : ?>
				<div id="ban_option"><?php
					$link = $config['http_host'] . '/form/user_ban.php?u='.$user_details['username'];
					echo '<a href="'.$link.'">' . ($user_details['is_banned'] ? 'Unban User' : 'Ban User') . '</a>';
				?>
				</a></div>
				<?php endif; ?>
			</div>
			<div id="right_large">
				<div class="tab">
					<?php
					$profile_link = $config['http_host'] . '/profile.php';
					if (isset($_GET['u']))
					{
						$profile_link .= '?u=' . urlencode($_GET['u']);
					}
					
					$tab_menus = array(
						'All Threads'       => $profile_link,
						'Recent Activities' => $profile_link . (preg_match('~\?.*$~', $profile_link) ? '&' : '?') . 'v=activity',
					);
					
					$active = 'All Threads';
					if (isset($_GET['v']))
					{
						if ($_GET['v'] == 'activity')
						{
							$active = 'Recent Activities';
						}
					}
					
					$theme->tab_menu($tab_menus, $active);
					?>
					<div class="tab_content">
					<?php
					if ((isset($_GET['v']) && $_GET['v'] !== 'activity') || !isset($_GET['v']))
					{
						require($config['database_path'] . '/threads_database.php');
						$threads_db = new Threads_database();
						
						require($config['__'] . '/pagination.php');
						$pagination = new Pagination($threads_db->get_num_threads_by_user($user_details['user_id']), 6);
						
						$offset = $pagination->get_start_index($pagination->get_current_page());
						$count = $pagination->get_items_per_page();
						$threads = $threads_db->get_threads_by_user_id($user_details['user_id'], $offset, $count);
						
						if ($threads !== null)
						{
							$num_replies_per_page = $config['default_num_replies_per_page'];
							if (USER_IS_LOGGED_IN)
							{
								require($config['database_path'] . '/users_settings_database.php');
								$settings_db = new Users_settings_database($sess_info['user_id']);
								$num_replies_per_page = $settings_db->get_num_replies_per_page();
							}
							
							foreach($threads as $t)
							{
								$t->topic = htmlspecialchars($t->topic);
								$t->thread_link = $config['http_host'] . '/view.php?id=' . $t->thread_id;
								unset($t->op);
								
								$t->time_created = readable_time($t->time_created);
								
								$options = array();
								$base_cat_id = $cat->get_base_cat($t->cat_id);
								$is_moderator = $user_db->is_moderator($sess_info['user_id'], $base_cat_id);
								if (USER_IS_LOGGED_IN && (USER_IS_ADMIN || $is_moderator))
								{
									$options['Delete'] = $config['http_host'] . '/form/delete_thread.php?id='
											. $t->thread_id . '&ref=';
									
									// Will be used to redirect back to this page after deleting.
									$ref = 'profile.php';
									if ((USER_IS_LOGGED_IN && $user_details['username'] != USER_NICK) || ! USER_IS_LOGGED_IN)
									{
										$ref .= '?u=' . $user_details['username'];
									}
									
									if (($page = $pagination->get_current_page()) > 1)
									{
										$ref .= (preg_match('-\?.*$-', $ref)) ? "&page=$page" : "?page=$page";
									}
									
									$options['Delete'] .= urlencode($ref);
								}
								
								if ($t->replies > $num_replies_per_page)
								{
									$options['Last &raquo;'] = $t->thread_link . '&page=l';
								}
								
								$theme->thread_item($t, $options);
							}
							
							$pages = $pagination->get_number_of_pages();
							if ($pages > 1)
							{
								$current_page = $pagination->get_current_page();
								$page_link = $config['http_host'] . '/profile.php';
								if (USER_IS_LOGGED_IN)
								{
									if ($user_details['username'] != USER_NICK)
									{
										$page_link .= '?u=' . USER_NICK;
									}
								}
								else
								{
									$page_link .= '?u=' . urlencode($_GET['u']);
								}
								
								$theme->pages($pages, $current_page, $page_link);
							}
						}
						else
						{
							echo 'No thread by '.$user_details['username'];
						}
					}
					else
					{
						echo '<div>';
						echo $user_details['username'] . ' created a new thread.';
						echo '</div>';
					}
					?>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
		<?php else : ?>
			<h3>Page not found</h3>
			<p>The page your requested was not found</p>
		<?php endif; ?>
	</div>
	<?php include($config['includes_path'] . '/right-sections.php'); ?>
	<div class="clearfix"></div>
</div>
<?php include($config['includes_path'] . '/footer.php'); ?>