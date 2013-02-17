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
		'from' => 'repas',
		'to' => 'user'
	) );
}


/*
 * Display meta repas
 *
 * filter the_content
 */
function lur_add_meta_repas_to_content( $the_content ){
	global $post;

	if( is_singular( 'repas') ){
		$date_repas = get_post_meta( get_the_ID(), 'lur_repas_date', true);
		$date_repas = new DateTime( $date_repas );

		if( $date_repas ){

			// if a user is login we may propose to register
			if( is_user_logged_in() ){

				if( isset( $_REQUEST['participant_id'] ) ){
					register_to_meal( $_REQUEST['participant_id'], get_the_ID() );
				} elseif( isset( $_REQUEST['conection_id'] ) ){
					p2p_delete_connection( $_REQUEST['conection_id'] );
				}

				$registration_display = '';

				// If the date is to close or past, the registration are close
				$today = new DateTime('now');
				if( $today > $date_repas->modify(' -1 day') ){

					$registration_display .= '<p>' . __('Registration are closed', 'lur-atable') . '</p>';

				} else{

					// Get the participants
					$participants = get_users( array(
							'connected_type'    => 'repas_registration',
							'connected_items'   => $post,
							'connected_orderby' => 'registration-date',
						) );

					// Is there a number limit of participant
					$max_participants = get_post_meta( get_the_ID(), 'lur_repas_max_participants', true);

					$registration_are_open = false;
					if( $max_participants ){

						// Can we add mor participants
						if( count($participants) < $max_participants ){
							$registration_are_open = true;
						} else {
							$registration_display .= __('We are full', 'lur-atable');
						}

					// No number limit of participants, registration are open !
					} else {
						$registration_are_open = true;
					}

					// if we can register lets show the registration form
					if( $registration_are_open ){

						$submit_value = __('Register', 'lur-atable');
						$disabled = false;

						// Check if the users is not alreday register beafore
						$connection_args = array(
								'from' => get_the_ID(),
								'to'   => get_current_user_id()
							);
						if( p2p_connection_exists( 'repas_registration', $connection_args )){
							$submit_value = __('Already Registered', 'lur-atable');
							$disabled = true;
						}

						$registration_display .= '<form method="post">';
						$registration_display .= '<input type="hidden" name="participant_id" value="'. get_current_user_id() .'">';
						$registration_display .= '<input type="submit" value="'. $submit_value .'" '. disabled( $disabled, true, false ) .'>';
						$registration_display .= '</form>';
					}

					// Any way we show teh participants liste
					if (is_array($participants) && ! empty( $participants )) {

						$registration_display .= '
						<table>
							<thead>
								<tr>
									<th>'. __('Registration Date', 'lur-atable') .'</th>
									<th>'. __('participant', 'lur-atable') .'</th>
									<th>'. __('Unregister', 'lur-atable') .'</th>
								</tr>
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
										<td>' .$participant->display_name .'</td>
										<td>';
											// Propose to unregister for the current user
											if( get_current_user_id() == $participant->ID ){
													$registration_display .= '<form method="post">';
													$registration_display .=    '<input type="hidden" name="conection_id" value="'. $participant->p2p_id .'">';
													$registration_display .=    '<input type="submit" value="'. __('Unregister', 'lur-atable') .'" >';
													$registration_display .= '</form>';
											}
										$registration_display .= '
										</td>
									</tr>';
								}
								$registration_display .= '
							</tbody>
						</table>';
					}
				}

				$the_content .= $registration_display;
			}
		}


	} // end if is_singular('repas')

	return $the_content;
}
add_filter('the_content', 'lur_add_meta_repas_to_content' );

function lur_add_repas_date( $the_title ){
	global $post;

	if( $the_title == $post->post_title && get_post_type() == 'repas' ){

		$date_repas = get_post_meta( get_the_ID(), 'lur_repas_date', true);

		if( $date_repas ){
			$the_title = mysql2date( get_option('date_format'), $date_repas ) . ' - ' . $the_title;
		}
	}

	return $the_title;
}
add_filter( 'the_title', 'lur_add_repas_date');

function register_to_meal( $participant_id, $repas_id ){

	$connection_args = array(
			'from'  => $repas_id,
			'to'    => $participant_id,
			'meta'  => array(
					'registration-date' => date('Y-m-d H:i:s'),
				),
		);

	// Don't register twice
	if( ! p2p_connection_exists( 'repas_registration', $connection_args ) )
		p2p_create_connection( 'repas_registration', $connection_args );

}