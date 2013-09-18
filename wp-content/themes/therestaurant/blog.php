<?php
/*
Template Name: Blog
*/
?>
<?php global $more, $post, $wpdb, $pageid;
get_header();
if (!$pageid) {
	$pageid = $post->ID;
}
if (is_category() ) {
	$cat_ID = get_query_var('cat');
}
$pagetitle = get_the_title($pageid);
$categories = get_post_meta($pageid, "categories", true);
$number = get_post_meta($pageid, "number", true);
$img_height = get_post_meta($pageid, "imageheight", true);
?>

<div id="content-top"></div>

<div id="content-border">
    <div id="content">
    	<div class="ribbon-container">
            <div class="title-container">
                <div class="title">
                    <div class="bar-left"></div>
                    <div class="bar-right"></div>
                    <h1 class="post-title"><?php echo $pagetitle; ?></h1>
                </div>
            </div>
        </div>
        <div id="post-container">
            <?php /*query_posts(array(
				'post__in' => get_option('sticky_posts'),
				'cat' => $categories
			));
			while( have_posts() ) : the_post(); $more = 0;
				include('post-layout.php');
            endwhile;
			wp_reset_query();*/
			
			$paged = intval(get_query_var('paged'));
            query_posts(array(
				'post__not_in' => get_option('sticky_posts'),
				'cat' => $categories,
				'posts_per_page' => $number,
				'paged' => $paged
			));
            while( have_posts() ) : the_post(); $more = 0;
				include('post-layout.php');
            endwhile; ?>
            <div id="nicepagination">
				<?php nicepagination(); ?>
            </div>
            <?php wp_reset_query(); ?>
        </div>
        <?php get_sidebar() ?>
    </div><!-- #content -->
</div>
<div id="content-bottom"></div>

<?php get_footer() ?>