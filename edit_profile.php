<?php
require('./config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if (!USER_IS_LOGGED_IN)
{
    header('Location: ' . $config['http_host'] . '/profile.php');
    die();
}

$title = 'Edit profile &ndash; '.$config['site_name'];
$css_paths = array('css/profile_layout.css', 'css/edit_profile.css');
$theme_css = array('side_sections_right.css', 'button.css');
include($config['includes_path'] . '/header.php');

$user_details = $user_db->get_user_meta(USER_NICK);
?>
<div id="content">
    <div id="left">
		<h3>Edit Profile</h3>
		<form action="<?php echo $config['http_host'].'/form/edit_profile.php'; ?>" method="POST" accept-charset="UTF-8" enctype="multipart/form-data" id="edit_form">
			<div id="left_small">
				<div id="profile_pic_wrapper"><img src="<?php echo $config['http_host'] . '/file.php?u=' . USER_NICK; ?>" /></div>
				<div id="file_chooser_wrapper">
					<label>Choose a profile picture</label><br/>
					<input type="file" name="profile_pic" />
					<?php
					if ($error->exists('edit_profile', 'profile_pic')) {
						echo '<div>'.$error->get_message('edit_profile', 'profile_pic').'</div>';
					}
					?>
				</div>
			</div>
			<div id="right_large">
				<div>
					<label for="sex" class="bold">Sex</label>
					<select name="sex" id="sex">
						<?php
						$sex = $user_details['sex'];
						
						$sexes = array('&mdash;', 'Female', 'Male');
						foreach ($sexes as $s)
						{
							echo '<option';
							if ($s == $sex)
							{
								echo ' selected="selected"';
							}
							echo '>'.$s.'</option>';
						}
						?>
					</select>
				</div>
				<div>
					<label for="#" class="bold">Birthdate<?php
					if ($error->exists('edit_profile', 'birthdate'))
					{
						echo $error->get_message('edit_profile', 'birthdate');	
					}
					?></label><br/>
					<select name="birthday" class="birthdate"><option value="0">Day</option><?php
					for ($i = 1; $i <= 31; $i++)
					{
						echo '<option value="'.$i.'">'.$i.'</option>';
					}
					?></select>
					<select name="birthmonth" class="birthdate">
						<?php
						$birthmonth = null;
						
						$months = array('Month', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
						for ($i = 0; $i < count($months); $i++)
						{
							echo '<option value="'.$i.'"';
							if ($birthmonth == $i)
							{
								echo ' selected="selected"';
							}
							echo '>'.$months[$i].'</option>';
						}
						?>
					</select>
					<select name="birthyear" class="birthdate"><option value="0">Year</option><?php
					for ($i = date('Y'); $i >= 1940; $i--)
					{
						echo '<option>'.$i.'</option>';
					}
					?></select>
				</div>
				<div>
					<label for="fullname" class="bold">Fullname<?php
					if ($error->exists('edit_profile', 'fullname'))
					{
						echo $error->get_message('edit_profile', 'fullname');
					}
					?></label><br/>
					<input type="text" name="fullname" id="fullname" class="textfield"<?php
					$fullname = null;
					
					if ($error->field_value_exists('edit_profile', 'fullname')) {
						$fullname = $error->get_field_value('edit_profile', 'fullname');
					} else {
						$fullname = $user_details['fullname'];
					}
					
					if ($fullname !== null)
					{
						echo ' value="'.$fullname.'"';
					}
					?> />
				</div>
				<div>
					<label for="location" class="bold">Location</label><br/>
					<input type="text" name="location" id="location" class="textfield"<?php
					$location = null;
					if ($error->field_value_exists('edit_profile', 'location')) {
						$location = $error->get_field_value('edit_profile', 'location');
					} else {
						$location = $user_details['location'];
					}
					
					if ($location !== null) {
						echo ' value="'.$location.'"';
					}
					?> />
				</div>
				<div><input type="submit" value="Save" class="button" /></div>
			</div>
		</form>
    </div>
    <?php include($config['includes_path'] . '/right-sections.php'); ?>
	<div class="clearfix"></div>
</div>
<?php include($config['includes_path'] . '/footer.php'); ?>
