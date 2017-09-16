<?php define('GUEST_ID', 0);

class Threads_views_database extends Database {
	
    function __construct()
    {
        parent::__construct();
    }
    
    function add_new_view($thread_id, $user_id)
    {
		$stmt = $this->db->prepare("INSERT INTO {$this->tables->threads_views}(thread_id, user_id, last_viewed, ip)VALUES(?,?,?,?)") or die($this->db->error);
		$ip = IP_ADDRESS;
		$stmt->bind_param('iiis', $thread_id, $user_id, time(), $ip);
		$stmt->execute();
    }
	
    function increase_view($thread_id, $user_id)
    {
		$query = "
			UPDATE {$this->tables->threads_views}
			SET
			  times_viewed=times_viewed+1,
			  last_viewed=?
			WHERE thread_id=? AND user_id=?";
		if ($user_id === GUEST_ID)
		{
			$query .= ' AND ip=?';
		}
		$stmt = $this->db->prepare($query) or die($this->db->error);
		if ($user_id === GUEST_ID) {
			$ip = IP_ADDRESS;
			$stmt->bind_param('iiis', time(), $thread_id, $user_id, $ip);
		} else
			$stmt->bind_param('iii', time(), $thread_id, $user_id);
		
		$stmt->execute();
    }
	
	/**
	 * Updates the time a thread was last viewed by a user.
	 *
	 * @param int The thread's database id.
	 * @param int The user's database id.
	 */
	function update_last_viewed($thread_id, $user_id) {
		$query = "UPDATE {$this->tables->threads_views} SET last_viewed=? WHERE thread_id=? AND user_id=?";
		if ($user_id === GUEST_ID) {
			$query .= ' AND ip=?';
		}
		$stmt = $this->db->prepare($query) or die($this->db->error);
		if ($user_id === GUEST_ID) {
			$ip = IP_ADDRESS;
			$stmt->bind_param('iiis', time(), $thread_id, $user_id, $ip);
		} else {
			$stmt->bind_param('iii', time(), $thread_id, $user_id);
		}
		$stmt->execute();
	}
    
    function user_has_viewed($thread_id, $user_id)
    {
		$stmt = null;
		if ($user_id === GUEST_ID)
		{
			$stmt = $this->db->prepare("SELECT view_id FROM {$this->tables->threads_views} WHERE thread_id=? AND user_id=? AND ip=?") or die($this->db->error);
			$ip = IP_ADDRESS;
			$stmt->bind_param('iis', $thread_id, $user_id, $ip);
		}
		else
		{
			$stmt = $this->db->prepare("SELECT view_id FROM {$this->tables->threads_views} WHERE thread_id=? AND user_id=?") or die($this->db->error);
			$stmt->bind_param('ii', $thread_id, $user_id);
		}
		$stmt->execute();
		
		return $this->select_one_row($stmt) !== NULL;
    }
	
	/**
	 * Returns the number of users not logged in current viewing the threads.
	 *
	 * @param int
	 */
	function get_number_of_guests($thread_id)
	{
		$stmt = $this->db->prepare("
			SELECT COUNT(view_id) AS guest_count
			FROM {$this->tables->threads_views}
			WHERE
			  user_id=?
			  AND thread_id=?
			  AND (? - last_viewed) < ?") or die($this->db->error);
		$guest_id = GUEST_ID;
		$sec_diff = 60;
		$stmt->bind_param('iiii', $guest_id, $thread_id, time(), $sec_diff);
		$stmt->execute();
		
		return $this->select_one_row($stmt)['guest_count'];
	}
	
	/**
	 * Returns a list of the usernames of the users currently viewing the thread.
	 *
	 * @param int
	 * @param int
	 */
	function get_current_viewers($thread_id, $exclude)
	{
		$query = "
			SELECT u.username
			FROM {$this->tables->threads_views} AS v
			
			INNER JOIN {$this->tables->users} AS u
			ON u.user_id=v.user_id
			
			WHERE v.thread_id=?
			  AND (?-v.last_viewed) < ?
			  AND NOT v.user_id=?";
		if ($exclude !== NULL)
			$query .= ' AND NOT v.user_id=?';
		$stmt = $this->db->prepare($query) or die($this->db->error);
		
		$sec_diff = 60;
		$guest_id = GUEST_ID;
		if ($exclude === null)
			$stmt->bind_param('iiii', $thread_id, time(), $sec_diff, $guest_id);
		else
			$stmt->bind_param('iiiii', $thread_id, time(), $sec_diff, $guest_id, $exclude);
		
		$stmt->execute();
		
		return $this->select_rows($stmt);
	}
}
