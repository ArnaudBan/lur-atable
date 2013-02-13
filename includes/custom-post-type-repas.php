<?php
/*
 * LUR aTable
 * CPT repas
 */

/*
 * register post type repas
 */
function lur_add_cpt_repas(){

	$repas_labels = array(
		'name'								=> 'Repas',
		'singular_name'				=> 'Repas',
		'add_new'							=> 'Ajouter',
		'add_new_item'				=> 'Ajouter un repas',
		'edit_item'						=> 'Modifier un repas',
		'new_item'						=> 'Nouveau repas',
		'all_items'						=> 'Tous les repas',
		'view_item'						=> 'Voir le repas',
		'search_items'				=> 'Rechercher des repas',
		'not_found'						=> 'Aucun repas trouvé',
		'not_found_in_trash'	=> 'Aucun repas trouvé dans la corbeille',
		'menu_name'						=> 'Repas'
	);

	$repas_args = array(
		'labels'						=> $repas_labels,
		'public'						=> true,
		'show_ui'						=> true,
		'show_in_menu'			=> true,
		'query_var'					=> true,
		'rewrite'						=> array( 'slug' => 'repas' ),
		'capability_type'		=> 'post',
		'has_archive'				=> true,
		'hierarchical'			=> false,
		'menu_position'			=> 20,
		'supports'					=> array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
	);

	register_post_type( 'repas', $repas_args );
}

add_action('init', 'lur_add_cpt_repas');


/*
 * Add metabox to repas
 */
function lur_repas_meta_boxes(){

	if ( class_exists( 'RW_Meta_Box' ) ){

		$meta_box = array(
				'id'			=> 'lur_repas_info_box',
				'title'		=> 'Info repas',
				'pages'		=> array('repas'),
				'fields'	=> array(
											array(
													'name' => 'Date picker',
													'id'   => "lur_repas_date",
													'type' => 'date',
												),
											array(
													'name' => 'Nombre max',
													'id'   => "lur_repas_max_participants",
													'type' => 'number',
												),
										)
			);

		new RW_Meta_Box( $meta_box );
	}




}
add_action( 'admin_init', 'lur_repas_meta_boxes' );