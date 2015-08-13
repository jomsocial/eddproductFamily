<?php
 
defined('ABSPATH') or die("No script kiddies please!");
?>
<div class="wrap">
	<h2><?php _e( 'Product Family', 'edd_product_family' ); ?><a href="<?php echo add_query_arg( array( 'edd_product_family' => 'add_product_family' ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'edd_product_family' ); ?></a></h2>
	<?php do_action( 'edd_discounts_page_top' ); ?>
	<form id="js-product-family-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd_product_family' ); ?>">
		<?php $product_family_table->search_box( __( 'Search', 'edd_product_family' ), 'edd_product_family' ); ?>

		<input type="hidden" name="post_type" value="download" />
		<input type="hidden" name="page" value="edd_product_family" />

		<?php $product_family_table->views() ?>
		<?php $product_family_table->display() ?>
	</form>
	<?php do_action( 'edd_family_page_bottom' ); ?>
</div>