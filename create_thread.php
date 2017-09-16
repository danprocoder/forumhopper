<?php
require('./config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if (!USER_IS_LOGGED_IN)
{
	header('Location: ' . $config['http_host']);
	die();
}

if (isset($_GET['cat_id']))
{
	$meta = $cat->get_cat_by_id($_GET['cat_id']);
	if ($meta != null)
	{
		define('CURRENT_CATEGORY_ID', $meta['id']);
		define('CURRENT_CATEGORY_NAME', $meta['name']);
	}
}
else
{
	header('Location: ' . $config['http_host']);
	die();
}

$title = 'Create New Thread &ndash; ' . $config['site_name'];

$css_paths = array('css/create_thread.css');

$theme_css = array('side_sections_right.css', 'button.css');

include($config['includes_path'] . '/header.php');
?>
<div id="content">
	<div id="left">
		<?php
		include('./includes/category_hierarchy.php');
		get_category_hierarchy(CURRENT_CATEGORY_ID, CURRENT_CATEGORY_NAME);
		?>
		<h3>Create New Thread</h3>
		<form id="thread_form" action="<?php echo $config['http_host'] . '/form/create_thread.php?cat_id=' . CURRENT_CATEGORY_ID; ?>" method="POST" accept-charset="UTF-8" enctype="multipart/form-data">
			<div class="group">
				<label for="topic" class="bold">Topic<?php
				if ($error->exists('thread', 'topic')) echo ' <span class="error">' . $error->get_message('thread', 'topic') . '</span>';
				?></label><br />
				<input type="text" name="topic" id="topic"<?php
				if ($error->field_value_exists('thread', 'topic')) echo ' value="' . $error->get_field_value('thread', 'topic') . '"';
				?> class="textfield" />
			</div>
			<div class="group">
				<label for="post" class="bold">Post<?php
				if ($error->exists('thread', 'body')) echo ' <span class="error">' . $error->get_message('thread', 'body') . '</span>';
				?></label><br />
				<textarea id="post" rows="9" name="post"><?php
				if ($error->field_value_exists('thread', 'body')) echo $error->get_field_value('thread', 'body');
				?></textarea>
			</div>
			<div class="group">
				<label class="bold">Add attachment(s)<?php
				if ($error->exists('thread', 'attachment')) echo $error->get_message('thread', 'attachment');
				?></label><br />
				<div class="field attachments">
					<input type="file" name="attachment[]" multiple="multiple" />
				</div>
			</div>
			<div class="group"><input type="submit" value="Create Thread" class="button" /></div>
		</form>
	</div>
	<?php include('./includes/right-sections.php'); ?>
	<div class="clearfix"></div>
</div>
<?php include('./includes/footer.php'); ?>