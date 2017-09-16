<div id="toolbar">
    <form id="user_search_form" action="<?php echo cp_link($_GET['s'], array('sort_by'=>$sort_by)); ?>" method="POST" accept-charset="UTF-8">
        <input type="text" name="q" placeholder="Search username" <?php if (isset($_POST['q'])) echo ' value="'.post_clean('q').'"'; ?> class="textfield" />
        <input type="submit" value="" class="search_btn" />
        <div class="clearfix"></div>
    </form>
    <div id="sort">Sort: <span><?php
    $sort = array('az' => 'A - Z', 'za' => 'Z - A', 'time' => 'Time joined',);
    $links = array();
    foreach ($sort as $k => $v)
    {
		$url = cp_link($_GET['s'], array('sort_by'=>$k));
		if (isset($_POST['q']) && post_clean('q') != '')
		{
			$url .= '&q='.$_POST['q'];
		}
        $anchor = '<a href="'.$url.'"';
        if ($k == $sort_by)
        {
            $anchor .= ' class="active"';
        }
        $anchor .= '>'.$v.'</a>';
        $links[] = $anchor;
    }
    echo implode(' | ', $links);
    ?></span></div>
    <div class="clearfix"></div>
</div>
