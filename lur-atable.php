<?php
/*
Plugin Name: LUR aTable
Plugin URI: http://atable.arnaudban.me
Description: Plugin WordPress qui permet de gérer nos repas en commun chez Eluère et associés
Version: 1.0
Author: ArnaudBan
Author URI: http://arnaudban.me
License: GPL2

Copyright 2013  ArnaudBan  (email : arnaud.banvillet@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Add a CPT
require_once plugin_dir_path(__FILE__) . 'includes/custom-post-type-repas.php';

// Modify user admin page and add user meta
require_once plugin_dir_path(__FILE__) . 'admin/user-meta.php';


// translate the plugin
load_plugin_textdomain('lur-atable', false, 'lur-atable/languages' );


// Add Widget
function lur_register_widget(){
	require_once plugin_dir_path(__FILE__) . 'widgets/next-meals.php';
	require_once plugin_dir_path(__FILE__) . 'widgets/all-users.php';
	register_widget( "Lur_next_meals_Widget" );
	register_widget( "Lur_All_Users_Widget" );
}
add_action( 'widgets_init', 'lur_register_widget');


/*
 *  Add Posts 2 posts core
 */
require_once plugin_dir_path(__FILE__) . 'scb/load.php';

scb_init( 'lur_atable_connection_init' );

function lur_atable_connection_init() {
	add_action( 'plugins_loaded', 'lur_atable_load_p2p_core', 20 );
	add_action( 'init', 'example_connection_types' );
}

function lur_atable_load_p2p_core() {

	if ( function_exists( 'p2p_register_connection_type' ) )
		return;

	// textdomain
	define( 'P2P_TEXTDOMAIN', 'lur-atable' );

	foreach ( array(
		'storage', 'query', 'query-post', 'query-user', 'url-query',
		'util', 'side', 'type-factory', 'type', 'directed-type',
		'api'
	) as $file ) {
		require_once plugin_dir_path(__FILE__) . "p2p-core/$file.php";
	}

	// TODO: can't use activation hook
	add_action( 'admin_init', array( 'P2P_Storage', 'install' ) );
}

function example_connection_types() {
	p2p_register_connection_type( array(
		'name' => 'repas_registration',
		'from' => 'meals',
		'to' => 'user'
	) );
}


/*
 * Display meta repas
 *
 * filter the_content
 */
function lur_add_meals_meta_to_content( $the_content ){
	global $post;

	$current_user_participate = get_user_meta( get_current_user_id(), 'lur_meals_participate', true );
	$current_user_participate =  ( $current_user_participate && $current_user_participate == 'true') ? true : false;


	if( get_post_type() == 'meals' && is_single() && $current_user_participate ){

		// Deal with registration and unregistration if there is a need to
		if( isset( $_REQUEST['participant_id'] ) ){
			register_to_meal( $_REQUEST['participant_id'], get_the_ID() );
		} elseif( isset( $_REQUEST['conection_id'] ) ){
			unregister_to_meal( $_REQUEST['conection_id'] );
		}

		// Get the date
		$date_repas = get_post_meta( get_the_ID(), 'lur_meals_date', true);
		$date_repas = new DateTime( $date_repas );

		// Get the number max of participants
		$max_participants = get_post_meta( get_the_ID(), 'lur_meals_max_participants', true);

		// Get the participants
		$participants = get_users( array(
				'connected_type'    => 'repas_registration',
				'connected_items'   => $post,
				'connected_orderby' => 'registration-date',
			) );

		// Display a title
		$the_content .= '<h2>'. __('Registration for the meal', 'lur-atable') . '</h2>';

		// Show the nuber of participants
		$nb_participant_string = '<p>';

		$nb_participant_for_n_translation = count($participants) == 0 ? 1 : count($participants);

		if( $max_participants ){
			$nb_participant_string .= sprintf( _n('%1$s of %2$s participant maximum', '%1$s of %2$s participants maximum', $nb_participant_for_n_translation, 'lur-atable'),
																	'<strong>' . count($participants) . '</strong>',
																	'<strong>' . $max_participants . '</strong>'
																);
		} else {
			$nb_participant_string .= '<strong>' . count($participants) . '</strong> '. _n('participant', 'participants', $nb_participant_for_n_translation, 'lur-atable');
		}

		$nb_participant_string .= '</p>';

		$the_content .= $nb_participant_string;

		// Initialisation a variable
		$registration_display = '';

		if( $date_repas ){

			// if a user is login we may propose to register
			if( is_user_logged_in() ){

				// If the date is to close or past, the registration are close
				$today = new DateTime('now');
				$is_old_meal = $today >= $date_repas;
				if( $is_old_meal ){

					$registration_display .= '<p>' . __('Registration are closed', 'lur-atable') . '</p>';

				} else{

					$registration_are_open = false;

					if( $max_participants ){

						// Can we add mor participants
						if( count($participants) < $max_participants ){
							$registration_are_open = true;
						} else {
							$registration_display .= '<p>' . __('Meal is full', 'lur-atable') . '</p>';
						}

					// No number limit of participants, registration are open !
					} else {
						$registration_are_open = true;
					}

					// No registration for the author of the meal
					if( get_current_user_id() == get_the_author_meta('ID') ){
						$registration_display .= '<p>' .__('You are the author, the author can not subscribe to his own meal', 'lur-atable') . '</p>';
						$registration_are_open = false;
					}

					// Check if the users is not alreday register beafore
					$connection_args = array(
							'from' => get_the_ID(),
							'to'   => get_current_user_id()
						);

					if( p2p_connection_exists( 'repas_registration', $connection_args )){
						$registration_display .= '<p><strong>' .__('You are already register', 'lur-atable') . '</strong></p>';
						$registration_are_open = false;
					}

					// if we can register lets show the registration form
					if( $registration_are_open ){

						$registration_display .= '<p><form method="post" action="'. get_permalink() .'">';
						$registration_display .= '<input type="hidden" name="participant_id" value="'. get_current_user_id() .'" />';
						$registration_display .=    __('Tempted', 'lur-atable') . ' ? ';
						$registration_display .= '<input type="submit" value="▶ '. __('Register', 'lur-atable') .'" />';
						$registration_display .= '</form></p>';
					}
				}

			// If user is not log in we sugeste him
			} else {
				$registration_display .= '<p>' . __('You have to log in to register', 'lur-atable') . ' : ' .
																		'<a href="' . wp_login_url() .'" title="'. __('Log in') .'">'. __('Log in') .'</a>' .
																'</p>';
			}

			// Any way we show the participants liste
			if (is_array($participants) && ! empty( $participants )) {

				$registration_display .= '
				<table>
					<thead>
						<tr>
							<th>'. __('Registration Date', 'lur-atable') .'</th>
							<th>'. __('participant', 'lur-atable') .'</th>';
							if( ! $is_old_meal){

							$registration_display .= '<th>'. __('Unregister', 'lur-atable') .'</th>';
							}
							$registration_display .=
						'</tr>
					</thead>
					<tbody>';

						foreach ( $participants as $participant ){
							//var_dump($participant);
							$registration_display .= '
							<tr>
								<td>';
								$registration_date = p2p_get_meta( $participant->p2p_id, 'registration-date', true);
								if ( $registration_date ){
									$registration_display .= mysql2date( get_option('date_format') . ' - ' . get_option('time_format') ,$registration_date );
								}
								$registration_display .= '
								</td>
								<td>' .$participant->display_name .'</td>';
								if( ! $is_old_meal){

								$registration_display .=
								'<td>';
									// Propose to unregister for the current user
									if( get_current_user_id() == $participant->ID ){
											$registration_display .= '<form method="post" action="'. get_permalink() .'">';
											$registration_display .=    '<input type="hidden" name="conection_id" value="'. $participant->p2p_id .'">';
											$registration_display .=    '<input type="submit" value="'. __('Unregister', 'lur-atable') .'" >';
											$registration_display .= '</form>';
									}
								$registration_display .= '
								</td>';
								}
								$registration_display .=
							'</tr>';
						}
						$registration_display .= '
					</tbody>
				</table>';
			}

			$the_content .= $registration_display;

		}
	} // end if is_singular('repas')

	return $the_content;
}
add_filter('the_content', 'lur_add_meals_meta_to_content' );

/**
 * Add the date in front of the meal title
 *
 * @global Object $post
 * @param string $the_title
 * @return string
 */
function lur_add_meals_date( $the_title ){
	global $post;

	if( !is_admin() && !empty( $post ) && sanitize_title($the_title) == sanitize_title($post->post_title) && get_post_type() == 'meals' ){

		$date_repas = get_post_meta( get_the_ID(), 'lur_meals_date', true);

		if( $date_repas ){
			$the_title = mysql2date( get_option('date_format'), $date_repas ) . ' - ' . $the_title;
		}
	}

	return $the_title;
}
add_filter( 'the_title', 'lur_add_meals_date');


/**
 * register to a meal
 *
 * +1 "meals point" to the author of the meal
 * -1 "meals point" to the user who is registered
 *
 * @param type $participant_id
 * @param type $repas_id
 */
function register_to_meal( $participant_id, $repas_id ){

	$connection_args = array(
			'from'  => $repas_id,
			'to'    => $participant_id,
			'meta'  => array(
					'registration-date' => date('Y-m-d H:i:s'),
				),
		);

	// Don't register twice
	if( ! p2p_connection_exists( 'repas_registration', $connection_args ) ){
		p2p_create_connection( 'repas_registration', $connection_args );

		// update user meta
		$user_meal_points = get_user_meta($participant_id, 'lur_meals_points', true);

		$user_meal_points = $user_meal_points ? ( $user_meal_points - 1) : -1;

		update_user_meta($participant_id, 'lur_meals_points', $user_meal_points);

		// upadte author meta
		$author_id = get_the_author_meta('ID');
		$author_meal_points = get_user_meta( $author_id , 'lur_meals_points', true);

		$author_meal_points = $author_meal_points ? ( $author_meal_points + 1 ) : 1;

		update_user_meta( $author_id, 'lur_meals_points', $author_meal_points);

	}

}


/**
 * Unregister user to meal
 *
 * -1 "meals point" to the author of the meal
 * give back 1 "meals point" to the user who is unregistered
 *
 * @param int $conection_id
 */
function unregister_to_meal( $conection_id ){

	// update user meta
	$connection = p2p_get_connection( $conection_id );
	$participant_id = $connection->p2p_to;
	$user_meal_points = get_user_meta($participant_id, 'lur_meals_points', true);

	$user_meal_points++;

	update_user_meta($participant_id, 'lur_meals_points', $user_meal_points);

	// upadte author meta
	$author_id = get_the_author_meta('ID');
	$author_meal_points = get_user_meta( $author_id , 'lur_meals_points', true);

	$author_meal_points--;

	update_user_meta( $author_id, 'lur_meals_points', $author_meal_points);

	// delete the connection
	p2p_delete_connection( $conection_id );
}


//Add query var
function lur_atable_add_query_var($public_query_vars) {

	$public_query_vars[] = "old_meal";
	return $public_query_vars;
}
add_filter('query_vars', 'lur_atable_add_query_var');

// Add rewrite rules
function lur_atable_add_rewrite_rules(){

	add_rewrite_rule( __('meals', 'lur-atable' ).'/' . __('old', 'lur-atable') .'/?$', 'index.php?post_type=meals&old_meal=show', 'top');
	add_rewrite_rule( __('meals', 'lur-atable' ).'/' . __('old', 'lur-atable') .'/page/([0-9]{1,})/?$', 'index.php?post_type=meals&old_meal=show&paged=$matches[1]', 'top');
}
add_action('init', 'lur_atable_add_rewrite_rules');

// Modification of the default query
function lur_meals_orderby_meals_date( $query ) {

	// Meal are order by meal date
	if ( $query->is_main_query() && ! is_admin() && is_post_type_archive('meals') ) {

		// Show old meal or new one
		if( get_query_var('old_meal') == 'show' ){
			$compare_meal = '<';
			$order_meal = 'DESC';
		} else {
			$compare_meal = '>=';
			$order_meal = 'ASC';
		}

		$meta_query = array(
										'key'     => 'lur_meals_date',
										'value'   => date('Y-m-d'),
										'compare' => $compare_meal,
									);
		$query->set( 'meta_key', 'lur_meals_date' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'order', $order_meal );
		$query->set( 'meta_query', array( $meta_query ) );

	// The author page show meals not post
	} elseif( $query->is_main_query() && is_author() ) {
		$query->set( 'post_type',  array( 'post', 'meals' ) );
	}
}
add_action( 'pre_get_posts', 'lur_meals_orderby_meals_date' );
