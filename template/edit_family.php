<?php
 
defined('ABSPATH') or die("No script kiddies please!");
$downloads = get_posts( array( 'post_type' => 'download', 'nopaging' => true ) );
?>
<h2><?php _e( 'Edit Product Family', 'edd_product_family' ); ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd_product_family' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd_product_family' ); ?></a></h2>
<form id="edd-add-discount" action="" method="POST">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-name"><?php _e( 'Name', 'edd_product_family' ); ?></label>
				</th>
				<td>
					<input name="name" id="family-name" type="text" value="<?php echo isset($family_details->post_title) ? $family_details->post_title : ''?>" style="width: 300px;"/>
					<p class="description"><?php _e( 'The name of this family', 'edd_product_family' ); ?></p>
				</td>
			</tr>
	<p class="submit">
		<input type="hidden" name="edd_product_family" value="add_family"/>
		<input type="hidden" name="family_id" value="<?php echo isset($family_details->ID) ? $family_details->ID : ''?>"/>
		<input type="hidden" name="edd_product_family-redirect" value="<?php echo admin_url( 'edit.php?post_type=download&page=edd_product_family' ); ?>"/>
		<input type="submit" value="<?php _e( 'Save Family', 'edd_product_family' ); ?>" class="button-primary"/>
	</p>
</form>