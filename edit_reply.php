<?php
require('./config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if (!USER_IS_LOGGED_IN || !isset($_GET['id']))
{
    header('Location: ' . $config['http_host']);
    die();
}
define('CURRENT_REPLY_ID', $_GET['id']);

require($config['database_path'] . '/threads_replies_database.php');
$reply_db = new Threads_replies_database();
$reply = $reply_db->get_reply_by_id(CURRENT_REPLY_ID);

if ($reply === NULL)
{
	header('Location: ' . $config['http_host']);
	die();
}

define('CURRENT_THREAD_ID', $reply['thread_id']);

if ($reply['user_id'] !== $sess_info['user_id'])
{
	header('Location: ' . $config['http_host'] . '/view.php?id=' . CURRENT_THREAD_ID);
	die();
}

$title = 'Edit reply &ndash; ' . $config['site_name'];

$css_paths = array('css/edit_reply.css');

$theme_css = array('side_sections_right.css', 'button.css');

include($config['includes_path'] . '/header.php');
?>
<div id="content">
	<div id="left">
		<?php
		include($config['includes_path'] . '/category_hierarchy.php');
		get_category_hierarchy($reply['cat_id'], $reply['cat']);
		?>
		<h3><?php echo $reply['topic']; ?></h3>
		<form id="edit_form" action="<?php echo $config['http_host'] . '/form/reply_action.php?a=edit&id=' . CURRENT_REPLY_ID; ?>" method="POST" accept-charset="UTF-8">
			<div>
				<label for="reply" class="bold">Edit reply<?php
				if ($error->exists('edit_reply', 'reply')) echo ' <span class="error">' . $error->get_message('edit_reply', 'reply') . '</span>';
				?></label><br/>
				<textarea name="reply" id="reply" rows="14"><?php
				if ($error->field_value_exists('edit_reply', 'reply'))
				{
					echo $error->get_field_value('edit_reply', 'reply');
				}
				else
				{
					echo $reply['reply'];
				}
				?></textarea>
			</div>
			<div>
				<input type="submit" value="Edit Reply" class="button" />
			</div>
		</form>
	</div>
	<?php include($config['includes_path'] . '/right-sections.php'); ?>
	<div class="clearfix"></div>
</div>
<?php include($config['includes_path'] . '/footer.php'); ?>