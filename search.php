<?php
require('./config/config_inc.php');
require($config['includes_path'] . '/constants.php');

$title = 'Search results &ndash; ' . $config['site_name'];

$css_paths = array('css/search.css');

$theme_css[] = 'side_sections_right.css';
if (isset($_GET['w']))
{
	if ($_GET['w'] == 'topics')
	{
		$theme_css[] = 'thread_item.css';
	}
	elseif ($_GET['w'] == 'posts')
	{
		$theme_css[] = 'thread_reply.css';
	}
}

include($config['includes_path'] . '/header.php');

function add_span_tag($str, $needle)
{
	return preg_replace('~(' . $needle . ')~i', '<span class="needle">$1</span>', $str);
}

/**
 * Returns the page a replies is in a thread.
 *
 * @param object 
 * @param int 
 * @param int 
 * @param int The number of replies to display in a page.
 */
function calc_page($db, $tid, $rid, $num_replies_per_page)
{
	$i = 0;
	do
	{
		$replies = $db->get_replies($tid, $i, $num_replies_per_page);
		$page = -1;
		foreach ($replies as $r)
		{
			if ($r->reply_id == $rid)
			{
				$page = ($i / $num_replies_per_page) + 1;
				break;
			}
		}
		if ($page != -1)
		{
			return $page;
		}
		$i += $num_replies_per_page;
	}
	while ($replies != null);
	
	return null;
}

function get_num_attachments($thread_id, $reply_id)
{
	global $config;
	$attachment_dir = $config['attachment_path'].'/thread'.$thread_id.'/reply'.$reply_id;
	if (file_exists($attachment_dir))
	{
		return count(scandir($attachment_dir)) - 2;
	}
	else
	{
		return 0;
	}
}

$num_threads_per_page = $config['default_num_threads_per_page'];
$num_replies_per_page = $config['default_num_replies_per_page'];
// If user is logged in, number of threads/replies to show per page
// will be gotten from user settings.
if (USER_IS_LOGGED_IN)
{
	require($config['database_path'] . '/users_settings_database.php');
	$user_setting_db = new Users_settings_database($sess_info['user_id']);
	
	$num_threads_per_page = $user_setting_db->get_num_threads_per_page();
	$num_replies_per_page = $user_setting_db->get_num_replies_per_page();
}
?>
<div id="content">
	<div id="left">
		<?php
		if (isset($_GET['q'])
			&& trim($_GET['q']) !== ''
			&& isset($_GET['w'])
			&& in_array(strtolower($_GET['w']), array('topics', 'posts')))
		{
			require $config['__'] . '/pagination.php';
			
			$pagination = null;
			
			$results = null;
			$result_total = 0;
			
			if ($_GET['w'] == 'topics')
			{
				require($config['database_path'] . '/threads_database.php');
				$threads_db = new Threads_database();
				
				// Get total number of results
				$result_total = $threads_db->get_thread_count_by_topic(trim($_GET['q']));
				
				$pagination = new Pagination($result_total, $num_threads_per_page);
				
				$offset = $pagination->get_start_index($pagination->get_current_page());
				$results = $threads_db->get_threads_by_topic(trim($_GET['q']), $offset, $pagination->get_items_per_page());
			}
			elseif ($_GET['w'] == 'posts')
			{
				require($config['database_path'] . '/threads_replies_database.php');
				$GLOBALS['replies_db'] = new Threads_replies_database();
				
				$result_total = $replies_db->get_reply_count_by_reply(trim($_GET['q']));
				
				$pagination = new Pagination($result_total, $num_replies_per_page);
				
				$offset = $pagination->get_start_index($pagination->get_current_page());
				$results = $replies_db->get_replies_by_reply(trim($_GET['q']), $offset, $pagination->get_items_per_page());
			}
			
			if ($results !== null)
			{
				require $config['includes_path'] . '/functions/time.php';
				
				echo '<h3>'.$result_total.' result'.($result_total > 1 ? 's' : '').' found</h3>';
				echo '<div id="results">';
				for ($i = 0; $i < count($results); $i++)
				{
					if ($_GET['w'] == 'topics')
					{
						$r = $results[$i];
						$r->topic = add_span_tag(htmlspecialchars($r->topic), htmlspecialchars($_GET['q']));
						$r->thread_link = $config['http_host'].'/view.php?id='.$r->thread_id;
						$r->op_profile_link = $config['http_host'].'/profile.php';
						$r->time_created = readable_time($r->time_created);
						if ((USER_IS_LOGGED_IN && $r->op != USER_NICK) || !USER_IS_LOGGED_IN)
						{
							$r->op_profile_link .= '?u=' . $r->op;
						}
						
						$options = array();
						
						$base_cat_id = $cat->get_base_cat($r->cat_id);
						$is_moderator = $user_db->is_moderator($sess_info['user_id'], $base_cat_id);
						
						if (USER_IS_LOGGED_IN && (USER_IS_ADMIN || $is_moderator))
						{
							$options['Delete'] = $config['http_host'].'/form/delete_thread.php?id='.$r->thread_id.'&ref=';
							
							// This will be used to redirect back to this page.
							$ref = 'search.php'.urlencode('?').'w='.urlencode($_GET['w'] . '&').'q='.urlencode($_GET['q']);
							if (($page = $pagination->get_current_page()) > 1)
							{
								$ref .= urlencode("&page=$page");
							}
							
							$options['Delete'] .= $ref;
						}
						
						if ($r->replies > $num_replies_per_page) {
							$options['Last &raquo;'] = $r->thread_link . '&page=l';
						}
						
						$theme->thread_item($r, $options);
					}
					elseif ($_GET['w'] == 'posts')
					{
						$r = $results[$i];
						$r->topic = htmlspecialchars($r->topic);
						$r->reply = add_span_tag(htmlspecialchars($r->reply), htmlspecialchars($_GET['q']));
						$r->time_replied = readable_time($r->time_replied);
						
						$r->thread_link = $config['http_host'].'/view.php?id=' . $r->thread_id;
						$thread_page = calc_page($replies_db, $r->thread_id, $r->reply_id, $num_replies_per_page);
						if ($thread_page > 1)
						{
							$r->thread_link .= '&page='.$thread_page;
						}
						$r->thread_link .= '#r'.$r->reply_id;
						
						$r->num_attachments = get_num_attachments($r->thread_id, $r->reply_id);
						
						$theme->search_result_reply_item($r);
					}
				}
				echo '</div>';
				
				$pages = $pagination->get_number_of_pages();
				if ($pages > 1)
				{
					$current_page = $pagination->get_current_page();
					$page_link = $config['http_host'] . '/search.php?w='.$_GET['w'].'&q='.$_GET['q'];
					
					$theme->pages($pages, $current_page, $page_link);
				}
			}
			else
			{
				echo '<h3>No result found for</h3>';
				echo '<div id="results">'.htmlspecialchars($_GET['q']).'</div>';
			}
		} else {
			echo '<h3>An error occurred</h3>';
		}
		?>
	</div>
	<?php include($config['includes_path'] . '/right-sections.php'); ?>
	<div class="clearfix"></div>
</div>
<?php include($config['includes_path'] . '/footer.php'); ?>
