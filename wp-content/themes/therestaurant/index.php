<?php global $more;
get_header();
$pagetitle = '';
if (is_category()) {
	$cat_ID = get_query_var('cat');
	$pagetitle = get_cat_name( $cat_ID );
}
?>
<div id="content-top"></div>

<div id="content-border">
    
    <div id="content">
    	<?php if ($pagetitle) { ?>
        <div class="ribbon-container">
            <div class="title-container">
                <div class="title">
                    <div class="bar-left"></div>
                    <div class="bar-right"></div>
                    <h1 class="post-title"><?php echo $pagetitle; ?></h1>
                </div>
            </div>
        </div>
        <?php } ?>
        <div id="post-container">
            <?php while( have_posts() ) : the_post(); $more = 0;
				include('post-layout.php');
            endwhile; ?>
			<div id="nicepagination">
				<?php nicepagination(); ?>
            </div>
        </div>
        <?php get_sidebar() ?>
    </div><!-- #content -->
</div>
<div id="content-bottom"></div>
<?php get_footer() ?>