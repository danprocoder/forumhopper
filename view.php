<?php
require('./config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if (isset($_GET['id']))
{
	define('CURRENT_THREAD_ID', $_GET['id']);
}
else
{
	header('Location: ' . $config['http_host']);
	die();
}

require($config['database_path'] . '/threads_database.php');
$threads_db = new Threads_database();

require($config['database_path'] . '/threads_views_database.php');
$views_db = new Threads_views_database();

$pagination = null;

$title = ' &ndash; ' . $config['site_name'];

$css_paths = array('css/view.css');

$theme_css[] = 'side_sections_right.css';

$current_thread = $threads_db->get_thread_meta(CURRENT_THREAD_ID);
if ($current_thread !== NULL)
{
	$base_cat_id = $cat->get_base_cat($current_thread['cat_id']);
	define('USER_IS_MODERATOR', $user_db->is_moderator($sess_info['user_id'], $base_cat_id));

	$current_thread['topic'] = htmlspecialchars($current_thread['topic']);
	
	$current_user_id = USER_IS_LOGGED_IN ? $sess_info['user_id'] : 0;
	if ($views_db->user_has_viewed(CURRENT_THREAD_ID, $current_user_id))
	{
		if (!isset($_COOKIE['current_thread']) || (isset($_COOKIE['current_thread']) && $_COOKIE['current_thread'] != CURRENT_THREAD_ID))
		{
			$views_db->increase_view(CURRENT_THREAD_ID, $current_user_id);
		}
		elseif (isset($_COOKIE['current_thread']) && $_COOKIE['current_thread'] == CURRENT_THREAD_ID)
		{
			$views_db->update_last_viewed(CURRENT_THREAD_ID, $current_user_id);
		}
	}
	else
	{
		$views_db->add_new_view(CURRENT_THREAD_ID, $current_user_id);
	}
	
	// Cookie is used the track the current thread the user is viewing.
	// It is used to prevent a page from getting multiple view count when a user
	// navigates through different pages in the same thread.
	setcookie('current_thread', CURRENT_THREAD_ID, 0);
	
	// Pagination
	$num_replies_per_page = $config['default_num_replies_per_page'];
	// If user is logged in, load from user's settings.
	if (USER_IS_LOGGED_IN)
	{
		require($config['database_path'] . '/users_settings_database.php');
		$user_setting_db = new Users_settings_database($sess_info['user_id']);
		$num_replies_per_page = $user_setting_db->get_num_replies_per_page();
	}
	require($config['__'] . '/pagination.php');
	$pagination = new Pagination($current_thread['replies'], $num_replies_per_page);
	
	// Title
	$title = $current_thread['topic'].$title;
	
	// Theme styles
	$theme_css[] = 'side_topics.css';
	$theme_css[] = 'thread_reply.css';
	$theme_css[] = 'button.css';
	$theme_css[] = 'currently_viewing.css';
}
else
{
	$title = 'Not found'.$title;
}

include($config['includes_path'] . '/header.php');
?>
<div id="content">
	<div id="left">
		<?php if ($current_thread !== NULL) : ?>
			<?php
			include($config['includes_path'] . '/category_hierarchy.php');
			
			$current_cat_meta = $cat->get_cat_by_id($current_thread['cat_id']);
			get_category_hierarchy($current_cat_meta['id'], $current_cat_meta['name']);
			?>
			<h3><?php echo $current_thread['topic'] . ' (' . $current_thread['replies'] . ')'; ?></h3>
			<div id="meta"><small>By <a href="<?php
			echo $config['http_host'] . '/profile.php';
			if ($current_thread['username'] !== USER_NICK)
			{
				echo '?u=' . $current_thread['username'];
			}
			?>"><?php echo $current_thread['username']; ?></a> on <?php echo date('d M, Y', $current_thread['time_created']).' at '.date('H:ia', $current_thread['time_created']); ?>.</small></div>
			<div id="users_replies">
				<div id="left_col">
					<?php
					$recent_threads = $threads_db->get_recent_threads($current_thread['cat_id'], CURRENT_THREAD_ID, 6);
					if ($recent_threads !== null) {
						for ($i = 0; $i < count($recent_threads); $i++) {
							$recent_threads[ $i ]->thread_link = $config['http_host'] . '/view.php?id='.$recent_threads[ $i ]->thread_id;
							unset($recent_threads[ $i ]->thread_id);
							
							$recent_threads[ $i ]->topic = htmlspecialchars( $recent_threads[ $i ]->topic );
						}
						$theme->side_topics( 'Recent Threads', $recent_threads );
					} else {
						echo '<p>No recent threads</p>';
					}
					?>
				</div>
				<div id="replies_wrapper">
					<div id="inner">
						<?php
						require($config['database_path'] . '/threads_replies_database.php');
						$replies_db = new Threads_replies_database();
						
						$offset = $pagination->get_start_index($pagination->get_current_page());
						$replies = $replies_db->get_replies(CURRENT_THREAD_ID, $offset, $pagination->get_items_per_page());
						if ($replies !== NULL)
						{
							require($config['__'] . '/file.php');
							require($config['includes_path'] . '/functions/time.php');
							
							foreach ($replies as $r)
							{
								$r->time_replied = readable_time($r->time_replied);
								$r->reply = htmlspecialchars($r->reply);
							
								// Get attachments
								$attachments = array();
								
								$relpath = 'thread' . CURRENT_THREAD_ID . '/reply' . $r->reply_id;
								$path = $config['attachment_path'] . '/' . $relpath;
								if (file_exists($path))
								{	
									$files = array_slice(scandir($path), 2);
									if (count($files) > 0)
									{
										foreach ($files as $f)
										{
											$a = array(
												'name' => $f,
												'size' => convert_bytes(filesize($path . '/' . $f)),
												'link' => $config['http_host'].'/file.php?p=' . urlencode($relpath . '/' . $f),
											);
											$a['download_link'] = $a['link'].'&download';
											$attachments[] = $a;
										}
									}
								}
								
								$options = array();
								if (USER_IS_LOGGED_IN)
								{
									$options['Quote'] = '#" id="'.$r->reply_id.'" class="quote_reply';
									
									if ($sess_info['user_id'] == $r->user_id)
									{
										$options['Edit'] = $config['http_host'] . '/edit_reply.php?id=' . $r->reply_id;
									}
									if ((USER_IS_ADMIN || USER_IS_MODERATOR) && $r->first_post !== 1)
									{
										$options['Delete'] = $config['http_host'] . '/form/reply_action.php?a=delete&id=' . $r->reply_id;
									}
								}
								
								$theme->thread_reply($r, $attachments, $options);
							}
						}
						?>
					</div>
					
					<?php
					$pages = $pagination->get_number_of_pages();
					if ($pages > 1) {
						$page_link = $config['http_host'] . '/view.php?id='.CURRENT_THREAD_ID;
						$current_page = $pagination->get_current_page();
						
						$theme->pages($pages, $current_page, $page_link);
					}
					?>
					
					<?php if (USER_IS_LOGGED_IN) : ?>
					<form id="reply_form" action="<?php echo $config['http_host'] . '/form/reply_thread.php?id=' . CURRENT_THREAD_ID; ?>" method="POST" accept-charset="UTF-8" enctype="multipart/form-data">
						<label for="reply_field" class="bold">Reply to thread<?php
						if ($error->exists('reply_thread', 'reply'))
						{
							echo $error->get_message('reply_thread', 'reply');
						}
						?></label><br />
						<textarea name="reply" id="reply_field" rows="4"><?php
						if ($error->field_value_exists('reply_thread', 'reply')) {
							echo htmlspecialchars($error->get_field_value('reply_thread', 'reply'));
						}
						?></textarea><br />
						<div id="attachment_div">
							<label>Attachment:<?php
							if ($error->exists('reply_thread', 'attachments'))
							{
								echo $error->get_message('reply_thread', 'attachments');
							}
							?></label><br/>
							<input type="file" name="attachment[]" multiple="multiple" />
						</div>
						<input type="submit" value="Post Reply" class="button" />
					</form>
					<script>
					var quote_opts = document.getElementsByClassName('quote_reply'), i;
					for (i = 0; i < quote_opts.length; i++) {
						quote_opts[i].onclick = function(e) {
							var replyField = document.getElementById('reply_field');
							replyField.value += '[quote=' + this.id + '/]';
							replyField.focus();
							
							window.location.hash = 'reply_form';
							
							e.preventDefault();
						}
					}
					</script>
					<?php else : ?>
					<div id="log_in"><a href="./index.php?ref=<?php echo urlencode('view.php?id='.CURRENT_THREAD_ID); ?>">Log in</a> to reply to thread</div>
					<?php endif; ?>
				</div>
				<div class="clearfix"></div>
			</div>
			<?php
			$current_viewers = $views_db->get_current_viewers(CURRENT_THREAD_ID, USER_IS_LOGGED_IN ? $sess_info['user_id'] : NULL);
			$guests_count = $views_db->get_number_of_guests(CURRENT_THREAD_ID);
			
			if (USER_IS_LOGGED_IN) {
				if ($current_viewers == null) {
					$current_viewers = array('You');
				} else {
					array_unshift($current_viewers, 'You'); 
				}
			}
			
			if ($guests_count > 0 || $current_viewers !== NULL)
			{
				$theme->currently_viewing_thread($current_viewers, $guests_count);
			}
			?>
		<?php else: ?>
			<h3>Not found</h3>
			<p id="error_message">The thread does not exists or has been deleted.</p>
		<?php endif; ?>
	</div>
	<?php require('./includes/right-sections.php'); ?>
	<div class="clearfix"></div>
</div>
<?php require('./includes/footer.php'); ?>