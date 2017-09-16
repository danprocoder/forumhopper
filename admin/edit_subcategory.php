<?php
require('includes/subcategory_init.php');

$title = 'Edit Subcategory &ndash; ' . $config['site_name'];

$css_paths[] = 'admin/css/subcategory.css';

$theme_css = array('button.css', 'side_sections_right.css');

include($config['includes_path'] . '/header.php');
?>
<div id="content">
	<div id="left">
		<?php
		include($config['includes_path'] . '/category_hierarchy.php');
		get_category_hierarchy(CURRENT_CATEGORY_ID, CURRENT_CATEGORY_NAME);
		?>
		<h3>Edit: <?php echo CURRENT_CATEGORY_NAME; ?></h3>
		<?php
		$form_action = $config['http_host'] . '/admin/form/subcategory.php?a=edit&cat_id=' . CURRENT_CATEGORY_ID;
		$subcat_name = CURRENT_CATEGORY_NAME;
		$subcat_description = CURRENT_CATEGORY_DESCRIPTION;
		$submit_button = 'Edit Subcategory';
		include('includes/subcategory_form.php');
		?>
	</div>
	<?php include($config['includes_path'] . '/right-sections.php'); ?>
</div>
<?php include($config['includes_path'] . '/footer.php'); ?>