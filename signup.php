<?php
require('./config/config_inc.php');
require($config['includes_path'] . '/constants.php');

$title = 'Sign Up &ndash; ' . $config['site_name'];

$css_paths[] ='css/signup.css';

$theme_css = array('side_sections_right.css', 'button.css');

include($config['includes_path'] . '/header.php');
?>
<div id="content">
	<div id="left">
		<h3>Sign Up for <?php echo $config['site_name']; ?></h3>
		<form action="<?php echo $config['http_host'] . '/form/signup.php'; ?>" id="signup_form" method="POST" accept-charset="UTF-8">
			<div>
				<label for="user" class="bold">Username<?php
				if ($error->exists('signup', 'username'))
					echo ' <span class="error">' . $error->get_message('signup', 'username') . '</span>';
				?></label><br/>
				<input type="text" name="user" id="user"<?php
				if ($error->field_value_exists('signup', 'username'))
				{
					echo ' value="' . $error->get_field_value('signup', 'username') . '"';
				}
				?> class="textfield" />
			</div>
			<div>
				<label for="email" class="bold">Email<?php
				if ($error->exists('signup', 'email'))
					echo ' <span class="error">' . $error->get_message('signup', 'email') . '</span>';
				?></label><br/>
				<input type="text" name="email" id="email"<?php
				if ($error->field_value_exists('signup', 'email'))
				{
					echo ' value="' . $error->get_field_value('signup', 'email') . '"';
				}
				?> class="textfield" />
			</div>
			<div>
				<label for="pass" class="bold">Password<?php
				if ($error->exists('signup', 'password'))
					echo ' <span class="error">' . $error->get_message('signup', 'password') . '</span>';
				?></label><br/>
				<input type="password" name="pass" id="pass"<?php
				if ($error->field_value_exists('signup', 'password'))
				{
					echo ' value="' . $error->get_field_value('signup', 'password') . '"';
				}
				?> class="textfield" />
			</div>
			<div>
				<label for="re_pass" class="bold">Re-enter password<?php
				if ($error->exists('signup', 're_password'))
					echo ' <span class="error">' . $error->get_message('signup', 're_password') . '</span>';
				?></label><br/>
				<input type="password" name="re_pass" id="re_pass"<?php
				if ($error->field_value_exists('signup', 're_password'))
				{
					echo ' value="' . $error->get_field_value('signup', 're_password') . '"';
				}
				?> class="textfield" />
			</div>
			<div id="accept_terms_div">
				<input type="checkbox" name="terms_accepted" id="terms_accepted" />
				<label for="terms_accepted">I have accepted the terms and conditions of <?php echo $config['site_name']; ?>.</label>
				<?php
				if ($error->exists('signup', 'terms_acceptance'))
				{
					echo ' <p><span class="error">' . $error->get_message('signup', 'terms_acceptance') . '</span></p>';
				}
				?>
			</div>
			<div><input type="submit" value="Sign Up" class="button" /></div>
		</form>
	</div>
	<?php
	$categories = $cat->get_categories(0);
	include($config['includes_path'] . '/right-sections.php');
	?>
	<div class="clearfix"></div>
</div>
<?php
$error->clear('signup');
include($config['includes_path'] . '/footer.php');
?>