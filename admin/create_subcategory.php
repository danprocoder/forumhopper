<?php
require('includes/subcategory_init.php');

$title = 'Add Subcategory &ndash; ' . $config['site_name'];
$css_paths = array('admin/css/subcategory.css');

$theme_css = array('side_sections_right.css', 'button.css');

include($config['includes_path'] . '/header.php');
?>
<div id="content">
	<div id="left">
	<?php
	include($config['includes_path'] . '/category_hierarchy.php');
	get_category_hierarchy(CURRENT_CATEGORY_ID, CURRENT_CATEGORY_NAME);
	?>
	<h3>Create Subcategory</h3> 
	<?php
	$form_action = $config['http_host'] . '/admin/form/subcategory.php?a=add&cat_id=' . CURRENT_CATEGORY_ID;
	$submit_button = 'Create Subcategory';
	
	$subcat_name = '';
	$subcat_description = '';
	if ($error->field_value_exists('subcat', 'name'))
	{
		$subcat_name = $error->get_field_value('subcat', 'name');
	}
	
	if ($error->field_value_exists('subcat', 'description'))
	{
		$subcat_description = $error->get_field_value('subcat', 'description');
	}
	
	include('includes/subcategory_form.php');
	?>
	</div>
	<?php include($config['includes_path'] . '/right-sections.php'); ?>
	<div class="clearfix"></div>
</div>
<?php include($config['includes_path'] . '/footer.php'); ?>