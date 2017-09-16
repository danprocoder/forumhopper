<?php
require('./includes/setup_inc.php');

define( 'SETUP_TOTAL_STEPS', 4 );

define('PAGE_URL', $config['http_host'].'/admin/setup.php');

if (!file_exists(TMP_DIR))
{
	mkdir(TMP_DIR);
}

// used to track user's current steps and stores error messages.
$setup = null;
if (isset($_COOKIE['setup']) && file_exists(TMP_DIR.'/'.$_COOKIE['setup'].'.txt'))
{
	$setup = unserialize(file_get_contents(TMP_DIR.'/'.$_COOKIE['setup'].'.txt'));
}
else
{
	$tmp_filename = md5(uniqid());
	setcookie('setup', $tmp_filename, 0);
	
	$setup = array('current_step' => 1);
	file_put_contents(TMP_DIR . "/$tmp_filename.txt", serialize($setup));
}

define('CURRENT_STEP', $setup['current_step']);

function steps() {
	$steps = array();
	for ($i = 1; $i <= SETUP_TOTAL_STEPS; $i++) {
		$step = '<span class="step';
		if ($i == CURRENT_STEP) {
			$step .= ' active';
		}
		$step .= "\">STEP $i";
		$step .= '</span>';
		
		$steps[] = $step; 
	}
	echo implode('<span class="sep"> &rsaquo; </span>', $steps);
}

function error($key, $tag)
{
	global $setup;
	if (isset($setup['error'][$key]))
	{
		if ($tag != '')
		{
			echo '<'.$tag.' class="error">';
		}
		echo $setup['error'][$key];
		if ($tag != '')
		{
			echo '</'.$tag.'>';
		}
	}
}

function field_value($key)
{
	global $setup;
	if (isset($setup['form_data'][$key]))
	{
		echo ' value="'.$setup['form_data'][$key].'"';
	}
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php
		$titles = array(
			'Database setup',
			'Create Admin Account',
			'Create Site',
			'Create Forum Categories'
		);
		echo $titles[CURRENT_STEP - 1];
		?> &ndash; ForumHopper</title>
		
		<link rel="stylesheet" type="text/css" href="css/setup_main.css" />
		<link rel="stylesheet" type="text/css" href="css/setup-2.css" />
		<?php if (CURRENT_STEP == 4) : ?>
		<link rel="stylesheet" type="text/css" href="css/setup_4.css" />
		<?php endif; ?>
    </head>
    <body>
        <div>
			<div id="header">
				<h1>ForumHopper</h1>
				<div id="steps"><?php steps(); ?></div>
			</div>
			<?php if (CURRENT_STEP == 1) : ?>
            <form action="<?php echo $config['http_host'].'/admin/form/setup.php'; ?>" method="POST" accept-charset="UTF-8">
				<div id="instruction">Enter details needed for database connection.<?php error('database', 'div'); ?></div>
                <div>
					<label for="host">Database Host<?php error('host', 'span'); ?></label><br/>
                    <input type="text" name="host" id="host" class="textfield"<?php field_value('host'); ?> />
                </div>
                <div>
                    <label for="user">Database Username<?php error('user', 'span'); ?></label><br/>
                    <input type="text" name="user" id="user" class="textfield"<?php field_value('user'); ?> />
                </div>
                <div>
                    <label for="pass">Database Password<?php error('pass', 'span'); ?></label><br/>
                    <input type="text" name="pass" id="pass" class="textfield"<?php field_value('pass'); ?> />
                </div>
                <div>
                    <label for="name">Database Name<?php error('name', 'span'); ?></label><br/>
                    <input type="text" name="name" id="name" class="textfield"<?php field_value('name'); ?> />
                </div>
                <div><input type="submit" value="Submit" /></div>
            </form>
			<?php elseif (CURRENT_STEP == 2) : ?>
			<form action="<?php echo $config['http_host'].'/admin/form/setup.php'; ?>" method="POST" accept-charset="UTF-8">
				<div id="instruction">Setup your admin account</div>
                <div>
                    <label for="username">Your Username<?php error('user', 'span'); ?></label><br/>
                    <input type="text" name="username" id="username" class="textfield"<?php field_value('user'); ?> />
                </div>
                <div>
                    <label for="pass">Your Password<?php error('pass', 'span'); ?></label><br/>
                    <input type="password" name="pass" id="pass" class="textfield" />
                </div>
                <div>
                    <label for="re_pass">Re-enter Your Password<?php error('re_pass', 'span'); ?></label><br/>
                    <input type="password" name="re_pass" id="re_pass" class="textfield" />
                </div>
                <div>
                    <label for="email">Your Email<?php error('email', 'span'); ?></label><br/>
                    <input type="text" name="email" id="email" class="textfield"<?php field_value('email'); ?> />
                </div>
				<div><input type="submit" value="Submit" /></div>
			</form>
			<?php elseif (CURRENT_STEP == 3) : ?>
			<form action="<?php echo $config['http_host'].'/admin/form/setup.php'; ?>" method="POST" accept-charset="UTF-8" enctype="multipart/form-data">
				<div id="instruction">Setup your site</div>
                <div>
                    <label for="site_name">Site Name<?php error('site_name', 'span'); ?></label><br/>
                    <input type="text" name="site_name" id="site_name" class="textfield"<?php field_value('site_name'); ?> />
                </div>
                <div>
                    <label for="site_logo">Select Site Logo<?php error('site_logo', 'span'); ?></label><br/>
                    <input type="file" name="site_logo" id="site_logo" class="textfield" />
                </div>
                <div>
                    <label>Select Site Theme<?php error('site_theme', 'span'); ?></label>
                    <div id="themes">
						<?php
						require($config['__'].'/Theme_manager.php');
						$themes = Theme_manager::get_themes();
						
						$selected = array_keys($themes)[0];
						if (isset($setup['form_data']) && array_key_exists('site_theme', $setup['form_data'])
							&& array_key_exists($setup['form_data']['site_theme'], $themes))
						{
							$selected = $setup['form_data']['site_theme'];
						}
						
						echo '<ul>';
						foreach ($themes as $th => $th_meta)
						{
							echo '<li><input type="radio" name="site_theme" value="'.$th.'" id="'.$th.'" onclick="onChangeTheme(\''.$th.'\')"';
							if ($th == $selected)
							{
								echo ' checked="checked"';
							}
							echo ' /><label for="'.$th.'">'.$th_meta->name.'</label></li>';
						}
						echo '</ul>';
						?>
						<img src="<?php echo $themes[$selected]->screenshot_url; ?>" id="theme_screenshot" />
						<div class="clearfix"></div>
						<script>
						<?php
						echo 'var thms = {';
						foreach ($themes as $th => $th_meta)
						{
							echo "'$th': '$th_meta->screenshot_url',";
						}
						echo '};';
						?>
						function onChangeTheme(th){var img=document.getElementById('theme_screenshot');img.src="#";img.src=thms[th];}
						</script>
					</div>
                </div>
				<div><input type="submit" value="Submit" /></div>
			</form>
			<?php elseif (CURRENT_STEP == 4) : ?>
			<form id="cat_form" action="<?php echo $config['http_host'].'/admin/form/setup.php'; ?>" method="POST" accept-charset="UTF-8" onsubmit="addToForm(event)">
				<div id="instruction">Setup Categories<div id="error" class="error"><?php error('category', ''); ?></div></div>
                <div id="table_wrapper">
					<div id="category_table"><div id="theader">
							<div class="col col1">Category</div>
							<div class="col col2">Description</div>
							<div class="clearfix"></div>
						</div><div id="body"></div>
					</div>
					<div id="info">Click on a row above to edit.</div>
				</div>
				<h4 id="add_cat_h4">Add Category</h4>
				<div>
					<label for="name">Category<span class="error error_name"></span></label><br/>
					<input type="text" name="name" id="name" class="textfield" />
				</div>
				<div>
					<label for="description">Description<span class="error error_description"></span></label><br/>
					<textarea name="description" id="description" class="textfield"></textarea>
				</div>
				<div>
					<input type="button" value="Add" name="addBtn" onclick="addCat()" />
					<input type="submit" id="finish_btn" value="Finish!" />
					<div class="clearfix"></div>
				</div>
				<script src="<?php echo ADMIN_URL; ?>/js/setup_4.js"></script>
			</form>
			<?php endif; ?>
		</div>
    </body>
</html>
<?php
// Delete error message here.
if (isset($_COOKIE['setup']))
{
	foreach (array('error', 'form_data') as $k)
	{
		if (isset($setup[$k]))
		{
			unset($setup[$k]);
		}
	}
	file_put_contents(TMP_DIR . '/' . $_COOKIE['setup'] . '.txt', serialize($setup));
}
?>
