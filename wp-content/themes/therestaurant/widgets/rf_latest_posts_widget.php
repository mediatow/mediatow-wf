<?php
/*
 * Plugin Name: Custom Latest Posts widget
 * Plugin URI: http://www.redfactory.nl
 * Description: A widget that displays the latest posts from a category.
 * Version: 1.0
 * Author: Red Factory
 * Author URI: http://www.redfactory.nl
 */



// Register widget
function rf_latest_posts_widget() {
	register_widget( 'rf_latest_posts_widget' );
}
add_action( 'widgets_init', 'rf_latest_posts_widget' );

// Widget class
class rf_latest_posts_widget extends WP_Widget {

	function rf_latest_posts_widget() {
		$widget_ops = array( 'classname' => 'rf_latest_posts_widget', 'description' => 'A widget that displays the latest posts from a category.');
		$control_ops = array( 'width' => 250, 'height' => 250, 'id_base' => 'rf_latest_posts_widget' );
		$this->WP_Widget( 'rf_latest_posts_widget', 'Custom Latest Posts Widget', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		global $wpdb, $theme_name;;
		extract( $args );

		// Our variables from the widget settings
		$title_markup = apply_filters('widget_title', $instance['title'] );
		$title = $instance['title'];
		$showimages = $instance['showimages'];
		$showdate = $instance['showdate'];
		$postcount = $instance['postcount'];
		$categories = implode(',',$instance['categories']);

		// Before widget (defined by themes)
		echo $before_widget;

		// Display the widget title if one was input (before and after defined by themes)
		echo $before_title . $title_markup . $after_title;
			
		// Display a containing div ?>
		<div class="rf_latest_posts_widget">
			<?php $count = 1;
			query_posts('&cat='.$categories.'&posts_per_page='.$postcount); ?>
			<div class="post-item <?php if ($count == 1) echo 'first'; ?>">

            <?php 
                while( have_posts() ) : the_post();
                $title = get_the_title();
                $url = get_permalink(); ?>
                <div class="each-post-item <?php if ($count == 1) echo 'first'; ?>">
                    <?php if ($showimages) { ?>
                    <div class="postimage-container">
                    	<?php $img_src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'fullwidth', false, '' ); ?>
                        <a class="hoverfade centerimg fancybox" rel="fancybox" href="<?php echo $img_src[0]; ?>" title="<?php echo $title; ?>" rel="bookmark">
                            <?php the_post_thumbnail('gallery_thumb'); ?>
                        </a>
                    </div>
                    <?php } ?>
                    <a href="<?php the_permalink() ?>" title="<?php the_title() ?>" rel="bookmark">
                        <h4>
                            <?php the_title() ?>
                        </h4>
                    </a>
                    <?php if ($showdate) { ?>
                    <div class="post-date">
                        <?php the_time('F j, Y'); ?>
                    </div>
                    <?php } ?>
                </div>
            	<?php $count++;
			endwhile;
            wp_reset_query(); ?>
		</div>
	</div>
		<?php 
		// After widget (defined by themes)
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		/* No need to strip tags */
		$instance['showimages'] = $new_instance['showimages'];
		$instance['showdate'] = $new_instance['showdate'];
		$instance['categories'] = $new_instance['categories'];
		$instance['postcount'] = $new_instance['postcount'];
		
		return $instance;
	}
	
	
	function form( $instance ) {
	
		/* Set up some default widget settings. */
		$defaults = array(
		'showimages' => 1,
		'postcount' => '5'
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
        <p>
        	<label for="<?php echo $this->get_field_id( 'showimages' ); ?>">Show images:</label>
        	<input class="" type="checkbox" <?php if ($instance['showimages'] == '1') { echo 'checked'; } ?> id="<?php echo $this->get_field_id( 'showimages' ); ?>" name="<?php echo $this->get_field_name( 'showimages' ); ?>" value="1" />
        </p>
        <p>
        	<label for="<?php echo $this->get_field_id( 'showdate' ); ?>">Show dates:</label>
        	<input class="" type="checkbox" <?php if ($instance['showdate'] == '1') { echo 'checked'; } ?> id="<?php echo $this->get_field_id( 'showdate' ); ?>" name="<?php echo $this->get_field_name( 'showdate' ); ?>" value="1" />
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'postcount' ); ?>">Number of posts:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'postcount' ); ?>" name="<?php echo $this->get_field_name( 'postcount' ); ?>" value="<?php echo $instance['postcount']; ?>" />
		</p>
        <div style="overflow:auto;width:auto;padding-right:20px;height:100px;border:1px solid #DFDFDF;display:inline-block;">
			<?php 
            $categories = get_categories('order_by=name&order=asc&hide_empty=0');
            foreach ($categories as $cat) { ?>
                <input type="checkbox" <?php if ($instance['categories'] && in_array($cat->term_id, $instance['categories'])) { echo 'checked'; } ?>  name="<?php echo $this->get_field_name( 'categories' ); ?>[]" id="<?php echo $this->get_field_id( 'categories' ); ?>" value="<?php echo $cat->term_id; ?>" /> <?php echo $cat->name; ?><br />
            <?php } ?>
        </div>
	<?php
	}
}
?>