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
		'"Gonna watch any football tonight?"',
		'"Let\'s go do lunges"',
		'Keep your core tight'
	];
	var random = quotes[Math.floor(Math.random() * quotes.length)];
	document.querySelector('#quote').innerHTML = random;
</script>
<?php } ?>

<!-- Matomo -->
<script type="text/javascript">
    var _paq = window._paq || [];
    /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    (function() {
        var u="//trexthepirate.com/traffic/";
        _paq.push(['setTrackerUrl', u+'matomo.php']);
        _paq.push(['setSiteId', '2']);
        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
    })();
</script>
<!-- End Matomo Code -->

<?php
	wp_footer();
?>
</body>
</html>
