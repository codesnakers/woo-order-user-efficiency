<?php
/**
* Add a custom "Note" column in wooCommerce's order list
*
*/
function note_column( $existing_columns){

		$columns                     	= array();
		$columns['cb']               	= $existing_columns['cb'];
		$columns['order_status']     	= '<span class="status_head tips" data-tip="' . esc_attr__( 'Status' ) . '">' . esc_attr__( 'Status' ) . '</span>';
		$columns['order_title']      	= __( 'Order' );
		$columns['order_items']      	= __( 'Purchased' );
		$columns['billing_address']  	= __( 'Billing' );
		$columns['shipping_address'] 	= __( 'Ship to' );
		$columns['customer_message'] 	= '<span class="notes_head tips" data-tip="' . esc_attr__( 'Customer Message' ) . '">' . esc_attr__( 'Customer Message' ) . '</span>';
		$columns['order_notes']      	= '<span class="order-notes_head tips" data-tip="' . esc_attr__( 'Order Notes' ) . '">' . esc_attr__( 'Order Notes' ) . '</span>';
		$columns['order_date']       	= __( 'Date' );
		$columns['order_total']      	= __( 'Total' );
		$columns['orderid']				= __('Order Id');
		$columns['time_taken']			= __('Time taken');
		$columns['username']			= __('Order Completed By');
		$columns['completed_on_date']	= __('Completed on date');
		$columns['order_actions']    	= __( 'Actions' );

		return $columns;
}

add_filter('manage_shop_order_posts_columns', 'note_column', 11, 1);

/**
* Add data to custom "Note" column of order list
*
*/
function set_data_to_note_column($column, $post_id){

	$args = array(
		'comment_post_ID' 	=> $post_id,
		/*'comment_type' 		=> TYPE,*/
		'comment_approved' 	=> 1,
		'orderby' 			=> 'comment_ID',
		'order' 			=> 'DESC'
		);

	$notes = get_comments( $args );
	
	//$note = $notes[count($notes)-1];
	
	switch ($column) {
		case 'orderid':

			foreach ($notes as $note) {
				
				if ($post_id === (int)$note->comment_post_ID && ($note->comment_type === FINAL_NOTE || $note->comment_type === INTER_NOTE) ) {
					echo '<p>'.$note->comment_post_ID.'</p>';
					break;
				}

			}

		break;

		case 'time_taken':

			foreach ($notes as $note) {
				
				if ($post_id === (int)$note->comment_post_ID && ($note->comment_type === FINAL_NOTE || $note->comment_type === INTER_NOTE) ) {
					
					$ex 	= explode(':', $note->comment_content);
					$min 	= ($ex[0] < 10)? '0'.$ex[0] : $ex[0];
					$sec 	= ($ex[1] < 10)? '0'.$ex[1] : $ex[1];

					echo '<p>'.$min.' : '.$sec.'</p>';
					break;
				}

			}

		break;

		case 'username':

			foreach ($notes as $note) {
				
				if ($post_id === (int)$note->comment_post_ID && ($note->comment_type === FINAL_NOTE || $note->comment_type === INTER_NOTE) ) {
					echo '<p>'.$note->comment_author.'</p>';
					break;
				}

			}

		break;

		case 'completed_on_date':

			foreach ($notes as $note) {
				
				if ($post_id === (int)$note->comment_post_ID && ($note->comment_type === FINAL_NOTE || $note->comment_type === INTER_NOTE) ) {
					$datetime = date_create($note->comment_date);
					echo '<p>'.date_format($datetime, DATEFORMAT).'</p>';
					break;
				}
			}
		break;
	}
}

add_action( 'manage_shop_order_posts_custom_column', 'set_data_to_note_column', 12, 2);
