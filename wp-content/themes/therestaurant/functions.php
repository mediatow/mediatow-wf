<?php global $theme_name, $theme_options, $color, $color_header, $pageid;
$theme_name = 'therestaurant';
$theme_options = get_option($theme_name);

// Translation
load_theme_textdomain($theme_name);

// Get theme colors from settings
if (!is_admin()) {
	include_once("csscolor.php");
	$color = new CSS_Color($theme_options['cp_color']);
	$color_header = new CSS_Color($theme_options['cp_color_header']);
}

//include the admin controlpanel
if (is_admin()) {
	require_once(TEMPLATEPATH . '/backend/controlpanel.php');
	$cpanel = new ControlPanel();
	require_once(TEMPLATEPATH . '/backend/customfields.php');
}

//load frontend scripts
function script_loader() {
	if (!is_admin()) {
		global $theme_options;
		wp_register_style('font2', 'http://fonts.googleapis.com/css?family=OFL+Sorts+Mill+Goudy+TT:regular,italic');
		wp_enqueue_style('font2');
		
		wp_register_style('style', get_bloginfo('stylesheet_url'));
		wp_enqueue_style('style');
		
		wp_deregister_script('jquery');
		wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
		wp_enqueue_script('jquery');
		
		if ($theme_options['cp_slider_show'] != 'disabled') {
			wp_register_script('nivo', get_bloginfo('template_directory').'/js/jquery.nivo.slider.js', array("jquery"));
			wp_enqueue_script('nivo');
			wp_register_style('nivostyle', get_bloginfo('template_directory').'/nivo-slider.css');
			wp_enqueue_style('nivostyle');
		}
		
		wp_register_script('cufon', get_bloginfo('template_directory').'/fonts/cufon-yui.js', array("jquery"));
		wp_enqueue_script('cufon');
		wp_register_script('font', get_bloginfo('template_directory').'/fonts/Junction_400.font.js', array("jquery"));
		wp_enqueue_script('font');
		
		wp_register_script('tweet', get_template_directory_uri().'/js/jquery.tweet.js', array("jquery"));
		wp_enqueue_script('tweet');
		
		wp_register_script('imgCenter', get_template_directory_uri().'/js/jquery.imgCenter.minified.js', array("jquery"));
		wp_enqueue_script('imgCenter');
		
		wp_register_script('fancybox', get_template_directory_uri().'/js/fancybox/jquery.fancybox-1.3.4.pack.js', array("jquery"));
		wp_enqueue_script('fancybox');
		wp_register_style('fancyboxstyle', get_template_directory_uri().'/js/fancybox/jquery.fancybox-1.3.4.css');
		wp_enqueue_style('fancyboxstyle');
	}
}
add_action('wp_enqueue_scripts', 'script_loader');

//load backend scripts
function admin_script_loader() {
	wp_register_style('controlpanel', get_bloginfo('template_directory').'/backend/controlpanel.css');
	wp_enqueue_style('controlpanel');
	
	wp_enqueue_script('jquery-ui-core');
	
	if ($_POST['page'] == $theme_name) {
		wp_register_style('mycolorpicker_style', get_bloginfo('template_directory').'/backend/css/colorpicker.css');
		wp_enqueue_style('mycolorpicker_style');
		wp_register_script('mycolorpicker', get_bloginfo('template_directory').'/backend/js/colorpicker.js');
		wp_enqueue_script('mycolorpicker');
		wp_register_script('mycolorpicker_eye', get_bloginfo('template_directory').'/backend/js/eye.js');
		wp_enqueue_script('mycolorpicker_eye');
		wp_register_script('mycolorpicker_utils', get_bloginfo('template_directory').'/backend/js/utils.js');
		wp_enqueue_script('mycolorpicker_utils');
		wp_register_script('mycolorpicker_layout', get_bloginfo('template_directory').'/backend/js/layout.js?ver=1.0.2');
		wp_enqueue_script('mycolorpicker_layout');
	}
	
	wp_register_script('upload', get_bloginfo('template_directory').'/backend/upload.js');
	wp_enqueue_script('upload');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_enqueue_style('thickbox');
	
	wp_enqueue_script('jquery-ui-sortable');
	wp_register_script('sortable', get_bloginfo('template_directory').'/backend/sortable.js');
	wp_enqueue_script('sortable');
}
add_action('admin_enqueue_scripts', 'admin_script_loader');

//register the main wp3 menu
if (function_exists('register_nav_menu')) {
	register_nav_menu('mainmenu', 'Main navigation menu');
}

//custom media image sizes
if ( function_exists( 'add_image_size' ) ) {
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'menucard_thumb', 9999, 100 );
	add_image_size( 'gallery_thumb', 260, 9999 );
	add_image_size( 'mediumwidth', 588, 9999 );
	add_image_size( 'fullwidth', 906, 9999 );
	add_image_size( 'slider', 1020, 9999 );
}

// Custom post type for slider
function custom_post_type_slider() {
  $labels = array(
    'name' => __('Slides', $theme_name ),
    'singular_name' => __('Slides', $theme_name ),
    'add_new' => __('Add New', $theme_name ),
    'add_new_item' => __('Add New Slide', $theme_name ),
    'edit_item' => __('Edit Slide', $theme_name ),
    'new_item' => __('New Slide', $theme_name ),
    'view_item' => __('View Slide', $theme_name ),
    'search_items' => __('Search Slides', $theme_name ),
    'not_found' =>  __('No slides found', $theme_name ),
    'not_found_in_trash' => __('No slides found in Trash', $theme_name ), 
    'parent_item_colon' => '',
    'menu_name' => __('Slides', $theme_name )

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => true,
    'capability_type' => 'post',
    'has_archive' => false, 
    'hierarchical' => false,
    'menu_position' => 20,
    'supports' => array('title','thumbnail','custom-fields')
  ); 
  register_post_type('slide',$args);
}
add_action('init', 'custom_post_type_slider');

//register the sidebars
if ( function_exists('register_sidebar') ) {
	/*
	register_sidebar(array(
		'name' => 'Frontpage top (max 3)',
		'id' => 'frontpage',
		'before_widget' => '<div class="sidepanel">',
		'after_widget' => '</div>',
		'before_title' => '<div class="ribbon-container"><div class="title-container"><div class="title"><div class="bar-left"></div><div class="bar-right"></div><h3 class="widget-title">',
		'after_title' => '</h3></div></div></div>'
	));
	*/
	register_sidebar(array(
		'name' => 'Frontpage bottom',
		'id' => 'frontpage2',
		'before_widget' => '<div class="sidepanel">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	));
	register_sidebar(array(
		'name' => 'Sidebar',
		'id' => 'sidebar',
		'before_widget' => '<div class="sidepanel">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	));
	register_sidebar(array(
		'name' => 'Footer',
		'id' => 'footer-widgets',
		'before_widget' => '<div class="sidepanel">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	));
	if ($theme_options['cp_sidebar_name[]']) {
		foreach ($theme_options['cp_sidebar_name[]'] as $i=>$value) {
			if ($value) {
				$title = $theme_options['cp_sidebar_name[]'][$i];
				register_sidebar(array(
					'name' => $title,
					'id' => $title,
					'before_widget' => '<div class="sidepanel">',
					'after_widget' => '</div>',
					'before_title' => '<h3>',
					'after_title' => '</h3>'
				));
			}
		}
	}
}

//load sidebar
function sidebar_pos() {
	global $post, $theme_options;
	$pageid = $post->ID;
	$sidebarpos = strtolower(get_post_meta($pageid, "sidebarpos", true));
	if ($sidebarpos == 'left' || $sidebarpos == 'right' || $sidebarpos == 'hidden') {
		$theme_options['cp_sidebar_position'] = $sidebarpos;
	}
	if ($theme_options['cp_sidebar_position'] == 'hidden') { 
		$pos = "fullwidthpage"; 
	} 
	elseif ($theme_options['cp_sidebar_position'] == 'left') {
		$pos = "sidebar_left";
	} 
	else { 
		$pos = "sidebar_right"; 
	}
	return $pos;
}

//create the breadcrumb
function the_breadcrumb() {
	if (!is_home()) {
		echo '<div id="breadcrumbs">';
		echo '<a href="'.get_bloginfo("url").'">' . __( 'Home', $theme_name ) . '</a>';
		if (is_category() || is_single()) {
			$cat_ID = get_query_var('cat');
			if ($cat_ID) {
				echo ' / '.get_cat_name($cat_ID);
			} else {
				$categories = array_reverse(get_the_category());
				foreach($categories as $category) {
					echo ' / <a href="'.get_category_link($category->term_id).'">'.__($category->cat_name).'</a>';
				} 	
			}
			if (is_single()) {
				echo ' / ';
				the_title();
			}
		} elseif (is_page()) {
			echo ' / ';
			echo the_title();
		}
		echo '</div>';
	}
}

//get custom page template path
function page_template($id = '') {
	if ($id) {
		$template = get_post_meta($id, "_wp_page_template", true);
	} else {
		global $wp_query;
		$template = get_post_meta($wp_query->post->ID, "_wp_page_template", true);
	}
	return $template;
}

//get nice pagination
function nicepagination() {
	global $paged, $wp_query;
	
	// get the number of pages
	if ( !$max_page ) {  
	  	$max_page = $wp_query->max_num_pages;
	}
	
	// if there's more then 1 page..
	if( $max_page > 1 ){
		if ( !$paged ) $paged = 1;
		
		echo '<div class="centering"><ul class="paging">';
		
		if ( $paged > 1 ) {
			echo '<li class="previous readmore"><a href="' . get_pagenum_link($paged-1) . '">' . __( 'Previous', $theme_name ) . '</a></li>';
		}
		
		for ( $i=1; $i <= $max_page; $i++ ) {
			echo '<li class="readmore';
			if ( $i == $paged ) echo ' active';
			echo '">';
			/*if ( $i != $paged )*/ echo '<a href="' . get_pagenum_link($i) . '">';
			echo $i;
			/*if ( $i != $paged )*/ echo '</a>';
			echo '</li>';
		}
		
		if ( $paged < $max_page ) {
			echo '<li class="next readmore"><a href="' . get_pagenum_link($paged+1) . '">' . __( 'Next', $theme_name ) . '</a></li>';
		}
		
		echo '</ul></div>';
	}
}

// Enable shortcodes in widgets and wrap widget content in div
add_filter('widget_text', 'do_shortcode');

// Force wordpress to use full image quality (no compression)
function jpeg_full_quality( $quality ) { 
	return 100;
}
//if ($theme_options['cp_img_quality']) {
	add_filter( 'jpeg_quality', 'jpeg_full_quality' );
//}

// Get stars
function get_stars() {
	global $theme_options;
	$numberStars = $theme_options['cp_stars'];
	if ($theme_options['cp_stars'] > 0) {
		for ($x=0; $x<$numberStars; $x++) {
			echo '<div class="star"></div>';
		}
	}
}

// Redirect to a gallery/blog/menucard category template
function custom_category_template($cat_template) {
	global $wp_query, $wpdb, $pageid;
	$sql = "SELECT DISTINCT p.ID, pm.meta_value AS page_template, pm2.meta_value AS mc_categories FROM " . $wpdb->posts . " AS p ";
	$sql .= "LEFT JOIN (SELECT post_id, meta_value FROM " . $wpdb->postmeta . " WHERE meta_key = '_wp_page_template') ";
	$sql .= "AS pm ON p.ID = pm.post_id ";
	$sql .= "LEFT JOIN (SELECT post_id, meta_value FROM " . $wpdb->postmeta . " WHERE meta_key = 'categories') ";
	$sql .= "AS pm2 ON p.ID = pm2.post_id ";
	$sql .= "WHERE pm.meta_value = 'gallery.php' OR pm.meta_value = 'blog.php' OR pm.meta_value = 'menucard.php' ";
	$rows = $wpdb->get_results($sql,OBJECT);
	
	$catID = get_query_var('cat');
	foreach($rows as $row) {
		$categories = explode(',', $row->mc_categories);
		if (in_array($catID, $categories)) {
			$path = TEMPLATEPATH . "/" . $row->page_template;
			if ( file_exists($path) ) {
				$cat_template = $path;
				$pageid = $row->ID;
			}

		}
	}
	
	return $cat_template;
}
add_filter('category_template', 'custom_category_template');

// Shortcodes
include_once('shortcodes.php');

// Custom widgets
include_once('widgets/rf_maps_widget.php');
include_once('widgets/rf_featured_content_widget.php');
include_once('widgets/rf_twitter_widget.php');
include_once('widgets/rf_contact_widget.php');
include_once('widgets/rf_latest_posts_widget.php');
include_once('widgets/rf_line_widget.php');
include_once('widgets/rf_user_friendly_search_widget.php');
?>