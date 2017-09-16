<div id="footer">
	<div id="wrapper">
		<div id="left"<?php if (USER_IS_LOGGED_IN) echo ' class="float"'; ?>>
			<?php echo $config['site_name'] . ' &copy; ' . date('Y'); ?> <span class="bull">&bull;</span> <span id="powered">Powered by <a href="http://github.com/danprocoder/forumhopper">ForumHopper</a>
		</div>
		<?php if (USER_IS_LOGGED_IN) : ?>
		<div id="col3">
			<p>Logged in as <a href="<?php echo $config['http_host'] . '/profile.php'; ?>"><?php echo USER_NICK; ?></a></p>
			<p id="logout_link"><a href="<?php echo $config['http_host'] . '/logout.php'; ?>">Logout<a/></p>
		</div>
		<div class="clearfix"></div>
		<?php endif; ?>
	</div>
</div>
</body>
</html>