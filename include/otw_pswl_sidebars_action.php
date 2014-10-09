<?php
/** Sidebar actions
  *  - delete sidebar
  *  - activate
  *  - deactivate
  */
	$otw_sidebar_values = array(
		'title'       =>  '',
		'description' =>  '',
		'replace'     =>  '',
		'status'      =>  'inactive'
	);
	
	$otw_sidebar_id = '';
	$otw_action = '';
	
	if( isset( $_GET['action'] ) ){
		
		switch( $_GET['action'] ){
			
			case 'delete':
					$otw_action = 'otw_pswl_sidebar_delete';
					$page_title = __( 'Delete Sidebar', 'otw_pswl' );
					$confirm_text = __( 'Please confirm to delete the sidebar', 'otw_pswl' );
				break;
			case 'activate':
					$otw_action = 'otw_pswl_sidebar_activate';
					$page_title = __( 'Activate Sidebar', 'otw_pswl' );
					$confirm_text = __( 'Please confirm to activate the sidebar', 'otw_pswl' );
				break;
			case 'deactivate':
					$otw_action = 'otw_pswl_sidebar_deactivate';
					$page_title = __( 'Deactivate Sidebar', 'otw_pswl' );
					$confirm_text = __( 'Please confirm to deactivate the sidebar', 'otw_pswl' );
				break;
		}
	}
	if( !$otw_action ){
		wp_die( __( 'Invalid sidebar action', 'otw_pswl' ) );
	}
	if( isset( $_GET['sidebar'] ) ){
		
		$otw_sidebar_id = $_GET['sidebar'];
		$otw_sidebars = get_option( 'otw_sidebars' );
		
		if( is_array( $otw_sidebars ) && isset( $otw_sidebars[ $otw_sidebar_id ] ) ){
			
			$otw_sidebar_values['title'] = $otw_sidebars[ $otw_sidebar_id ]['title'];
			$otw_sidebar_values['description'] = $otw_sidebars[ $otw_sidebar_id ]['description'];
			$otw_sidebar_values['replace'] = $otw_sidebars[ $otw_sidebar_id ]['replace'];
			$otw_sidebar_values['status'] = $otw_sidebars[ $otw_sidebar_id ]['status'];
			$otw_sidebar_values['validfor'] = $otw_sidebars[ $otw_sidebar_id ]['validfor'];
		}
	}
	if( !$otw_sidebar_id ){
		wp_die( __( 'Invalid sidebar', 'otw_pswl' ) );
	}
	
?>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br/></div>
	<h2>
		<?php echo $page_title; ?>
		<a class="preview button" href="admin.php?page=otw-pswl-sidebars-list"><?php _e( 'Back To List Of Sidebars', 'otw_pswl' );?></a>
	</h2>
	<?php include_once( 'otw_pswl_help.php' );?>
	<div id="ajax-response"></div>
	<div class="form-wrap" id="poststuff">
		<form method="post" action="" class="validate">
			<input type="hidden" name="otw_action" value="<?php echo $otw_action?>" />
			<?php wp_original_referer_field(true, 'previous'); wp_nonce_field('otw-pswl-sidebars-action'); ?>

			<div id="post-body">
				<div id="post-body-content">
					<div id="col-right">
						<div class="form-field form-required">
							<?php _e( 'Sidebar title', 'otw_pswl' );?>:
							<strong><?php echo $otw_sidebar_values['title']?></strong>
						</div>
						<div class="form-field">
							<?php _e( 'Status', 'otw_pswl' );?>:
							<strong><?php _e( ucfirst( $otw_sidebar_values['status']  ), 'otw_pswl' )?></strong>
						</div>
						<div class="form-field">
							<?php _e( 'Description', 'otw_pswl' )?>:
							<strong><?php echo $otw_sidebar_values['description']?></strong>
						</div>
					</div>
					<div id="col-left">
						<p>
							<?php echo $confirm_text;?>
						</p>
						<p class="submit">
							<input type="submit" class="button button-primary button-large" value="<?php _e( 'Confirm', 'otw_pswl') ?>" name="submit" />
							<input type="submit" value="<?php _e( 'Cancel', 'otw_pswl' ) ?>" name="cancel" class="button"/>
						</p>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>