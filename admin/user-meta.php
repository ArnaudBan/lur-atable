<?php
/**
 * All about the user and the user-meta
 */


/**
 * Add Meals point in the user column
 */
add_action('manage_users_columns','lur_atable_add_users_columns');
add_action('manage_users_custom_column','custom_manage_users_custom_column',10,3);

function lur_atable_add_users_columns($column_headers) {
  $column_headers['lur_meals_points'] = __( 'Meals Points', 'lur-atable');
  return $column_headers;
}

function custom_manage_users_custom_column($custom_column,$column_name,$user_id) {
  if ($column_name =='lur_meals_points') {
    $meal_points = get_user_meta( $user_id , 'lur_meals_points', true);
    $meal_points_count = $meal_points == 0 ? 1 : $meal_points;

    $custom_column = ( $meal_points == '' ) ? __( 'No points yet', 'lur-atable') : sprintf( _n('%d point', '%d points', $meal_points_count,'lur-atable'), $meal_points);
  }
  return $custom_column;
}


/*
 *  Add Meals points to the user profil
 */
add_action( 'show_user_profile', 'lur_atable_add_user_profile_fields' );

function lur_atable_add_user_profile_fields( $user ){
  $meal_points = get_user_meta( $user->ID , 'lur_meals_points', true);
  $meals_participate = get_user_meta( $user->ID , 'lur_meals_participate', true);
  $meals_mail = get_user_meta( $user->ID , 'lur_meals_mail', true);
  ?>
  <h3><?php _e('Meals', 'lur-atable'); ?></h3>
  <table class="form-table">
    <tbody>
      <tr>
        <th><?php _e('Your meals points', 'lur-atable'); ?> :</th>
        <td>
          <?php
          $meal_points_count = $meal_points == 0 ? 1 : $meal_points;
          echo ( $meal_points == '' ) ? __( 'No points yet', 'lur-atable') : sprintf( _n('%d point', '%d points', $meal_points_count,'lur-atable'), $meal_points);
          ?>
        </td>
      </tr>
      <tr>
        <th><?php _e('Participate in meals', 'lur-atable'); ?> :</th>
        <td>
          <input type="checkbox" value="true" name="lur_meals_participate" id="lur_meals_participate" <?php checked( 'true', $meals_participate, true ); ?>/>
          <label for="lur_meals_participate"><?php _e('I check, I participate', 'lur-atable'); ?></label>
        </td>
      </tr>
      <tr>
        <th><?php _e('Meals\'s mail', 'lur-atable'); ?> :</th>
        <td>
          <input type="checkbox" value="true" name="lur_meals_mail" id="lur_meals_mail" <?php checked( 'true', $meals_mail, true ); ?>/>
          <label for="lur_meals_mail"><?php _e('I check, I get mail when there are new meals', 'lur-atable'); ?></label>
        </td>
      </tr>
    </tbody>
  </table>
  <?php
}


add_action('personal_options_update', 'lud_update_meals_user_meta');

function lud_update_meals_user_meta($user_id) {

  if ( current_user_can('edit_user',$user_id) ){
    if( isset( $_POST['lur_meals_participate'] ) && $_POST['lur_meals_participate'] == 'true' )
      update_user_meta($user_id, 'lur_meals_participate', 'true');
    else
      delete_user_meta( $user_id, 'lur_meals_participate', 'true');
  }
  if ( current_user_can('edit_user',$user_id) ){
    if( isset( $_POST['lur_meals_mail'] ) && $_POST['lur_meals_mail'] == 'true' )
      update_user_meta($user_id, 'lur_meals_mail', 'true');
    else
      delete_user_meta( $user_id, 'lur_meals_mail', 'true');
  }
}