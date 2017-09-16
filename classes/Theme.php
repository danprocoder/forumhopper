<?php
/**
 * Base class for all themes.
 */
abstract class Theme {
	
    abstract function thread_item($thread, $options);
	
    abstract function category_item($cat);
	
	abstract function sub_category_item($cat, $options);
	
	abstract function search_result_topic_item($topic, $options);
	abstract function search_result_reply_item($reply);
	
	abstract function thread_reply($reply, $attachments, $options);
	
	/**
	 * @param int    current number of pages.
	 * @param int    The current page number.
	 * @param string link for the current page user is viewing.
	 */
	abstract function pages($pages, $current_page, $page_link);
	
	abstract function side_sections($categories);
	
	abstract function currently_viewing_thread($users, $guests_count);
	
	abstract function category_hierarchy($hierarchy);
	
	abstract function nav_menu($menus, $active);
	abstract function nav_search($form_action, $query, $radios, $active_radio);
	
	abstract function tab_menu($menus, $active);
	
	abstract function side_topics($title, $topics);
	
	abstract function side_nav($menus, $active);
	
	function cp_user($user)
	{
		echo '<div class="user">';
						
		echo '<div>';
		echo '<img src="'.$user['picture_url'].'" alt="'.$user['username'].'" />';
		
		echo '<div class="beside_img">';
		echo '<p><a href="'.$user['profile_url'].'" class="username">'.$user['username'].'</a></p>';
		
		echo '<div>';
		foreach ($user['info'] as $k => $v)
		{
			$k = ucfirst($k);
			echo "<p><span>$k:</span> <i>$v</i></p>";
		}
		echo '</div>';
		
		echo '</div>';
		
		echo '<div class="clearfix"></div>';
		
		echo '<div class="options">'.implode(' | ', $user['options']).'</div>';
		
		echo '</div>';
		
		echo '</div>';
	}
}
