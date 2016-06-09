 var t = '',bt = '';

     /**
      * Stopwatch function of stopwatch meta box and set the value of time field
      *
      * */
 		function timer(){
 			var min = parseInt(jQuery('#min').text());
 			var sec = parseInt(jQuery('#sec').text());

 			var s = sec+1;
 			if (s === 60) { s = 0 };

 			jQuery('#time').val(min+':'+s);

 			if (sec === 59) {
 				jQuery('#sec').text(00);
				jQuery('#min').text(++min);
 			} else {
 				jQuery('#sec').text(++sec);
 			}
			
 			t = setTimeout(timer, 1000);
 		};

 		timer();
	
	function breakTimer(){
		var min = parseInt(jQuery('#min-break').text());
		var sec = parseInt(jQuery('#sec-break').text());

		var s = sec+1;
		if (s === 60) { s = 0 };

		jQuery('#time-break').val(min+':'+s);

		if (sec === 59) {
			jQuery('#sec-break').text(00);
			jQuery('#min-break').text(++min);
		} else {
			jQuery('#sec-break').text(++sec);
		}

		bt = setTimeout(breakTimer, 1000);
	}
	
	function clearBreakTimer(){
		jQuery('#min-break').text("0");
		jQuery('#sec-break').text("0");
		jQuery('#time-break').val("");
		clearTimeout(bt);
	}
	
 jQuery(document).ready(function(){
 	var $ = jQuery;
	
    /**
     * To pause stopwatch, hide it self and show resume button
     *
     * */
 	jQuery('#stopwatch_metabox').on('click', '#pause', function(){
 		//jQuery('#resume').show();
		//tb_show('Test', '#TB_inline?width=400&height=200&inlineId=stopwatch_pause_modal');
		//e.preventDefault();
 		//jQuery('#resume').show();
		tb_show('Break Time', '#TB_inline?width=400&height=200&inlineId=stopwatch_pause_modal');
		jQuery("#TB_overlay").unbind("click");
		breakTimer();
		jQuery(this).hide();
 		clearTimeout(t);

 		return false;
 	});

	/*jQuery('#xyz').click(function(e){
		e.preventDefault();
 		//jQuery('#resume').show();
		tb_show('Break Time', '#TB_inline?width=400&height=200&inlineId=stopwatch_pause_modal');
		jQuery("#TB_overlay").unbind("click");
		//jQuery(".bs_open_modal").click();
 		return false;
 	});*/

     /**
      * To resume stopwatch, hide it self and show pause button
      *
      * */
 	jQuery('#resume-break').click(function(){
 		jQuery('#pause').show();
		breakTime = jQuery("#break").val()+" "+$("#time-break").val();
		$.ajax({
			url: ajaxurl,
			data: {
				'action':'bs_insert_note',
				'break_or_time':'break',
				'time' : $("#time").val(),
				'order_status':$("#order_status").val(),
				'order_id':jQuery("#post_ID").val(),
				'breakTime':breakTime
			}
		}).done(function(data){
			console.log(data);
		}).error(function(e){
			console.log(e);
		});
		clearBreakTimer();
		tb_remove();
 		//jQuery(this).hide();
 		timer();
 		return false;
 	});
    /**
     * To set value of bs_action field
     * */
 	jQuery('#order_status').on('blur', function(){
 		var value = jQuery(this).val();
 		jQuery('#bs_action').val(jQuery().text());
 	});

	
	jQuery(document).on("click","#TB_overlay",function(){
		console.log("closed");
	});
	

 });