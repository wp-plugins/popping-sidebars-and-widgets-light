<?php
	global $validate_messages, $otw_pswl_grid_manager_object, $wp_pswl_int_items, $otw_pswl_overlay_object;
	
	$page_title = __( 'Create New overlay', 'otw_pswl' );
	
	$otw_overlay_id = 0;
	
	$page_settings = array();
	
	
	$otw_overlay_values = array();
	$otw_overlay_values['loaded'] = false;
	$otw_overlay_values['title'] = '';
	$otw_overlay_values['status'] = 'active';
	$otw_overlay_values['type'] = 'full_bar';
	$otw_overlay_values['grid_content'] = '';
	$otw_overlay_values['validfor'] = array();
	$otw_overlay_values['exclude_posts_for'] = array();
	$otw_overlay_values['options'] = array();
	
	$otw_admin_settings = get_option( 'otw_admin_settings' );
	
	//init default values of the elements
	foreach( $otw_pswl_overlay_object->overlay_types as $overlay_type => $overlay_type_data ){
		
		foreach( $overlay_type_data['options'] as $option_type => $type_options ){
			
			$page_settings['metabox_'.$option_type ] = 'open';
			
			if( is_array( $otw_admin_settings ) && isset( $otw_admin_settings['page'] ) && isset( $otw_admin_settings['page']['otw-pswl-manage'] ) && isset( $otw_admin_settings['page']['otw-pswl-manage']['metabox_'.$option_type] ) ){
				
				if( in_array( $otw_admin_settings['page']['otw-pswl-manage']['metabox_'.$option_type], array( 'open', 'closed' ) ) ){
					$page_settings['metabox_'.$option_type ] = $otw_admin_settings['page']['otw-pswl-manage']['metabox_'.$option_type];
				}
			}
			
			foreach( $type_options['items'] as $option_name => $option_item ){
			
				$otw_overlay_values['options'][ $overlay_type.'_'.$option_name ] = $option_item['default'];
				
				if( isset( $option_item['subfields'] ) && count( $option_item['subfields'] ) ){
					
					foreach( $option_item['subfields'] as $subfield => $subfield_data ){
					
						$otw_overlay_values['options'][ $overlay_type.'_'.$option_name.'_'.$subfield ] = $subfield_data['default'];
					}
				}
			}
		}
	}
	
	$valid_for_values = array();
	
	if( isset( $_GET['overlay'] ) ){
		
		$otw_overlay_id = $_GET['overlay'];
		$otw_overlays = otw_get_overlays();
		
		if( is_array( $otw_overlays ) && isset( $otw_overlays[ $otw_overlay_id ] ) ){
			
			foreach( $otw_overlays[ $otw_overlay_id ] as $o_key => $o_data ){
				if( $o_key == 'options' ){
					foreach( $o_data as $option_name => $option_value ){
						$otw_overlay_values['options'][ $option_name ] = $option_value;
					}
				}else{
					$otw_overlay_values[ $o_key ] = $o_data;
				}
			}
			$otw_overlay_values['loaded'] = true;
			$otw_overlay_values['title'] = $otw_overlays[ $otw_overlay_id ]['title'];
			$otw_overlay_values['type'] = $otw_overlays[ $otw_overlay_id ]['type'];
			$otw_overlay_values['status'] = $otw_overlays[ $otw_overlay_id ]['status'];
			$otw_overlay_values['grid_content'] = otw_stripslashes( $otw_overlays[ $otw_overlay_id ]['grid_content'] );
			
			if( isset( $otw_overlays[ $otw_overlay_id ]['validfor'] ) ){
				$otw_overlay_values['validfor'] = $otw_overlays[ $otw_overlay_id ]['validfor'];
			}
			if( isset( $otw_overlays[ $otw_overlay_id ]['exclude_posts_for'] ) ){
				$otw_overlay_values['exclude_posts_for'] = $otw_overlays[ $otw_overlay_id ]['exclude_posts_for'];
			}
			$page_title = __( 'Edit overlay', 'otw_pswl' );
		}
	}
	
	if( isset( $_POST['otw_action'] ) ){
		
		foreach( $otw_overlay_values as $otw_field_key => $otw_field_default_value ){
			
			if( $otw_field_key == 'options' ){
				
				foreach( $otw_field_default_value as $d_key => $d_value ){
				
					if( isset( $_POST[ $d_key ] ) ){
						$otw_overlay_values['options'][ $d_key ] = $_POST[ $d_key ];
					}
				}
			
			}elseif( isset( $_POST[ $otw_field_key ] ) ){
				$otw_overlay_values[ $otw_field_key ] = $_POST[ $otw_field_key ];
			}
		}
		if( isset( $_POST['_otw_grid_manager_content'] ) && isset( $_POST['_otw_grid_manager_content']['code'] ) ){
			$otw_overlay_values['grid_content'] = otw_stripslashes( $_POST['_otw_grid_manager_content']['code'] );
		}
	}
	
	$info_box_class = 'otw_info_box';
	
	if( isset( $otw_admin_settings['page'] ) && isset( $otw_admin_settings['page']['otw-pswl-manage'] ) && isset( $otw_admin_settings['page']['otw-pswl-manage']['infobox'] ) && in_array( $otw_admin_settings['page']['otw-pswl-manage']['infobox'], array( 'open', 'closed') ) ){
		$info_box_class .= ' otw_ib_'.$otw_admin_settings['page']['otw-pswl-manage']['infobox'];
	}else{
		$info_box_class .= ' otw_ib_open';
	}
?>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br/></div>
	<h2>
		<?php echo $page_title; ?>
		<a class="preview button" href="admin.php?page=otw-pswl"><?php _e('Back To Available overlays', 'otw_pswl')?></a>
	</h2>
	<?php include_once( 'otw_pswl_help.php' );?>
	<div class="updated <?= $info_box_class?>">
		<div class="otw_info_box_controls">
			<a href="javascript:;" class="otw_open_info_box"><?php _e( 'info', 'otw_pswl' )?></a>
			<a href="javascript:;" class="otw_close_info_box"><?php _e( 'x', 'otw_pswl' )?></a>
		</div>
		<div class="otw_info_box_content">
			<p><?php _e( 'Wondering how this works?', 'otw_pswl' );?></p>
			<ul>
				<li><?php _e( '1. Choose the Overlay Type', 'otw_pswl' );?></li>
				<li><?php _e( '2. Set it up in <a href="javascript:;" class="otw_scrollto" rel="otw_options_main">Overlay Options</a> bellow', 'otw_pswl' );?></li>
				<li><?php _e( '3. If you need to style it add a custom css class in <a href="javascript:;" class="otw_scrollto" rel="otw_options_custom">Custom Styling Options</a> bellow', 'otw_pswl' );?></li>
				<li><?php _e( '4. Go down to the <a href="javascript:;" rel="otw_grid_manager_content" class="otw_scrollto">OTW Grid Manager Light</a> to create a layout and insert sidebars in your overlay. The layout is the skeleton that holds the sidebars and makes sure everything is well arranged.', 'otw_pswl' );?></li>
				<li><?php _e( '5. If you need to have custom sidebars you can create as many sidebars as you need from <a href="admin.php?page=otw-pswl-sidebars-manage">Add Sidebar</a> in the left menu of the plugin.', 'otw_pswl' );?></li>
			</ul>
		</div>
	</div>
	<?php if( isset( $validate_messages ) && count( $validate_messages ) ){?>
		<div id="message" class="error">
			<?php foreach( $validate_messages as $v_message ){
				echo '<p>'.$v_message.'</p>';
			}?>
		</div>
	<?php }?>
	<div class="form-wrap" id="poststuff">
		<form method="post" action="" class="validate">
			<input type="hidden" name="otw_action" value="manage_otw_pswl_overlay" />
			<input type="hidden" id="otw_overlay_id" value="<?php echo $otw_overlay_id?>" />
			<?php wp_original_referer_field(true, 'previous'); wp_nonce_field('otw-pswl-manage'); ?>
			<div id="post-body">
				<div id="post-body-content">
					<div id="otw-col-left">
						<div class="form-field form-required">
							<label for="title"><?php _e( 'Overlay Title', 'otw_pswl' );?></label>
							<input type="text" id="title" value="<?php echo $otw_overlay_values['title']?>" tabindex="1" size="30" name="title"/>
							<p><?php _e( 'The name is how it appears on your admin panel.', 'otw_pswl' );?></p>
						</div>
						<div class="form-field">
							<label for="status"><?php _e( 'Status', 'otw_pswl' );?></label>
							<select id="status" tabindex="2" style="width: 170px;" name="status">
								<option value="active"<?php if( $otw_overlay_values['status'] == 'active' ){ echo ' selected="selected" ';}?>><?php _e( 'Active', 'otw_pswl' )?></option>
								<option value="inactive"<?php if( $otw_overlay_values['status'] == 'inactive' ){ echo ' selected="selected" ';}?>><?php _e( 'Inactive', 'otw_pswl' )?></option>
							</select>
						</div>
						<div class="form-field form-required">
							<label for="type"><?php _e( 'Overlay Type', 'otw_pswl' );?></label>
							<select id="type" tabindex="3" name="type">
								<?php foreach( $otw_pswl_overlay_object->overlay_types as $overlay_type => $overlay_data ){?>
									<option value="<?php echo $overlay_type ?>" <?php echo ( $otw_overlay_values['type'] == $overlay_type )?' selected="selected"':''?> ><?php echo $overlay_data['label']?></option>
								<?php }?>
							</select>
							<p><?php _e( 'Choose the type of your overlay.', 'otw_pswl' );?></p>
						</div>
						<?php foreach( $otw_pswl_overlay_object->overlay_types as $overlay_type => $overlay_data ){?>
							
							<div class="otw_overlay_options" id="otw_overlay_options_<?php echo $overlay_type?>">
							
								<?php if( isset( $overlay_data['options'] ) && count( $overlay_data['options'] ) ){?>
									
									<?php foreach( $overlay_data['options'] as $option_name => $option_data ){?>
										
										<?php if( in_array( $option_name, array( 'main', 'custom' ) ) ){?>
											<div class="meta-box-sortables otw_options_<?= $option_name?>">
												<div data-type="<?= $option_name?>" class="postbox<?php echo ( $page_settings['metabox_'.$option_name ] == 'closed'  )?' closed':''?>">
													<div title="<?php _e('Click to toggle', 'otw_pswl')?>" class="handlediv sitem_toggle"><br /></div>
													<h3 class="hndle sitem_header"><span><?php echo $option_data['label']?></span></h3>
													<div class="inside <?php echo ( $page_settings['metabox_'.$option_name ] == 'closed' )?' otw_closed':''?>">
														<?php foreach( $option_data['items'] as $element_name => $element_data ){?>
															
															<?php switch( $element_data['type'] ) {
																
																case 'active_period':
																		echo OTW_Form::datetimepicker( array( 'id' => $overlay_type.'_'.$element_name, 'name' => $overlay_type.'_'.$element_name, 'id_from' => $overlay_type.'_'.$element_name.'_from', 'name_from' => $overlay_type.'_'.$element_name.'_from', 'id_to' => $overlay_type.'_'.$element_name.'_to', 'name_to' => $overlay_type.'_'.$element_name.'_to', 'label' => $element_data['label'], 'parse' => $otw_overlay_values['options'], 'description' => $element_data['description'], 'format' => 'from_to', 'value' => $otw_overlay_values['options'][ $overlay_type.'_'.$element_name ] ) );
																	break;
																case 'input_text': 
																		echo OTW_Form::text_input( array( 'id' => $overlay_type.'_'.$element_name, 'name' => $overlay_type.'_'.$element_name, 'label' => $element_data['label'], 'parse' => $otw_overlay_values['options'], 'description' => $element_data['description'], 'value' => $otw_overlay_values['options'][ $overlay_type.'_'.$element_name ] ) );
																	break;
																case 'select':
																		echo OTW_Form::select( array( 'id' => $overlay_type.'_'.$element_name, 'name' => $overlay_type.'_'.$element_name, 'label' => $element_data['label'], 'parse' => $otw_overlay_values['options'], 'options' => $element_data['options'], 'description' => $element_data['description'], 'value' => $otw_overlay_values['options'][ $overlay_type.'_'.$element_name ] )  );
																	break;
																case 'color_picker':
																		echo OTW_Form::color_picker( array( 'id' => $overlay_type.'_'.$element_name, 'name' => $overlay_type.'_'.$element_name, 'label' => $element_data['label'], 'parse' => $otw_overlay_values['options'], 'description' => $element_data['description'], 'value' => $otw_overlay_values['options'][ $overlay_type.'_'.$element_name ] )  );
																	break;
																case 'uploader':
																		echo OTW_Form::uploader( array( 'id' => $overlay_type.'_'.$element_name, 'name' => $overlay_type.'_'.$element_name, 'label' => $element_data['label'], 'parse' => $otw_overlay_values['options'], 'description' => $element_data['description'], 'value' => $otw_overlay_values['options'][ $overlay_type.'_'.$element_name ] )  );
																	break;
																case 'select_subfields':
																		echo OTW_Form::select_subfields( array( 'id' => $overlay_type.'_'.$element_name, 'name' => $overlay_type.'_'.$element_name, 'label' => $element_data['label'], 'parse' => $otw_overlay_values['options'], 'options' => $element_data['options'], 'description' => $element_data['description'], 'value_from' => $otw_overlay_values['options'], 'subfields' => $element_data['subfields'] ) );
																	break;
															}?>
														<?php }?>
													</div>
												</div>
											</div>
										<?php }?>
									<?php }?>
								<?php }?>
							</div>
						<?php }?>
						<div>
							<p class="submit">
								<input type="submit" value="<?php _e( 'Save Overlay', 'otw_pswl') ?>" name="submit" class="button button-primary button-large"/>
							</p>
						</div>
					</div>
					<div id="otw-col-right" class="otw_pswl_<?php echo $otw_overlay_id?>">
						
					</div>
					<div id="<?php echo $otw_pswl_grid_manager_object->meta_name;?>">
						<div class="meta-box-sortables">
							<div class="postbox">
								<h3 class="hndle sitem_header"><span><?php _e( 'OTW Grid Manager Light', 'otw_pswl' )?></span></h3>
								<div class="inside">
									<?php echo $otw_pswl_grid_manager_object->build_custom_box( $otw_overlay_values['grid_content'] );?>
								</div>
							</div>
						</div>
					</div>
					<div>
						<p class="submit">
							<input type="submit" value="<?php _e( 'Save Overlay', 'otw_pswl') ?>" name="submit" class="button button-primary button-large"/>
						</p>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
