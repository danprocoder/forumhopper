<?php
$current_cat_meta = NULL;
if (isset($_GET['cat_id']))
{
	$current_cat_meta = $cat->get_cat_by_id($_GET['cat_id']);
}
elseif (isset($current_thread['cat_id']))
{
	$current_cat_meta = $cat->get_cat_by_id($current_thread['cat_id']);
}

if ($current_cat_meta !== NULL)
{
	define('CURRENT_CATEGORY_ID', $current_cat_meta['id']);
	define('CURRENT_CATEGORY_NAME', $current_cat_meta['name']);
}
?>
