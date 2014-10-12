<?php
class OTW_Overlay extends OTW_Component{

	/**
	 * The option name
	 */
	public $option_name = 'otw_overlays';
	
	/**
	 * Default views for the main overlay types
	 */
	public $views = array(
		'full_bar' => 'views/full_bar',
		'side_box' => 'views/side_box',
		'lightbox' => 'views/lightbox'
	);
	
	public $overlay_types = array(
	
	);
	
	public $create_nav_menu = false;
	
	public $overlays = false;
	
	/**
	 * instance of grid component object
	 */
	public $grid_manager_component_object = false;
	
	public function init(){
		
		$this->init_types();
		
		if( !is_admin() ){
			
			add_action( 'wp_footer', array( $this, 'display_overlays' ) );
			wp_enqueue_style( 'otw-popups', $this->component_url.'css/otw-popups.css', array( ), $this->css_version );
			wp_enqueue_style( 'otw-mfp', $this->component_url.'css/otw-mfp.css', array( ), $this->css_version );
			wp_enqueue_style( 'otw-overlay', $this->component_url.'css/otw-overlay.css', array( ), $this->css_version );
			
			wp_enqueue_script('otw-mpf-code', $this->component_url.'js/mfp-core-inline-0.9.9.js' , array( 'jquery' ), $this->js_version );
			wp_enqueue_script('otw-overlay', $this->component_url.'js/otw-overlay.js' , array( 'jquery' ), $this->js_version );
			
			
			$this->process_tracking();
		}
		
	}
	
	/**
	 * process tracking
	 */
	public function process_tracking(){
	
		if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && isset( $_POST['otwcalloverlay'] ) ){
			
			$overlays = get_option( $this->option_name );
			
			if( is_array( $overlays ) && array_key_exists( $_POST['otwcalloverlay'], $overlays ) ){
				$this->_display_overlay( $overlays[ $_POST['otwcalloverlay'] ], true );
				die;
			}
		
		}elseif( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && isset( $_POST['otw_overlay_action'] ) && ( $_POST['otw_overlay_action'] == 'otw-overlay-tracking' ) ){
		
			$overlays = get_option( $this->option_name );
			
			if( is_array( $overlays ) && array_key_exists( $_POST['overlay_id'], $overlays ) ){
			
				$type = '';
				
				if( isset( $overlays[ $_POST['overlay_id'] ]['type'] ) ){
				
					$type = $overlays[ $_POST['overlay_id'] ]['type'];
				}
				
				switch( $_POST['method'] ){
				
					case 'open_mouse':
						
							$cookie_name = 'otw_ovom_'.md5( $_POST['overlay_id'] );
							
							setcookie( $cookie_name, true, strtotime( 'now +30 days' ) );
							echo 'true';
							die;
						break;
					case 'close_forever':
							
							$cookie_name = 'otw_ovcf_'.md5( $_POST['overlay_id'] );
							
							setcookie( $cookie_name, true, strtotime( 'now +5 years' ) );
							echo 'true';
							die;
						break;
					case 'close_loads':
							if( isset( $overlays[ $_POST['overlay_id' ] ]['options'] ) && isset( $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_close_type_loads'] ) && intval( $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_close_type_loads'] ) ){
								
								$cookie_name = 'otw_ovcl_'.md5( $_POST['overlay_id'] );
								
								setcookie( $cookie_name, $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_close_type_loads'], strtotime( 'now +5 years' ) );
								echo 'true';
								die;
							}
						break;
					case 'close_days':
							if( isset( $overlays[ $_POST['overlay_id' ] ]['options'] ) && isset( $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_close_type_days'] ) && intval( $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_close_type_days'] ) ){
								
								$cookie_name = 'otw_ovcd_'.md5( $_POST['overlay_id'] );
								
								setcookie( $cookie_name, $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_close_type_days'], strtotime( 'now +'.intval( $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_close_type_days']  ).' days' ) );
								echo 'true';
								die;
							}
						break;
					case 'close_loaded':
							if( isset( $overlays[ $_POST['overlay_id' ] ]['options'] ) && isset( $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_close_type_loads'] ) && intval( $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_close_type_loads'] ) ){
								
								$cookie_name = 'otw_ovcl_'.md5( $_POST['overlay_id'] );
								
								if( isset( $_COOKIE[ $cookie_name ] ) && intval( $_COOKIE[ $cookie_name ] ) ){
									
									setcookie( $cookie_name, intval( $_COOKIE[ $cookie_name ] ) - 1, strtotime( 'now +5 years' ) );
									echo 'true';
									die;
								}
							}
						break;
					case 'open_loads':
							if( isset( $overlays[ $_POST['overlay_id' ] ]['options'] ) && isset( $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_open_type_loads'] ) && intval( $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_open_type_loads'] ) ){
							
								$cookie_name = 'otw_ovol_'.md5( $_POST['overlay_id'] );
								
								$current_loads = 0;
								
								if( isset( $_COOKIE[ $cookie_name ] ) && intval( $_COOKIE[ $cookie_name ] ) ){
									$current_loads = intval( $_COOKIE[ $cookie_name ] );
								}
								
								if( $current_loads == $overlays[ $_POST['overlay_id' ] ]['options'][ $type.'_open_type_loads'] ){
									echo 'open';
								}else{
									setcookie( $cookie_name, $current_loads + 1, strtotime( 'now +5 years' ) );
								}
							}
							die;
						break;
				}
			}
		}
	}
	
	/**
	 * Init overlay types
	 */
	private function init_types(){
		
		//full bar
		$this->init_full_bar_types();
		
		//site box
		$this->init_side_box_types();
		
		//lightbox
		$this->init_lightbox_types();
	}
	
	/**
	 * Full bar options
	 */
	private function init_full_bar_types()
	{
		$type = 'full_bar';
		
		$this->overlay_types[ $type ] = array();
		$this->overlay_types[ $type ]['label'] = $this->get_label( 'Full bar - Header' );
		$this->overlay_types[ $type ]['options'] = array();
		
		//options
		$this->overlay_types[ $type ]['options']['main'] = array();
		$this->overlay_types[ $type ]['options']['main']['label'] = $this->get_label( 'Overlay Options' );
		$this->overlay_types[ $type ]['options']['main']['items'] = array();
		
		//position
		$this->overlay_types[ $type ]['options']['main']['items']['position'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['position']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['position']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['position']['options'] = array( 'otw-top-sticky' => $this->get_label( 'Header' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['position']['label'] = $this->get_label( 'Position' );
		$this->overlay_types[ $type ]['options']['main']['items']['position']['default'] = 'otw-top-sticky';
		$this->overlay_types[ $type ]['options']['main']['items']['position']['description'] = '';
		
		//type
		$this->overlay_types[ $type ]['options']['main']['items']['type'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['type']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['type']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['type']['options'] = array( 'fixed-position' => $this->get_label( 'Fixed (default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['type']['label'] = $this->get_label( 'Type' );
		$this->overlay_types[ $type ]['options']['main']['items']['type']['default'] = 'fixed-position';
		$this->overlay_types[ $type ]['options']['main']['items']['type']['description'] = '';
		
		//animation
		$this->overlay_types[ $type ]['options']['main']['items']['animation'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['options'] = array( 'otw-slide-animation' => $this->get_label( 'Slide Animation (default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['label'] = $this->get_label( 'Animation' );
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['default'] = 'otw-slide-animation';
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['description'] = '';
		
		//open_type
		$this->overlay_types[ $type ]['options']['main']['items']['open_type'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['type'] = 'select_subfields';
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['subfields'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['options'] = array( 'otw-show-sticky-load' => $this->get_label( 'On Page Load (default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['label'] = $this->get_label( 'Overlay Load Event' );
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['default'] = 'otw-show-sticky-load';
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['description'] = '';
		
		//close_type
		$this->overlay_types[ $type ]['options']['main']['items']['close_type'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['type'] = 'select_subfields';
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['subfields'] = array( );
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['options'] = array( 'otw-close-forever' => $this->get_label( 'Close for ever' ), 'otw-close-page' => $this->get_label( 'Close until next page load' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['label'] = $this->get_label( 'Overlay Close Options' );
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['default'] = 'otw-close-forever';
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['description'] = '';
		
		//content_width
		$this->overlay_types[ $type ]['options']['main']['items']['content_width'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['type'] = 'input_text';
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['options'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['label'] = $this->get_label( 'Content Width' );
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['default'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['description'] = $this->get_label( 'The width of your content in px. If left empty this means the content is fluid.' );
		
		//content_position
		$this->overlay_types[ $type ]['options']['main']['items']['content_position'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['options'] = array( 'otw-align-left' => $this->get_label( 'Left (default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['label'] = $this->get_label( 'Content Position' );
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['default'] = 'otw-align-left';
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['description'] = '';
		
		//hide_for_small
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['options'] = array( 'hide-overlay-for-small' => $this->get_label( 'Enable(default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['label'] = $this->get_label( 'Hide for Small Screens' );
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['default'] = 'hide-overlay-for-small';
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['description'] = '';
		
		//Show Powered by link
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['type'] = 'input_text';
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['options'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['label'] = $this->get_label( 'Show Powered by link' );
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['default'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['description'] = $this->get_label( 'Want to make some money and help us spread the word for this wonderful plugin? Enter your CodeCanyon.net username and we will build and place a referral link for you at the bottom of your overlay. You earn money from all users that purchase anything from the market. <a href="http://codecanyon.net/make_money/affiliate_program" target="_blank">More info</a>.' );
		
		//styling
		$this->overlay_types[ $type ]['options']['styling'] = array();
		$this->overlay_types[ $type ]['options']['styling']['label'] = $this->get_label( 'Styling Options' );
		$this->overlay_types[ $type ]['options']['styling']['items'] = array();
		
		//shadow
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['target'] = 'section_class';
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['options'] = array( 'otw-sticky-shadow-small' => $this->get_label( 'Small (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['label'] = $this->get_label( 'Shadow' );
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['default'] = 'otw-sticky-shadow-small';
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['description'] = $this->get_label( 'Choose shadow type.' );
		
		//border
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['options'] = array( '' => $this->get_label( '0px (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['label'] = $this->get_label( 'Border Width' );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['default'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['description'] = $this->get_label( 'Choose border width.' );
		
		//border_color_class
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['options'] = array( 'otw-sticky-border-black' => $this->get_label( 'Black (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['label'] = $this->get_label( 'Border Color' );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['default'] = 'otw-sticky-border-black';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['description'] = $this->get_label( 'Choose border color.' );
		
		//border_style_class
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['options'] = array( 'otw-sticky-border-type-solid' => $this->get_label( 'solid (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['label'] = $this->get_label( 'Border Style' );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['default'] = 'otw-sticky-border-type-solid';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['description'] = $this->get_label( 'Choose border style.' );
		
		//rounded_corners
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['options'] = array( '' => $this->get_label( 'none (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['label'] = $this->get_label( 'Rounded Corners' );
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['default'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['description'] = $this->get_label( 'Choose rounded corners.' );
		
		//background_color_class
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['target'] = 'section_class';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['options'] = array( 'otw-sticky-background-white' => $this->get_label( 'White (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['label'] = $this->get_label( 'Background Color' );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['default'] = 'otw-sticky-background-white';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['description'] = $this->get_label( 'Choose background color.' );
		
		//background_pattern_class
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['target'] = 'section_class';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['options'] = array( '' => $this->get_label( 'none (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['label'] = $this->get_label( 'Background Pattern' );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['default'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['description'] = $this->get_label( 'Choose a background pattern.' );
		
		//show_hide_background_color_class
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['target'] = 'show_hide_button_class';
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['options'] = array( 'otw-sticky-background-black' => $this->get_label( 'Black (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['label'] = $this->get_label( 'Show/Hide Button Background Color' );
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['default'] = 'otw-sticky-background-black';
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['description'] = $this->get_label( 'Choose a background color for the show/hide buttons.' );
		
		//custom options
		$this->overlay_types[ $type ]['options']['custom'] = array();
		$this->overlay_types[ $type ]['options']['custom']['label'] = $this->get_label( 'Custom Styling Options' );
		$this->overlay_types[ $type ]['options']['custom']['items'] = array();
		
		//css_class_name
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name'] = array();
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['type'] = 'input_text';
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['target'] = 'wrapper_class';
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['options'] = array();
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['label'] = $this->get_label( 'CSS Class' );
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['default'] = '';
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['description'] = $this->get_label( 'If you\'d like to style this element separately enter a name here. A CSS class with this name will be available for you to style this particular element.' );
	}
	
	/**
	 * Side box options
	 */
	private function init_side_box_types()
	{
		$type = 'side_box';
		
		$this->overlay_types[ $type ] = array();
		$this->overlay_types[ $type ]['label'] = $this->get_label( 'Side box - Right' );
		$this->overlay_types[ $type ]['options'] = array();
		
		//options
		$this->overlay_types[ $type ]['options']['main'] = array();
		$this->overlay_types[ $type ]['options']['main']['label'] = $this->get_label( 'Overlay Options' );
		$this->overlay_types[ $type ]['options']['main']['items'] = array();
		
		//position
		$this->overlay_types[ $type ]['options']['main']['items']['position'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['position']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['position']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['position']['options'] = array( 'otw-right-sticky' => $this->get_label( 'Right' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['position']['label'] = $this->get_label( 'Position' );
		$this->overlay_types[ $type ]['options']['main']['items']['position']['default'] = 'otw-right-sticky';
		$this->overlay_types[ $type ]['options']['main']['items']['position']['description'] = '';
		
		//type
		$this->overlay_types[ $type ]['options']['main']['items']['type'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['type']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['type']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['type']['options'] = array( 'fixed-position' => $this->get_label( 'Fixed (default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['type']['label'] = $this->get_label( 'Type' );
		$this->overlay_types[ $type ]['options']['main']['items']['type']['default'] = 'fixed-position';
		$this->overlay_types[ $type ]['options']['main']['items']['type']['description'] = '';
		
		//animation
		$this->overlay_types[ $type ]['options']['main']['items']['animation'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['options'] = array( 'otw-slide-animation' => $this->get_label( 'Slide Animation (default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['label'] = $this->get_label( 'Animation' );
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['default'] = 'otw-slide-animation';
		$this->overlay_types[ $type ]['options']['main']['items']['animation']['description'] = '';
		
		//open_type
		$this->overlay_types[ $type ]['options']['main']['items']['open_type'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['type'] = 'select_subfields';
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['subfields'] = array(  );
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['options'] = array( 'otw-show-sticky-load' => $this->get_label( 'On Page Load (default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['label'] = $this->get_label( 'Overlay Load Event' );
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['default'] = 'otw-show-sticky-load';
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['description'] = '';
		
		//close_type
		$this->overlay_types[ $type ]['options']['main']['items']['close_type'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['type'] = 'select_subfields';
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['subfields'] = array(  );
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['options'] = array( 'otw-close-forever' => $this->get_label( 'Close for ever' ), 'otw-close-page' => $this->get_label( 'Close until next page load' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['label'] = $this->get_label( 'Overlay Close Options' );
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['default'] = 'otw-close-forever';
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['description'] = '';
		
		//content_width
		$this->overlay_types[ $type ]['options']['main']['items']['content_width'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['type'] = 'input_text';
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['options'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['label'] = $this->get_label( 'Content Width' );
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['default'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['description'] = $this->get_label( 'The width of your content in px. If left empty this means the content is fluid.' );
		
		//content_position
		$this->overlay_types[ $type ]['options']['main']['items']['content_position'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['options'] = array( 'otw-align-left' => $this->get_label( 'Left (default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['label'] = $this->get_label( 'Content Position' );
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['default'] = 'otw-align-left';
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['description'] = '';
		
		//hide_for_small
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['options'] = array( 'hide-overlay-for-small' => $this->get_label( 'Enable(default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['label'] = $this->get_label( 'Hide for Small Screens' );
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['default'] = 'hide-overlay-for-small';
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['description'] = '';
		
		//Show Powered by link
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['type'] = 'input_text';
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['options'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['label'] = $this->get_label( 'Show Powered by Link' );
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['default'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['description'] = $this->get_label( 'Want to make some money and help us spread the word for this wonderful plugin? Enter your CodeCanyon.net username and we will build and place a referral link for you at the bottom of your overlay. You earn money from all users that purchase anything from the market. <a href="http://codecanyon.net/make_money/affiliate_program" target="_blank">More info</a>.' );
		
		//styling
		$this->overlay_types[ $type ]['options']['styling'] = array();
		$this->overlay_types[ $type ]['options']['styling']['label'] = $this->get_label( 'Styling Options' );
		$this->overlay_types[ $type ]['options']['styling']['items'] = array();
		
		//shadow
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['target'] = 'section_class';
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['options'] = array( 'otw-sticky-shadow-small' => $this->get_label( 'Small (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['label'] = $this->get_label( 'Shadow' );
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['default'] = 'otw-sticky-shadow-small';
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['description'] = $this->get_label( 'Choose shadow type.' );
		
		//border
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['options'] = array( '' => $this->get_label( '0px (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['label'] = $this->get_label( 'Border Width' );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['default'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['description'] = $this->get_label( 'Choose border width.' );
		
		//border_color_class
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['options'] = array( 'otw-sticky-border-black' => $this->get_label( 'Black (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['label'] = $this->get_label( 'Border Color' );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['default'] = 'otw-sticky-border-black';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['description'] = $this->get_label( 'Choose border color.' );
		
		//border_style_class
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['options'] = array( 'otw-sticky-border-type-solid' => $this->get_label( 'solid (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['label'] = $this->get_label( 'Border Style' );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['default'] = 'otw-sticky-border-type-solid';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['description'] = $this->get_label( 'Choose border style.' );
		
		//rounded_corners
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['options'] = array( '' => $this->get_label( 'none (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['label'] = $this->get_label( 'Rounded Corners' );
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['default'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['description'] = $this->get_label( 'Choose rounded corners.' );
		
		//background_color_class
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['target'] = 'section_class';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['options'] = array( 'otw-sticky-background-white' => $this->get_label( 'White (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['label'] = $this->get_label( 'Background Color' );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['default'] = 'otw-sticky-background-white';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['description'] = $this->get_label( 'Choose background color.' );
		
		//background_pattern_class
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['target'] = 'section_class';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['options'] = array( '' => $this->get_label( 'none (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['label'] = $this->get_label( 'Background Pattern' );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['default'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['description'] = $this->get_label( 'Choose a background pattern.' );
		
		//show_hide_background_color_class
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['target'] = 'show_hide_button_class';
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['options'] = array( 'otw-sticky-background-black' => $this->get_label( 'Black (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['label'] = $this->get_label( 'Show/Hide Button Background Color' );
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['default'] = 'otw-sticky-background-black';
		$this->overlay_types[ $type ]['options']['styling']['items']['show_hide_background_color_class']['description'] = $this->get_label( 'Choose a background color for the show/hide buttons.' );
		
		//custom options
		$this->overlay_types[ $type ]['options']['custom'] = array();
		$this->overlay_types[ $type ]['options']['custom']['label'] = $this->get_label( 'Custom Styling Options' );
		$this->overlay_types[ $type ]['options']['custom']['items'] = array();
		
		//css_class_name
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name'] = array();
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['type'] = 'input_text';
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['target'] = 'wrapper_class';
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['options'] = array();
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['label'] = $this->get_label( 'CSS Class' );
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['default'] = '';
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['description'] = $this->get_label( 'If you\'d like to style this element separately enter a name here. A CSS class with this name will be available for you to style this particular element.' );
		
	}
	
	/**
	 * light box options
	 */
	private function init_lightbox_types()
	{
		$type = 'lightbox';
		
		$this->overlay_types[ $type ] = array();
		$this->overlay_types[ $type ]['label'] = $this->get_label( 'Lightbox' );
		$this->overlay_types[ $type ]['options'] = array();
		
		//options
		$this->overlay_types[ $type ]['options']['main'] = array();
		$this->overlay_types[ $type ]['options']['main']['label'] = $this->get_label( 'Overlay Options' );
		$this->overlay_types[ $type ]['options']['main']['items'] = array();
		
		//open_type
		$this->overlay_types[ $type ]['options']['main']['items']['open_type'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['type'] = 'select_subfields';
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['subfields'] = array( );
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['options'] = array( 'otw-show-sticky-load' => $this->get_label( 'On Page Load (default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['label'] = $this->get_label( 'Overlay Load Event' );
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['default'] = 'otw-show-sticky-load';
		$this->overlay_types[ $type ]['options']['main']['items']['open_type']['description'] = '';
		
		//close_type
		$this->overlay_types[ $type ]['options']['main']['items']['close_type'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['type'] = 'select_subfields';
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['subfields'] = array( );
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['options'] = array( 'otw-close-forever' => $this->get_label( 'Close for ever (default)' ), 'otw-close-page' => $this->get_label( 'Close until next page load' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['label'] = $this->get_label( 'Overlay Close Options' );
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['default'] = 'otw-close-forever';
		$this->overlay_types[ $type ]['options']['main']['items']['close_type']['description'] = '';
		
		//content_width
		$this->overlay_types[ $type ]['options']['main']['items']['content_width'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['type'] = 'input_text';
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['options'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['label'] = $this->get_label( 'Content Width' );
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['default'] = '500';
		$this->overlay_types[ $type ]['options']['main']['items']['content_width']['description'] = $this->get_label( 'The width of your content in px. If left empty this means the content is fluid.' );
		
		//content_position
		$this->overlay_types[ $type ]['options']['main']['items']['content_position'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['options'] = array( 'otw-align-left' => $this->get_label( 'Left (default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['label'] = $this->get_label( 'Content Position' );
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['default'] = 'otw-align-left';
		$this->overlay_types[ $type ]['options']['main']['items']['content_position']['description'] = '';
		
		//animation type
		$this->overlay_types[ $type ]['options']['main']['items']['animation_type'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['animation_type']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['animation_type']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['animation_type']['options'] = array( 'mfp-zoom-in' => $this->get_label( 'Zoom in(default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['animation_type']['label'] = $this->get_label( 'Animation Type' );
		$this->overlay_types[ $type ]['options']['main']['items']['animation_type']['default'] = 'mfp-zoom-in';
		$this->overlay_types[ $type ]['options']['main']['items']['animation_type']['description'] = '';
		
		//hide_for_small
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['options'] = array( 'hide-overlay-for-small' => $this->get_label( 'Enable(default)' ) );
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['label'] = $this->get_label( 'Hide for Small Screens' );
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['default'] = 'hide-overlay-for-small';
		$this->overlay_types[ $type ]['options']['main']['items']['hide_for_small']['description'] = '';
		
		//Show Powered by link
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['type'] = 'input_text';
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['target'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['options'] = array();
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['label'] = $this->get_label( 'Show Powered by Link' );
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['default'] = '';
		$this->overlay_types[ $type ]['options']['main']['items']['affiliate_username']['description'] = $this->get_label( 'Want to make some money and help us spread the word for this wonderful plugin? Enter your CodeCanyon.net username and we will build and place a referral link for you at the bottom of your overlay. You earn money from all users that purchase anything from the market. <a href="http://codecanyon.net/make_money/affiliate_program" target="_blank">More info</a>.' );
		
		//styling
		$this->overlay_types[ $type ]['options']['styling'] = array();
		$this->overlay_types[ $type ]['options']['styling']['label'] = $this->get_label( 'Styling Options' );
		$this->overlay_types[ $type ]['options']['styling']['items'] = array();
		
		//shadow
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['options'] = array( 'otw-sticky-shadow-small' => $this->get_label( 'Small (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['label'] = $this->get_label( 'Shadow' );
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['default'] = 'otw-sticky-shadow-small';
		$this->overlay_types[ $type ]['options']['styling']['items']['shadow']['description'] = $this->get_label( 'Choose Shadow Type.' );
		
		//border
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['options'] = array( '' => $this->get_label( '0px (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['label'] = $this->get_label( 'Border Width' );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['default'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_width']['description'] = $this->get_label( 'Choose border width.' );
		
		//border_color_class
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['options'] = array( 'otw-sticky-border-black' => $this->get_label( 'Black (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['label'] = $this->get_label( 'Border Color' );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['default'] = 'otw-sticky-border-black';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_color_class']['description'] = $this->get_label( 'Choose border color.' );
		
		//border_style_class
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['options'] = array( 'otw-sticky-border-type-solid' => $this->get_label( 'solid (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['label'] = $this->get_label( 'Border Style' );
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['default'] = 'otw-sticky-border-type-solid';
		$this->overlay_types[ $type ]['options']['styling']['items']['border_style_class']['description'] = $this->get_label( 'Choose border style.' );
		
		//rounded_corners
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['target'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['options'] = array( '' => $this->get_label( 'none (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['label'] = $this->get_label( 'Rounded Corners' );
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['default'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['rounded_corners']['description'] = $this->get_label( 'Choose rounded corners.' );
		
		//background_color_class
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['options'] = array( 'otw-sticky-background-white' => $this->get_label( 'White (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['label'] = $this->get_label( 'Background Color' );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['default'] = 'otw-sticky-background-white';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_color_class']['description'] = $this->get_label( 'Choose background color.' );
		
		//background_pattern_class
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['options'] = array( '' => $this->get_label( 'none (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['label'] = $this->get_label( 'Background Pattern' );
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['default'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['background_pattern_class']['description'] = $this->get_label( 'Choose a background pattern.' );
		
		//overlay_color_class
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_color_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_color_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_color_class']['target'] = 'data-effect';
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_color_class']['options'] = array( '' => $this->get_label( 'none (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_color_class']['label'] = $this->get_label( 'Overlay Color' );
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_color_class']['default'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_color_class']['description'] = $this->get_label( 'Choose the overlay color.' );
		
		//overlay_pattern_class
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_pattern_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_pattern_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_pattern_class']['target'] = 'data-effect';
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_pattern_class']['options'] = array( '' => $this->get_label( 'none (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_pattern_class']['label'] = $this->get_label( 'Overlay Pattern' );
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_pattern_class']['default'] = '';
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_pattern_class']['description'] = $this->get_label( 'Choose the overlay pattern.' );
		
		//overlay_opacity_class
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_opacity_class'] = array();
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_opacity_class']['type'] = 'select';
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_opacity_class']['target'] = 'data-effect';
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_opacity_class']['options'] = array( 'otw-opacity-40' => $this->get_label( '0.4 (default)' ) );
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_opacity_class']['label'] = $this->get_label( 'Overlay Opacity' );
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_opacity_class']['default'] = 'otw-opacity-40';
		$this->overlay_types[ $type ]['options']['styling']['items']['overlay_opacity_class']['description'] = $this->get_label( 'Choose the overlay opacity.' );
		
		//custom options
		$this->overlay_types[ $type ]['options']['custom'] = array();
		$this->overlay_types[ $type ]['options']['custom']['label'] = $this->get_label( 'Custom Styling Options' );
		$this->overlay_types[ $type ]['options']['custom']['items'] = array();
		
		//css_class_name
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name'] = array();
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['type'] = 'input_text';
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['target'] = 'class';
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['options'] = array();
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['label'] = $this->get_label( 'CSS Class' );
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['default'] = '';
		$this->overlay_types[ $type ]['options']['custom']['items']['css_class_name']['description'] = $this->get_label( 'If you\'d like to style this element separately enter a name here. A CSS class with this name will be available for you to style this particular element.' );
	}
	

	
	/**
	 * Output the outlays
	 *
	 */
	public function display_overlays(){
		
		$overlays = get_option( $this->option_name );
		
		if( is_array( $overlays ) ){
			
			$requested_objects = $this->get_current_object();
			
			foreach( $overlays as $overlay ){
			
				$is_active = $this->is_active( $overlay, $requested_objects );
				
				if( $is_active['active'] ){
				
					$this->_display_overlay( $overlay );
					
				}elseif( strlen( $is_active['replacement'] ) ){
					echo $is_active['replacement'];
				}
			}
		}
	}
	
	private function _display_overlay( $overlay, $from_link = false ){
	
	
					if( isset( $this->views[ $overlay['type'] ] ) ){
						
						$overlay_vars = array();
						$overlay_vars['id'] = $overlay['id'];
						$overlay_vars['show_label'] = $this->get_label( 'Show' );
						$overlay_vars['content'] = '';
						$overlay_vars['class'] = $overlay['id'];
						$overlay_vars['style'] = '';
						$overlay_vars['data-effect'] = '';
						$overlay_vars['data-style'] = '';
						$overlay_vars['data-close-effect'] = '';
						$overlay_vars['wrapper_class'] = 'otw-sticky-wrapoer';
						$overlay_vars['section_class'] = 'otw-sticky-content';
						$overlay_vars['show_hide_button_class'] = '';
						$overlay_vars['show_hide_button_style'] = '';
						$overlay_vars['section_style'] = '';
						$overlay_vars['content_inner_style'] = '';
						$overlay_vars['data_param'] = '';
						$overlay_vars['data_index'] = '';
						$overlay_vars['affiliate_username'] = '';
						
						if( $overlay['type'] == 'lightbox' ){
							$overlay_vars['class'] = $this->append_attribute( $overlay_vars['class'], 'otw-white-popup mfp-with-anim' );
						}
						if( in_array( $overlay['type'], array( 'full_bar', 'side_box' ) ) ){
							$overlay_vars['class'] = $this->append_attribute( $overlay_vars['class'], 'otw-first-show-sticky otw-hide-sticky' );
						}
						
						if( isset( $this->overlay_types[ $overlay['type'] ] ) && isset( $this->overlay_types[ $overlay['type'] ]['options'] ) ){
							
							foreach( $this->overlay_types[ $overlay['type'] ]['options'] as $option_type => $option_data ){
							
								foreach( $option_data['items'] as $element_name => $element_data ){
								
									if( isset( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ){
									
										if( strlen( $element_data['target'] ) ){
											
											$overlay_vars[ $element_data['target'] ] = $this->append_attribute( $overlay_vars[ $element_data['target'] ], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
										}
										else
										{
											switch( $element_name ){
												
												case 'zindex':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															switch( $overlay['type'] ){
																case 'lightbox':
																		$overlay_vars['data_index'] = $this->append_attribute( $overlay_vars['data_index'], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
																	break;
																default:
																		$overlay_vars['style'] = $this->append_attribute( $overlay_vars['style'], 'z-index: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].';' );
																	break;
															}
														}
													break;
												case 'background_pattern_url':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															switch( $overlay['type'] ){
																case 'lightbox':
																		$overlay_vars['style'] = $this->append_attribute( $overlay_vars['style'], 'background-image: url(\''.$overlay['options'][ $overlay['type'].'_'.$element_name ].'\');' );
																	break;
																default:
																		$overlay_vars['section_style'] = $this->append_attribute( $overlay_vars['section_style'], 'background-image: url(\''.$overlay['options'][ $overlay['type'].'_'.$element_name ].'\');' );
																	break;
															}
														}
													break;
												case 'border_color_class':
												case 'border_style_class':
												case 'rounded_corners':
														
														switch( $overlay['type'] ){
														
															case 'lightbox':
																	$target = 'class';
																break;
															default:
																	$target = 'section_class';
																break;
														}
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) && isset( $overlay['options'][ $overlay['type'].'_border_width'] ) && ( $overlay['options'][ $overlay['type'].'_border_width'] > 0 ) ){
															
															$overlay_vars[ $target ] = $this->append_attribute( $overlay_vars[ $target ], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
														}
													break;
												case 'border_color':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) && isset( $overlay['options'][ $overlay['type'].'_border_width'] ) && ( $overlay['options'][ $overlay['type'].'_border_width'] > 0 ) ){
															switch( $overlay['type'] ){
																case 'lightbox':
																		$overlay_vars['style'] = $this->append_attribute( $overlay_vars['style'], 'border-color: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].';' );
																	break;
																default:
																		$overlay_vars['section_style'] = $this->append_attribute( $overlay_vars['section_style'], 'border-color: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].';' );
																	break;
															}
														}
													break;
												case 'show_hide_background_color':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															$overlay_vars['show_hide_button_style'] = $this->append_attribute( $overlay_vars['show_hide_button_style'], 'background-color: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].';' );
														}
													break;
												case 'background_color':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															switch( $overlay['type'] ){
																case 'lightbox':
																		$overlay_vars['style'] = $this->append_attribute( $overlay_vars['style'], 'background-color: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].';' );
																	break;
																default:
																		$overlay_vars['section_style'] = $this->append_attribute( $overlay_vars['section_style'], 'background-color: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].';' );
																	break;
															}
														}
													break;
												case 'content_padding_top':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															
															switch( $overlay['type'] ){
																default:
																		$overlay_vars['content_inner_style'] = $this->append_attribute( $overlay_vars['content_inner_style'], 'padding-top: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].'px;' );
																	break;
															}
														}
													break;
												case 'content_padding_right':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															switch( $overlay['type'] ){
																default:
																		$overlay_vars['content_inner_style'] = $this->append_attribute( $overlay_vars['content_inner_style'], 'padding-right: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].'px;' );
																	break;
															}
														}
													break;
												case 'content_padding_bottom':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															switch( $overlay['type'] ){
																default:
																		$overlay_vars['content_inner_style'] = $this->append_attribute( $overlay_vars['content_inner_style'], 'padding-bottom: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].'px;' );
																	break;
															}
														}
													break;
												case 'content_padding_left':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															switch( $overlay['type'] ){
																default:
																		$overlay_vars['content_inner_style'] = $this->append_attribute( $overlay_vars['content_inner_style'], 'padding-left: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].'px;' );
																	break;
															}
														}
													break;
												case 'height_from_top':
														switch( $overlay['type'] ){
															
															case 'side_box':
																	if( isset( $overlay['options'][ $overlay['type'].'_position'] ) && in_array( $overlay['options'][ $overlay['type'].'_position'], array( 'otw-left-sticky', 'otw-right-sticky') ) ){
																	
																		$top = false;
																		$bottom = false;
																		
																		if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
																			$top = $overlay['options'][ $overlay['type'].'_'.$element_name ].'%';
																			$bottom = 'auto';
																		}elseif( isset( $overlay['options'][ $overlay['type'].'_height_from_bottom' ] ) && strlen( trim( $overlay['options'][ $overlay['type'].'_height_from_bottom' ] ) ) ){
																			$top = 'auto';
																			$bottom = $overlay['options'][ $overlay['type'].'_height_from_bottom' ].'%';
																		}
																		if( $top && $bottom ){
																			$overlay_vars['style'] = $this->append_attribute( $overlay_vars['style'], 'top: '.$top.'; bottom: '.$bottom.';' );
																		}
																	}
																break;
														}
													break;
												case 'content_position':
														switch( $overlay['type'] ){
															case 'lightbox':
																	if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
																	
																		$overlay_vars['class'] = $this->append_attribute( $overlay_vars['class'], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
																	}
																break;
															default:
																	
																	if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
																		
																		if( isset( $overlay['options'][ $overlay['type'].'_position'] ) && in_array( $overlay['options'][ $overlay['type'].'_position'], array( 'otw-top-sticky', 'otw-bottom-sticky', 'otw-left-sticky', 'otw-right-sticky') ) ){
																			$overlay_vars['class'] = $this->append_attribute( $overlay_vars['class'], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
																		}
																	}
																break;
														}
													break;
												case 'content_width':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
														
															switch( $overlay['type'] ){
															
																case 'full_bar':
																		$overlay_vars['content_inner_style'] = $this->append_attribute( $overlay_vars['content_inner_style'], 'width: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].'px;' );
																	break;
																case 'side_box':
																		if( isset( $overlay['options'][ $overlay['type'].'_position'] ) && in_array( $overlay['options'][ $overlay['type'].'_position'], array( 'otw-top-sticky', 'otw-bottom-sticky') ) ){
																			$overlay_vars['style'] = $this->append_attribute( $overlay_vars['style'], 'width: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].'px;' );
																		}else{
																			$overlay_vars['content_inner_style'] = $this->append_attribute( $overlay_vars['content_inner_style'], 'width: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].'px;' );
																		}
																	break;
																case 'lightbox':
																		$overlay_vars['style'] = $this->append_attribute( $overlay_vars['style'], 'width: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].'px; max-width: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].'px;' );
																	break;
															}
														}
													break;
												case 'show_button_label':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															$overlay_vars['show_label'] = $overlay['options'][ $overlay['type'].'_'.$element_name ];
														}
													break;
												case 'open_type':
														
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
														
															switch( $overlay['type'] ){
															
																case 'lightbox':
																		switch( $overlay['options'][ $overlay['type'].'_'.$element_name ] ){
																		
																			case 'otw-show-sticky-load':
																			case 'otw-show-sticky-loads':
																					$overlay_vars[ 'class' ] = $this->append_attribute( $overlay_vars[ 'class' ], 'lh-'.$overlay['options'][ $overlay['type'].'_'.$element_name ] );
																					if( !$from_link ){
																						$overlay_vars[ 'class' ] = $this->append_attribute( $overlay_vars[ 'class' ], 'mfp-hide' );
																					}
																				break;
																			case 'otw-show-sticky-delay':
																					$overlay_vars[ 'class' ] = $this->append_attribute( $overlay_vars[ 'class' ], 'lh-'.$overlay['options'][ $overlay['type'].'_'.$element_name ] );
																					
																					if( !empty( $overlay['options'][ $overlay['type'].'_'.$element_name.'_delay'] ) ){
																						$overlay_vars['data_param'] = ' data-param="'.$overlay['options'][ $overlay['type'].'_'.$element_name.'_delay'].'"';
																					}
																					if( !$from_link ){
																						$overlay_vars[ 'class' ] = $this->append_attribute( $overlay_vars[ 'class' ], 'mfp-hide' );
																					}
																				break;
																			case 'otw-show-sticky-mouse':
																					
																					if( !isset( $_COOKIE ) || !isset( $_COOKIE['otw_ovom_'.md5( $overlay['id'] )] ) || !$_COOKIE['otw_ovom_'.md5( $overlay['id'] )] ){
																					
																						//$overlay_vars[ 'class' ] = $this->append_attribute( $overlay_vars[ 'class' ], 'lh-'.$overlay['options'][ $overlay['type'].'_'.$element_name ] );
																					}
																					$overlay_vars[ 'class' ] = $this->append_attribute( $overlay_vars[ 'class' ], 'lh-'.$overlay['options'][ $overlay['type'].'_'.$element_name ] );
																					if( !$from_link ){
																						$overlay_vars[ 'class' ] = $this->append_attribute( $overlay_vars[ 'class' ], 'mfp-hide' );
																					}
																				break;
																		}
																	break;
																default:
																		switch( $overlay['options'][ $overlay['type'].'_'.$element_name ] ){
																			
																			case 'otw-show-sticky-mouse':
																					
																					if( !isset( $_COOKIE ) || !isset( $_COOKIE['otw_ovom_'.md5( $overlay['id'] )] ) || !$_COOKIE['otw_ovom_'.md5( $overlay['id'] )] ){
																					
																						//$overlay_vars[ 'class' ] = $this->append_attribute( $overlay_vars[ 'class' ], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
																					}
																					$overlay_vars[ 'class' ] = $this->append_attribute( $overlay_vars[ 'class' ], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
																				break;
																			case 'otw-show-sticky-loads':
																					
																					$overlay_vars[ 'wrapper_class' ] = $this->append_attribute( $overlay_vars[ 'wrapper_class' ], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
																					$overlay_vars[ 'wrapper_class' ] = $this->append_attribute( $overlay_vars[ 'wrapper_class' ], 'otw-hide-sticky' );
																				break;
																			case 'otw-show-sticky-delay':
																					
																					$overlay_vars[ 'wrapper_class' ] = $this->append_attribute( $overlay_vars[ 'wrapper_class' ], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
																					$overlay_vars[ 'wrapper_class' ] = $this->append_attribute( $overlay_vars[ 'wrapper_class' ], 'otw-hide-sticky' );
																					
																					if( !empty( $overlay['options'][ $overlay['type'].'_'.$element_name.'_delay'] ) ){
																						$overlay_vars['data_param'] = ' data-param="'.$overlay['options'][ $overlay['type'].'_'.$element_name.'_delay'].'"';
																					}
																				break;
																			case 'otw-show-sticky-link':
																					$overlay_vars[ 'class' ] = $this->append_attribute( $overlay_vars[ 'class' ], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
																				break;
																		}
																	break;
															}
														}
													break;
												case 'affiliate_username':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															$overlay_vars['affiliate_username']  = $overlay['options'][ $overlay['type'].'_'.$element_name ];
															$overlay_vars[ 'section_class' ] = $this->append_attribute( $overlay_vars[ 'section_class' ], 'otw-affiliate' );
														}
													break;
												case 'border_width':
														
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
														
															switch( $overlay['type'] ){
															
																case 'lightbox':
																		$overlay_vars[ 'style' ] = $this->append_attribute( $overlay_vars[ 'style' ], 'border-width: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].'px;' );
																	break;
																default:
																		$overlay_vars[ 'section_style' ] = $this->append_attribute( $overlay_vars[ 'section_style' ], 'border-width: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].'px;' );
																	break;
															}
														}
													break;
												case 'animation_type':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
														
															switch( $overlay['options'][ $overlay['type'].'_'.$element_name ] ){
																case 'hinge':
																		$overlay_vars[ 'data-close-effect' ] = $this->append_attribute( $overlay_vars[ 'data-close-effect' ], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
																	break;
																default:
																		$overlay_vars[ 'data-effect' ] = $this->append_attribute( $overlay_vars[ 'data-effect' ], $overlay['options'][ $overlay['type'].'_'.$element_name ] );
																	break;
															}
														}
													break;
												case 'overlay_color':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															$overlay_vars[ 'data-style' ] = $this->append_attribute( $overlay_vars[ 'data-style' ], 'background-color: '.$overlay['options'][ $overlay['type'].'_'.$element_name ].';' );
														}
													break;
												case 'overlay_pattern_url':
														if( strlen( trim( $overlay['options'][ $overlay['type'].'_'.$element_name ] ) ) ){
															$overlay_vars[ 'data-style' ] = $this->append_attribute( $overlay_vars[ 'data-style' ], 'background-image: url('.$overlay['options'][ $overlay['type'].'_'.$element_name ].');' );
														}
													break;
											}
										}
									}
								}
							}
						}
						
						if( strlen( $overlay_vars['show_hide_button_class'] ) ){
							$overlay_vars['show_hide_button_class'] =  ' '.$overlay_vars['show_hide_button_class'];
						}
						if( strlen( $overlay_vars[ 'show_hide_button_style' ] ) ){
							$overlay_vars[ 'show_hide_button_style' ] = ' style="'.$overlay_vars[ 'show_hide_button_style' ].'"';
						}
						if( strlen( $overlay_vars[ 'content_inner_style' ] ) ){
							$overlay_vars[ 'content_inner_style' ] = ' style="'.$overlay_vars[ 'content_inner_style' ].'"';
						}
						if( strlen( $overlay_vars[ 'section_style' ] ) ){
							$overlay_vars[ 'section_style' ] = ' style="'.$overlay_vars[ 'section_style' ].'"';
						}
						if( strlen( $overlay_vars[ 'style' ] ) ){
							$overlay_vars[ 'style' ] = ' style="'.$overlay_vars[ 'style' ].'"';
						}
						if( strlen( $overlay_vars[ 'data-effect' ] ) ){
							$overlay_vars[ 'data-effect' ] = ' data-effect="'.$overlay_vars[ 'data-effect' ].'"';
						}
						if( strlen( $overlay_vars[ 'data-style' ] ) ){
							$overlay_vars[ 'data-style' ] = ' data-style="'.$overlay_vars[ 'data-style' ].'"';
						}
						if( strlen( $overlay_vars[ 'data_index' ] ) ){
							$overlay_vars[ 'data_index' ] = ' data-index="'.$overlay_vars[ 'data_index' ].'"';
						}
						if( strlen( $overlay_vars[ 'data-close-effect' ] ) ){
							$overlay_vars[ 'data-close-effect' ] = ' data-ceffect="'.$overlay_vars[ 'data-close-effect' ].'"';
						}
						
						if( $this->grid_manager_component_object ){
						
							$overlay_content = $this->grid_manager_component_object->decode_grid_content( 'ogm_'.$overlay['id'], otw_stripslashes( $overlay['grid_content'] ) );
							
							$overlay_vars['content'] = $this->otw_shortcode_remove_wpautop( $overlay_content );
						}
						
						include( $this->views[ $overlay['type'] ].'.view.php' );
					}
	}
	
	/**
	 * Check if the overlays is active
	 */
	private function is_active( $overlay, $requested_objects ){
		
		$result = array();
		$result['active'] = false;
		$result['replacement'] = '';
		
		if( $overlay['status'] == 'active' ){
			
			
			//check if its closed forever
			if( isset( $_COOKIE ) && isset( $_COOKIE['otw_ovcf_'.md5( $overlay['id'] ) ] ) && ( isset( $overlay['options'] ) ) && isset( $overlay['options'][ $overlay['type'].'_close_type' ] ) && ( $overlay['options'][ $overlay['type'].'_close_type' ] == 'otw-close-forever' ) ){
				
				$result['active'] = false;
				$result['replacement'] = '<div id="ovcf_'.$overlay['id'].'" class="otcl_track"></div>';
				return $result;
			}
			
			$result['active'] = true;
			
		}//end has status active
		
		return $result;
	}
	
	/** get current requested object
	 *
	 *  @return array
	 */
	 
	private function get_current_object(){
		
		return array( array( '', 0, 'flow' ) );
	}
	
	/** check if given pverlay is valid for the given object and object_id
	  *  @param string
	  *  @param string
	  *  @param string
	  *  @return string
	  */
	private function validate_overlay_object( $overlay, $object, $object_id ){
	
		$valid = true;
		
		return $valid;
	}
	
	/**
	 * Resolve excluded items give list of ids or slugs
	*/
	private function check_from_excluded_items( $object, $object_id, $items ){
		
		$valid = true;
		
		return $valid;
		
	}
	
	/** append attribute to existing list with attributes
	 *
	 *  @param string
	 *  @param string
	 *  @return string
	 */
	public function append_attribute( $append_to, $attribute ){
		
		$result = $append_to;
		
		if( strlen( $result ) ){
			$result .= ' '.$attribute;
		}else{
			$result .= $attribute;
		}
		return $result;
	}
}
?>
