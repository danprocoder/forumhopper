<form id="subcat_form" action="<?php echo $form_action; ?>" method="POST" accept-charset="UTF-8">
    <div>
        <label for="name" class="bold">Name<?php
		if ($error->exists('sub_cat', 'name')) echo $error->get_message('sub_cat', 'name');
		?></label><br/>
        <input type="text" class="textfield" name="name" id="name"<?php if (isset($subcat_name)) echo ' value="' . $subcat_name . '"'; ?> />
    </div>
    <div>
        <label for="description" class="bold">Description<?php
		if ($error->exists('sub_cat', 'description')) echo $error->get_message('sub_cat', 'description');
		?></label><br/>
        <textarea rows="5" id="description" name="description" class="textfield"><?php if (isset($subcat_description)) echo $subcat_description; ?></textarea>
    </div>
    <div><input type="submit" value="<?php echo $submit_button; ?>" class="button" /></div>
</form>
