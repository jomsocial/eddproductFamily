<?php
/**
 * Plugin Name: Edd Product Family
 * Plugin URI: http://peepso.com
 * Description: The EDD Product Family plugin allows you to build product families with EDD (Easy Digital Downloads)
 * Version: 1.0
 * Author: peepso.com
 * Author URI: peepso.com
 * Text Domain: edd_product_family 
 * License: 
 */
 
defined('ABSPATH') or die("No script kiddies please!");

/**
 * Renders the Product Family Pages Admin Page
 *
 * @return void
*/
function edd_product_family_page() {
	global $edd_options;

	require_once plugin_dir_path(__FILE__) . 'helpers/product_family_class.php';		
	$product_family_table = new EDD_Product_Family();

	if ( isset( $_REQUEST['edd_product_family'] ) && $_REQUEST['edd_product_family'] == 'edit_product_family' ) {
		
		$family_details = $product_family_table->get_edit_family();
		require_once plugin_dir_path(__FILE__) . 'template/edit_family.php';

	} elseif ( isset( $_REQUEST['edd_product_family'] ) && $_REQUEST['edd_product_family'] == 'add_product_family' ) {

		require_once plugin_dir_path(__FILE__) . 'template/add_family.php';

	} else {

		$product_family_table->prepare_items();
		require_once plugin_dir_path(__FILE__) . 'template/default.php';	

	}
}

function edd_update_product_family($post_id){
	if ( isset( $_REQUEST['edd_product_family'] ) ) {
		$existing_family = get_post_meta( $post_id, 'edd_product_family', true );
		if($existing_family >= 0){
			update_post_meta( $post_id, 'edd_product_family', esc_html($_POST['edd_product_family']) );
		}else{
			add_post_meta( $post_id, 'edd_product_family', esc_html($_POST['edd_product_family']) );
		}

	}
}
add_action( 'save_post', 'edd_update_product_family', 12 );

/**
 * Creates the admin submenu pages under the Downloads menu 
 *
 * @since 1.0
 * @global $edd_discounts_page
 * @global $edd_payments_page
 * @global $edd_settings_page
 * @global $edd_reports_page
 * @global $edd_add_ons_page
 * @global $edd_settings_export
 * @global $edd_upgrades_screen
 * @return void
 */
function edd_product_family_add_submenu() {
	global $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_add_ons_page, $edd_settings_export, $edd_upgrades_screen, $edd_tools_page;

	$edd_product_family     = add_submenu_page( 'edit.php?post_type=download', __( 'Product Family', 'edd_product_family' ), __( 'Product Family', 'edd_product_family' ), 'manage_shop_settings', 'edd_product_family', 'edd_product_family_page' );
}
add_action( 'admin_menu', 'edd_product_family_add_submenu', 10 );


function edd_add_product_family_metabox($post){
	$defaults = array(
		'post_type'      => 'edd_product_family',
		'paged'          => null,
		'post_status'    => array( 'active' )
	);

	$args = wp_parse_args( $defaults );

	$families = get_posts( $args );

	if(empty($families)){
		_e( 'No product families found.', 'edd_product_family' );
		return true;
	}
	
	$existing_family = get_post_meta( $post->ID, 'edd_product_family', true );
	echo '<table class="form-table">';	
		echo '<tr class="edd_product_family_row">';
			echo '<td class="edd_product_family_select">';
				echo '<select name="edd_product_family" id="edd_product_family_select">';
					echo '<option value="0"' . selected( 0, $existing_family, false ) . '>' . __( 'Select product family', 'edd_product_family' ) . '</option>';
					foreach($families as $family){
						echo '<option value="' . $family->ID . '"' . selected( $family->ID, $existing_family, false ) . '>' . $family->post_title . '</option>';
					}
				echo '</select>&nbsp;';
			echo '<td>';
		echo '</tr>';	
	echo '</table>';		
}

/**
 * Add License Meta Box
 *
 * @since 1.0
 */
function edd_add_product_family_meta_box() {
	global $post;

	add_meta_box( 'edd_add_product_family', __( 'Product Family', 'edd_product_family' ), 'edd_add_product_family_metabox', 'download', 'normal', 'low' );
}
add_action( 'add_meta_boxes', 'edd_add_product_family_meta_box', 101 );