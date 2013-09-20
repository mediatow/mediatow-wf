<?php global $more;
get_header(); ?>
    <!--<div id="content-top"></div>-->
	<?php if (is_active_sidebar('frontpage')) { ?>
	<div id="content-border">
        <div id="content" class="home">
        	<div class="widget-container">
				<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('frontpage') ) :
                endif; ?>
            </div>
        </div><!-- #content -->
	</div>
    <div id="content-bottom">
        <ul id="homePageCtas">
            <li id="mailingList">
                <h3>Mailing List</h3>
                <form action="">
                    <input type="text" placeholder="Name"> 
                    <input type="text" placeholder="Email">
                    <input type="submit">
                </form>
            </li>
            <li id="orderOnline"><h3>Order Online</h3></li>
            <li id="recentEvents">
                <h3>Events</h3>
                <ul id="events">
                    <?php
                        query_posts('category_id=18&posts_per_page=5');
                        if( have_posts() ) : while( have_posts() ) : the_post();
                    ?>
                    <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                        <?php endwhile; endif; ?>
                </ul>
            </li>
        </ul>
    </div><!-- #content-bottom -->
    <?php }
    if (is_active_sidebar('frontpage2')) { ?>
    <div id="widget-border">
        <div id="frontwidgets_home">
        	<div class="widget-container">
				<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('frontpage2') ) :
                endif; ?>
            </div>
        </div><!-- #content -->
	</div>
    <div id="widget-bottom"></div>
    <?php } ?>
<?php get_footer() ?>