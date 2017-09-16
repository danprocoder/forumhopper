<div id="right">
	<div>
		<h4>Sections</h4>
		<?php
		$categories = $cat->get_categories(CATEGORY_NO_PARENT);
		
		for ($i = 0; $i < count($categories); $i++) {
			$categories[ $i ]['category'] = htmlspecialchars( $categories[ $i ]['category'] );
			$categories[ $i ]['category_link'] = $config['http_host'] . '/threads.php?cat_id=' . $categories[ $i ]['cat_id'];
		}
		$theme->side_sections($categories);
		?>
	</div>
</div>