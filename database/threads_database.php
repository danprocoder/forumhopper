<?php
class Threads_database extends Database {
    
    function __construct()
    {
        parent::__construct();
    }
    
    function add_new_thread($user_id, $cat_id, $topic)
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->tables->threads}(user_id, cat_id, topic, time_created)VALUES(?,?,?,?)") or die($this->db->error);
		$stmt->bind_param('sisi', $user_id, $cat_id, $topic, time());
		$stmt->execute();
		
		return $this->last_insert_id();
    }
	
	function get_thread_meta($thread_id)
	{
		$stmt = $this->db->prepare("
			SELECT
				t.cat_id AS cat_id,
				u.username AS username,
				t.topic AS topic,
				t.time_created AS time_created,
				r.replies AS replies
			FROM {$this->tables->threads} AS t
			
			LEFT JOIN {$this->tables->users} AS u
			ON u.user_id=t.user_id
			
			INNER JOIN (
				SELECT thread_id, COUNT(reply_id) AS replies
				FROM {$this->tables->threads_replies}
				GROUP BY thread_id
			) AS r
			ON t.thread_id=r.thread_id
			
			WHERE t.thread_id=?") or die($this->db->error);
		$stmt->bind_param('i', $thread_id);
		$stmt->execute();
		
		return $this->select_one_row($stmt);
	}
	
	function get_recent_threads($cat_id, $current_thread_id, $limit)
	{
		$stmt = $this->db->prepare("
			SELECT
			  t.thread_id AS thread_id,
			  t.topic AS topic
			  
			FROM {$this->tables->threads} AS t
			
			INNER JOIN (
				SELECT
				  thread_id,
				  MAX(time_replied) AS last_replied
				FROM {$this->tables->threads_replies}
				GROUP BY thread_id
			) AS r
			ON r.thread_id=t.thread_id
			
			WHERE
			  t.cat_id=?
			  AND NOT t.thread_id=?
			
			ORDER BY r.last_replied DESC
			LIMIT ?") or die($this->db->error);
		$stmt->bind_param('iii', $cat_id, $current_thread_id, $limit);
		$stmt->execute();
		
		return $this->select_rows($stmt);
	}
	
	function get_thread_count_by_topic($topic)
	{
		$stmt = $this->db->prepare("SELECT COUNT(`thread_id`) AS count FROM {$this->tables->threads} WHERE `topic` LIKE ?") or die($this->db->error);
		$like = "%$topic%";
		$stmt->bind_param('s', $like);
		$stmt->execute();
		
		return $this->select_one_row($stmt)['count'];
	}
	
	function get_threads_by_topic($topic, $offset,$count)
	{
		$stmt = $this->db->prepare("
			SELECT
		      t.cat_id,
			  t.thread_id AS thread_id,
			  u.username AS op,
			  t.topic AS topic,
			  t.time_created AS time_created,
			  v.views AS views,
			  r.replies AS replies
			FROM {$this->tables->threads} AS t
			
			INNER JOIN {$this->tables->users} AS u
			ON u.user_id=t.user_id
			
			LEFT JOIN (
				SELECT thread_id, SUM(times_viewed) AS views
				FROM {$this->tables->threads_views}
				GROUP BY thread_id
			) AS v
			ON v.thread_id=t.thread_id
			
			INNER JOIN (
				SELECT
					thread_id,
					COUNT(reply_id) AS replies,
					MAX(time_replied) AS last_reply_sec
				FROM {$this->tables->threads_replies}
				GROUP BY thread_id
			) AS r
			ON r.thread_id=t.thread_id
			
			WHERE t.topic LIKE ?
			
			ORDER BY r.last_reply_sec DESC
			
			LIMIT ?, ?") or die($this->db->error);
		$like = '%'.$topic.'%';
		$stmt->bind_param('sii', $like, $offset, $count);
		$stmt->execute();
		return $this->select_rows($stmt);
	}
	
	function get_threads_by_cat_id($cat_id, $offset, $count)
	{
		return $this->_get_threads_by('t.cat_id', $cat_id, $offset, $count, 'r.last_replied DESC');
	}
	
	function get_threads_by_user_id($user_id, $offset, $count)
	{
		return $this->_get_threads_by('t.user_id', $user_id, $offset, $count, 't.time_created DESC');
	}
	
	/**
	 * @param count -1 Will select all items in the database.
	 */
	function _get_threads_by($field, $value, $offset, $count, $order_by)
	{
		$sql = "
			SELECT
				t.cat_id,
				t.thread_id,
				u.username AS op,
				t.topic,
				t.time_created,
				r.reply_count AS replies,
				v.views
			FROM {$this->tables->threads} AS t
			
			INNER JOIN {$this->tables->users} AS u
			ON u.user_id=t.user_id
			
			INNER JOIN (
				SELECT
					thread_id,
					COUNT(reply_id) AS reply_count,
					MAX(time_replied) AS last_replied
				FROM {$this->tables->threads_replies}
				GROUP BY thread_id
			) AS r
			ON r.thread_id=t.thread_id
			
			LEFT JOIN (
				SELECT thread_id, SUM(times_viewed) AS views
				FROM {$this->tables->threads_views}
				GROUP BY thread_id
			) AS v
			ON v.thread_id=t.thread_id
			
			WHERE $field=?
			ORDER BY $order_by";
		if ($count != -1)
		{
			$sql .= " LIMIT ?,?";
		}
		$stmt = $this->db->prepare($sql) or die($this->db->error);
		if ($count != -1)
		{
			$stmt->bind_param('iii', $value, $offset, $count);
		}
		else
		{
			$stmt->bind_param('i', $value);
		}
		$stmt->execute();
		
		return $this->select_rows($stmt);
	}
	
	function get_num_threads_by_user($user_id) {
		$stmt = $this->db->prepare("SELECT COUNT(thread_id) AS threads FROM {$this->tables->threads} WHERE user_id=?") or die($this->db->error);
		$stmt->bind_param('i', $user_id);
		$stmt->execute();
		
		return $this->select_one_row($stmt)['threads'];
	}
	
	function delete_thread($thread_id)
	{
		$stmt = $this->db->prepare("DELETE FROM {$this->tables->threads} WHERE thread_id=?") or die($this->db->error);
		$stmt->bind_param('i', $thread_id);
		$stmt->execute();
	}
}
