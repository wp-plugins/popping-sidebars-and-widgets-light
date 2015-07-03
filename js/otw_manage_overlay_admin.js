var otw_pswl_items = {};

jQuery(document).ready(function() {
	init_manage_page();
});
function init_manage_page(){
	
	var s_labels = jQuery( '.otw_overlay_item_filter label' ).click(  function( event ){
		event.preventDefault();
		otw_select_sitem( this );
	} );
	
	jQuery( '#type' ).change( function(){
		
		var overlay_options = jQuery( 'div.otw_overlay_options' );
		
		overlay_options.removeClass( 'otw_overlay_openened' );
		
		if( this.value ){
			jQuery( '#otw_overlay_options_' + this.value ).addClass( 'otw_overlay_openened' );
		};
	} );
	
	jQuery( '#otw_overlay_options_' + jQuery( '#type' ).val() ).addClass( 'otw_overlay_openened' );
	
	jQuery( '#full_bar_position, #side_box_position' ).change( function(){
		set_default_content_width( jQuery( this ) );
	} );
	set_default_content_width( jQuery( '#full_bar_position' ) );
	set_default_content_width( jQuery( '#side_box_position' ) );
	
	jQuery( '#side_box_position' ).change( function(){
		
		set_height_from_top_and_bottom();
	} );
	
	set_height_from_top_and_bottom();
	
	jQuery('#otw-col-left').find('.sitem_toggle, .sitem_header').click(function() {
	
		
		if( jQuery(this).parent().attr( 'data-type' ) ){
			
			var post_params = {};
			post_params.type = 'page';
			post_params.page = 'otw-pswl-manage';
			post_params.setting = 'metabox_' + jQuery(this).parent().attr( 'data-type' )
			if( jQuery(this).parent().find( '.inside').hasClass( 'otw_closed' ) ){
				post_params.value = 'open';
			}else{
				post_params.value = 'closed';
			}
			var req_url = 'admin-ajax.php?action=otw_pswl_admin_settings';
			
			var settings = {
				url: req_url,
				type: 'post',
				data: post_params
			};
			jQuery.ajax( settings );
		}
		jQuery(this).parent().find( '.inside').toggleClass('otw_closed');
		jQuery(this).parent().toggleClass('closed');
	});
	
	jQuery( '.otw_info_box .otw_info_box_controls a' ).click( function(){
	
		var matches = false;
		
		var link = jQuery( this );
		
		var info_box = link.parent().parent();
		
		if( matches = link.attr( 'class' ).match( /otw_(open|close)_info_box/ ) ){
			var post_params = {};
			post_params.type = 'page';
			post_params.page = 'otw-pswl-manage';
			post_params.setting = 'infobox';
			
			switch( matches[1] ){
			
				case 'open':
						info_box.removeClass( 'otw_ib_closed' ).addClass( 'otw_ib_open' );
						post_params.value = 'open';
					break;
				default:
						info_box.removeClass( 'otw_ib_open' ).addClass( 'otw_ib_closed' );
						post_params.value = 'closed';
					break;
			}
			
			var req_url = 'admin-ajax.php?action=otw_pswl_admin_settings';
			
			var settings = {
				url: req_url,
				type: 'post',
				data: post_params
			};
			jQuery.ajax( settings );
		}
	} );
	
	jQuery( '.otw_info_box .otw_info_box_content a.otw_scrollto' ).click( function(){
	
		var link = jQuery( this );
		
		if( link.attr( 'rel' ).match( /^otw_options_(.*)$/ ) ){
			
			jQuery( '.otw_overlay_openened .' + link.attr( 'rel' ) ).each( function(){
			
				var node = jQuery( this );
				
				if( node.css( 'display' ) == 'block' ){
					
					jQuery('html, body').animate({
						scrollTop: ( node.offset().top - 30 )
					}, 1000 );
				}
			
			});
		}else if( link.attr( 'rel' ) == 'otw_grid_manager_content' ){
		
			jQuery( '#otw_grid_manager_content' ).each( function(){
			
				var node = jQuery( this );
				
				jQuery('html, body').animate({
					scrollTop: ( node.offset().top - 30 )
				}, 1000 );
			
			});
		}
	});

};
function set_height_from_top_and_bottom(){

	var position = jQuery( '#side_box_position' ).val();
	
	switch( position ){
	
		case 'otw-right-sticky':
		case 'otw-left-sticky':
				jQuery( '#side_box_height_from_top' ).parent().show();
				jQuery( '#side_box_height_from_bottom' ).parent().show();
			break;
		default:
				jQuery( '#side_box_height_from_top' ).parent().hide();
				jQuery( '#side_box_height_from_bottom' ).parent().hide();
			break;
	}
}
function set_default_content_width( position_node ){
	
	var ov_id = jQuery( '#otw_overlay_id' ).val();
	
	if( ov_id == '0' ){
		
		var type = '';
		
		var matches = false;
		
		if( matches = position_node.attr( 'name' ).match( /^(.*)_position$/ ) ){
			type = matches[1];
		}
		
		if( type ){
			
			var default_val = '';
			switch( position_node.val() ){
			
				case 'otw-top-sticky':
				case 'otw-bottom-sticky':
						
						switch( type ){
						
							case 'full_bar':
							case 'side_box':
									default_val = 600;
								break;
						}
						
					break;
				case 'otw-left-sticky':
				case 'otw-right-sticky':
						
						switch( type ){
						
							case 'full_bar':
							case 'side_box':
									default_val = 400;
								break;
						}
						
					break;
			}
			if( jQuery( '#' + type + '_content_width' ).size() ){
				jQuery( '#' + type + '_content_width' ).val( default_val );
			};
		};
	};
	
}