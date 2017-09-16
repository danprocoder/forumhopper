<?php
require('./config/config_inc.php');
require('./includes/constants.php');

if (isset($_GET['cat_id']))
{
	$meta = $cat->get_cat_by_id($_GET['cat_id']);
	if ($meta !== null) {
		define('CURRENT_CATEGORY_ID', $meta['id']);
		define('CURRENT_CATEGORY_NAME', $meta['name']);
		
		define('CURRENT_CATEGORY_THREAD_COUNT', $meta['threads'] === null ? 0 : $meta['threads']);
	
		$base_cat = $cat->get_base_cat(CURRENT_CATEGORY_ID);
		define('USER_IS_MODERATOR', $user_db->is_moderator($sess_info['user_id'], $base_cat));
	}
}
else
{
	header('Location: ' . $config['http_host']); die();
}

$pagination = null;
$title = ' &ndash; ' . $config['site_name'];

$user_settings_db = null;
if (defined('CURRENT_CATEGORY_ID')) {
	// Get number of threads to display per page.
	$num_threads_per_page = $config['default_num_threads_per_page'];
	if (USER_IS_LOGGED_IN)
	{
		// If user is logged in, load from user's settings.
		require($config['database_path'] . '/users_settings_database.php');
		$user_settings_db = new Users_settings_database($sess_info['user_id']);
		$num_threads_per_page = $user_settings_db->get_num_threads_per_page();
	}
	
	require($config['__'] . '/pagination.php');
	$pagination = new Pagination(CURRENT_CATEGORY_THREAD_COUNT, $num_threads_per_page);

	$title = CURRENT_CATEGORY_NAME.$title;
} else {
	$title = 'Page not found'.$title;
}

$css_paths[] = 'css/threads.css';

$theme_css[] = 'thread_item.css';
$theme_css[] = 'side_sections_right.css';
$theme_css[] = 'threads_toolbar.css';

require( $config['includes_path'] . '/header.php' );
?>
<div id="content">
	<div id="left">
		<?php if (defined('CURRENT_CATEGORY_ID')) : ?>
			<div id="top">
			<?php
			include($config['includes_path'] . '/category_hierarchy.php');
			get_category_hierarchy(CURRENT_CATEGORY_ID, CURRENT_CATEGORY_NAME);
			?>
			<h3><?php echo htmlspecialchars(CURRENT_CATEGORY_NAME) . ' (' . CURRENT_CATEGORY_THREAD_COUNT . ')'; ?></h3>
			<?php
			if (USER_IS_LOGGED_IN)
			{
				echo '<div id="options">';
				echo '<a href="' . $config['http_host'] . '/create_thread.php?cat_id=' . CURRENT_CATEGORY_ID . '">Create Thread</a>';
				
				if (USER_IS_ADMIN || USER_IS_MODERATOR)
				{
					echo '<a href="' . $config['http_host'] . '/admin/create_subcategory.php?cat_id=' . CURRENT_CATEGORY_ID . '">Create Subcategory</a>';
				}
				
				echo '</div>';
			}
			?>
			</div>
			<div id="threads">
			<?php
			// Load sub categories.
			$categories = $cat->get_categories(CURRENT_CATEGORY_ID);
			foreach ($categories as $c)
			{
				$c['category'] = htmlspecialchars($c['category']);
				$c['category_link'] = $config['http_host'] . '/threads.php?cat_id=' . $c['cat_id'];
				$c['description'] = htmlspecialchars($c['description']);
				
				$options = array();
				if (USER_IS_LOGGED_IN && (USER_IS_ADMIN || USER_IS_MODERATOR)) {
					$options['Edit'] = $config['http_host'] . '/admin/edit_subcategory.php?cat_id=' . $c['cat_id'];
					$options['Delete'] = $config['http_host'] . '/admin/form/subcategory.php?a=delete&cat_id=' . $c['cat_id'];
				}
				$theme->sub_category_item($c, $options);
			}
			
			require($config['database_path'] . '/threads_database.php');
			$threads_db = new Threads_Database();
			
			$offset = $pagination->get_start_index($pagination->get_current_page());
			$threads = $threads_db->get_threads_by_cat_id(CURRENT_CATEGORY_ID, $offset, $pagination->get_items_per_page());
			
			if ($threads !== null)
			{
				// Number of replies to display per page in view.php
				$num_replies_per_page = $config['default_num_replies_per_page'];
				if (USER_IS_LOGGED_IN)
				{
					$num_replies_per_page = $user_settings_db->get_num_replies_per_page();
				}
				
				require $config['includes_path'] . '/time.php';
				
				foreach ($threads as $t)
				{
					$t->thread_link = $config['http_host'] . '/view.php?id=' . $t->thread_id;
					$t->op_profile_link = $config['http_host'] . '/profile.php';
					if (USER_IS_LOGGED_IN && $t->op != USER_NICK) {
						$t->op_profile_link .= '?u=' . $t->op;
					}
					$t->topic = htmlspecialchars($t->topic);
					
					$t->time_created = readable_time($t->time_created);
					
					$links = array();
					if (USER_IS_LOGGED_IN && (USER_IS_ADMIN || USER_IS_MODERATOR)) {
						$links['Delete'] = $config['http_host'] . '/form/delete_thread.php?id=' . $t->thread_id;
					}
					
					if ($t->replies > $num_replies_per_page)
					{
						$links['Last &raquo;'] = $config['http_host'] . '/view.php?id=' . $t->thread_id . '&page=l';
					}
					
					$theme->thread_item($t, $links);
				}
			}
			else
			{
				echo '<div class="no_thread">No threads under ' . CURRENT_CATEGORY_NAME . '</div>';
			}
			?>
			</div>
			<?php
			$pages = $pagination->get_number_of_pages();
			if ($pages > 1) {
				$current_page = $pagination->get_current_page();
				$page_link = $config['http_host'] . '/threads.php?cat_id=' . CURRENT_CATEGORY_ID;
				
				$theme->pages($pages, $current_page, $page_link);
			}
			?>
		<?php else: ?>
			<h3>Page not found</h3>
			<p id="page_not_found">The page you requested was not found.</p>
		<?php endif; ?>
	</div>
	<?php include('./includes/right-sections.php'); ?>
	<div class="clearfix"></div>
</div>
<?php include('./includes/footer.php'); ?>