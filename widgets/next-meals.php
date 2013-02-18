<?php

/*
 * LUR aTable
 * Widget that Display the next meals to come
 */

/**
 * Adds Lur_next_meals widget.
 */
class Lur_next_meals_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'lur_next_meal', // Base ID
			__('aTable Next Meals', 'lur-atable'), // Name
			array( 'description' => __( 'Display the next meals to come', 'lur-atable' ), ) // Args
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
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;

		$next_meals_args = array(
												'post_type'      => 'meals',
												'meta_key'       => 'lur_meals_date',
												'orderby'        => 'meta_value',
												'order'          => 'ASC',
												'posts_per_page' => $instance['nb_meals'],
												'meta_query'     => array(
																							array(
																									'key'     => 'lur_meals_date',
																									'value'   => date('Y-m-d'),
																									'compare' => '>=',
																								)
																							),

											);

		$next_meals = new WP_Query( $next_meals_args );

		if( $next_meals->have_posts() ){
			?>
			<ul>
			<?php
			while( $next_meals->have_posts() ){
				$next_meals->the_post();
				?>
				<li>
					<a href="<?php the_permalink() ?>" title="<?php the_title() ?>">
						<?php the_title() ?>
					</a>
				</li>
				<?php
			}
			wp_reset_postdata();
			?>
			</ul>
			<?php
		}

		echo $after_widget;
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
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['nb_meals'] = is_numeric($new_instance['nb_meals']) ? $new_instance['nb_meals'] : 5;

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'next Meals', 'lur-atable' );
		$nb_meals = isset( $instance[ 'nb_meals' ] ) ? $instance[ 'nb_meals' ] : 5;

		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'lur-atable' ); ?> :</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'nb_meals' ); ?>"><?php _e( 'Number of meals to display', 'lur-atable' ); ?> :</label>
		<input class="small-text" id="<?php echo $this->get_field_id( 'nb_meals' ); ?>" name="<?php echo $this->get_field_name( 'nb_meals' ); ?>" type="number" value="<?php echo esc_attr( $nb_meals ); ?>" />
		</p>
		<?php
	}

} // class Foo_Widget
