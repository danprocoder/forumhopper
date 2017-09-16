<?php
require('./config/config_inc.php');
require($config['includes_path'] . '/constants.php');

$title = 'Welcome to ' . $config['site_name'];

$css_paths[] = 'css/index.css';

$theme_css[] =  'thread_item.css';

if ( ! USER_IS_LOGGED_IN)
{
	$css_paths[] = 'css/login.css';
	
	$theme_css[] = 'button.css';
}

include($config['includes_path'] . '/header.php');
?>
<div id="content">
	<div>
		<div id="left">
			<h2>Welcome to <?php echo $config['site_name']; ?></h2>
			<div id="categories">
			<?php
			foreach ($cat->get_categories(CATEGORY_NO_PARENT) as $c)
			{
				$c['category_link'] = $config['http_host'] . '/threads.php?cat_id=' . $c['cat_id'];
				$theme->category_item($c);
			}
			?>
			</div>
		</div>
		<?php if ( ! USER_IS_LOGGED_IN) : ?>
		<form action="<?php echo $config['http_host'] . '/form/auth.php' ?>" method="POST" accept-charset="UTF-8" id="right">
			<h3>Log in to participate</h3>
			<?php
			if ($error->exists('login', 'login'))
				echo '<p id="error_p"><span class="error">' . $error->get_message('login', 'login') . '<span></p>';
			?>
			<div>
				<label for="login" class="bold">Username or e-mail</label><br/>
				<input type="text" name="login" id="login"<?php
				if ($error->field_value_exists('login', 'login'))
					echo ' value="' . $error->get_field_value('login', 'login') . '"';
				?> class="textfield" />
			</div>
			<div>
				<label for="pass" class="bold">Password</label><br/>
				<input type="password" name="pass" id="pass" class="textfield" />
			</div>
			<div id="remember_div">
				<input type="checkbox" name="remember" id="remember" />
				<label for="remember">Keep me logged in</label>
			</div>
			<div><input type="submit" value="Log In" class="button" /></div>
			<div><a href="<?php echo $config['http_host']; ?>/signup.php">Sign up for <?php echo $config['site_name']; ?></a></div>
			<?php
			if (isset($_GET['ref']))
			{
				echo '<input type="hidden" name="ref" value="'.urlencode(trim($_GET['ref'])).'" />';
			}
			?>
		</form>
		<?php else : ?>
		<?php endif; ?>
		<div class="clearfix"></div>
	</div>
</div>
<?php include('./includes/footer.php'); ?>