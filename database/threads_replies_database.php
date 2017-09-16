<?php require_once('database.php');

class Threads_replies_database extends Database {
	
    function __construct()
    {
        parent::__construct();
    }
	
	function add_reply($thread_id, $user_id, $reply, $is_first_post)
	{
		$stmt = $this->db->prepare("INSERT INTO {$this->tables->threads_replies}(thread_id, user_id, reply, time_replied, first_post)VALUES(?,?,?,?,?)");
		$first_post = $is_first_post ? 1 : 0;
		$stmt->bind_param('iisii', $thread_id, $user_id, $reply, time(), $first_post);
		$stmt->execute();
		
		return $this->last_insert_id();
	}
	
	function get_replies($thread_id, $offset, $count)
	{
		return $this->get_replies_by('r.thread_id=?', $thread_id, $offset, $count);
	}
	
	function edit_reply($reply_id, $reply)
	{
		$stmt = $this->db->prepare("UPDATE {$this->tables->threads_replies} SET reply=? WHERE reply_id=?");
		$stmt->bind_param('si', $reply, $reply_id);
		$stmt->execute();
	}
	
	function delete_reply($reply_id)
	{
		$stmt = $this->db->prepare("DELETE FROM {$this->tables->threads_replies} WHERE reply_id=? AND NOT first_post=?");
		$first_post = 1;
		$stmt->bind_param('ii', $reply_id, $first_post);
		$stmt->execute();
	}
	
	function get_reply_by_id($reply_id)
	{
		$stmt = $this->db->prepare("
			SELECT
				t.cat_id,
				c.cat,
				r.user_id,
				t.topic,
				r.thread_id,
				r.reply
			FROM {$this->tables->threads_replies} AS r
			INNER JOIN {$this->tables->threads} AS t
			ON t.thread_id=r.thread_id
			INNER JOIN {$this->tables->categories} AS c
			ON c.cat_id=t.cat_id
			WHERE r.reply_id=?
		");
		$stmt->bind_param('i', $reply_id);
		$stmt->execute();
		
		return $this->select_one_row($stmt);
	}
	
	function get_reply_count_by_reply($reply)
	{
		$stmt = $this->db->prepare("
			SELECT COUNT(`reply_id`) AS count
			FROM {$this->tables->threads_replies}
			WHERE `reply` LIKE ?
		");
		$like = "%$reply%";
		$stmt->bind_param('s', $like);
		$stmt->execute();
		
		return $this->select_one_row($stmt)['count'];
	}
	
	function get_replies_by_reply($reply, $offset, $count)
	{
		$stmt = $this->db->prepare("
			SELECT
				r.reply_id,
				r.thread_id,
				t.topic,
				r.reply,
				u.username,
				r.time_replied
			FROM {$this->tables->threads_replies} AS r
			
			INNER JOIN (SELECT thread_id, topic FROM {$this->tables->threads}) AS t
			ON t.thread_id=r.thread_id
			
			INNER JOIN (SELECT user_id, username FROM {$this->tables->users}) AS u
			ON u.user_id=r.user_id
			
			WHERE r.reply LIKE ?
			ORDER BY r.time_replied DESC
			LIMIT ?, ?
		");
		$like = "%$reply%";
		$stmt->bind_param('sii', $like, $offset, $count);
		$stmt->execute();
		
		return $this->select_rows($stmt);
	}
	
	function get_replies_by_user_id($user_id)
	{
		return $this->get_replies_by('r.user_id=?', $user_id, 0, -1);
	}
	
	private function get_replies_by($where, $val, $offset, $count, $order_by='')
	{
		$sql = "
			SELECT
				r.user_id,
				r.thread_id,
				r.reply_id,
				t.topic,
				r.reply,
				u.username,
				r.time_replied,
				r.first_post
			FROM {$this->tables->threads_replies} AS r
			
			INNER JOIN (SELECT thread_id, topic FROM {$this->tables->threads}) AS t
			ON t.thread_id=r.thread_id
			
			INNER JOIN (SELECT user_id, username FROM {$this->tables->users}) AS u
			ON u.user_id=r.user_id
			
			WHERE $where";
		if ($order_by != '')
		{
			$sql .= "ORDER BY $order_by";
		}
		if ($count != -1)
		{
			$sql .= ' LIMIT ?,?';
		}
		$stmt = $this->db->prepare($sql);
		if ($count != -1)
		{
			$stmt->bind_param('sii', $val, $offset, $count);
		}
		else
		{
			$stmt->bind_param('s', $val);
		}
		$stmt->execute();
		
		return $this->select_rows($stmt);
	}
}
