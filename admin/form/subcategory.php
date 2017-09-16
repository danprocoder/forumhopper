<?php
require('../../config/config_inc.php');
require($config['includes_path'] . '/constants.php');

$redirect_url = $config['http_host'];

if (USER_IS_LOGGED_IN)
{
	if (isset($_GET['cat_id'])
		&& preg_match('~^\d+$~', trim($_GET['cat_id']))
		&& trim($_GET['cat_id']) > 1)
	{
		$cat_meta = $cat->get_cat_by_id($_GET['cat_id']);
		if ($cat_meta !== NULL)
		{
			if (isset($_GET['a']) && in_array(strtolower($_GET['a']), array('add', 'edit', 'delete')))
			{
				$base_cat_id = $cat->get_base_cat($_GET['cat_id']);
				$is_moderator = $user_db->is_moderator($sess_info['user_id'], $base_cat_id);
				
				if (USER_IS_ADMIN || $is_moderator)
				{
					if ((strtolower($_GET['a']) === 'add' || strtolower($_GET['a']) === 'edit')
						&& (!isset($_POST['name']) || !isset($_POST['description'])))
					{
						$redirect_url .= '/threads.php?cat_id=' . $_GET['cat_id'];
					}
					else
					{
						switch(strtolower($_GET['a']))
						{
						case 'add':
							if (validate(trim($_POST['name']), trim($_POST['description'])))
							{
								$subcat_id = $cat->add_category($_GET['cat_id'], trim($_POST['name']), trim($_POST['description']));
								$redirect_url .= '/threads.php?cat_id=' . $subcat_id;
							}
							else
							{
								$error_key = $error->save();
								$redirect_url .= '/admin/create_subcategory.php?cat_id=' . $_GET['cat_id'] . '&e_k=' . $error_key;
							}
							break;
						case 'edit':
							if (validate(trim($_POST['name']), trim($_POST['description'])))
							{
								$cat->edit_category($_GET['cat_id'], trim($_POST['name']), trim($_POST['description']));
								$redirect_url .= '/threads.php?cat_id=' . $cat_meta['parent'];
							}
							else
							{
								$error_key = $error->save();
								$redirect_url .= '/admin/edit_subcategory.php?cat_id=' . $_GET['cat_id'] . '&e_k=' . $error_key;
							}
							break;
						case 'delete':
							$cat->delete_category($cat_meta['id']);
						
							// Redirect to parent category.
							$redirect_url .= '/threads.php?cat_id=' . $cat_meta['parent'];
							break;
						}
					}
				}
				else
				{
					$redirect_url .= '/threads.php?cat_id=' . $_GET['cat_id'];
				}
			}
			else
			{
				$redirect_url .= '/threads.php?cat_id=' . $_GET['cat_id'];
			}
		}
	}
}

function validate($cat_name, $description) {
	global $error;
	
	if ($cat_name === '') {
		$error->add('sub_cat', 'name', 'Category name cannot be empty');
	}
	
	if ($description === '') {
		$error->add('sub_cat', 'description', 'Description cannot be empty');
	}
	
	if ($error->exists_group('sub_cat')) {
		$error->add_field_value('subcat', 'name', $cat_name);
		$error->add_field_value('subcat', 'description', $description);
		
		return False;
	}
	
	return True;
}

header("Location: $redirect_url");
