<?php

/*
 * LUR aTable
 * Widget that Display all the users and their meals points
 */

/**
 * Adds Lur_next_meals widget.
 */
class Lur_All_Users_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'lur_all_users', // Base ID
			__('aTable All Users', 'lur-atable'), // Name
			array( 'description' => __( 'Display all the users and their meals points', 'lur-atable' ), )
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

		$all_users = get_users( array(
										'fields'   		=> 'all_with_meta',
										'meta_key'		=> 'lur_meals_participate',
										'meta_value'	=> 'true',
										'meta_compare'=> '='
									));

		usort($all_users, function($a, $b){
			if( $a->lur_meals_points == $b->lur_meals_points ) return 0;
			return( $a->lur_meals_points > $b->lur_meals_points ) ? -1 : 1;
		})


		?>
		<div class="entry-content">
			<table>
				<thead>
					<tr>
						<th><?php _e('User', 'lur-atable'); ?></th>
						<th><?php _e('Meals Points', 'lur-atable'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach( $all_users as $user ){
						?>
						<tr>
							<td>
								<a href="<?php echo get_author_posts_url( $user->ID); ?>" title="<?php printf(__('All %s\'s meal', 'lur-atable'), $user->display_name ) ?>">
									<?php echo $user->display_name; ?>
								</a>
							</td>
							<td><?php echo isset( $user->lur_meals_points) ? $user->lur_meals_points : __( 'No points yet', 'lur-atable'); ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>

		<?php
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
	public function update( $new_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

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

		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'Users', 'lur-atable' );

		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'lur-atable' ); ?> :</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}

} // class Foo_Widget
