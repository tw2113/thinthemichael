<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="theme-color" content="#ffffff">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
	<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<header>
			<div class="navbar navbar-inverse">
				<div class="navbar-inner">
					<div class="container-fluid">
						<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</a>
						<a class="brand" href="<?php bloginfo( 'url' ); ?>/"><?php echo get_bloginfo( 'title' ); ?></a>
						<div class="nav-collapse collapse">
							<?php wp_nav_menu(
								[
									'theme_location' => 'primary',
									'container' => 'ul',
									'menu_class' => 'nav',
								]
							); ?>
						</div><!--/.nav-collapse -->
					</div>
				</div>
			</div>
		</header>
		<div <?php post_class( [ 'mainContent', 'container' ] ); ?>>

