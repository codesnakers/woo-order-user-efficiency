<?php

/**
* Add a custom "stopwatch metabox" to order page
*
*/
function add_stopwatch_metabox(){
	add_meta_box('stopwatch_metabox', __('Stopwatch'), 'stopwatch_meta_callback', 'shop_order', 'side', 'high');
}
add_action('add_meta_boxes', 'add_stopwatch_metabox');

/**
* Callback function for "add_stopwatch_metabox" function
*
*/
function stopwatch_meta_callback(){
	global $post;
?>
	<?php if($post->post_status=="wc-processing"): ?>
	<div id="custom-metabox">
		<?php 
			$comments = get_comments(array("post_id"=>$post->ID,"orderby"=>"comment_ID"));

			$m = 0;	$s = 0;
			$com = "";
			foreach($comments as $comment){
				if($comment->comment_type == INTER_NOTE){
					$com = $comment;
					break;
				}
			}
			
			if($com!=""){
				$t = explode(":",$com->comment_content);
				$m = $t[0];
				$s = $t[1];
			}
			
			//foreach($comments as $i => $c){
				//if($c->comment_type == TYPE){
					//echo $c->comment_content;
				//}
			//}
		?>
        <div id="stopwatch">
			<span id="min"><?php echo $m; ?></span> : <span id="sec"><?php echo $s; ?></span>
            <input type="hidden" name="time" id="time">
        </div>
        <div id="dropdown">
            <select name="dropdown" id="break">
                <?php
                    $args = array(
                        'post_status'   => STATUS,
                        'post_type'     => TYPE,
                    );
                    $break_settings = new WP_Query($args);
                    foreach ($break_settings->posts as $bs): ?>
                        <option value="<?php echo ucwords(str_replace('-', ' ', $bs->post_name)); ?>"><?php echo ucwords(str_replace('-', ' ', $bs->post_name)); ?></option>
                    <?php endforeach; ?>
            </select>
        </div>
        <div id="operation">
			<button class="button button-primary" name="pause" id="pause">Pause</button>
        </div>
	</div>
	<script>
		jQuery(document).ready(function(){
			var $ = jQuery;
			window.onbeforeunload = confirmExit;
			function confirmExit(){
				var status = $("#order_status").val();
				if(status=="wc-processing"){
					breakTime = "";
					break_or_time = "time";
					if(jQuery("#TB_overlay").length>0){/* break modal is open */
						breakTime = jQuery("#break").val()+" "+$("#time-break").val();
						break_or_time = "both";
					}
					$.ajax({
						url: ajaxurl,
						async: false,
						data: {
							'action':'bs_insert_note',
							'time' : $("#time").val(),
							'break_or_time':break_or_time,
							'order_status':$("#order_status").val(),
							'order_id':'<?php echo $post->ID; ?>',
							'type':'<?php echo INTER_NOTE; ?>',
							'breakTime':breakTime
						}
					}).done(function(data){
						console.log(data);
						clearBreakTimer();
						tb_remove();
						timer();
					}).error(function(e){
						console.log(e);
					});
				}
				//alert("confirm exit is being called");
				return "";
			}
		});
	</script>
	<?php endif; ?>
	<!--<button type="button" class="button button-primary" name="xyz" id="xyz">Pause</button>
	<a href="#TB_inline?width=400&height=200&inlineId=stopwatch_pause_modal" class="thickbox bs_open_modal" style="display:block;">Hi</a>-->
	<?php add_thickbox(); ?>
		<div id="stopwatch_pause_modal" style="display:none;">
			 <p>
				<div id="stopwatch-break">
					<span id="min-break">0</span> : <span id="sec-break">0</span>
					<input type="hidden" name="time-break" id="time-break">
				</div>
				<button class="button button-primary" name="resume-break" id="resume-break">Resume</button>
			 </p>
		</div>
<?php
}

/**
* Save stopwatch meta box time in comment when order status is wc-completed
*
*/
function bs_save_data($post_id){

     if (isset($_POST['order_status']) && $_POST['order_status'] == 'wc-completed') {

        $user = get_userdata(get_current_user_id());

        $current_time = current_time('mysql');
        $data = array(
            'comment_post_ID'       => $post_id,
            'comment_author'        => $user->user_login,
            'comment_author_email'  => $user->user_email,
            'comment_content'       => sanitize_text_field($_POST['time']),
            'comment_type'          => FINAL_NOTE,
            'comment_parent'        => 0,
            'user_id'               => get_current_user_id(),
            'comment_date'          => $current_time,
            'comment_approved'      => 1,
        );

        wp_insert_comment($data);
     }
}

function bs_ajax_save_data(){
	 //echo $_REQUEST['time'];
	 if (isset($_REQUEST['order_status']) and ($_REQUEST['order_status'] == 'wc-processing' or $_REQUEST['order_status'] == 'wc-completed')) {
		$user = get_userdata(get_current_user_id());
		$current_time = current_time('mysql');

		if($_REQUEST['break_or_time']=="time" or $_REQUEST['break_or_time']=="both"){
			$data = array(
				'comment_post_ID'       => $_REQUEST['order_id'],
				'comment_author'        => $user->user_login,
				'comment_author_email'  => $user->user_email,
				'comment_content'       => sanitize_text_field($_REQUEST['time']),
				'comment_type'          => INTER_NOTE,
				'comment_parent'        => 0,
				'user_id'               => get_current_user_id(),
				'comment_date'          => $current_time,
				'comment_approved'      => 1,
			);
			wp_insert_comment($data);
		}
		if($_REQUEST['break_or_time']=="break" or $_REQUEST['break_or_time']=="both"){
			$data = array(
				'comment_post_ID'       => $_REQUEST['order_id'],
				'comment_author'        => $user->user_login,
				'comment_author_email'  => $user->user_email,
				'comment_content'       => sanitize_text_field($_REQUEST['breakTime']),
				'comment_type'          => BREAK_NOTE,
				'comment_parent'        => 0,
				'user_id'               => get_current_user_id(),
				'comment_date'          => $current_time,
				'comment_approved'      => 1,
			);
			wp_insert_comment($data);
		}
     }
 }
 //admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=16&_wpnonce=02d24ceb0c
 function bs_ajax_order_comp_lv(){
	 global $wpdb;
	if (isset($_REQUEST['status']) and $_REQUEST['status'] == 'completed') {
		$user = get_userdata(get_current_user_id());
		$current_time = current_time('mysql');
		$order_id = $_REQUEST['order_id'];

		//$comments = get_comments(array("post_id"=>$order_id,"orderby"=>"comment_ID"));
		$comments = $wpdb->get_results('SELECT comment_ID, comment_post_ID, comment_author,comment_type, comment_content, comment_date 
									FROM '.$wpdb->prefix.'comments 
									WHERE	comment_post_ID = "'.$order_id.'"
									order by comment_ID DESC
									');
		
		$com = null;
		error_log( print_r( $order_id, true ) );
		error_log( print_r( $comments, true ) );
		
		foreach($comments as $comment){
			if($comment->comment_type == INTER_NOTE){
				$com = $comment;
				break;
			}
		}
		
		if(is_null($com)){
			$data = array(
				'comment_post_ID'       => $order_id,
				'comment_author'        => $user->user_login,
				'comment_author_email'  => $user->user_email,
				'comment_content'       => "0:0",
				'comment_type'          => FINAL_NOTE,
				'comment_parent'        => 0,
				'user_id'               => get_current_user_id(),
				'comment_date'          => $current_time,
				'comment_approved'      => 1,
			);
			wp_insert_comment($data);
		}else{
			$commentarr = array();
			$commentarr['comment_ID'] = $com->comment_ID;
			$commentarr['comment_type'] = FINAL_NOTE;
			wp_update_comment( $commentarr );
		}
     }
 }
 
 /*function bs_ajax_save_break_data(){
	 //echo $_REQUEST['time'];
	 if (isset($_REQUEST['order_status']) and ($_REQUEST['order_status'] == 'wc-processing' or $_REQUEST['order_status'] == 'wc-completed')) {

        $user = get_userdata(get_current_user_id());

        $current_time = current_time('mysql');
        $data = array(
            'comment_post_ID'       => $_REQUEST['order_id'],
            'comment_author'        => $user->user_login,
            'comment_author_email'  => $user->user_email,
            'comment_content'       => sanitize_text_field($_REQUEST['time']),
            'comment_type'          => BREAK_NOTE,
            'comment_parent'        => 0,
            'user_id'               => get_current_user_id(),
            'comment_date'          => $current_time,
            'comment_approved'      => 1,
        );

        wp_insert_comment($data);
     }
 }*/
 
 add_action('woocommerce_order_status_changed', 'bs_save_data');
 add_action('wp_ajax_bs_insert_note', 'bs_ajax_save_data');
 add_action('wp_ajax_woocommerce_mark_order_status', 'bs_ajax_order_comp_lv');
 /*add_action('wp_ajax_bs_insert_break_note', 'bs_ajax_save_break_data');*/