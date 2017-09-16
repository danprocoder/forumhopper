<?php
require_once('database.php');

define('CATEGORY_NO_PARENT', 1);

class Thread_category extends Database {
	
	function __construct()
	{
		parent::__construct();
	}

	function add_category($parent, $category, $description)
	{
		$stmt = $this->db->prepare("INSERT INTO {$this->tables->categories}(parent,cat,description)VALUES(?,?,?)") or die($this->db->error);
		$stmt->bind_param('iss', $parent, $category, $description);
		$stmt->execute();
		
		return $this->last_insert_id();
	}
	
	function edit_category($cat_id, $name, $description)
	{
		$stmt = $this->db->prepare("UPDATE {$this->tables->categories} SET cat=?, description=? WHERE cat_id=?") or die($this->db->error);
		$stmt->bind_param('ssi', $name, $description, $cat_id);
		$stmt->execute();
	}
	
	function get_base_cat($cat_id)
	{
		$stmt = $this->db->prepare("
			SELECT parent
			FROM {$this->tables->categories}
			WHERE cat_id=?
		") or die($this->db->error);
		$stmt->bind_param('i', $cat_id);
		$stmt->execute();
		
		$row = $this->select_one_row($stmt);
		if ($row['parent'] == 1)
		{
			return $cat_id;
		}
		else
		{
			return $this->get_base_cat($row['parent']);
		}
	}
	
	function get_categories($parent)
	{
		$stmt = $this->db->prepare("
			SELECT c1.cat_id, c1.cat, c1.description, t.thread_count
			FROM {$this->tables->categories} AS c1
			LEFT JOIN (
				SELECT cat_id, COUNT(thread_id) AS thread_count
				FROM {$this->tables->threads}
				GROUP BY cat_id
			) AS t
			ON t.cat_id=c1.cat_id
			WHERE c1.parent=?
		");
		$stmt->bind_param('i', $parent);
		$stmt->execute();
		
		$stmt->store_result();
		
		$stmt->bind_result($cat_id, $cat, $description, $thread_count);
		
		$categories = array();
		while ($stmt->fetch() !== NULL)
		{
			$categories[] = array(
				'cat_id' => $cat_id,
				'child_count' => intval($thread_count),
				'category' => $cat,
				'description' => $description);
		}
		return $categories;
	}
	
	function get_hierarchy($cat_id)
	{
		$stmt = $this->db->prepare("
			SELECT
			c1.parent, c2.cat
			FROM categories AS c1
			INNER JOIN categories AS c2
			ON c2.cat_id=c1.parent
			WHERE c1.cat_id=?");
		
		$parent = $cat_id;
		$cat_name = '';
		
		$hierarchy = array();
		while($parent != null)
		{
			$stmt->bind_param('i', $parent);
			$stmt->execute();
			
			$stmt->store_result();
			$stmt->bind_result($parent, $cat_name);
			
			if ($stmt->fetch() !== NULL && $parent != 1)
			{
				$hierarchy[] = (object) array('id' => $parent, 'name' => $cat_name);
			}
		}
		
		return array_reverse($hierarchy);
	}
	
	function get_cat_by_name($name)
	{
		return $this->_get_cat_where('c.cat=?', $name);
	}
	
	function get_cat_by_id($cat_id)
	{
		return $this->_get_cat_where('c.cat_id=?', $cat_id);
	}
	
	function _get_cat_where($where, $value)
	{
		$stmt = $this->db->prepare("
			SELECT
			  c.cat_id AS id,
			  c.parent AS parent,
			  c.cat AS name,
			  c.description AS description,
			  t.threads AS threads
			FROM {$this->tables->categories} AS c
			
			LEFT JOIN (
				SELECT cat_id, COUNT(thread_id) AS threads
				FROM {$this->tables->threads}
				GROUP BY cat_id
			) AS t
			ON t.cat_id=c.cat_id
			
			WHERE ".$where) or die($this->db->error);
		$stmt->bind_param(is_int($value) ? 'i' : 's', $value);
		$stmt->execute();
		
		return $this->select_one_row($stmt);
	}
	
	function delete_category($cat_id)
	{
		$stmt = $this->db->prepare("DELETE FROM {$this->tables->categories} WHERE cat_id=?") or die($this->db->error);
		$stmt->bind_param('i', $cat_id);
		$stmt->execute();
	}
	
	function delete_all_except($cat_ids, $parent)
	{	
		$stmt = $this->db->prepare("
			DELETE FROM {$this->tables->categories}
			WHERE
			  NOT cat_id IN (".rtrim(str_repeat('?,', count($cat_ids)), ',').")
			  AND parent=?") or die($this->db->error);
		$params = array_merge(array(str_repeat('i', count($cat_ids)).'i'), $cat_ids, array($parent));
		for ($i = 1; $i < count($params); $i++)
		{
			$params[$i] =& $params[$i];
		}
		call_user_func_array(array($stmt, 'bind_param'), $params);
		$stmt->execute();
	}
}
