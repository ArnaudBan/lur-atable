<?php
/*
 * LUR aTable
 * CPT meals
 */

/*
 * register post type meals
 */
function lur_add_cpt_meals(){

	$meals_labels = array(
		'name'               => __( 'Meals', 'lur-atable'),
		'singular_name'      => __( 'Meal', 'lur-atable' ),
		'add_new'            => __( 'Add New', 'lur-atable' ),
		'add_new_item'       => __( 'Add New Meal', 'lur-atable' ),
		'edit_item'          => __( 'Edit Meal', 'lur-atable' ),
		'new_item'           => __( 'New Meal', 'lur-atable' ),
		'all_items'          => __( 'All Meals', 'lur-atable' ),
		'view_item'          => __( 'View Meal', 'lur-atable' ),
		'search_items'       => __( 'Search Meals', 'lur-atable' ),
		'not_found'          => __( 'No Meal found', 'lur-atable' ),
		'not_found_in_trash' => __( 'No Meal found in trash', 'lur-atable' ),
		'menu_name'          => __( 'Meals', 'lur-atable' ),
	);

	$meals_args = array(
		'labels'               => $meals_labels,
		'public'               => true,
		'show_ui'              => true,
		'show_in_menu'         => true,
		'query_var'            => true,
		'rewrite'              => array( 'slug' => 'meals' ),
		'capability_type'      => 'post',
		'has_archive'          => true,
		'hierarchical'         => false,
		'register_meta_box_cb' => 'lur_atable_add_wedding_metabox',
		'menu_position'        => 20,
		'supports'             => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
	);

	register_post_type( 'meals', $meals_args );
}

add_action('init', 'lur_add_cpt_meals');


/*
 * Add metabox to meals
 */
function lur_atable_add_wedding_metabox(){
	add_meta_box(
			'lur_meals_meta',
			__( 'Meal informations', 'lur-atable' ),
			'lur_meals_meta_metabox_content',
			'meals',
			'side',
			'core'
	);

	add_meta_box(
			'lur_meals_participants',
			__( 'Meal participants', 'lur-atable' ),
			'lur_meals_participants_metabox_content',
			'meals',
			'side',
			'core'
	);
}


/*
 * Dispaly metabox lur_meals_meta
 */
function lur_meals_meta_metabox_content(){

	$meal_date = get_post_meta(get_the_ID(), 'lur_meals_date', true);
	$meal_max_participants = get_post_meta(get_the_ID(), 'lur_meals_max_participants', true);

	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'lur_meals_meta_metabox_nonce' );

	?>
	<p>
		<label for="lur_meals_date">
			<?php _e("Meal Date", 'lur-atable' ); ?>
		</label>
		<input type="date" id="lur_meals_date" name="lur_meals_date" value="<?php if( $meal_date ) esc_attr_e( $meal_date ) ?>" />
	</p>

	<p>
		<label for="lur_meals_max_participants">
			<?php _e("Meal max participants", 'lur-atable' ); ?>
		</label>
		<input type="number" id="lur_meals_max_participants" name="lur_meals_max_participants" value="<?php if( $meal_max_participants ) esc_attr_e( $meal_max_participants ); ?>" max="99" class="small-text"/>
	</p>
	<?php
}


/**
 * Dispaly the liste of participants
 */
function lur_meals_participants_metabox_content(){
	global $post;

	// Get the participants
	$participants = get_users( array(
				'connected_type'    => 'repas_registration',
				'connected_items'   => $post,
				'connected_orderby' => 'registration-date',
			) );

	if( empty( $participants ) ){
		_e('Sorry, nobody so far', 'lur-atable');
	} else {
		echo '<p>' . __('number of registered', 'lur-atable') . ' : ' . count($participants) . '</p>';
		echo '<ol>';
		foreach ( $participants as $participant ){
			echo '<li>' . $participant->display_name . '</li>';
		}
		echo '</ol>';
	}
}


/**
 * Save the metabox
 *
 * @param int $post_id
 */
function lur_meals_meta_metabox_save_postdata( $post_id ) {

	// verify this came from the our screen and with proper authorization,
	if ( isset( $_POST['lur_meals_meta_metabox_nonce'] ) && wp_verify_nonce( $_POST['lur_meals_meta_metabox_nonce'], plugin_basename( __FILE__ ) ) ){

		// Check permissions
		if ( current_user_can( 'edit_page', $post_id ) ){

			//update post meta
			if( is_numeric( $_POST['lur_meals_max_participants'] ) )
				update_post_meta($post_id, 'lur_meals_max_participants', $_POST['lur_meals_max_participants']);

			//if photo id is numeric get sizes from flickr
			list($yy,$mm,$dd)= explode("-", $_POST['lur_meals_date']);
			if (is_numeric($yy) && is_numeric($mm) && is_numeric($dd) && checkdate($mm,$dd,$yy) ) {
				update_post_meta($post_id, 'lur_meals_date', $_POST['lur_meals_date']);
			}
		}
	}
}
add_action( 'save_post', 'lur_meals_meta_metabox_save_postdata' );
