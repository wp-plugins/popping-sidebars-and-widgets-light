<?php
/**
 * Init function
 */
if( !function_exists( 'otw_pswl_init' ) ){
	
	function otw_pswl_init(){
		
		global $wp_registered_sidebars, $otw_pswl_plugin_url, $otw_sbm_widget_settings, $otw_pswl_grid_manager_component, $otw_pswl_shortcode_component, $otw_pswl_shortcode_object, $otw_pswl_form_component, $otw_pswl_validator_component, $otw_pswl_form_object, $otw_pswl_js_version, $otw_pswl_css_version, $otw_pswl_grid_manager_object, $wp_pswl_int_items, $otw_pswl_overlay_component, $otw_pswl_overlay_object;
		
		if( is_admin() ){
			
			add_action('admin_menu', 'otw_pswl_init_admin_menu' );
			
			add_action('admin_print_styles', 'otw_pswl_enqueue_admin_styles' );
			
			add_action('admin_enqueue_scripts', 'otw_pswl_enqueue_admin_scripts');
		}
		else
		{
			otw_pswl_enqueue_styles();
			
			otw_pswl_enqueue_scripts();
		}
		
		add_shortcode('otw_is', 'otw_call_sidebar');
		
		$otw_registered_sidebars = get_option( 'otw_sidebars' );
		
		$otw_widget_settings = get_option( 'otw_widget_settings' );
		
		if( !is_array( $otw_widget_settings ) ){
				$otw_widget_settings = array();
		}
		if( is_array( $otw_registered_sidebars ) && count( $otw_registered_sidebars ) ){
			
			foreach( $otw_registered_sidebars as $otw_sidebar_id => $otw_sidebar ){
				
				$sidebar_params = array();
				$sidebar_params['id']  = $otw_sidebar_id;
				$sidebar_params['name']  = $otw_sidebar['title'];
				$sidebar_params['description']  = $otw_sidebar['description'];
				$sidebar_params['replace']  = $otw_sidebar['replace'];
				$sidebar_params['status']  = $otw_sidebar['status'];
				if( isset( $otw_sidebar['widget_alignment'] ) ){
					$sidebar_params['widget_alignment']  = $otw_sidebar['widget_alignment'];
				}
				$sidebar_params['validfor']  = $otw_sidebar['validfor'];
				
				if( isset( $otw_sidebar['exclude_posts_for'] ) ){
					$sidebar_params['exclude_posts_for']  = $otw_sidebar['exclude_posts_for'];
				}
				
				//collect all replacements for faster search in font end
				if( strlen( $sidebar_params['replace'] ) ){
					
					if( !isset( $otw_replaced_sidebars[ $sidebar_params['replace'] ] ) ){
						$otw_replaced_sidebars[ $sidebar_params['replace'] ] = array();
					}
					$otw_replaced_sidebars[ $sidebar_params['replace'] ][ $sidebar_params['id'] ] = $sidebar_params['id'];
					
					if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ] ) ){
						if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ]['class'] ) ){
							$sidebar_params['class'] = $wp_registered_sidebars[ $sidebar_params['replace'] ]['class'];
						}
						if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ]['before_widget'] ) ){
							$sidebar_params['before_widget'] = $wp_registered_sidebars[ $sidebar_params['replace'] ]['before_widget'];
						}
						if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ]['after_widget'] ) ){
							$sidebar_params['after_widget'] = $wp_registered_sidebars[ $sidebar_params['replace'] ]['after_widget'];
						}
						if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ]['before_title'] ) ){
							$sidebar_params['before_title'] = $wp_registered_sidebars[ $sidebar_params['replace'] ]['before_title'];
						}
						if( isset( $wp_registered_sidebars[ $sidebar_params['replace'] ]['after_title'] ) ){
							$sidebar_params['after_title'] = $wp_registered_sidebars[ $sidebar_params['replace'] ]['after_title'];
						}
					}
				
				}else{
				
					foreach( $otw_sbm_widget_settings as $s_type => $s_data ){
					
						if( isset( $otw_plugin_options[ 'otw_sbm_'.$s_type ] ) ){
							$sidebar_params[ $s_type ] = $otw_plugin_options[ 'otw_sbm_'.$s_type ];
						}else{
							$sidebar_params[ $s_type ] = $s_data[1];
						}
					}
				}
				
				register_sidebar( $sidebar_params );
			}
		}
		
		//apply validfor settings to all sidebars
		if( is_array( $wp_registered_sidebars ) && count( $wp_registered_sidebars ) ){
			foreach( $wp_registered_sidebars as $wp_widget_key => $wo_widget_data ){
			
				if( array_key_exists( $wp_widget_key, $otw_widget_settings ) ){
					$wp_registered_sidebars[ $wp_widget_key ]['widgets_settings'] = $otw_widget_settings[ $wp_widget_key ];
				}else{
					$wp_registered_sidebars[ $wp_widget_key ]['widgets_settings'] = array();
				}
			}
		}
		
		$custom_post_types = get_post_types( array(  'public'   => true, '_builtin' => false ), 'object' );
		
		if( is_array( $custom_post_types ) ){
			foreach( $custom_post_types as $c_key => $c_cust ){
				
				if( otw_pswl_installed_plugin( 'bbpress' ) && $c_key == 'reply' ){
					//skip reply they appear on same pages as topics
				}else{
					$wp_pswl_int_items[ 'cpt_'. $c_cust->name ] = array( array(), $c_cust->label, __( 'All ', 'otw_pswl' ).$c_cust->labels->name );
				}
			}
		}
		
		$custom_taxonomies = get_taxonomies( array(  'public'   => true, '_builtin' => false ), 'object' );
		
		if( is_array( $custom_taxonomies ) ){
			foreach( $custom_taxonomies as $c_cust ){
				$wp_pswl_int_items[ 'ctx_'. $c_cust->name ] = array( array(), $c_cust->label.' '.__( 'archives', 'otw_pswl' ),__( 'All ', 'otw_pswl' ).$c_cust->label.' '.__('archives', 'otw_pswl' ) );
				foreach( $c_cust->object_type as $c_object ){
					
					if( $c_object_info = get_post_type_object( $c_object ) ){
						$wp_pswl_int_items[ $c_object.'_in_ctx_'. $c_cust->name ] = array( array(), __( 'All', 'otw_pswl' ).' '.$c_object_info->labels->name.' '.__( 'from taxonomy', 'otw_pswl' ).' '.$c_cust->label, __( 'All', 'otw_pswl' ).' '.$c_object_info->labels->name.' '.__( 'from taxonomy', 'otw_pswl' ).' '.$c_cust->label );
					}
				}
				
			}
		}
		
		//otw grid manager component
		$otw_pswl_grid_manager_component = otw_load_component( 'otw_overlay_grid_manager' );
		$otw_pswl_grid_manager_object = otw_get_component( $otw_pswl_grid_manager_component );
		$otw_pswl_grid_manager_object->js_version = $otw_pswl_js_version;
		$otw_pswl_grid_manager_object->css_version = $otw_pswl_css_version;
		$otw_pswl_grid_manager_object->text_info = __( 'Add some rows and columns in the rows. Then you will be able to add sidebars in the columns. Once you build your layout you can save it so you can use it for another page.', 'otw_pswl' );
		
		include_once( plugin_dir_path( __FILE__ ).'otw_labels/otw_pswl_grid_manager_object.labels.php' );
		$otw_pswl_grid_manager_object->init();
		
		//shortcode component
		$otw_pswl_shortcode_component = otw_load_component( 'otw_overlay_shortcode' );
		$otw_pswl_shortcode_object = otw_get_component( $otw_pswl_shortcode_component );
		$otw_pswl_shortcode_object->js_version = $otw_pswl_js_version;
		$otw_pswl_shortcode_object->css_version = $otw_pswl_css_version;
		
		$otw_pswl_shortcode_object->add_default_external_lib( 'css', 'style', get_stylesheet_directory_uri().'/style.css', 'live_preview', 10 );
		
		$otw_pswl_shortcode_object->shortcodes['sidebars'] = array( 'title' => __('Sidebars', 'otw_pswl'),'enabled' => true,'children' => false,'order' => 132,'parent' => false, 'path' => dirname( __FILE__ ).'/otw_components/otw_overlay_shortcode/', 'url' => $otw_pswl_plugin_url.'/include/otw_components/otw_overlay_shortcode/' );
		
		include_once( plugin_dir_path( __FILE__ ).'otw_labels/otw_pswl_shortcode_object.labels.php' );
		$otw_pswl_shortcode_object->init();
		
		//form component
		$otw_pswl_form_component = otw_load_component( 'otw_form' );
		$otw_pswl_form_object = otw_get_component( $otw_pswl_form_component );
		$otw_pswl_form_object->js_version = $otw_pswl_js_version;
		$otw_pswl_form_object->css_version = $otw_pswl_css_version;
		
		include_once( plugin_dir_path( __FILE__ ).'otw_labels/otw_pswl_form_object.labels.php' );
		$otw_pswl_form_object->init();
		
		//overlay component
		$otw_pswl_overlay_component = otw_load_component( 'otw_overlay' );
		$otw_pswl_overlay_object = otw_get_component( $otw_pswl_overlay_component );
		$otw_pswl_overlay_object->js_version = $otw_pswl_js_version;
		$otw_pswl_overlay_object->css_version = $otw_pswl_css_version;
		$otw_pswl_overlay_object->grid_manager_component_object = $otw_pswl_grid_manager_object;
		$otw_pswl_overlay_object->create_nav_menu = true;
		include_once( plugin_dir_path( __FILE__ ).'otw_labels/otw_pswl_overlay_object.labels.php' );
		$otw_pswl_overlay_object->init();
		
		
		include_once( 'otw_pswl_process_actions.php' );
		
	}
}
/**
 * include needed styles
 */
if( !function_exists( 'otw_pswl_enqueue_styles' ) ){
	function otw_pswl_enqueue_styles(){
		global $otw_pswl_plugin_url, $otw_pswl_css_version;
		
		if( !is_admin() ){
		}
	}
}

/**
 * include needed scripts
 */
if( !function_exists( 'otw_pswl_enqueue_scripts' ) ){
	function otw_pswl_enqueue_scripts(){
		global $otw_pswl_plugin_url, $otw_pswl_js_version;
		
		if( !is_admin() ){
		
		}
	}
}
/**
 * Admin styles
 */
if( !function_exists( 'otw_pswl_enqueue_admin_styles' ) ){
	
	function otw_pswl_enqueue_admin_styles(){
		
		global $otw_pswl_plugin_url, $otw_pswl_css_version;
		
		wp_enqueue_style( 'otw_pswl_admin', $otw_pswl_plugin_url.'/css/otw_pswl_admin.css', array( 'thickbox' ), $otw_pswl_css_version );
	}
}

/**
 * Admin scripts
 */
if( !function_exists( 'otw_pswl_enqueue_admin_scripts' ) ){
	
	function otw_pswl_enqueue_admin_scripts( $requested_page ){
		
		global $otw_pswl_plugin_url, $otw_pswl_js_version;
		
		switch( $requested_page ){
			
			case 'popping-sidebars-and-widgets-light_page_otw-pswl-manage':
					wp_enqueue_style('thickbox');
					wp_enqueue_script('thickbox');
					wp_enqueue_script('media-models');
					wp_enqueue_script('media-upload');
					wp_enqueue_media();
					wp_enqueue_script("otw_pswl_manage_overlay_admin", $otw_pswl_plugin_url.'/js/otw_manage_overlay_admin.js'  , array( 'jquery', 'jquery-ui-sortable' ), $otw_pswl_js_version );
				break;
		}
	}
	
}

/**
 * Init admin menu
 */
if( !function_exists( 'otw_pswl_init_admin_menu' ) ){
	
	function otw_pswl_init_admin_menu(){
		
		global $otw_pswl_plugin_url;
		
		add_menu_page(__('Popping Sidebars and Widgets Light', 'otw_pswl'), __('Popping Sidebars and Widgets Light', 'otw_pswl'), 'manage_options', 'otw-pswl', 'otw_pswl_list', $otw_pswl_plugin_url.'/images/otw-menu-icon.png');
		add_submenu_page( 'otw-pswl', __('All Overlays', 'otw_pswl'), __('All Overlays', 'otw_pswl'), 'manage_options', 'otw-pswl', 'otw_pswl_list' );
		add_submenu_page( 'otw-pswl', __('Manage Overlay', 'otw_pswl'), __('Add Overlay', 'otw_pswl'), 'manage_options', 'otw-pswl-manage', 'otw_pswl_manage' );
		add_submenu_page( 'otw-pswl', __('All Sidebars', 'otw_pswl'), __('All Sidebars', 'otw_pswl'), 'manage_options', 'otw-pswl-sidebars-list', 'otw_pswl_sidebars_list' );
		add_submenu_page( 'otw-pswl', __('Manage Sidebar', 'otw_pswl'), __('Add Sidebar', 'otw_pswl'), 'manage_options', 'otw-pswl-sidebars-manage', 'otw_pswl_sidebars_manage' );
		add_submenu_page( __FILE__, __('Overlay action', 'otw_pswl'), __('Overlay action', 'otw_pswl'), 'manage_options', 'otw-pswl-action', 'otw_pswl_action' );
		add_submenu_page( __FILE__, __('Sidebar action', 'otw_pswl'), __('Sidebar action', 'otw_pswl'), 'manage_options', 'otw-pswl-sidebars-action', 'otw_pswl_sidebars_action' );
	}
}

/**
 * Get overlays
 */
if( !function_exists( 'otw_get_overlays' ) ){
	function otw_get_overlays(){
		
		$otw_overlays = get_option( 'otw_overlays' );
		
		if( !is_array( $otw_overlays ) ){
			$otw_overlays = array();
		}
		return $otw_overlays;
	}
}

/**
 * Save overlays
 */
if( !function_exists( 'otw_save_overlays' ) ){
	function otw_save_overlays( $otw_overlays ){
		
		return update_option( 'otw_overlays', $otw_overlays );
	}
}

/**
 * Get next overlay id
 */
if( !function_exists( 'otw_get_next_overlay_id' ) ){
	function otw_get_next_overlay_id(){
		$next_id = 0;
		$existing_overlays = otw_get_overlays();
		
		if( is_array( $existing_overlays ) && count( $existing_overlays ) ){
			
			foreach( $existing_overlays as $key => $s_data ){
			
				if( preg_match( "/^otw\-overlay\-([0-9]+)$/", $key, $matches ) ){
				
					if( $matches[1] > $next_id ){
						$next_id = $matches[1];
					}
				}
			}
		}
		return $next_id + 1;
	}
}

/** calls page where to create new overlay
  *
  */
function otw_pswl_manage(){
	require_once( 'otw_pswl_manage.php' );
}

/** calls page where to list all overlays
  *
  */
function otw_pswl_list(){
	require_once( 'otw_pswl_list.php' );
}

function otw_pswl_items_by_type(){
	require_once( 'otw_pswl_items_by_type.php' );
	die;
}

/** calls page where to create new sidebars
  *
  */
function otw_pswl_sidebars_manage(){
	require_once( 'otw_pswl_sidebars_manage.php' );
}

/** calls page where to list all sidebars
  *
  */
function otw_pswl_sidebars_list(){
	require_once( 'otw_pswl_sidebars_list.php' );
}

/** calls page where to process sidebars actions
  *
  */
function otw_pswl_sidebars_action(){
	require_once( 'otw_pswl_sidebars_action.php' );
}

/** calls page where to process overlay actions
  *
  */
function otw_pswl_action(){
	require_once( 'otw_pswl_overlays_action.php' );
}

if( !function_exists( 'otw_pswl_get_item_sort_options' ) ){
	function otw_pswl_get_item_sort_options( $item_type ){
	
		$sort_options = array();
		
		switch( $item_type ){
		
			case 'page':
			case 'post':
					$sort_options['a_z'] = __( 'Alphabetically: A-Z', 'otw_pswl' );
					$sort_options['z_a'] = __( 'Alphabetically: Z-A', 'otw_pswl' );
					$sort_options['date_latest'] = __( 'Latest created', 'otw_pswl' );
					$sort_options['date_oldest'] = __( 'Oldest created', 'otw_pswl' );
					$sort_options['modified_latest'] = __( 'Latest Modified', 'otw_pswl' );
					$sort_options['modified_oldest'] = __( 'Oldest Modified', 'otw_pswl' );
				break;
			case 'templatehierarchy':
			case 'pagetemplate':
			case 'archive':
			case 'author_archive':
			case 'userroles':
			case 'wpmllanguages':
			case 'bbp_page':
			case 'buddypress_page':
					$sort_options['a_z'] = __( 'Alphabetically: A-Z', 'otw_pswl' );
					$sort_options['z_a'] = __( 'Alphabetically: Z-A', 'otw_pswl' );
				break;
			default:
					if( preg_match( "/^cpt_(.*)$/", $item_type, $matches ) ){
						$sort_options['a_z'] = __( 'Alphabetically: A-Z', 'otw_pswl' );
						$sort_options['z_a'] = __( 'Alphabetically: Z-A', 'otw_pswl' );
						$sort_options['date_latest'] = __( 'Latest created', 'otw_pswl' );
						$sort_options['date_oldest'] = __( 'Oldest created', 'otw_pswl' );
						$sort_options['modified_latest'] = __( 'Latest Modified', 'otw_pswl' );
						$sort_options['modified_oldest'] = __( 'Oldest Modified', 'otw_pswl' );
					}else{
						$sort_options['a_z'] = __( 'Alphabetically: A-Z', 'otw_pswl' );
						$sort_options['z_a'] = __( 'Alphabetically: Z-A', 'otw_pswl' );
						$sort_options['date_latest'] = __( 'Latest created', 'otw_pswl' );
						$sort_options['date_oldest'] = __( 'Oldest created', 'otw_pswl' );
					}
				break;
		}
		return $sort_options;
	}
}

if( !function_exists( 'otw_pswl_wp_item_exclude' ) ){
	function otw_pswl_wp_item_exclude( $exclude_object, $type, $overlay_data ){
		
		if( isset( $overlay_data['exclude_posts_for'] ) && isset( $overlay_data['exclude_posts_for'][ $type ] )  && strlen( trim( $overlay_data['exclude_posts_for'][ $type ] ) ) ){
			return $overlay_data['exclude_posts_for'][ $type ];
		}
		return '';
	}
}

if (!function_exists( "otw_pswl_get_filtered_items" )){
	function otw_pswl_get_filtered_items( $type, $filter, $overlay_id, $displayed_items = 20, $id_in_list = array(), $id_not_in_list = array(), $show = 'all', $order = 'a_z', $current_page = 0 ){
		
		global $string_filter, $id_list_filter;
		
		$string_filter = $filter;
		$id_list_filter = $id_in_list;
		$pager_data = array();
		
		switch( $type )
		{
			case 'page':
					$args = array();
					$args['post_type']      = $type;
					$args['posts_per_page'] = -1;
					if( count( $id_list_filter ) ){
						$args['post__in']       = $id_list_filter;
					}
					if( $string_filter ){
						add_filter( 'posts_where', 'otw_pswl_post_by_title' );
					}
					
					if( otw_pswl_installed_plugin( 'buddypress' ) ){
						
						global $bp;
						
						if( isset( $bp->pages->activity ) && $bp->pages->activity->id ){
							$id_not_in_list[] = $bp->pages->activity->id;
						}
						if( isset( $bp->pages->members ) && $bp->pages->members->id ){
							$id_not_in_list[] = $bp->pages->members->id;
						}
					}
					
					if( count( $id_not_in_list ) ){
						$args['post__not_in'] = $id_not_in_list;
					}
					
					$the_query = new WP_Query( $args );
					
					$all_items = count( $the_query->posts );
					
					$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
					
					$args['offset'] = $pager_data['first'];
					$args['posts_per_page'] = ($displayed_items)?$displayed_items:-1;
					
					switch( $order )
					{
						case 'a_z':
								$args['orderby']        = 'title';
								$args['order']          = 'ASC';
							break;
						case 'z_a':
								$args['orderby']        = 'title';
								$args['order']          = 'DESC';
							break;
						case 'date_latest':
								$args['orderby']        = 'date';
								$args['order']          = 'DESC';
							break;
						case 'date_oldest':
								$args['orderby']        = 'date';
								$args['order']          = 'ASC';
							break;
						case 'modified_latest':
								$args['orderby']        = 'modified';
								$args['order']          = 'DESC';
							break;
						case 'modified_oldest':
								$args['orderby']        = 'modified';
								$args['order']          = 'ASC';
							break;
						default:
								$args['orderby']        = 'ID';
								$args['order']          = 'DESC';
							break;
					}
					
					$the_query = new WP_Query( $args );
					
					if( $string_filter ){
						remove_filter('posts_where', 'otw_pswl_post_by_title');
					}
					
					return array( $all_items, $the_query->posts, $pager_data );
				break;
			case 'post':
					$args = array();
					$args['post_type']      = $type;
					$args['posts_per_page'] = -1;
					if( count( $id_list_filter ) ){
						$args['post__in']       = $id_list_filter;
					}
					if( $string_filter ){
						add_filter( 'posts_where', 'otw_pswl_post_by_title' );
					}
					
					if( count( $id_not_in_list ) ){
						$args['post__not_in'] = $id_not_in_list;
					}
					
					$the_query = new WP_Query( $args );
					
					$all_items = count( $the_query->posts );
					
					$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
					
					$args['posts_per_page'] = ($displayed_items)?$displayed_items:-1;
					
					switch( $order )
					{
						case 'a_z':
								$args['orderby']        = 'title';
								$args['order']          = 'ASC';
							break;
						case 'z_a':
								$args['orderby']        = 'title';
								$args['order']          = 'DESC';
							break;
						case 'date_latest':
								$args['orderby']        = 'date';
								$args['order']          = 'DESC';
							break;
						case 'date_oldest':
								$args['orderby']        = 'date';
								$args['order']          = 'ASC';
							break;
						case 'modified_latest':
								$args['orderby']        = 'modified';
								$args['order']          = 'DESC';
							break;
						case 'modified_oldest':
								$args['orderby']        = 'modified';
								$args['order']          = 'ASC';
							break;
						default:
								$args['orderby']        = 'ID';
								$args['order']          = 'DESC';
							break;
					}
					
					$the_query = new WP_Query( $args );
					
					if( $string_filter ){
						remove_filter('posts_where', 'otw_pswl_post_by_title');
					}
					
					return array( $all_items, $the_query->posts, $pager_data );
				break;
			case 'category':
			case 'postsincategory':
					//first get all
					$args = array();
					$args['type']            = 'post';
					$args['hide_empty']      = 0;
					$args['number']          = 0;
					
					if( count( $id_list_filter ) ){
						sort( $id_list_filter );
						$args['include']  = $id_list_filter;
					}
					
					if( $string_filter ){
						$args['search'] = $string_filter;
					}
					
					if( count( $id_not_in_list ) ){
						sort( $id_not_in_list );
						$args['exclude'] = $id_not_in_list;
					}
					
					$all_items = count( get_categories( $args ) );
					
					$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
					
					$args['offset'] = $pager_data['first'];
					$args['number']          = ($displayed_items)?$displayed_items:0;
					
					switch( $order )
					{
						case 'a_z':
								$args['orderby']        = 'name';
								$args['order']          = 'ASC';
							break;
						case 'z_a':
								$args['orderby']        = 'name';
								$args['order']          = 'DESC';
							break;
						case 'date_latest':
								$args['orderby']        = 'ID';
								$args['order']          = 'DESC';
							break;
						case 'date_oldest':
								$args['orderby']        = 'ID';
								$args['order']          = 'ASC';
							break;
						default:
								$args['orderby']        = 'ID';
								$args['order']          = 'DESC';
							break;
					}
					return array( $all_items, get_categories( $args ), $pager_data );
				break;
			case 'posttag':
			case 'postsintag':
					$args = array();
					$args['hide_empty']      = 0;
					$args['number']          = 0;
					
					if( count( $id_list_filter ) ){
						sort( $id_list_filter );
						$args['include']  = $id_list_filter;
					}
					
					if( $string_filter ){
						$args['search'] = $string_filter;
					}
					
					if( count( $id_not_in_list ) ){
						sort( $id_not_in_list );
						$args['exclude'] = $id_not_in_list;
					}
					
					$all_items = count( get_terms( 'post_tag', $args ) );
					
					$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
					
					$args['offset'] 	 = $pager_data['first'];
					$args['number']          = ($displayed_items)?$displayed_items:0;
					
					switch( $order )
					{
						case 'a_z':
								$args['orderby']        = 'name';
								$args['order']          = 'ASC';
							break;
						case 'z_a':
								$args['orderby']        = 'name';
								$args['order']          = 'DESC';
							break;
						case 'date_latest':
								$args['orderby']        = 'ID';
								$args['order']          = 'DESC';
							break;
						case 'date_oldest':
								$args['orderby']        = 'ID';
								$args['order']          = 'ASC';
							break;
						default:
								$args['orderby']        = 'ID';
								$args['order']          = 'DESC';
							break;
					}
					return array( $all_items, get_terms( 'post_tag', $args ), $pager_data );
				break;
			case 'author_archive':
					$args = array();
					$args['number']          = 0;
					
					if( count( $id_list_filter ) ){
						sort( $id_list_filter );
						$args['include']  = $id_list_filter;
					}
					
					if( $string_filter ){
						$args['search'] = '*'.$string_filter.'*';
					}
					
					if( count( $id_not_in_list ) ){
						sort( $id_not_in_list );
						$args['exclude'] = $id_not_in_list;
					}
					
					$all_items = count( get_users( $args ) );
					
					$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
					
					$args['offset'] 	 = $pager_data['first'];
					$args['number']          = ($displayed_items)?$displayed_items:0;
					
					switch( $order )
					{
						case 'a_z':
								$args['orderby']        = 'login';
								$args['order']          = 'ASC';
							break;
						case 'z_a':
								$args['orderby']        = 'login';
								$args['order']          = 'DESC';
							break;
						case 'date_latest':
								$args['orderby']        = 'registered';
								$args['order']          = 'DESC';
							break;
						case 'date_oldest':
								$args['orderby']        = 'registered';
								$args['order']          = 'ASC';
							break;
						default:
								$args['orderby']        = 'ID';
								$args['order']          = 'DESC';
							break;
					}
					
					return array( $all_items, get_users( $args ), $pager_data );
				break;
			case 'customposttype':
			case 'templatehierarchy':
			case 'archive':
					$all_items = otw_pswl_get_wp_items( $type );
					$items = array();
					foreach( $all_items as $item_key => $item_object ){
					
						if( $string_filter ){
							if( ( stripos( $item_object->name, $string_filter ) === false ) ){
								continue;
							}
						}
						
						if( count( $id_list_filter ) && !in_array( $item_object->ID, $id_list_filter ) && !in_array( 'all', $id_list_filter ) ){
							continue;
						}
						if( count( $id_not_in_list ) && ( in_array( $item_object->ID, $id_not_in_list ) || in_array( 'all', $id_not_in_list ) ) ){
							continue;
						}
						$items[] = $item_object;
					}
					$all_items = count( $items );
					$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
					
					$sort_args = array();
					switch( $order )
					{
						case 'a_z':
								$sort_args['name'] = 'ASC';
							break;
						case 'z_a':
								$sort_args['name'] = 'DESC';
							break;
						default:
								$sort_args['name'] = 'ASC';
							break;
					}
					
					if( count( $items ) ){
						
						$items = otw_pswl_asort( $items, $sort_args );
						if( $displayed_items ){
							$items = array_slice( $items, $pager_data['first'], $displayed_items );
						}
					}
					
					return array( $all_items, $items, $pager_data );
				break;
			case 'pagetemplate':
					$all_items = otw_pswl_get_wp_items( $type );
					$items = array();
					foreach( $all_items as $item_key => $item_object ){
					
						if( $string_filter ){
							if( ( stripos( $item_object->name, $string_filter ) === false ) ){
								continue;
							}
						}
						if( count( $id_list_filter ) && !in_array( $item_object->script, $id_list_filter ) && !in_array( 'all', $id_list_filter ) ){
							continue;
						}
						if( count( $id_not_in_list ) && ( in_array( $item_object->script, $id_not_in_list ) || in_array( 'all', $id_not_in_list ) ) ){
							continue;
						}
						$items[] = $item_object;
					}
					$all_items = count( $items );
					$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
					
					$sort_args = array();
					switch( $order )
					{
						case 'a_z':
								$sort_args['name'] = 'ASC';
							break;
						case 'z_a':
								$sort_args['name'] = 'DESC';
							break;
						default:
								$sort_args['name'] = 'ASC';
							break;
					}
					
					if( count( $items ) ){
						
						$items = otw_asort( $items, $sort_args );
						if( $displayed_items ){
							$items = array_slice( $items, $pager_data['first'], $displayed_items );
						}
					}
					
					return array( $all_items, $items, $pager_data );
				break;
			case 'userroles':
					$items = array();
					$wp_roles = new WP_Roles;
					$all_items = $wp_roles->get_names();
					$all_items['notlogged'] = __( 'Not Logged in', 'otw_pswl' );
					
					foreach( $all_items as $u_role_code => $u_role_name ){
						
						if( $string_filter ){
							
							if( ( stripos( $u_role_name, $string_filter ) === false ) ){
								continue;
							}
						}
						
						
						$item = new stdClass();
						$item->ID = $u_role_code;
						if( $u_role_code != 'notlogged' ){
							$item->name = __( 'Logged in as ', 'otw_pswl' ).$u_role_name;
						}else{
							$item->name = $u_role_name;
						}
						
						if( count( $id_list_filter ) && !in_array( $item->ID, $id_list_filter ) && !in_array( 'all', $id_list_filter ) ){
							continue;
						}
						if( count( $id_not_in_list ) && ( in_array( $item->ID, $id_not_in_list ) || in_array( 'all', $id_not_in_list ) ) ){
							continue;
						}
						
						$items[] = $item;
						
					}
					$all_items = count( $items );
					$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
					
					switch( $order )
					{
						case 'a_z':
								$sort_args['name'] = 'ASC';
							break;
						case 'z_a':
								$sort_args['name'] = 'DESC';
							break;
						default:
								$sort_args['name'] = 'ASC';
							break;
					}
					
					if( count( $items ) ){
						
						$items = otw_pswl_asort( $items, $sort_args );
						if( $displayed_items ){
							$items = array_slice( $items, $pager_data['first'], $displayed_items );
						}
					}
					
					return array( count( $all_items ), $items, $pager_data );
				break;
			case 'wpmllanguages':
					if( otw_pswl_installed_plugin( 'wpml' ) ){
						
						$wpml_languages = icl_get_languages( 'skip_missing=0' );
						
						$all_items = count( $wpml_languages );
						
						$items = array();
						foreach( $wpml_languages as $wpml_lang ){
							
							if( $string_filter ){
								
								if( ( stripos( $wpml_lang['translated_name'], $string_filter ) === false ) && ( stripos( $wpml_lang['translated_name'], $string_filter ) === false ) ){
									continue;
								}
							}
							
							$item = new stdClass();
							$item->ID = $wpml_lang['language_code'];
							$item->name = '<img src="'.$wpml_lang['country_flag_url'].'" alt="'.$wpml_lang['language_code'].'" border="0"/>&nbsp;'.$wpml_lang['native_name'];
							
							if( count( $id_list_filter ) && !in_array( $item->ID, $id_list_filter ) && !in_array( 'all', $id_list_filter ) ){
								continue;
							}
							if( count( $id_not_in_list ) && ( in_array( $item->ID, $id_not_in_list ) || in_array( 'all', $id_not_in_list ) ) ){
								continue;
							}
							
							$items[] = $item;
						}
						
						$all_items = count( $items );
						$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
						
						switch( $order )
						{
							case 'a_z':
									$sort_args['name'] = 'ASC';
								break;
							case 'z_a':
									$sort_args['name'] = 'DESC';
								break;
							default:
									$sort_args['name'] = 'ASC';
								break;
						}
						
						if( count( $items ) ){
							
							$items = otw_pswl_asort( $items, $sort_args );
							if( $displayed_items ){
								$items = array_slice( $items, $pager_data['first'], $displayed_items );
							}
						}
						return array( $all_items, $items, $pager_data );
					}
				break;
			case 'bbp_page':
					if( otw_pswl_installed_plugin( 'bbpress' ) ){
						
						$bbp_pages = array();
						
						$bbp_pages[] = array( 'id' => 'forums', 'name' => __( 'Forums', 'otw_pswl' ) );
						$bbp_pages[] = array( 'id' => 'noreplies', 'name' => __( 'Topics no reply', 'otw_pswl' ) );
						$bbp_pages[] = array( 'id' => 'mostpopular', 'name' => __( 'Topics popular', 'otw_pswl' ) );
						$bbp_pages[] = array( 'id' => 'search', 'name' => __( 'Search', 'otw_pswl' ) );
						$bbp_pages[] = array( 'id' => 'singleuser', 'name' => __( 'User pages', 'otw_pswl' ) );
						
						$all_items = count( $bbp_pages );
						
						$items = array();
						foreach( $bbp_pages as $bbp_page ){
							
							if( $string_filter ){
								
								if( stripos( $bbp_page['name'], $string_filter ) === false ){
									continue;
								}
							}
							
							$item = new stdClass();
							$item->ID = $bbp_page['id'];
							$item->name = $bbp_page['name'];
							
							if( count( $id_list_filter ) && !in_array( $item->ID, $id_list_filter ) && !in_array( 'all', $id_list_filter ) ){
								continue;
							}
							if( count( $id_not_in_list ) && ( in_array( $item->ID, $id_not_in_list ) || in_array( 'all', $id_not_in_list ) ) ){
								continue;
							}
							
							$items[] = $item;
						}
						
						$all_items = count( $items );
						$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
						
						switch( $order )
						{
							case 'a_z':
									$sort_args['name'] = 'ASC';
								break;
							case 'z_a':
									$sort_args['name'] = 'DESC';
								break;
							default:
									$sort_args['name'] = 'ASC';
								break;
						}
						
						if( count( $items ) ){
							
							$items = otw_pswl_asort( $items, $sort_args );
							if( $displayed_items ){
								$items = array_slice( $items, $pager_data['first'], $displayed_items );
							}
						}
						
						return array( $all_items, $items, $pager_data );
					}
				break;
			case 'buddypress_page':
					if( otw_pswl_installed_plugin( 'buddypress' ) ){
						global $bp;
						$buddypress_pages = array();
						
						if( isset( $bp->pages->activity ) && $bp->pages->activity->id ){
							$buddypress_pages[] = array( 'id' => $bp->pages->activity->id, 'name' => $bp->pages->activity->title.' '.__( 'page', 'otw_pswl' ) );
						}
						if( isset( $bp->pages->members ) && $bp->pages->members->id ){
							$buddypress_pages[] = array( 'id' => $bp->pages->members->id, 'name' => $bp->pages->members->title.' '.__( 'pages', 'otw_pswl' ) );
						}
						
						$all_items = count( $buddypress_pages );
						
						$items = array();
						foreach( $buddypress_pages as $buddypress_page ){
							
							if( $string_filter ){
								
								if( stripos( $buddypress_page['name'], $string_filter ) === false ){
									continue;
								}
							}
							
							$item = new stdClass();
							$item->ID = $buddypress_page['id'];
							$item->name = $buddypress_page['name'];
							
							if( count( $id_list_filter ) && !in_array( $item->ID, $id_list_filter ) && !in_array( 'all', $id_list_filter ) ){
								continue;
							}
							if( count( $id_not_in_list ) && ( in_array( $item->ID, $id_not_in_list ) || in_array( 'all', $id_not_in_list ) ) ){
								continue;
							}
							
							$items[] = $item;
						}
						
						$all_items = count( $items );
						$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
						
						switch( $order )
						{
							case 'a_z':
									$sort_args['name'] = 'ASC';
								break;
							case 'z_a':
									$sort_args['name'] = 'DESC';
								break;
							default:
									$sort_args['name'] = 'ASC';
								break;
						}
						
						if( count( $items ) ){
							
							$items = otw_pswl_asort( $items, $sort_args );
							if( $displayed_items ){
								$items = array_slice( $items, $pager_data['first'], $displayed_items );
							}
						}
						
						return array( $all_items, $items, $pager_data );
					}
					
				break;
			default:
					
					if( preg_match( "/^cpt_(.*)$/", $type, $matches ) ){
						
						$args = array();
						$args['post_type']      = $matches[1];
						$args['posts_per_page'] = -1;
						
						if( count( $id_list_filter ) ){
							$args['post__in']       = $id_list_filter;
						}
						
						if( $string_filter ){
							add_filter( 'posts_where', 'otw_pswl_post_by_title' );
						}
						
						if( count( $id_not_in_list ) ){
							$args['post__not_in'] = $id_not_in_list;
						}
						
						$the_query = new WP_Query( $args );
						
						$all_items = count( $the_query->posts );
						
						$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
						
						$args['posts_per_page'] = ($displayed_items)?$displayed_items:-1;
						
						switch( $order )
						{
							case 'a_z':
									$args['orderby']        = 'title';
									$args['order']          = 'ASC';
								break;
							case 'z_a':
									$args['orderby']        = 'title';
									$args['order']          = 'DESC';
								break;
							case 'date_latest':
									$args['orderby']        = 'date';
									$args['order']          = 'DESC';
								break;
							case 'date_oldest':
									$args['orderby']        = 'date';
									$args['order']          = 'ASC';
								break;
							case 'modified_latest':
									$args['orderby']        = 'modified';
									$args['order']          = 'DESC';
								break;
							case 'modified_oldest':
									$args['orderby']        = 'modified';
									$args['order']          = 'ASC';
								break;
								
							default:
									$args['orderby']        = 'ID';
									$args['order']          = 'DESC';
								break;
						}
						
						$the_query = new WP_Query( $args );
						
						if( $string_filter ){
							remove_filter('posts_where', 'otw_pswl_post_by_title');
						}
						
						return array( $all_items, $the_query->posts, $pager_data );
					}elseif( preg_match( "/^ctx_(.*)$/", $type, $matches ) ){
						
						$args = array();
						$args['hide_empty']      = 0;
						$args['number']          = 0;
						
						if( count( $id_list_filter ) ){
							sort( $id_list_filter );
							$args['include']  = $id_list_filter;
						}
						
						if( $string_filter ){
							$args['search'] = $string_filter;
						}
						
						if( count( $id_not_in_list ) ){
							sort( $id_not_in_list );
							$args['exclude'] = $id_not_in_list;
						}
						
						$all_items = count( get_terms( $matches[1], $args ) );
						
						$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
						
						$args['offset'] = $pager_data['first'];
						$args['number']          = ($displayed_items)?$displayed_items:0;
						
						switch( $order )
						{
							case 'a_z':
									$args['orderby']        = 'name';
									$args['order']          = 'ASC';
								break;
							case 'z_a':
									$args['orderby']        = 'name';
									$args['order']          = 'DESC';
								break;
							case 'date_latest':
									$args['orderby']        = 'ID';
									$args['order']          = 'DESC';
								break;
							case 'date_oldest':
									$args['orderby']        = 'ID';
									$args['order']          = 'ASC';
								break;
							default:
									$args['orderby']        = 'ID';
									$args['order']          = 'DESC';
								break;
						}
						
						return array( $all_items, get_terms( $matches[1], $args ), $pager_data );
					}elseif( preg_match( "/(.*)_in_ctx_(.*)$/", $type, $matches ) ){
						
						$args = array();
						$args['hide_empty']      = 0;
						$args['number']          = 0;
						
						if( count( $id_list_filter ) ){
							sort( $id_list_filter );
							$args['include']  = $id_list_filter;
						}
						
						if( $string_filter ){
							$args['search'] = $string_filter;
						}
						
						if( count( $id_not_in_list ) ){
							sort( $id_not_in_list );
							$args['exclude'] = $id_not_in_list;
						}
						
						$all_items = count( get_terms( $matches[2], $args ) );
						
						$pager_data = otw_pswl_get_pager_data( $all_items, $displayed_items, $current_page );
						
						$args['offset'] = $pager_data['first'];
						$args['number']          = ($displayed_items)?$displayed_items:0;
						
						switch( $order )
						{
							case 'a_z':
									$args['orderby']        = 'name';
									$args['order']          = 'ASC';
								break;
							case 'z_a':
									$args['orderby']        = 'name';
									$args['order']          = 'DESC';
								break;
							case 'date_latest':
									$args['orderby']        = 'ID';
									$args['order']          = 'DESC';
								break;
							case 'date_oldest':
									$args['orderby']        = 'ID';
									$args['order']          = 'ASC';
								break;
							default:
									$args['orderby']        = 'ID';
									$args['order']          = 'DESC';
								break;
						}
						return array( $all_items, get_terms( $matches[2], $args ), $pager_data );
					}
				break;
		}
		
		return array();
	}
}

/** get wp items based on type
  * @param string
  * @return array
  */
if( !function_exists( 'otw_pswl_get_wp_items' ) ){
	function otw_pswl_get_wp_items( $item_type ){
		switch( $item_type ){
			case 'page':
					$pages = get_pages();
					$pages = otw_group_items( $pages, 'ID', 'post_parent', 0 );
					return $pages;
				break;
			case 'post':
					return get_posts( array( 'numberposts' => -1 )  );
				break;
			case 'postsincategory':
					$categories = get_categories(array('hide_empty' => 0));
					$categories = otw_group_items( $categories, 'cat_ID', 'parent', 0 );
					return $categories;
				break;
			case 'postsintag':
					return get_terms( 'post_tag', '&orderby=name&hide_empty=0' );
				break;
			case 'category':
					$categories = get_categories(array('hide_empty' => 0));
					$categories = otw_group_items( $categories, 'cat_ID', 'parent', 0 );
					
					return $categories;
				break;
			case 'posttag':
					return get_terms( 'post_tag', '&orderby=name&hide_empty=0' );
				break;
			case 'pagetemplate':
					$templates = array();
					$all_templates = get_page_templates();
					
					if( is_array( $all_templates ) && count( $all_templates ) )
					{
						foreach( $all_templates as $page_template_name => $page_template_script )
						{
							$tplObject = new stdClass();
							$tplObject->name = $page_template_name;
							$tplObject->script = $page_template_script;
							$templates[] = $tplObject;
						}
					}
					return $templates;
				break;
			case 'archive':
					$archive_types = array();
					$a_types = array( 'daily' => __('Daily', 'otw_pswl'), 'monthly' => __('Monthly', 'otw_pswl'), 'yearly' => __('Yearly', 'otw_pswl') );

					foreach( $a_types as $a_type => $a_name )
					{
						$aObject = new stdClass();
						$aObject->ID = $a_type;
						$aObject->name = $a_name;
						$archive_types[] = $aObject;
					}
					return $archive_types;
				break;
			case 'customposttype':
					return get_post_types( array(  'public'   => true, '_builtin' => false ), 'object' );
				break;
			case 'templatehierarchy':
					$h_types = array();
					$a_types = array(
							'home'        =>    __('Home', 'otw_pswl'),
							'front'       =>    __('Front Page', 'otw_pswl'),
							'404'         =>    __('Error 404 Page', 'otw_pswl'),
							'search'      =>    __('Search', 'otw_pswl'),
							'date'        =>    __('Date', 'otw_pswl'),
							'author'      =>    __('Author', 'otw_pswl'),
							'category'    =>    __('Category', 'otw_pswl'),
							'tag'         =>    __('Tag', 'otw_pswl'),
							'taxonomy'    =>    __('Taxonomy', 'otw_pswl'),
							'archive'     =>    __('Archive', 'otw_pswl'),
							'single'      =>    __('Singular', 'otw_pswl'),
							'attachment'  =>    __('Attachment', 'otw_pswl'),
							'page'        =>    __('Page', 'otw_pswl')
						);

					foreach( $a_types as $a_type => $a_name )
					{
						$aObject = new stdClass();
						$aObject->ID = $a_type;
						$aObject->name = $a_name;
						$h_types[] = $aObject;
					}
					return $h_types;
				break;
			default:
					if( preg_match( "/^cpt_(.*)$/", $item_type, $matches ) ){
						return get_posts( array( 'post_type' =>  $matches[1], 'numberposts' => -1 )  );
					}elseif( preg_match( "/^ctx_(.*)$/", $item_type, $matches ) ){
						return get_terms( $matches[1], '&orderby=name&hide_empty=0' );
					}
				break;
		}
	}
}

/**
 * Build pager data
 *
 * @param integer total items
 * @param integer total items returned for with limit
 * @param integer limit
 * @param integer current page
 * @return array
 */
if( !function_exists( 'otw_pswl_get_pager_data' ) ){
	function otw_pswl_get_pager_data( $total_items, $items_limit, $current_page  ){
		$pager_data = array();
		
		$pager_data['current'] = $current_page;
		$pager_data['links'] = array();
		$pager_data['links']['next'] = false;
		$pager_data['links']['prev'] = false;
		$pager_data['links']['first']= false;
		$pager_data['links']['last'] = false;
		$pager_data['links']['page'] = array();
		$pager_data['first'] = 0;
		
		if( $items_limit ){
			
			if( ( $total_items %  $items_limit ) == 0 ){
				$pager_data['pages'] = $total_items / $items_limit;
			}else{
				$pager_data['pages'] = $total_items / $items_limit;
				$pager_data['pages'] = (int)$pager_data['pages'] + 1;
			}
			
			if( $pager_data['current'] >= $pager_data['pages'] ){
				$pager_data['current'] = 0;
			}
			
			$pager_data['first'] = $pager_data['current'] * $items_limit;
			$pager_data['last']  = $pager_data['first'] + $items_limit - 1;
			
			if( $pager_data['pages'] > 1 ){
				
				if( $pager_data['current'] < ( $pager_data['pages'] - 1 ) ){
					$pager_data['links']['next'] = $pager_data['current'] + 1;
					$pager_data['links']['last'] = ( $pager_data['pages'] - 1 );
				}
			}
			if( $pager_data['current'] > 0 ){
				$pager_data['links']['first'] = 0;
				$pager_data['links']['prev'] = $pager_data['current'] - 1;
			}
			
			$l_size = 3;
			if( $pager_data['pages'] > 1 ){
				$pager_data['links']['page'][] = $pager_data['current'];
				//build page numbe links
				for( $cP = 1; $cP <= $l_size; $cP++ ){
					
					if( ( $pager_data['current'] - $cP ) >= 0 ){
						$pager_data['links']['page'][] = $pager_data['current'] - $cP;
					}elseif( ( $pager_data['current'] + $l_size + $cP ) < $pager_data['pages'] ){
						$pager_data['links']['page'][] = $pager_data['current'] + $l_size + $cP;
					}
					
					if( ( $pager_data['current'] + $cP ) < $pager_data['pages'] ){
						$pager_data['links']['page'][] = $pager_data['current'] + $cP;
					}elseif( ( $pager_data['current'] - $l_size - $cP ) >= 0 ){
						$pager_data['links']['page'][] = $pager_data['current'] - $l_size - $cP;
					}
				}
				sort( $pager_data['links']['page'] );
			}
		}
		return $pager_data;
	}
}

/**
 * Array sort
 */
if( !function_exists( 'otw_pswl_asort' ) ){
	function otw_pswl_asort( $array, $settings ){
		
		global $otw_pswl_asort_fields;
		
		$otw_pswl_asort_fields = $settings;
		uasort( $array, 'otw_pswl_asort_compare' );
		
		return $array;
	}
}
if( !function_exists( 'otw_pswl_asort_compare' ) ){
	function otw_pswl_asort_compare( $item_1, $item_2 ){
		
		global $otw_pswl_asort_fields;
		
		foreach( $otw_pswl_asort_fields as $field => $order ){
		
			switch( strtolower( gettype( $item_1 ) ) ){
				
				case 'object':
						if( isset( $item_1->$field ) && isset( $item_2->$field ) ){
							
							$s_result = strnatcmp( $item_1->$field, $item_2->$field );
							
							if( $s_result > 0 ){
								return ( $order == "ASC" ) ? 1 : -1;
							}elseif( $s_result < 0 ){
								return ( $order == "ASC" ) ? -1 : 1;
							}
							
						}elseif( isset( $item_1->$field ) && !isset( $item_2->$field ) ){
							
							return ( $order == "ASC" ) ? 1 : -1;
							
						}elseif( !isset( $item_1->$field ) && isset( $item_2->$field ) ){
							
							return ( $order == "ASC" ) ? -1 : 1;
							
						}
					break;
			}
		}
		return 0;
	}
}
if (!function_exists( "otw_overlay_item_attributes" )){
	function otw_overlay_item_attributes( $tag, $item_type, $item_id, $overlay_data, $item_data ){
		
		$attributes = '';
		
		switch( $tag ){
			case 'p':
					$attributes_array = array();
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] ) || isset( $_POST[ 'otw_sbi_'.$item_type ][ 'all' ] ) ){
							$attributes_array['class'][] = 'sitem_selected';
						}else{
							$attributes_array['class'][] = 'sitem_notselected';
						}
					}else{
						if( isset( $overlay_data['validfor'][ $item_type ]['all'] ) ){
							$attributes_array['class'][] = 'sitem_selected';
						}elseif( isset( $overlay_data['validfor'][ $item_type ][ $item_id ] ) ){
							$attributes_array['class'][] = 'sitem_selected';
						}else{
							$attributes_array['class'][] = 'sitem_notselected';
						}
					}
					if( isset( $attributes_array['class'] ) ){
						$attributes .= ' class="'.implode( ' ', $attributes_array['class'] ).'"';
					}
				break;
			case 'c':
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] )  || isset( $_POST[ 'otw_sbi_'.$item_type ][ 'all' ] ) ){
							$attributes .= ' checked="checked"';
						}
					}else{
						if( isset( $overlay_data['validfor'][ $item_type ]['all'] ) ){
							$attributes .= ' checked="checked"';
						}elseif( isset( $overlay_data['validfor'][ $item_type ][ $item_id ] ) ){
							$attributes .= ' checked="checked"';
						}
					}
				break;
			case 'ap':
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] ) ){
							$attributes .= ' class="all sitem_selected"';
						}else{
							$attributes .= ' class="all sitem_notselected"';
						}
					}else{
						if( isset( $overlay_data['validfor'][ $item_type ][ $item_id ] ) ){
							$attributes .= ' class="all sitem_selected"';
						}else{
							$attributes .= ' class="all sitem_notselected"';
						}
					}
				break;
			case 'ac':
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] ) ){
							$attributes .= ' checked="checked"';
						}
					}else{
						if( isset( $overlay_data['validfor'][ $item_type ][ $item_id ] ) ){
							$attributes .= ' checked="checked"';
						}
					}
				break;
			case 'l':
					if( isset( $item_data->_sub_level ) && $item_data->_sub_level ){
						$attributes .= ' style="margin-left: '.( $item_data->_sub_level * 20 ).'px"';
					}
				break;
		}
		echo $attributes;
	}
}

/** get the attribute of wp item
  *  @param string
  *  @param stdClass
  *  @return string
  */
if( !function_exists( 'otw_pswl_wp_item_attribute' ) ){
	function otw_pswl_wp_item_attribute( $item_type, $attribute, $object ){
		
		switch( $attribute ){
			
			case 'ID':
					switch( $item_type ){
						case 'postsincategory':
								return $object->cat_ID;
							break;
						case 'category':
								return $object->cat_ID;
							break;
						case 'postsintag':
								return $object->term_id;
							break;
						case 'posttag':
								return $object->term_id;
							break;
						case 'pagetemplate':
								return $object->script;
							break;
						case 'customposttype':
								return $object->name;
							break;
						case 'author_archive':
								return $object->ID;
							break;
						default:
								if( preg_match( "/^ctx_(.*)$/", $item_type, $matches ) ){
									return $object->term_id;
								}elseif( preg_match( "/^(.*)_in_ctx_(.*)$/", $item_type, $matches ) ){
									return $object->term_id;
								}
								return $object->ID;
							break;
					}
				break;
			case 'TITLE':
					switch( $item_type ){
						case 'page':
						case 'post':
								return $object->post_title;
							break;
						case 'author_archive':
								return $object->display_name;
							break;
						case 'customposttype':
								return $object->label;
							break;
						default:
								if( preg_match( "/^cpt_(.*)$/", $item_type, $matches ) ){
									return $object->post_title;
								}
								return $object->name;
							break;
					}
				break;
		}
	}
}

/**
 * Check if external plugin is installed
 *
 * @param string - plugin name
 * @return boolean
 */
if( !function_exists( 'otw_pswl_installed_plugin' ) ){
	function otw_pswl_installed_plugin( $plugin_name ){
		
		$installed = false;
		switch( $plugin_name ){
			case 'bbpress':
					if(function_exists( 'bbp_get_db_version_raw') && bbp_get_db_version_raw() ){
						$installed = true;
					}
				break;
			case 'wpml':
					if( function_exists( 'icl_get_languages' ) ){
						$installed = true;
					}
				break;
			case 'buddypress':
					if( class_exists( 'BuddyPress' ) ){
						
						global $bp;
						
						if( strtolower( get_class( $bp ) ) == 'buddypress' )
						{
							$installed = true;
						}
					}
				break;
		}
		
		return $installed;
	}
}

if (!function_exists( "otw_pswl_post_by_title" )){
	function otw_pswl_post_by_title( $query ){
		
		global $string_filter, $id_list_filter;
		
		$query .= " AND post_title LIKE '%".$string_filter."%'";
		return $query;
	}
}


if( !function_exists( 'get_next_otw_pswl_sidebar_id' ) ){
	function get_next_otw_pswl_sidebar_id(){
		
		$next_id = 0;
		$existing_sidebars = get_option( 'otw_sidebars' );
		
		if( is_array( $existing_sidebars ) && count( $existing_sidebars ) ){
		
			foreach( $existing_sidebars as $key => $s_data ){
				
				if( preg_match( "/^otw\-sidebar\-([0-9]+)$/", $key, $matches ) ){
				
					if( $matches[1] > $next_id ){
						$next_id = $matches[1];
					}
				}
			}
		}
		return $next_id + 1;
	}
}
if( !function_exists( 'otw_pswl_admin_settings' ) ){
	function otw_pswl_admin_settings(){
		
		$otw_admin_settings = get_option( 'otw_admin_settings' );
		
		if( !is_array( $otw_admin_settings ) ){
			$otw_admin_settings = array();
		}
		
		if( $_POST['type'] ){
		
			switch( $_POST['type'] ){
				
				case 'page':
						if( isset( $_POST['page'] ) ){
						
							switch( $_POST['page'] ){
							
								case 'otw-pswl-manage':
										if( isset( $_POST['setting'] ) && strlen( trim( $_POST['setting'] ) ) && isset( $_POST['value'] ) && strlen( trim( $_POST['value'] ) ) ){
											
											if( !isset( $otw_admin_settings['page'] ) ){
												$otw_admin_settings['page'] = array();
											}
											
											if( !isset( $otw_admin_settings['page'][ $_POST['page'] ] ) ){
												$otw_admin_settings['page'][ $_POST['page'] ] = array();
											}
											$otw_admin_settings['page'][ $_POST['page'] ][ $_POST['setting'] ] = $_POST['value'];
											
											update_option( 'otw_admin_settings', $otw_admin_settings );
										}
									break;
							}
						
						}
					break;
			}
		}
		die;
	}
}