<?php

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

add_filter( 'widget_text', 'do_shortcode' );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'bootstrap', get_stylesheet_directory_uri() . '/assets/components/bootstrap/css/bootstrap.min.css' );
	wp_enqueue_style( 'bootstrap-responsive', get_stylesheet_directory_uri() . '/assets/components/bootstrap/css/bootstrap-responsive.min.css', [ 'bootstrap' ] );
	wp_enqueue_style( 'thinthemichael', get_stylesheet_uri() );
	$debug = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG === true ) ? '' : '.min';
	wp_enqueue_script( 'bootstrap-js', get_stylesheet_directory_uri() . "/assets/components/bootstrap/js/bootstrap{$debug}.js", ['jquery'], false, true );
} );

add_filter( 'wp_default_scripts', function( $scripts ) {
	if ( ! is_admin() ) {
		$scripts->remove( 'jquery' );
		$scripts->add( 'jquery', false, array( 'jquery-core' ) );
	}
} );

add_action( 'after_setup_theme', function() {
	add_theme_support( 'post-formats', [ 'quote', 'link' ] );
	add_theme_support( 'post-thumbnails' ); // This theme uses Featured Images
	add_theme_support( 'automatic-feed-links' ); // Add default posts and comments RSS feed links to <head>
	register_nav_menus( [ 'primary' => 'Primary Navigation' ] );
} );

add_filter( 'body_class', function( $classes ) {
    //WordPress global vars available.
    global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;
    if ( $is_lynx ) {
    	$classes[] = 'lynx';
    } elseif ( $is_gecko ) {
    	$classes[] = 'firefox';
    } elseif ( $is_opera ) {
    	$classes[] = 'opera';
    } elseif ( $is_NS4 ) {
    	$classes[] = 'ns4';
    } elseif ( $is_safari ) {
    	$classes[] = 'safari';
    } elseif ( $is_chrome ) {
    	$classes[] = 'chrome';
    } elseif ( $is_IE ) {
    	$classes[] = 'ie';
    } else {
		$classes[] = 'unknown';
	}

    if ( $is_iphone ) {
    	$classes[] = 'iphone';
    }

    if ( is_singular() && ! is_home() ) {
    	$classes[] = 'singular';
    }

    return $classes;
} );

add_filter( 'post_class', function( $classes ) {
	global $wp_query;

	if ( $wp_query->found_posts < 1 ) {
		return $classes;
	}

	if ( $wp_query->current_post == 0 ) {
		$classes[] = 'post-first';
	}

	if ( $wp_query->current_post % 2 ) {
		$classes[] = 'post-even';
	} else {
		$classes[] = 'post-odd';
	}

	if ( $wp_query->current_post == ( $wp_query->post_count - 1 ) ) {
		$classes[] = 'post-last';
	}

	return $classes;
} );

add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
	if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
		return;
	}

	$wp_admin_bar->add_node(
		[
			'parent' => 'site-name',
			'id'     => 'ab-plugins',
			'title'  => 'Plugins',
			'href'   => admin_url('plugins.php')
		]
	);
}, 35 );


add_action( 'widgets_init', function() {
	register_sidebar( [
		'name'          => 'Sidebar',
		'id'            => 'sidebar-1',
		'description'   => 'Add widgets here to appear in your sidebar on blog posts and archive pages.',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	] );
} );

function thin_the_posted_on() {
	printf( 'Posted on <a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="by-author"> by <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		sprintf( esc_attr__( 'View all posts by %s', 'twentyeleven' ), get_the_author() ),
		esc_html( get_the_author() )
	);
}

function thin_the_posted_in() {
	$cats = get_the_category_list( ', ' );
	if ( $cats ) {
		printf(
			'This entry was posted in %1$s. <a href="%2$s" rel="bookmark">Permalink</a>.',
			$cats,
			get_permalink()
		);
	}
}

// Remove query string from static files
function thin_the_cssjs_ver( $src ) {
	if ( strpos( $src, '?ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'thin_the_cssjs_ver', 10, 2 );
add_filter( 'script_loader_src', 'thin_the_cssjs_ver', 10, 2 );

add_action( 'init', function() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
} );

add_action( 'init', function() {

    // Remove the REST API endpoint.
    remove_action('rest_api_init', 'wp_oembed_register_route');

    // Turn off oEmbed auto discovery.
    // Don't filter oEmbed results.
    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);

    // Remove oEmbed discovery links.
    remove_action('wp_head', 'wp_oembed_add_discovery_links');

    // Remove oEmbed-specific JavaScript from the front-end and back-end.
    remove_action('wp_head', 'wp_oembed_add_host_js');
} );

add_action( 'wp_dashboard_setup', function() {
  global$wp_meta_boxes;

  unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins'] );
  unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary'] );
  unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary'] );
} );


function thin_the_atom_links() {
    $tmpl = '<link rel="%s" type="%s" title="%s" href="%s" />';

    printf(
        $tmpl,
        esc_attr( 'alternate' ),
        esc_attr( 'application/atom+xml' ),
        esc_attr( get_bloginfo( 'name' ) . '&raquo; Atom Feed link'  ),
		get_bloginfo( 'atom_url' )
    );
}
add_action( 'wp_head', 'thin_the_atom_links' );

function thin_the_add_atom_mime_support( $mimes ) {
	$mimes = array_merge(
		$mimes,
		array(
			'atom' => 'application/atom+xml',
		)
	);

	return $mimes;
}
add_filter( 'mime_types', 'thin_the_add_atom_mime_support' );

function thin_the_favicon() {
	?>
	<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>💪</text></svg>">
	<?php
}
add_action( 'wp_head', 'thin_the_favicon' );
add_action( 'admin_head', 'thin_the_favicon' );