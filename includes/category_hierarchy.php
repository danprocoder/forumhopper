<?php
function get_category_hierarchy($cat_id, $cat_name) {
	$hierarchy = $GLOBALS['cat']->get_hierarchy($cat_id);
	
	$h['Home'] = $GLOBALS['config']['http_host'];
	foreach ($hierarchy as $parent) {
		$h[ htmlspecialchars($parent->name) ] = $GLOBALS['config']['http_host'] . '/threads.php?cat_id=' . $parent->id;
	}
	$h[ htmlspecialchars($cat_name) ] = $GLOBALS['config']['http_host'] . '/threads.php?cat_id=' . $cat_id;
	
	$GLOBALS['theme']->category_hierarchy($h);
}
?>