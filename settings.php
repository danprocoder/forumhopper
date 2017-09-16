<?php
require('./config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if ( ! USER_IS_LOGGED_IN)
{
	header('Location: ' . $config['http_host']);
	die();
}

// User settings.
require($config['database_path'] . '/users_settings_database.php');
// $sess_info declared in 'constants.php'
$user_settings_db = new Users_settings_database($sess_info['user_id']);

$title = 'Settings &ndash; '.$config['site_name'];

$css_paths = array('css/settings.css');

$theme_css = array('side_nav.css', 'side_sections_right.css', 'button.css');

include($config['includes_path'] . '/header.php');

if (!isset($_GET['a']) || !in_array(strtolower($_GET['a']), array('ch_email', 'ch_password', 'thr_pagination')))
{
	$_GET['a'] = 'ch_email';
}
else
{
	$_GET['a'] = strtolower(trim($_GET['a']));
}
?>
<div id="content">
	<div id="left">
		<h2>Settings</h2>
		<div id="outer">
			<?php
			$side_nav = array(
				'Security' => array(
					'Change Email'    => "$config[http_host]/settings.php?a=ch_email",
					'Change Password' => "$config[http_host]/settings.php?a=ch_password",
				),
				'Threads' => array(
					'Pagination' => "$config[http_host]/settings.php?a=thr_pagination",
				),
			);
			$theme->side_nav($side_nav, $active);
			?>
			<div id="settings_form_wrapper">
			<?php if ($_GET['a'] === 'ch_email') : ?>
				<form action="<?php echo "$config[http_host]/form/settings.php?a=ch_email"; ?>" method="POST" accept-charset="UTF-8">
					<div>
						<label for="current_email" class="bold">Current Email<?php
						if ($error->exists('ch_email', 'current_email'))
						{
							echo $error->get_message('ch_email', 'current_email');
						}
						?></label><br/>
						<input type="text" name="current_email" id="current_email" class="textfield"<?php
						if ($error->field_value_exists('ch_email', 'current_email'))
						{
							echo ' value="' . $error->get_field_value('ch_email', 'current_email') . '"';
						}
						?> />
					</div>
					<div>
						<label for="new_email" class="bold">New Email<?php
						if ($error->exists('ch_email', 'new_email'))
						{
							echo $error->get_message('ch_email', 'new_email');
						}
						?></label><br/>
						<input type="text" name="new_email" id="new_email" class="textfield"<?php
						if ($error->field_value_exists('ch_email', 'new_email'))
						{
							echo ' value="' . $error->get_field_value('ch_email', 'new_email') . '"';
						}
						?> />
					</div>
					<div>
						<label for="pass" class="bold">Your password<?php
						if ($error->exists('ch_email', 'pass'))
						{
							echo $error->get_message('ch_email', 'pass');
						}
						?></label><br/>
						<input type="password" name="pass" id="pass" class="textfield" />
					</div>
					<div><input type="submit" class="button" value="Save" /></div>
				</form>
			<?php elseif ($_GET['a'] == 'ch_password') : ?>
				<form action="<?php echo $config['http_host'].'/form/settings.php?a=ch_password'; ?>" method="POST" accept-charset="UTF-8">
					<div>
						<label for="pass" class="bold">Current password<?php
						if ($error->exists('ch_password', 'current_password'))
						{
							echo $error->get_message('ch_password', 'current_password');
						}
						?></label><br/>
						<input type="password" name="pass" id="pass" class="textfield" />
					</div>
					<div>
						<label for="new_pass" class="bold">New password<?php
						if ($error->exists('ch_password', 'new_password'))
						{
							echo $error->get_message('ch_password', 'new_password');
						}
						?></label><br/>
						<input type="password" name="new_pass" id="new_pass" class="textfield" />
					</div>
					<div>
						<label for="re_new_pass" class="bold">Re-enter new password<?php
						if ($error->exists('ch_password', 're_new_password'))
						{
							echo $error->get_message('ch_password', 're_new_password');
						}
						?></label><br/>
						<input type="password" name="re_new_pass" id="re_new_pass" class="textfield" />
					</div>
					<div><input type="submit" class="button" value="Save" /></div>
				</form>
			<?php elseif ($_GET['a'] === 'thr_pagination') : ?>
				<form action="<?php echo "$config[http_host]/form/settings.php?a=thr_pagination" ?>" method="POST" accept-charset="UTF-8">
					<table cellspacing="0" cellpadding="0">
						<tr>
							<td><label class="bold" for="threads">Number of threads per page</label></td>
							<td class="col2"><select name="threads" id="threads"><?php
							$num_threads_per_page = $user_settings_db->get_num_threads_per_page();
							for ($i = 4; $i <= 12; $i++)
							{
								echo '<option';
								if ($i === $num_threads_per_page)
								{
									echo ' selected="selected"';
								}
								echo '>'.$i.'</option>';
							}
							?></select></td>
						</tr>
						<tr>
							<td><label class="bold" for="replies">Number of replies per page</label></td>
							<td class="col2"><select name="replies" id="replies"><?php
							$num_replies_per_page = $user_settings_db->get_num_replies_per_page();
							for ($i = 4; $i <= 12; $i++)
							{
								echo '<option';
								if ($i === $num_replies_per_page)
								{
									echo ' selected="selected"';
								}
								echo'>'.$i.'</option>';
							}
							?></select></td>
						</tr>
					</table>
					<input type="submit" value="Save" class="button" />
				</form>
			<?php endif; ?>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<?php include($config['includes_path'].'/right-sections.php'); ?>
	<div class="clearfix"></div>
</div>
<?php include($config['includes_path'] . '/footer.php'); ?>