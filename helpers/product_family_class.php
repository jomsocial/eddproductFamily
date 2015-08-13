<?php

defined('ABSPATH') or die("No script kiddies please!");

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class EDD_Product_Family extends WP_List_Table{
	
	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.4
	 */
	public $per_page = 30;

	/**
	 *
	 * Total number of discounts
	 * @var string
	 * @since 1.4
	 */
	public $total_count;

	/**
	 * Active number of discounts
	 *
	 * @var string
	 * @since 1.4
	 */
	public $active_count;

	/**
	 * Inactive number of discounts
	 *
	 * @var string
	 * @since 1.4
	 */
	public $inactive_count;

	/**
	 * Get things started
	 *
	 * @since 1.4
	 * @uses EDD_Discount_Codes_Table::get_discount_code_counts()
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;
		register_post_type( 'edd_product_family', array() );//$edd_product_family_args
		parent::__construct( array(
			'singular'  => edd_get_label_singular(),    // Singular name of the listed records
			'plural'    => edd_get_label_plural(),    	// Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );

	}	
	
	/**
	 * Retrieve the discount code counts
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function get_product_family_counts() {
		$product_family_count  	= wp_count_posts( 'edd_product_family' );
		$this->active_count   = $product_family_count->active;
		$this->inactive_count = $product_family_count->inactive;
		$this->total_count    = $product_family_count->active + $product_family_count->inactive;		
	}	

	/**
	 * Get Discounts
	 *
	 * Retrieves an array of all available discount codes.
	 *
	 * @since 1.0
	 * @param array $args Query arguments
	 * @return mixed array if discounts exist, false otherwise
	 */
	function edd_get_families( $args = array() ) {
		$this->get_product_family_counts();
		
		$defaults = array(
			'post_type'      => 'edd_product_family',
			'posts_per_page' => 30,
			'paged'          => null,
			'post_status'    => array( 'active', 'inactive', 'expired' )
		);
	
		$args = wp_parse_args( $args, $defaults );
	
		$family = get_posts( $args );
	
		if ( $family ) {
			return $family;
		}
	
		if( ! $family && ! empty( $args['s'] ) ) {
			// If no discounts are found and we are searching, re-query with a meta key to find discounts by code
			$args['meta_key']     = 'edd_product_family';
			$args['meta_value']   = $args['s'];
			$args['meta_compare'] = 'LIKE';
			unset( $args['s'] );
			$family = get_posts( $args );
		}
	
		if( $family ) {
			return $family;
		}
	
		return false;
	}

	/**
	 * Retrieve all the data for all the discount codes
	 *
	 * @access public
	 * @since 1.4
	 * @return array $discount_codes_data Array of all the data for the discount codes
	 */
	public function product_family_data() {
		$discount_codes_data = array();

		$per_page = $this->per_page;

		$orderby 		= isset( $_GET['orderby'] )  ? $_GET['orderby']                  : 'ID';
		$order 			= isset( $_GET['order'] )    ? $_GET['order']                    : 'DESC';
		$order_inverse 	= $order == 'DESC'           ? 'ASC'                             : 'DESC';
		$status 		= isset( $_GET['status'] )   ? $_GET['status']                   : array( 'active', 'inactive' );
		$meta_key		= isset( $_GET['meta_key'] ) ? $_GET['meta_key']                 : null;
		$search         = isset( $_GET['s'] )        ? sanitize_text_field( $_GET['s'] ) : null;
		$order_class 	= strtolower( $order_inverse );

		$families = $this->edd_get_families( array(
			'posts_per_page' => $per_page,
			'paged'          => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			'orderby'        => $orderby,
			'order'          => $order,
			'post_status'    => $status,
			'meta_key'       => $meta_key,
			's'              => $search
		) );

		return $families;
	}

	public function get_edit_family(){
		$id = isset( $_REQUEST['family'] ) ? esc_html($_REQUEST['family']) : '';
		if($id > 0){
			$family = get_post( $id );
			return $family;
		}
		return new stdClass();
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 1.4
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base           = admin_url('edit.php?post_type=download&page=edd_product_family');

		$current        = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . $this->inactive_count  . ')</span>';

		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'edd_product_family') . $total_count ),
			'active'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'active', $base ), $current === 'active' ? ' class="current"' : '', __('Active', 'edd_product_family') . $active_count ),
			'inactive'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'inactive', $base ), $current === 'inactive' ? ' class="current"' : '', __('Inactive', 'edd_product_family') . $inactive_count ),
		);

		return $views;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'        	=> '<input type="checkbox" />',
			'post_title'  	=> __( 'Name', 'edd_product_family' ),
/*			'code'  	=> __( 'Code', 'edd' ),
			'amount'  	=> __( 'Amount', 'edd' ),
			'uses'  	=> __( 'Uses', 'edd' ),
			'max_uses' 	=> __( 'Max Uses', 'edd' ),
			'start_date'=> __( 'Start Date', 'edd' ),
			'expiration'=> __( 'Expiration', 'edd' ),*/
			'status'  	=> __( 'Status', 'edd' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'post_title'   => array( 'post_title', false )
		);
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.4
	 *
	 * @param array $item Contains all the data of the discount code
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ){
			default:
				return $item->$column_name;
		}
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 1.4
	 * @param array $item Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'family',
			/*$2%s*/ $item->ID
		);
	}

	/**
	 * Render the status column
	 *
	 * @access public
	 * @since 1.9.9
	 * @param array $item Contains all the data for the checkbox column
	 * @return string Displays the discount status
	 */
	function column_status( $item ) {
		switch( $item->post_status ){
			case 'expired' :
				$status = __( 'Expired', 'edd_product_family' );
				break;
			case 'inactive' :
				$status = __( 'Inactive', 'edd_product_family' );
				break;
			case 'active' :
			default :
				$status = __( 'Active', 'edd_product_family' );
				break;
		}
		return $status;
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.7.2
	 * @access public
	 */
	function no_items() {
		_e( 'No product families found.', 'edd_product_family' );
	}
	
	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since 1.4
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'activate_family'   => __( 'Activate', 'edd' ),
			'deactivate_family' => __( 'Deactivate', 'edd' ),
			'delete_family'     => __( 'Delete', 'edd' )
		);

		return $actions;
	}	
	
	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function process_bulk_action() {
		$ids = isset( $_GET['family'] ) ? $_GET['family'] : false;

		if ( ! is_array( $ids ) )
			$ids = array( $ids );

		foreach ( $ids as $id ) {
			if ( 'delete_family' === $this->current_action() ) {
				$this->edd_remove_family( $id );
			}
			if ( 'activate_family' === $this->current_action() ) {
				$this->edd_update_family_status( $id, 'active' );
			}
			if ( 'deactivate_family' === $this->current_action() ) {
				$this->edd_update_family_status( $id, 'inactive' );
			}
		}

	}	
	/**
	 * Process the actions
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function process_actions() {
		if ( isset( $_REQUEST['edd_product_family'] ) && $_REQUEST['edd_product_family'] == 'add_family' ) {
			$this->add_product_family();

 		} elseif ( isset( $_REQUEST['edd_product_family'] ) && $_REQUEST['edd_product_family'] == 'deactivate_family' ) {
			$this->edd_update_family_status($_REQUEST['family'], 'inactive');
		} elseif ( isset( $_REQUEST['edd_product_family'] ) && $_REQUEST['edd_product_family'] == 'activate_family' ) {
			$this->edd_update_family_status($_REQUEST['family'], 'active');
		} elseif ( isset( $_REQUEST['edd_product_family'] ) && $_REQUEST['edd_product_family'] == 'delete_family' ) {
			$this->edd_remove_family($_REQUEST['family']);
		}
	}
	/**
	 * Render the Name Column
	 *
	 * @access public
	 * @since 1.4
	 * @param array $item Contains all the data of the product family
	 * @return string Data shown in the Name column
	 */
	public function column_post_title( $item ) {
		$family     	= get_post( $item->ID );
		$base         	= admin_url( 'edit.php?post_type=download&page=edd-discounts&edd_product_family=edit_product_family&family=' . $item->ID );
		$row_actions  	= array();

		$row_actions['edit'] = '<a href="' . add_query_arg( array( 'edd_product_family' => 'edit_product_family', 'family' => $family->ID ) ) . '">' . __( 'Edit', 'edd_product_family' ) . '</a>';

		if( strtolower( $item->post_status ) == 'active' ) {
			$row_actions['deactivate'] = '<a href="' . add_query_arg( array( 'edd_product_family' => 'deactivate_family', 'family' => $family->ID ) ) . '">' . __( 'Deactivate', 'edd_product_family' ) . '</a>';
		} elseif( strtolower( $item->post_status ) == 'inactive' ) {
			$row_actions['activate'] = '<a href="' . add_query_arg( array( 'edd_product_family' => 'activate_family', 'family' => $family->ID ) ) . '">' . __( 'Activate', 'edd_product_family' ) . '</a>';
		}

		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'edd_product_family' => 'delete_family', 'family' => $family->ID ) ), 'edd_product_family_nonce' ) . '">' . __( 'Delete', 'edd_product_family' ) . '</a>';

		$row_actions = apply_filters( 'edd_product_family_nonce', $row_actions, $family );

		return stripslashes( $item->post_title ) . $this->row_actions( $row_actions );
	}

	/**
	 * Updates a family status from one status to another.
	 *
	 * @since 1.0
	 * @param int $code_id Family ID (default: 0)
	 * @param string $new_status New status (default: active)
	 * @return bool
	 */
	public function edd_update_family_status( $code_id = 0, $new_status = 'active' ) {
		$family = get_post(  esc_html($code_id) );
	
		if ( isset($family->ID) && $family->ID > 0 ) {
			wp_update_post( array( 'ID' => $code_id, 'post_status' => $new_status ) );
			return true;
		}
		return false;
	}
	
	/**
	 * Removes a product family.
	 *
	 * @since 1.0
	 * @param int $code_id Family ID (default: 0)
	 * @return bool
	 */
	public function edd_remove_family( $code_id = 0 ) {
		$family = get_post(  esc_html($code_id) );
	
		if ( isset($family->ID) && $family->ID > 0 ) {
			wp_delete_post( $code_id );
			return true;
		}
		return false;
	}	

	/**
	 * Setup the final data for the table
	 *
	 * @return void
	 */
	public function prepare_items() {
		$per_page = $this->per_page;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();
		$this->process_actions();
		$data = $this->product_family_data();
		$current_page = $this->get_pagenum();
		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch( $status ) {
			case 'active':
				$total_items = $this->active_count;
				break;
			case 'inactive':
				$total_items = $this->inactive_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page )
			)
		);
	}
	
	public function add_product_family(){
		$name 		= isset( $_REQUEST['name'] ) ? esc_html( $_REQUEST['name'] ) : '';
		$family_id	= isset( $_REQUEST['family_id'] ) ? esc_html( $_REQUEST['family_id'] ) : '';		
		$redirect 	= $_REQUEST['edd_product_family-redirect'];

		$family_post = get_post($family_id);
		
		if($family_id > 0 && $family_post->ID > 0){
			wp_update_post( array(
				'ID'          => $family_id,
				'post_title'  => $name
			) );
	
			//wp_update_post($wp_update_post);
			//wp_redirect($redirect);
			//exit;					
		}else{		
			$discount_id = wp_insert_post( array(
				'post_type'   => 'edd_product_family',
				'post_title'  => $name,
				'post_status' => 'active'
			) );		
			//wp_redirect($redirect);
			//exit;
		}
		return true;
	}

}