<?php
class Default_theme extends Theme {
	
	function thread_item($thread, $options) {
		echo '<div class="item thread_item">';
		
		echo '<div>';
		echo '<div class="topic"><a href="' . $thread->thread_link . '">' . $thread->topic . '</a> (' . $thread->replies . ')</div>';
		
		echo '<div class="options">';
		foreach ($options as $o => $link)
		{
			echo '<a href="' . $link . '">' . $o . '</a>';
		}
		echo '</div>';
		
		echo '<div class="clearfix"></div>';
		echo '</div>';
		
		echo '<div class="meta">';
		echo '<div class="creation_info">';
		if (isset($thread->op))
		{
			echo 'By <a href="' . $thread->op_profile_link . '">'.$thread->op.'</a>, ';
		}
		echo '<span>'.$thread->time_created.'</span></div>';
		echo '<div class="views_count">' . $thread->views . ' view' . ($thread->views > 1 ? 's' : '') . '</div>';
		echo '<div class="clearfix"></div>';
		echo '</div>';
		
		echo '</div>';
	}
	
	function category_item($category)
	{
		echo '<div class="item category_item">';
		
		echo '<div class="name">';
		echo '<a href="' . $category['category_link'] . '">' . $category['category'] . '</a> (' . $category['child_count'] . ')';
		echo '</div>';
		
		echo '<div class="description">' . $category['description'] . '</div>';
		
		echo '</div>';
	}
	
	function sub_category_item($category, $options)
	{
		echo '<div class="item sub_category_item">';
		
		echo '<div>';
		echo '<div class="name"><a href="'.$category['category_link'].'">' . $category['category'] . '</a> (' . $category['child_count'] . ')</div>';
		if ( ! empty($options))
		{
			echo '<div class="options">';
			foreach ($options as $o => $link)
			{
				echo '<a href="' . $link . '">' . $o . '</a>';
			}
			echo '</div>';
		}
		echo '<div class="clearfix"></div>';
		
		echo '</div>';
		
		echo '<div class="description">' . $category['description'] . '</div>';
		
		echo '</div>';
	}
	
	function search_result_topic_item($topic, $options)
	{
		$this->thread_item($topic, $options);
	}
	
	function search_result_reply_item($reply)
	{
		echo '<div class="reply">';
		
		echo '<div class="meta">';
		
		echo '<div class="topic"><a href="'.$reply->thread_link.'">'.$reply->topic.'</a></div>';
		echo '<div class="creation_info">';
		echo 'By <a href="'.user_profile_url($reply->username).'">'.$reply->username.'</a>: ';
		echo '<span>'.$reply->time_replied.'</span> ';
		echo '</div>';
		
		echo '</div>';
		
		echo '<div class="content">'.$reply->reply.'</div>';
		
		if (isset($reply->num_attachments) && $reply->num_attachments > 0)
		{
			echo '<div class="num_attachments">'.$reply->num_attachments.' attachment';
			if ($reply->num_attachments > 1)
			{
				echo 's';
			}
			echo '</div>';
		}
		
		echo '</div>';
	}
	
	function thread_reply($reply, $attachments, $options)
	{
		echo '<div class="reply" id="r' . $reply->reply_id . '">';
		
		echo '<div class="meta">';
		echo '<a href="'.user_profile_url($reply->username).'">'.$reply->username.'</a>: ';
		echo '<span>'.$reply->time_replied.'</span>';
		echo '</div>';
		
		echo '<div class="content">';
		echo '<div class="text">' . $reply->reply . '</div>';
		
		if ( ! empty($attachments))
		{
			echo '<div class="attachments"><h5>Attachments</h5><ul>';
			foreach ($attachments as $a)
			{
				echo '<li><a href="' . $a['link'] . '">' . $a['name'] . '</a> (' . $a['size'] . ')';
				echo '<a href="' . $a['download_link'] . '" class="download_link">Download</a>';
				echo '</li>';
			}
			echo '</ul></div>';
		}
		
		echo '<div class="options">';
		foreach ($options as $option => $href)
		{
			echo '<a href="'.$href.'">'.$option.'</a>';
		}
		echo '</div>';
		echo '</div>';
		
		echo '</div>';
	}

    function pages($pages, $current_page, $page_link)
	{
        echo '<div id="pages">';
		for ($i = 1; $i <= $pages; $i++)
		{
			if ($i == $current_page)
			{
				echo "<b class='page'>$i</b>";
			}
			else
			{
				echo '<a href="' . $page_link;
				if (preg_match('/\?.*$/', $page_link))
				{
					echo '&';
				}
				else
				{
					echo '?';
				}
				echo 'page=' . $i . '" class="page">' . $i . '</a>';
			}
		} 
		echo '</div>';
    }
	
	function side_sections($categories)
	{
		echo '<ul>';
		foreach ($categories as $c)
		{
			echo '<li><a href="' . $c['category_link'] . '">' . $c['category'] . '</a> (' . $c['child_count'] . ')</li>';
		}
		echo '</ul>';
	}
	
	function currently_viewing_thread($users, $guests_count)
	{
		echo '<div id="currently_viewing">';
		echo '<h5>Currently viewing this thread</h5>';
		
		$buf = '';
		for ($i = 0; $i < count($users); $i++)
		{
			$sep = '';
			if ($i == count($users) - 1 && $guests_count === 0)
			{
				$sep = $buf !== '' ? ' and ' : '';
			}
			else
			{
				$sep = $buf !== '' ? ', ' : '';
			}
			
			if (is_string($users[$i]))
			{
				$buf .= $users[$i];
			}
			else
			{
				$buf .= $sep . '<a href="'.$this->user_profile_url($users[$i]->username).'">' . $users[$i]->username . '</a>';
			}
		}
		
		if ($guests_count > 0)
		{
			$buf .= ($buf !== '' ? ' and ' : '') . $guests_count . ' guest'.($guests_count > 1 ? 's' : '');
		}
		
		echo "<p>$buf</p>";
		
		echo '</div>';
	}
	
	function nav_menu($menus, $active)
	{
		echo '<div id="nav_menu">';
		$this->_menu($menus, $active);
		echo '</div>';
	}
	
	function tab_menu($menus, $active)
	{
		echo '<div class="tab_menu">';
		$this->_menu($menus, $active);
		echo '</div>';
	}
	
	private function _menu($menus, $active)
	{
		foreach ($menus as $m => $url)
		{
			echo '<a href="' . $url . '"';
			if ($m == $active)
			{
				echo ' class="active"';
			}
			echo '>' . $m . '</a>';
		}
	}
	
	function category_hierarchy($hierarchy)
	{
		echo '<div id="cat-hierarchy">';
		
		$str = '';
		foreach ($hierarchy as $cat => $url)
		{
			$str .= '<a href="' . $url . '">' . $cat . '</a> &rsaquo; ';
		}
		echo substr($str, 0, count($str) - (strlen(' &rsaquo; ') + 1));
		
		echo '</div>';
	}
	
	function side_nav($menus, $active)
	{
		echo '<div id="side_nav">';
		foreach ($menus as $menu => $submenus)
		{
			echo '<div class="menu">';
			
			echo '<h5>'.$menu.'</h5>';
			
			echo '<ul>';
			foreach ($submenus as $name => $url)
			{
				echo '<li><a href="'.$url.'"';
				if ($name == $active)
				{
					echo ' class="active"';
				}
				echo '>'.$name.'</a></li>';
			}
			echo '</ul>';
			
			echo '</div>';
		}
		echo '</div>';
	}
	
	function side_topics($title, $topics)
	{
		echo '<div class="side_topics">';
		echo "<h5>$title</h5>";
		echo '<ul>';
		foreach ($topics as $t)
		{
			echo "<li><a href=\"$t->thread_link\">$t->topic</a></li>";
		}
		echo '</ul>';
		echo '</div>';
	}
	
	function nav_search($form_action, $query, $radios, $active_radio)
	{
		echo '<form id="nav_search" action="'.$form_action.'" method="GET" accept-charset="UTF-8">';
		
		echo '<div id="search-box-wrapper">';
		echo '<input type="text" name="q"'.(strlen($query) > 1 ? ' value="'.$query.'"' : ' placeholder="Search..."').' />';
		echo '<input type="submit" value="" id="search-btn" />';
		echo '<div class="clearfix"></div>';
		echo '</div>';
		
		echo '<div id="radio-wrapper">';
		
		foreach ( $radios as $value ) {
			echo '<span>';
			echo '<input type="radio" name="w" value="'.$value.'" id="'.$value.'"';
			if ( $value == $active_radio ) {
				echo ' checked="checked"';
			}
			echo ' />';
			echo '<label for="'.$value.'">'.ucfirst($value).'</label>';
			echo '</span>';
		}

		echo '</div>';
		
		echo '</form>';
	}
}
 