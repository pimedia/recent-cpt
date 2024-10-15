<?php
/*
Plugin Name: Recent CPT Widget Thumbnails
Plugin URI: http://www.parorrey.com
Description: A plugin that adds a widget that lists your most recent posts, cpt with excerpts & thumbnails. The number of posts and the character limit of the excerpts are configurable.
Version: 0.3.0
Text Domain: recent-cpt
Author: Ali Qureshi
Author URI: https://www.parorrey.com
License: GPL2

Release notes: WordPress 6.6 compatibility tested

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// Custom pi_rcwt_excerpt funtion that allows to limit the number of characters
function pi_rcwt_excerpt( $count ) {
	$permalink = get_permalink();
	$excerpt = get_the_content();
	$excerpt = strip_shortcodes( $excerpt );
	$excerpt = wp_strip_all_tags( $excerpt );
	$excerpt = substr( $excerpt, 0, $count );
	$excerpt = substr( $excerpt, 0, strripos( $excerpt, " " ));
	$excerpt = $excerpt.'<a href="'.$permalink.'">...</a>';
	return $excerpt;
}

class PI_RCWT_RecentCPTWidgetThumbnails extends WP_Widget {
	
	function __construct() {
		$widget_ops = array( 'classname' => 'recent-cpt-thumbnails', 'description' => esc_html(__( 'The most recent posts on your site with excerpts & thumbnails', 'recent-cpt' ) ) );
		parent::__construct( 'PI_RCWT_RecentCPTWidgetThumbnails', esc_html(__( 'Recent CPT Widget Thumbnails', 'recent-cpt' ) ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance[ 'title' ] ) ? esc_html(__( 'Recent Posts', 'recent-cpt' ) )  : $instance[ 'title' ]  );
		
		$allowed_tags = array(
        'div'   => array(
		   'id',
		   'class'),
        'p'   => array(
		   'id',
		   'class'),
		 'a'   => array(
		   'id',
		   'class'),
		 'h1'   => array(
		   'id',
		   'class'),
		 'h2'   => array(
		   'id',
		   'class'),
		 'h3'   => array(
		   'id',
		   'class'),
		 'h4'   => array(
		   'id',
		   'class'),
		 'aside'   => array(
		   'id',
		   'class')
    );

		echo wp_kses(
    $before_widget,
   $allowed_tags
);
		echo wp_kses(
    $before_title . $title . $after_title,
   $allowed_tags
); ?>

		<dl>
		<?php 
		// Get the recent posts
		$q = 'showposts=' . $instance[ 'numposts' ];
		if ( !empty( $instance[ 'post_type' ] )) $q .= '&post_type=' . $instance[ 'post_type' ];
		if ( !empty( $instance[ 'cat' ] )) $q .= '&cat=' . $instance[ 'cat' ];
		if ( !empty( $instance[ 'tag' ] )) $q .= '&tag=' . $instance[ 'tag' ];
		query_posts( $q  );

		while (have_posts()) : the_post(); ?>
           
    
			<dt>
				<?php the_post_thumbnail( array( $instance[ 'thumb_width' ], $instance[ 'thumb_height' ]), array('class' => 'alignleft')); 
    ?>
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
			</dt>
			<dd>
				<?php echo wp_kses(
    pi_rcwt_excerpt( $instance[ 'characters' ] ),
     $allowed_tags
); ?>
			</dd>
		<?php endwhile; ?>
		</dl>

		<?php if( $instance[ 'linkurl' ] !="" ) { ?>
			<a href="<?php echo esc_html($instance[ 'linkurl' ]); ?>" class="morelink"><?php echo  esc_html($instance[ 'linktext' ]); ?></a>
		<?php } ?>

		<?php
		echo wp_kses(
    $after_widget,
     $allowed_tags
);
		wp_reset_query();
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ] = wp_strip_all_tags( $new_instance[ 'title' ] );
		$instance[ 'numposts' ] = $new_instance[ 'numposts' ];
		$instance[ 'characters' ] = $new_instance[ 'characters' ];
		$instance[ 'post_type' ] = $new_instance[ 'post_type' ];
		$instance[ 'cat' ] = $new_instance[ 'cat' ];
		$instance[ 'tag' ] = $new_instance[ 'tag' ];
 	$instance[ 'thumb_width' ] = $new_instance[ 'thumb_width' ];
  $instance[ 'thumb_height' ] = $new_instance[ 'thumb_height' ];
		$instance[ 'linktext' ] = $new_instance[ 'linktext' ];
		$instance[ 'linkurl' ] = $new_instance[ 'linkurl' ];
		return $instance;
	}

	function form( $instance ) {
		// Widget defaults
		$instance = wp_parse_args( (array) $instance, array( 
			'title' => 'Recent Posts',
			'numposts' => 5,
			'characters' => 100,
			'post_type' => '',
			'cat' => 0,
			'tag' => '',
 		'thumb_width' => 55,
  	    'thumb_height' => 55,
			'linktext' => '',
			'linkurl' => ''
		)); ?>

		<p>
			<label for="<?php echo esc_html($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title:', 'recent-cpt' ); ?></label>
			<input class="widefat" id="<?php echo esc_html($this->get_field_id( 'title' )); ?>" name="<?php echo esc_html($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo  esc_html($instance[ 'title' ]); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_html($this->get_field_id( 'numposts' )); ?>"><?php  esc_html_e( 'Number of posts to show:', 'recent-cpt' ); ?></label> 
			<input class="widefat" id="<?php echo esc_html($this->get_field_id( 'numposts' )); ?>" name="<?php echo esc_html($this->get_field_name( 'numposts' )); ?>" type="text" value="<?php echo  esc_html($instance[ 'numposts' ]); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_html($this->get_field_id( 'characters' )); ?>"><?php  esc_html_e( 'Excerpt length in number of characters:', 'recent-cpt' ); ?></label> 
			<input class="widefat" id="<?php echo esc_html($this->get_field_id( 'characters' )); ?>" name="<?php echo  esc_html($this->get_field_name( 'characters' )); ?>" type="text" value="<?php echo  esc_html($instance[ 'characters' ]); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_html($this->get_field_id( 'post_type' )); ?>"><?php  esc_html_e( 'Custom post type:', 'recent-cpt' ); ?></label>
			<select id="<?php echo esc_html($this->get_field_id( 'post_type' )); ?>" name="<?php echo esc_html($this->get_field_name( 'post_type' )); ?>">
			<option value=""><?php echo esc_html(__( 'None', 'recent-cpt' )) . ' (' . esc_html(__( 'Posts', 'recent-cpt' )) . ')'; ?></option>
			<?php
				$args = array(
					'public' => true,
					'_builtin' => false
				);
				$output = 'names'; // names or objects, note names is the default
				$operator = 'and'; // 'and' or 'or'
				$post_types = get_post_types( $args, $output, $operator );
				foreach ( $post_types  as $post_type ) {
					echo wp_kses(
   '<option value="' . $post_type . '"' . selected( $instance[ 'post_type' ], $post_type ). '>' . $post_type . '</option>',
   array( 'option' => array() )
);
				}
			?>
			</select>
		</p>
 	
    	<p>
			<label for="<?php echo esc_html($this->get_field_id( 'thumb_width' )); ?>"><?php  esc_html_e( 'Thumbnail Width:', 'recent-cpt' ); ?></label>
			<input class="widefat" id="<?php echo esc_html($this->get_field_id( 'thumb_width' )); ?>" name="<?php echo esc_html($this->get_field_name( 'thumb_width' )); ?>" type="text" value="<?php echo esc_html($instance[ 'thumb_width' ]); ?>" />
			<br /><small><?php esc_html_e( 'Enter Thumbnail width', 'recent-cpt' ); ?></small>
		</p>
 	
  <p>
			<label for="<?php echo esc_html($this->get_field_id( 'thumb_height' )); ?>"><?php  esc_html_e( 'Thumbnail Height:', 'recent-cpt' ); ?></label>
			<input class="widefat" id="<?php echo esc_html($this->get_field_id( 'thumb_height' )); ?>" name="<?php echo esc_html($this->get_field_name( 'thumb_height' )); ?>" type="text" value="<?php echo esc_html($instance[ 'thumb_height' ]); ?>" />
			<br /><small><?php esc_html_e( 'Enter Thumbnail height', 'recent-cpt' ); ?></small>
		</p>

		<p>
			<label for="<?php echo esc_html($this->get_field_id( 'cat' )); ?>"><?php  esc_html_e( 'Limit to category: ', 'recent-cpt' ); ?>
			<?php wp_dropdown_categories(array( 'name' => $this->get_field_name( 'cat' ), 'show_option_all' => __( 'None (all categories)', 'recent-cpt' ), 'hide_empty'=>0, 'hierarchical'=>1, 'selected'=>$instance[ 'cat' ] ) ); ?></label>
		</p>

		<p>
			<label for="<?php echo esc_html($this->get_field_id( 'tag' )); ?>"><?php  esc_html_e( 'Limit to tags:', 'recent-cpt' ); ?></label>
			<input class="widefat" id="<?php echo esc_html($this->get_field_id( 'tag' )); ?>" name="<?php echo esc_html($this->get_field_name( 'tag' )); ?>" type="text" value="<?php echo esc_html($instance[ 'tag' ]); ?>" />
			<br /><small><?php esc_html_e( 'Enter post tags separated by commas (\'country, city\' )', 'recent-cpt' ); ?></small>
		</p>

		<p>
			<label for="<?php echo esc_html($this->get_field_id( 'linktext' )); ?>"><?php  esc_html_e( 'Link text:', 'recent-cpt' ); ?></label> 
			<input class="widefat" id="<?php echo esc_html($this->get_field_id( 'linktext' )); ?>" name="<?php echo esc_html($this->get_field_name( 'linktext' )); ?>" type="text" value="<?php echo esc_html($instance[ 'linktext' ]); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_html($this->get_field_id( 'linkurl' )); ?>"><?php  esc_html_e( 'URL:', 'recent-cpt' ); ?></label> 
			<input class="widefat" id="<?php echo esc_html($this->get_field_id( 'linkurl' )); ?>" name="<?php echo esc_html($this->get_field_name( 'linkurl' )); ?>" type="text" value="<?php echo esc_html($instance[ 'linkurl' ]); ?>" />
		</p>

		<?php
	}
}

function pi_rcwt_recent_cpt_widget_thumbnails_init() {
	register_widget( 'PI_RCWT_RecentCPTWidgetThumbnails' );
}

add_action( 'widgets_init', 'pi_rcwt_recent_cpt_widget_thumbnails_init' ); ?>
