<?php
require('config/config_inc.php');
require($config['includes_path'] . '/constants.php');

if (!USER_IS_BANNED)
{
	header('Location: '.$config['http_host']);
	die();
}

$title = 'Error &ndash; '.$config['site_name'];

$css_paths[] = 'css/banned.css';

include($config['includes_path'] . '/header.php');
?>
<div id="content">
	<h2>Error</h2>
	<div>Sorry, you have been banned from <b><?php echo $config['site_name']; ?></b>.</div>
	<div><?php
	echo '<img src="'.$config['http_host'].'/file.php?u='.USER_NICK.'" alt="'.USER_NICK.'" />';
	?></div>
</div>
<?php include($config['includes_path'].'/footer.php'); ?>