<?php global $theme_name, $theme_options; ?>
<!-- <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> -->
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta name="description" content="<?php bloginfo('description') ?>" />
<meta name="generator" content="WordPress <?php bloginfo('version') ?>" /><!-- Please leave for stats -->
<title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> Blog Archive <?php } ?> <?php wp_title(); ?></title>
<link rel="icon" href="<?php echo $theme_options['cp_favicon']; ?>" type="image/x-icon" />
<link rel="shortcut icon" href="<?php echo $theme_options['cp_favicon']; ?>" type="image/x-icon" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> Comments RSS Feed" href="<?php bloginfo('comments_rss2_url') ?>"  />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head();
include_once('style.php');
include_once('font-replacement.php'); ?>

<!--[if lte IE 8]>
<style type="text/css">
.readmore, input[type="submit"], .arrow_bit_bottom, .arrow_bit_left, .arrow_bit_right {
	behavior: url(<?php echo get_template_directory_uri(); ?>/PIE.htc);
}
</style>
<![endif]-->
</head>

<?php
flush();

$bodyclasses = '';
global $sidebar_pos;
$sidebar_pos = sidebar_pos();
$bodyclasses .= $sidebar_pos . ' ';
if (is_singular() && !is_page_template('blog.php') && !is_page_template('gallery.php') && !is_page_template('menucard.php')) { $bodyclasses .= 'singular '; }
?>
<!--[if IE 7]> <body class="<?php echo $bodyclasses; ?> ie7"> <![endif]-->
<!--[if IE 8]> <body class="<?php echo $bodyclasses; ?> ie8"> <![endif]-->
<!--[if IE 9]> <body class="<?php echo $bodyclasses; ?> ie9"> <![endif]-->
<!--[if !(IE)]><!--> <body class="<?php echo $bodyclasses; ?>"> <!--<![endif]-->

<div id="wrapper">
	<div id="mainmenu">
    	<div class="wrapper">
            <div id="menu-left"></div>
            <div id="menu-right"></div>
            <div id="menu-container-border">
            	<div id="menu-shadow"></div>
                <div id="menu-container">
                	<div id="stars-left" <?php if ($theme_options['cp_stars'] < 1) { echo 'style="display:none;"'; } ?>>
                    	<?php get_stars(); ?>
                    </div>
                    <div class="wp_nav_menu">
                        <?php wp_nav_menu('main'); ?>
                    </div>
                    <div id="stars-right" <?php if ($theme_options['cp_stars'] < 1) { echo 'style="display:none;"'; } ?>>
                    	<?php get_stars(); ?>
                    </div>
            	</div>
            </div>
    	</div>
    </div>
	<div id="top">
    	<div class="wrapper">
            <div id="logo-container">
                <?php if (!is_front_page()) { ?>
                <h3 id="logo"><a href="<?php echo get_settings('home'); ?>" title="<?php bloginfo('name'); ?>"><?php if ($theme_options['cp_bloglogo']) { ?><img src="<?php echo $theme_options['cp_bloglogo']; ?>" alt="Logo" /><?php } else { ?><span id="text_logo"><?php bloginfo('name'); ?></span><?php } ?></a></h3>
                <?php if (!$theme_options['cp_bloglogo']) { ?><div id="description"><?php bloginfo('description') ?></div><?php }
                } else { ?>
                <h1 id="logo"><a href="<?php echo get_settings('home'); ?>" title="<?php bloginfo('name'); ?>"><?php if ($theme_options['cp_bloglogo']) { ?><img src="<?php echo $theme_options['cp_bloglogo']; ?>" alt="Logo" /><?php } else { ?><span id="text_logo"><?php bloginfo('name'); ?></span><?php } ?></a></h1>
                <?php if (!$theme_options['cp_bloglogo']) { ?><div id="description"><?php bloginfo('description') ?></div><?php }
				} ?>
            </div>
            <div id="tagline">
            	<p><?php echo stripslashes(__($theme_options['cp_tagline'])); ?></p>
            </div>
    	</div>
    </div>
	<div id="header">
    	<div id="header_pattern"></div>
    	<div id="slider" class="<?php if ($theme_options['cp_font'] == 'Junction') echo 'junction'; ?>">
			<?php if ($theme_options['cp_slider_show'] != 'disabled') {
				$slides = new WP_Query( array( 'post_type' => 'slide', 'posts_per_page' => 25 ) );
				if ($slides->have_posts()) {
					while ( $slides->have_posts() ) : $slides->the_post();
						if (has_post_thumbnail()) {
							$img_source = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'slider' );
							$url = get_post_meta(get_the_ID(), "slidelink", true);
							$newwindow = get_post_meta(get_the_ID(), "newwindow", true);
							$hidetitle = get_post_meta(get_the_ID(), "hidetitle", true);
							if ($url) { ?>
								<a href="<?php echo $url; ?>" <?php if ($newwindow) { echo 'target="_BLANK"'; } ?>>
							<?php } ?>
							<img <?php if ($hidetitle) { echo 'class="hidetitle"'; } ?> src="<?php echo $img_source[0]; ?>" title="<?php the_title() ?>" alt="<?php the_title() ?>" />
							<?php if ($url) { ?>
								</a>
							<?php }
						}
					endwhile;
				}
				wp_reset_query();
			} ?>
        </div>
	</div><!--  #header -->
	<div id="container">
    	<?php if ($theme_options['cp_slider_show'] != 'disabled') { ?>
    		<div id="slider_arrow" class="arrow_down">
            	<div class="arrow_bit_bottom"></div>
                <div class="arrow_bit_top"></div>
                <div class="arrow_bit_left"></div>
                <div class="arrow_bit_right"></div>
                <div class="arrow_bit_middle"></div>
            </div>
        <?php } ?>
        <div id="ornament"></div>
    	<div class="wrapper">
        	<?php if ($theme_options['cp_breadcrumbs']) {
        		the_breadcrumb();
			} ?>