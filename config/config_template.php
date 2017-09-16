<?php
define('FORUM_ROOT', dirname(dirname(__FILE__)));

$relpath = substr(FORUM_ROOT, strlen($_SERVER['DOCUMENT_ROOT']) + 1);
$config['http_host'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . $relpath;
unset($relpath);

define('ADMIN_DIR', FORUM_ROOT . '/admin');

define('TMP_DIR', FORUM_ROOT . '/tmp');

define('IMAGES_DIR', FORUM_ROOT . '/images');

$config['includes_path']     = FORUM_ROOT . '/includes';
$config['database_path']     = FORUM_ROOT . '/database';
$config['__']                = FORUM_ROOT . '/classes';
$config['attachment_path']   = FORUM_ROOT . '/uploads/attachments';
$config['user_picture_path'] = FORUM_ROOT . '/uploads/users';
$config['themes_path']       = FORUM_ROOT . '/themes';

$config['themes_url'] = $config['http_host'].'/themes';

define('ADMIN_URL', $config['http_host'] . '/admin');
define('IMAGES_URL', $config['http_host'] . '/images');

$config['min_thread_topic_length']      = 20;
$config['default_num_threads_per_page'] = 5;
$config['default_num_replies_per_page'] = 5;

$config['session_cookie_name']     = 'fsess';
$config['session_cookie_lifetime'] = 60 * 60 * 24 * 30; // 30 days

$config['user_photo_max_size']   = 1024 * 1024;
$config['user_photo_extensions'] = array('jpg', 'jpeg', 'png', 'gif');

$config['attachment_max']                  = 6; // Maximum number of files that can be attached.
$config['attachment_max_size']             = 1024 * 1024 * 4;
$config['attachment_supported_extensions'] = array('png', 'jpg', 'jpeg', 'gif');

$config['db_host'] = 'localhost';
$config['db_user'] = 'root';
$config['db_pass'] = '';
$config['db_name'] = 'forum';

$config['site_logo_max_width'] = 500;
$config['site_logo_max_height'] = 35;
$config['site_logo_max_filesize'] = 1024 * 1024 * 2;
$config['site_logo_url']          = IMAGES_URL.'/pnCFf_oUAq6_IRoaW4_d.png';

$config['site_theme'] = 'sapphire';

define('APOSTROPHE', '&apos;');

$config['site_name'] = 'Anonymous';
