</div>
		<footer class="container">
			<?php
			$lines = [
				"I've got a cunning plan.",
				"Damn the man! Thin the Michael!",
				"If you're not questioning my sanity, I'm not trying hard enough",
				"Sorry I'm late, I had to clean the carcas of the cardio beast I just slayed."
			];

			printf(
				'%s %s %s %s',
				'&copy;',
				date( 'Y' ),
				get_bloginfo( 'title' ),
				$lines[ rand(0,3) ]
			);

			?>
		</footer>

<?php if ( ! is_page( 'charts' ) ) { ?>
<script>
	var quotes = [
		'"Let\'s go row."',
		'"Come with me if you want to lift"',
		'"Gonna watch any football tonight?"'
	];
	var random = quotes[Math.floor(Math.random() * quotes.length)];
	document.querySelector('#quote').innerHTML = random;
</script>
<?php } ?>
<!-- Piwik -->
<script src="https://trexthepirate.com/traffic/piwik.js" async></script>
<script>
	try {
		var piwikTracker = Piwik.getTracker("https://trexthepirate.com/traffic/piwik.php", 2);
		piwikTracker.trackPageView();
		piwikTracker.enableLinkTracking();
	} catch (err) {}
</script>
<noscript><p>
	<img src="https://trexthepirate.com/traffic/piwik.php?idsite=2" style="border:0" alt="" />
</p></noscript>
<!-- End Piwik Tracking Code -->
<?php
	wp_footer();
?>
</body>
</html>
