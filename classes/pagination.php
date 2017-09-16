<?php
class Pagination {
	
	var $num_pages = 0;
	var $items_per_page = 0;

    function __construct($items_total, $items_per_page)
    {
		$this->items_per_page = $items_per_page;
		
        $this->num_pages = (int)($items_total / $items_per_page);
		if ($items_total % $items_per_page !== 0)
		{
			$this->num_pages++;
		}
    }
	
	function get_start_index($page)
	{
		return ($page - 1) * $this->items_per_page;
	}
	
	function get_number_of_pages()
	{
		return $this->num_pages;
	}
	
	function get_items_per_page()
	{
		return $this->items_per_page;
	}
	
	function get_current_page()
	{	
		$current_page = 1;
		if (isset($_GET['page']))
		{
			if (preg_match('~^[0-9]+$~', $_GET['page']) && $_GET['page'] <= $this->num_pages)
			{
				$current_page = $_GET['page'];
			}
			elseif ($_GET['page'] == 'l')
			{
				$current_page = $this->num_pages;
			}
		}
		
		return $current_page;
	}
}
