<?php
/**
 * Process otw actions
 *
 */
if( isset( $_POST['otw_action'] ) ){

	switch( $_POST['otw_action'] ){
		
		case 'otw_pswl_overlay_activate':
				if( isset( $_POST['cancel'] ) ){
					wp_redirect( 'admin.php?page=otw-pswl' );
				}else{
					$otw_overlays = otw_get_overlays();
					
					if( isset( $_GET['overlay'] ) && isset( $otw_overlays[ $_GET['overlay'] ] ) ){
						$otw_overlay_id = $_GET['overlay'];
						
						$otw_overlays[ $otw_overlay_id ]['status'] = 'active';
						
						otw_save_overlays( $otw_overlays );
						
						wp_redirect( 'admin.php?page=otw-pswl&message=3' );
					}else{
						wp_die( __( 'Invalid overlay', 'otw_pswl' ) );
					}
				}
			break;
		case 'otw_pswl_overlay_deactivate':
				if( isset( $_POST['cancel'] ) ){
					wp_redirect( 'admin.php?page=otw-pswl' );
				}else{
					$otw_overlays = otw_get_overlays();
					
					if( isset( $_GET['overlay'] ) && isset( $otw_overlays[ $_GET['overlay'] ] ) ){
						$otw_overlay_id = $_GET['overlay'];
						
						$otw_overlays[ $otw_overlay_id ]['status'] = 'inactive';
						
						otw_save_overlays( $otw_overlays );
						
						wp_redirect( 'admin.php?page=otw-pswl&message=4' );
					}else{
						wp_die( __( 'Invalid overlay', 'otw_pswl' ) );
					}
				}
			break;
		case 'otw_pswl_overlay_delete':
				if( isset( $_POST['cancel'] ) ){
					wp_redirect( 'admin.php?page=otw-pswl' );
				}else{
					
					$otw_overlays = otw_get_overlays();
					
					if( isset( $_GET['overlay'] ) && isset( $otw_overlays[ $_GET['overlay'] ] ) ){
						$otw_overlay_id = $_GET['overlay'];
						
						$new_overlays = array();
						
						//remove the overlay from otw_overlays
						foreach( $otw_overlays as $overlay_key => $overlay ){
						
							if( $overlay_key != $otw_overlay_id ){
							
								$new_overlays[ $overlay_key ] = $overlay;
							}
						}
						otw_save_overlays( $new_overlays );
						
						wp_redirect( admin_url( 'admin.php?page=otw-pswl&message=2' ) );
					}else{
						wp_die( __( 'Invalid overlay', 'otw_pswl' ) );
					}
				}
			break;
		case 'otw_pswl_sidebar_activate':
				if( isset( $_POST['cancel'] ) ){
					wp_redirect( 'admin.php?page=otw-pswl-sidebars-list' );
				}else{
					$otw_sidebars = get_option( 'otw_sidebars' );
					
					if( isset( $_GET['sidebar'] ) && isset( $otw_sidebars[ $_GET['sidebar'] ] ) ){
						$otw_sidebar_id = $_GET['sidebar'];
						
						$otw_sidebars[ $otw_sidebar_id ]['status'] = 'active';
						
						update_option( 'otw_sidebars', $otw_sidebars );
						
						wp_redirect( 'admin.php?page=otw-pswl-sidebars-list&message=3' );
					}else{
						wp_die( __( 'Invalid sidebar', 'otw_pswl' ) );
					}
				}
			break;
		case 'otw_pswl_sidebar_deactivate':
				if( isset( $_POST['cancel'] ) ){
					wp_redirect( 'admin.php?page=otw-pswl-sidebars-list' );
				}else{
					$otw_sidebars = get_option( 'otw_sidebars' );
					
					if( isset( $_GET['sidebar'] ) && isset( $otw_sidebars[ $_GET['sidebar'] ] ) ){
						$otw_sidebar_id = $_GET['sidebar'];
						
						$otw_sidebars[ $otw_sidebar_id ]['status'] = 'inactive';
						
						update_option( 'otw_sidebars', $otw_sidebars );
						
						wp_redirect( 'admin.php?page=otw-pswl-sidebars-list&message=4' );
					}else{
						wp_die( __( 'Invalid sidebar', 'otw_pswl' ) );
					}
				}
			break;
		case 'otw_pswl_sidebar_delete':
				if( isset( $_POST['cancel'] ) ){
					wp_redirect( 'admin.php?page=otw-pswl-sidebars-list' );
				}else{
					
					$otw_sidebars = get_option( 'otw_sidebars' );
					
					if( isset( $_GET['sidebar'] ) && isset( $otw_sidebars[ $_GET['sidebar'] ] ) ){
						$otw_sidebar_id = $_GET['sidebar'];
						
						$new_sidebars = array();
						
						//remove the sidebar from otw_sidebars
						foreach( $otw_sidebars as $sidebar_key => $sidebar ){
						
							if( $sidebar_key != $otw_sidebar_id ){
							
								$new_sidebars[ $sidebar_key ] = $sidebar;
							}
						}
						update_option( 'otw_sidebars', $new_sidebars );
						
						//remove sidebar from widget
						$widgets = get_option( 'sidebars_widgets' );
						
						if( isset( $widgets[ $otw_sidebar_id ] ) ){
							
							$new_widgets = array();
							foreach( $widgets as $sidebar_key => $widget ){
								if( $sidebar_key != $otw_sidebar_id ){
								
									$new_widgets[ $sidebar_key ] = $widget;
								}
							}
							update_option( 'sidebars_widgets', $new_widgets );
						}
						
						wp_redirect( admin_url( 'admin.php?page=otw-pswl-sidebars-list&message=2' ) );
					}else{
						wp_die( __( 'Invalid sidebar', 'otw_pswl' ) );
					}
				}
			break;
		case 'otw_pswl_sidebars_manage':
				global $validate_messages, $wpdb;
				
				$validate_messages = array();
				$valid_page = true;
				if( !isset( $_POST['sbm_title'] ) || !strlen( trim( $_POST['sbm_title'] ) ) ){
					$valid_page = false;
					$validate_messages[] = __( 'Please type valid sidebar title', 'otw_pswl' );
				}
				if( !isset( $_POST['sbm_status'] ) || !strlen( trim( $_POST['sbm_status'] ) ) ){
					$valid_page = false;
					$validate_messages[] = __( 'Please select status', 'otw_pswl' );
				}
				
				if( $valid_page ){
					$otw_sidebars = get_option( 'otw_sidebars' );
					
					if( !is_array( $otw_sidebars ) ){
						$otw_sidebars = array();
					}
					
					if( isset( $_GET['sidebar'] ) && isset( $otw_sidebars[ $_GET['sidebar'] ] ) ){
						$otw_sidebar_id = $_GET['sidebar'];
						$sidebar = $otw_sidebars[ $_GET['sidebar'] ];
					}else{
						$sidebar = array();
						$otw_sidebar_id = false;
					}
					
					$sidebar['title'] = (string) $_POST['sbm_title'];
					$sidebar['description'] = (string) $_POST['sbm_description'];
					$sidebar['status'] = (string) $_POST['sbm_status'];
					$sidebar['widget_alignment'] = (string) $_POST['sbm_widget_alignment'];
					
					if( $otw_sidebar_id === false ){
						
						$otw_sidebar_id = 'otw-sidebar-'.( get_next_otw_pswl_sidebar_id() );
						$sidebar['id'] = $otw_sidebar_id;
						$sidebar['replace'] = '';
						$sidebar['validfor'] = array();
					}
					
					$otw_sidebars[ $otw_sidebar_id ] = $sidebar;
					
					if( !update_option( 'otw_sidebars', $otw_sidebars ) && $wpdb->last_error ){
						
						$valid_page = false;
						$validate_messages[] = __( 'DB Error: ', 'otw_pswl' ).$wpdb->last_error.'. Tring to save '.strlen( maybe_serialize( $otw_sidebars ) ).' bytes.';
					}else{
						wp_redirect( 'admin.php?page=otw-pswl-sidebars-list&message=1' );
					}
				}
			break;
		case 'manage_otw_pswl_overlay':
				
				global $validate_messages, $wpdb, $otw_pswl_overlay_object;
				
				$validate_messages = array();
				
				$valid_page = true;
				if( !isset( $_POST['title'] ) || !strlen( trim( $_POST['title'] ) ) ){
					$valid_page = false;
					$validate_messages[] = __( 'Please type valid overlay title', 'otw_pswl' );
				}
				if( !isset( $_POST['status'] ) || !strlen( trim( $_POST['status'] ) ) ){
					$valid_page = false;
					$validate_messages[] = __( 'Please select status', 'otw_pswl' );
				}
				if( !isset( $_POST['type'] ) || !strlen( trim( $_POST['type'] ) ) ){
					$valid_page = false;
					$validate_messages[] = __( 'Please select overlay type', 'otw_pswl' );
				}
				if( $valid_page ){
					$otw_overlays = otw_get_overlays();
					
					if( isset( $_GET['overlay'] ) && isset( $otw_overlays[ $_GET['overlay'] ] ) ){
						$otw_overlay_id = $_GET['overlay'];
						$overlay = $otw_overlays[ $_GET['overlay'] ];
					}else{
						$overlay = array();
						$otw_overlay_id = false;
					}
					
					$overlay['title'] = (string) $_POST['title'];
					$overlay['type'] = (string) $_POST['type'];
					$overlay['status'] = (string) $_POST['status'];
					$overlay['grid_content'] = $_POST['_otw_grid_manager_content']['code'];
					$overlay['options'] = array();
					
					//save options
					foreach( $otw_pswl_overlay_object->overlay_types as $overlay_type => $overlay_type_data ){
						
						foreach( $overlay_type_data['options'] as $o_type => $type_options ){
							
							if( in_array( $o_type, array( 'main', 'custom' ) ) ){
								
								foreach( $type_options['items'] as $option_name => $option_item ){
									
									if( isset( $_POST[$overlay_type.'_'.$option_name] ) ){
										
										$overlay['options'][ $overlay_type.'_'.$option_name ] = $_POST[$overlay_type.'_'.$option_name];
										
									}elseif( isset( $overlay['options'][ $overlay_type.'_'.$option_name ] ) ){
										
										unset( $overlay['options'][ $overlay_type.'_'.$option_name ] );
									}
									
									if( isset( $option_item['subfields'] ) && is_array( $option_item['subfields'] ) && count( $option_item['subfields'] ) ){
									
										foreach( $option_item['subfields'] as $subfield => $subfield_data ){
										
											if( isset( $_POST[$overlay_type.'_'.$option_name.'_'.$subfield ] ) ){
												
												$overlay['options'][ $overlay_type.'_'.$option_name.'_'.$subfield ] = $_POST[$overlay_type.'_'.$option_name.'_'.$subfield];
												
											}elseif( isset( $overlay['options'][ $overlay_type.'_'.$option_name.'_'.$subfield  ] ) ){
												
												unset( $overlay['options'][ $overlay_type.'_'.$option_name.'_'.$subfield  ] );
											}
										}
									
									}
								}
								
							}else{
								foreach( $type_options['items'] as $option_name => $option_item ){
									
									$overlay['options'][ $overlay_type.'_'.$option_name ] = $option_item['default'];
								}
							}
						}
					}
					
					if( $otw_overlay_id === false ){
						
						$otw_overlay_id = 'otw-overlay-'.( otw_get_next_overlay_id() );
						$overlay['id'] = $otw_overlay_id;
					}
					
					$otw_overlays[ $otw_overlay_id ] = $overlay;
					
					if( !otw_save_overlays( $otw_overlays ) && $wpdb->last_error ){
						
						$valid_page = false;
						$validate_messages[] = __( 'DB Error: ', 'otw_pswl' ).$wpdb->last_error.'. Tring to save '.strlen( maybe_serialize( $otw_overlays ) ).' bytes.';
					}else{
						wp_redirect( 'admin.php?page=otw-pswl&message=1' );
					}
				}
			break;
	}
}
