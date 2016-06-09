<?php

/**
* Plugin Name: woo-order-user-efficiency
* Plugin URI: https://github.com/codesnakers/woo-order-user-efficiency
* Description: Wordpress plugin that records average time taken by a user to complete orders
* Version: 1.0
* Author: codesnakers
* Author URI: http://www.codesnakers.com
*/

/**
* Exit if accessed directly.
*
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* Define post type and status constants
*
*/
define('TYPE', 'break-settings');
define('INTER_NOTE', 'inter_time');
define('FINAL_NOTE', 'final_time');
//define('BREAK_NOTE', 'break_time');
define('BREAK_NOTE', 'order_note');
define('STATUS', 'break');
define('DATEFORMAT', 'd-m-Y');

/**
* Load files in plugin
*
*/
require plugin_dir_path(__FILE__).'note_column.php';
require plugin_dir_path(__FILE__).'stopwatch_metabox.php';
require plugin_dir_path(__FILE__).'break_settings_page.php';

/**
* To add stylesheet or script for plugin
* 
*/

function bs_enqueue_scripts(){
	global $pagenow, $typenow;

	/**
	* Load style or script for break settings page.
	*
	*/
	if (isset($_GET['page']) && $_GET['page'] == TYPE && $pagenow == 'admin.php' ) {
		wp_enqueue_style('setings-css', plugins_url('css/settings.css', __FILE__));
	}

	/**
	* Load style for stopwatch metabox
	*
	*/
	if ($pagenow === 'post.php' || $pagenow === 'post-new.php' || $typenow === 'shop_order') {
	    wp_enqueue_style('stopwatch-css', plugins_url('css/stopwatch.css', __FILE__));
	}

	/**
    * Load script for stopwatch metabox
    *
    */
	if ($pagenow === 'post.php' && $typenow === 'shop_order') {
		wp_enqueue_script('stopwatch-js', plugins_url('js/stopwatch.js', __FILE__), array('jquery'), '0.0.1', true);
	}
	
	wp_enqueue_style('thickbox');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('jquery-ui-datepicker');

}

add_action('admin_enqueue_scripts', 'bs_enqueue_scripts');

function abc($column){

$c = array();

$c['stopwatch_time'] = array(

	'title' => 'User Efficiency',
	'reports' => array(
		'custom' => array(
				'title' => 'Custom',
				'description' => '',
				'callback' => 'abc_callback',
				'hide_title' => true
		),
	),

);

$result = array_merge($column, $c);

	return $result;
}

add_filter('woocommerce_admin_reports', 'abc');

function prefixZero($x){
	return $x<10?"0".$x:$x;
}
function formatSeconds( $seconds )
{
  $hours = 0;
  $milliseconds = str_replace( "0.", '', number_format($seconds - floor( $seconds ),3) );

  if ( $seconds > 3600 )
  {
    $hours = floor( $seconds / 3600 );
  }
  $seconds = $seconds % 3600;

	
  return str_pad( $hours, 2, '0', STR_PAD_LEFT )
       . gmdate( ':i:s', $seconds )
       . ($milliseconds ? ".$milliseconds" : '')
  ;
}
function abc_callback(){
	 global $wpdb;
	//
	//  $args = array(
 // 		'comment_type' => TYPE,
 // 	);
 // 	$comments = get_comments();
	//
 // 	 var_dump(get_comments());

	 /*$rows = $wpdb->get_results('SELECT comment_post_ID, comment_author,comment_type, comment_content, comment_date 
									FROM '.$wpdb->prefix.'comments 
									WHERE comment_ID IN
									(SELECT 
										MAX(comment_ID) 
										FROM
										'.$wpdb->prefix.'comments 
										WHERE	comment_type = "'.FINAL_NOTE.'"
										GROUP BY comment_post_ID, comment_type )
									order by comment_ID DESC ');*/
		$cond = "";
		if(isset($_REQUEST['bs_to_date']) and $_REQUEST['bs_to_date']!="" and $_REQUEST['bs_from_date']!=""){
			$cond = " and comment_date BETWEEN '".$_REQUEST['bs_from_date']."' and '".$_REQUEST['bs_to_date']."'";
		}
		$rows = $wpdb->get_results('SELECT comment_post_ID, comment_author,comment_type, comment_content, comment_date 
									FROM '.$wpdb->prefix.'comments 
									WHERE	comment_type = "'.FINAL_NOTE.'"
									'.$cond.'
									order by comment_ID DESC
								');
		$data = array();
		foreach ($rows as $row){
			$data[$row->comment_author][] = $row->comment_content;
		}
		$show = array();
		foreach($data as $user => $arr){
			$total_s = 0;
			$count = 0;
			foreach($arr as $a){
				$ex 	= explode(':', $a);
				$total_s = $total_s + ($ex[0]*60) + $ex[1];
				$count++;
			}
			$show[$row->comment_author] = array('total_orders'=>$count,'total_time'=>formatSeconds($total_s),'avg'=>formatSeconds(((float)($total_s/$count))));
		}
	?>
	<div>
		<form style="float:left;">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
		<input type="hidden" name="tab" value="<?php echo $_REQUEST['tab']; ?>">
		FROM: <input name="bs_from_date" id="bs_from_date" value="<?php echo isset($_REQUEST['bs_from_date'])?$_REQUEST['bs_from_date']:""; ?>"> 
		TO: <input name="bs_to_date" id="bs_to_date" value="<?php echo isset($_REQUEST['bs_to_date'])?$_REQUEST['bs_to_date']:""; ?>"> <button class="button button-primary" id="bs_apply_filter">Apply</button>
		</form>
		<form style="float:left;">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
		<input type="hidden" name="tab" value="<?php echo $_REQUEST['tab']; ?>">
		<button class="button" >Clear</button>
		</form>
	</div>
	<table class="wp-list-table widefat fixed striped stock">
				<thead>
					<tr>
						<th>Username</th>
						<th>Total Orders</th>
						<th>Total Time</th>
						<th>Average Time Per Order</th>
						<!--<th>Type</th>-->
						
						<!--<th>Completed on Date</th>-->
					</tr>
				</thead>
				<tbody>
				<?php 
					$flag = array(); 
					$total_m = $total_s = 0;
				?>
					<?php foreach ($show as $user => $data): ?>
						<tr>
							<td><?php echo $user; ?></td>
							<td><?php echo $data['total_orders']; ?></td>
							<td><?php echo $data['total_time']; ?></td>
							<td><?php echo $data['avg']; ?></td>
						</tr>

					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<th>Username</th>
						<th>Total Orders</th>
						<th>Total Time</th>
						<th>Average Time Per Order</th>
					</tr>
				</tfoot>
	</table>
	<script>
		jQuery(document).ready(function(){
			
			var $ = jQuery;
			$("#bs_from_date").datepicker({dateFormat: "yy-mm-dd"});
			$("#bs_to_date").datepicker({dateFormat: "yy-mm-dd"});
			
		});
	</script>
	<?php
}
