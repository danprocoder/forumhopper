<?php
/**
 * Blue theme class.
 *
 * @author Daniel Austin
 */
class Sapphire extends Theme {
	
	function thread_item($thread, $options) {
		echo '<div class="item thread_item">';
		
		echo '<div class="topic">';
		echo '<a href="'.$thread->thread_link.'" class="name">'.$thread->topic.'</a>';
		
		if (!empty($options))
		{
			echo '<div class="options">';
			$anchors = array();
			foreach ($options as $n => $u)
			{
				$anchors[] = "<a href='$u'>$n</a>";
			}
			echo implode(' | ', $anchors);
			echo '</div>';
		}
		echo '</div>';
		
		echo '<div class="creation_info">';
		if (isset($thread->op))
		{
			echo "<div class='user'>By <a href='$thread->op_profile_link'>$thread->op</a></div>";
		}
		echo "<div class='time'>$thread->time_created</div>";
		echo '</div>';
		
		echo '<div class="stats">';
		echo '<p>'.$thread->views.' <span>view'.($thread->views > 1 ? 's' : '').' &amp;</span></p>';
		echo '<p class="replies">'.$thread->replies.' <span>repl'.($thread->replies > 1 ? 'ies' : 'y').'</span></p>';
		echo '</div>';
		
		echo '<div class="clearfix"></div>';
		
		echo '</div>';
	}
	
	function category_item($category)
	{
		echo '<div class="item category_item">';
		
		echo '<div class="name">';
		echo '<a href="' . $category['category_link'] . '">' . $category['category'] . '</a> <span>'.$category['child_count'].'</span>';
		echo '</div>';
		
		echo '<div class="description">' . $category['description'] . '</div>';
		
		echo '</div>';
	}
	
	function sub_category_item($category, $options)
	{
		echo '<div class="item sub_category_item">';
		
		echo '<div>';
		echo '<div class="name"><a href="'.$category['category_link'].'">' . $category['category'] . '</a> <span>'.$category['child_count'].'</span></div>';
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
		return $this->_thread_reply($reply);
	}
	
	function thread_reply($reply, $attachments, $options)
	{
		return $this->_thread_reply($reply, $attachments, $options);
	}
	
	private function _thread_reply($reply, $attachments=array(), $options=array())
	{
		echo '<div class="reply" id="r' . $reply->reply_id . '">';
		
		echo '<div class="meta">';
		if (isset($reply->thread_link) && isset($reply->topic))
		{
			echo '<div class="topic"><a href="'.$reply->thread_link.'">'.$reply->topic.'</a></div>';
		}
		
		echo '<div'.(isset($reply->thread_link) && isset($reply->topic) ? ' class="row2"' : '').'>';
		echo '<a href="'.user_profile_url($reply->username).'">'.$reply->username.'</a>: ';
		echo $reply->time_replied;
		echo '</div>';
		
		echo '</div>';
		
		echo '<div class="content">';
		
		if (!empty($options))
		{
			echo '<div class="options">';
			foreach ($options as $name => $url)
			{
				echo '<a href="'.$url.'">'.$name.'</a>';
			}
			echo '</div>';
		}
		
		echo '<div class="text">'.$reply->reply.'</div>';
		
		if (!empty($attachments))
		{
			echo '<div class="attachments"><h5>Attachments</h5><ul>';
			foreach ($attachments as $a)
			{
				echo '<li>';
				echo '<div class="filename"><a href="'.$a['link'].'">'.$a['name'].'</a> ('.$a['size'].')</div>';
				echo '<div><a href="'.$a['download_link'].'" class="download_link">Download</a></div>';
				echo '</li>';
			}
			echo '</ul></div>';
		}
		
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
			echo '<li><a href="' . $c['category_link'] . '"><span class="wrapper">' . $c['category'] . ' <span class="threads_count">' . $c['child_count'] . '</span><div class="clearfix"></div></span></a></li>';
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
				$buf .= $sep . '<a href="'.user_profile_url($users[$i]->username).'">' . $users[$i]->username . '</a>';
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
		echo '<input type="text" name="q"'.(strlen($query) > 1 ? ' value="'.$query.'"' : ' placeholder="Search '.$active_radio.'..."').' />';
		
		echo '<div id="radio_dropdown_wrapper">';
		echo '<div id="down_arrow"></div>';
		
		echo '<ul id="dropdown">';
		foreach ($radios as $value) {
			echo '<li><input type="radio" id="'.$value.'" name="w" value="'.$value.'"';
			if ($value == $active_radio) {
				echo ' checked="checked"';
			}
			echo '/><label for="'.$value.'">'.ucfirst($value).'</label></li>';
		}
		echo '</ul>';
		echo '</div>';
		
		echo '<input type="submit" value="" id="search-btn" />';
		echo '<div class="clearfix"></div>';
		echo '</div>';
		
		echo '</form>';
	}
}
 