<?php
require('./config/config_inc.php');
require($config['__'].'/user_session.php');

(new User_Session())->end_current_session();

header('Location: ' . $config['http_host']);
