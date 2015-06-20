<?php
/*
Plugin Name: Juan Pablo Top Blogs
Description: Plugin para los post mas populares
Version:     1.0
Author:      Juan Pablo De la torre Valdez
Author URI:  http://URI_Of_The_Plugin_Author
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

function popular_post_views($postID) {
	$total_key = 'views';

	$total = get_post_meta($postID, $total_key, true);

	if ($total == '') {
		delete_post_meta($postID, $total_key);
		add_post_meta($postID, $total_key, '0');
	} else {
		//en caso de que tenga un valor.
		$total++;
		update_post_meta($postID, $total_key, $total);
	}
}



/* Inyectar dinamicamente dentro de single post */

function my_count_popular_posts($post_id) {
	if (!is_single() ) {
		return;
	}
	if (!is_user_logged_in()) {
		//get the post ID
		if (empty ($post_id)) {
			global $post;
			$post_id = $post->ID;
		}
		// correr la funcion
		popular_post_views($post_id);
	}
}

add_action('wp_head','my_count_popular_posts');


// Agregar columna en All Post

function views_column($defaults) {
	$defaults['post_views'] = 'Visto';
	return $defaults;
}
add_filter('manage_posts_columns', 'views_column');


function display_views($column_name) {
	if($column_name === 'post_views') {
		echo (int) get_post_meta(get_the_ID(), 'views', true);
	}
}
add_action('manage_posts_custom_column','display_views',5,2);



/**
 * Adds Popular Post widget.
 */
class popular_post extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'popular_post', // Base ID
			__( 'Popular Post', 'text_domain' ), // Name
			array( 'description' => __( 'Displays the 5 most popular post', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}

		// WP_QUERY

			$query_args = array(
				'post_type' => 'post',
				'posts_per_page' => 5,
				'meta_key' => 'views',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'ignore_sticky_posts' => true
			);

			$the_query = new WP_Query($query_args); 

			if($the_query->have_posts()) { ?>
				<ul>
					<?php while($the_query->have_posts()) { ?>
						<?php $the_query->the_post(); ?>
						<li>
							<a href="<?php echo get_the_permalink(); ?>">
							<?php echo get_the_title(); ?>
							</a>
							<span>Veces Visto: <?php echo get_post_meta(get_the_ID(), 'views', true); ?>
							</span>
						</li>


					<?php } ?>
				</ul>
			<?php 	} ?>


		<?php // FIN WP_QUERY 

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Popular Post', 'text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class popular_post

// register popular_post widget
function register_popular_post_widget() {
    register_widget( 'popular_post' );
}
add_action( 'widgets_init', 'register_popular_post_widget' );