<?php
/**
* To add custom page in admin menu
* 
*/
function custom_add_menu_page(){
	add_menu_page ( 
			ucwords(str_replace('-', ' ', TYPE)),
			ucwords(str_replace('-', ' ', TYPE)),
			'manage_options',
			TYPE,
			'break_settings_callback',
			'', // for icon url
			81 );
}

add_action('admin_menu', 'custom_add_menu_page');

/**
* Callback function for 'custom_add_menu_page' function
* that contains field list and add break field
* 
*/
function break_settings_callback(){

    /**
    * To add break in post
    *
    */
	if (isset($_POST['add'])) {

		$post = array(
				'post_content' 	=> sanitize_text_field($_POST['add']),
				'post_status' 	=> STATUS,
				'post_type' 	=> TYPE,
				'post_name' 	=> sanitize_text_field($_POST['add'])
			);

		if (wp_insert_post ( $post )) {
			$success = 'successfully added.';
		} else {
			$error = 'error in add.';
		}
		
	}

    /**
    * To delete break from post
    *
    */
	if (isset($_POST['delete_break'])) {
		
		if (wp_delete_post ( (int)sanitize_text_field($_POST['delete_break']) , true )) {
			$success = 'successfully deleted';
		} else {
			$error = 'error in delete.';
		}
		
	}
	 

	?>

	<div id="heading">
		<h1><?=ucwords(str_replace('-', ' ', TYPE));?></h1>

		<?php if (isset($success)): ?>
			<div id="message" class="updated"><p><?=$success; ?></p></div>
		<?php endif; ?>

		<?php if (isset($error)): ?>
			<div id="message" class="error"><p><?=$error; ?></p></div>
		<?php endif; ?>

	</div>

	<div id="settings">
		
		<div id="add_break">
			
			<form action="admin.php?page=break-settings" method="post">
				<div class="form-group">
					<label for="add" class="label">Add Break : </label>
					<input type="text" name="add" id="add" placeholder="Enter Break" class="form-control">
					<button type="submit" name="submit" class="button button-primary add">Add Break</button>
				</div>
			</form>

		</div>
		
		<div id="list_break">
			<?php 

				$args = array(
					'post_status' 		=> STATUS,
					'post_type' 		=> TYPE,
					'posts_per_page'	=> 25
				);

				$break_settings = new WP_Query($args);

				$count = 1;
			?>

			<?php if (!empty($break_settings->posts)): ?>

				<table class="wp-list-table widefat fixed striped pages table">
					<tr class="iedit author-self level-0 type-page status-publish hentry">
						<th class="manage-column">#</th>
						<th class="manage-column">Title</th>
						<th class="manage-column">Action</th>
					</tr>
				
					<?php foreach($break_settings->posts as $bs) : ?>

						<tr class="iedit author-self level-0 type-page status-publish hentry">
							<td><?=$count; ?></td>
							<td class="row-title"><?=ucwords(str_replace('-', ' ', $bs->post_name)); ?></td>
							<td>
								<form action=<?="admin.php?page=".TYPE;?> method="post">
									<input type="hidden" name="delete_break" class="delete_break" value="<?=$bs->ID; ?>">
									<span class="trash">
									<input type="submit" name="delete_btn" class="delete-button" value="Delete">
									</span>
								</form>
							</td>
						</tr>

					<?php $count++; ?>
					<?php endforeach; ?>

				</table>


			<?php else: ?>

				<p class="label">No Breaks to show.....</p>

			<?php endif; ?>

			
		</div>

	</div>

	<?php
}