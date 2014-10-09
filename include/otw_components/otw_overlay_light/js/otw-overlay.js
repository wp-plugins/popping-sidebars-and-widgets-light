jQuery(document).ready(function(){
	
	otw_set_full_bar_height();
	
	// Switch stickies
	jQuery('.otw-hide-label').click(function(){
	
		
		if( jQuery(this).parent().hasClass( 'otw-bottom-sticky' ) && jQuery(this).parent().hasClass( 'scrolling-position' ) ){
			var node = jQuery( this );
			
			node.parent().find( '.otw-show-label' ).hide();
			
			setTimeout( function(){
				node.parent().css( 'top', 'auto' );
				node.parent().css( 'position', 'fixed' );
				node.parent().find( '.otw-show-label' ).show();
				
				
			}, 1000 );
		}
		if( jQuery(this).parent().hasClass( 'otw-right-sticky' ) && jQuery(this).parent().hasClass( 'scrolling-position' ) ){
			var node = jQuery( this );
			node.parent().find( '.otw-show-label' ).hide();
			
			setTimeout( function(){
				node.parent().css( 'top', node.parent()[0].initial_top );
				node.parent().css( 'position', 'fixed' );
				node.parent().find( '.otw-show-label' ).show();
			}, 1000 );
		}
		jQuery(this).parent().toggleClass("otw-hide-sticky");
		jQuery(this).parent().toggleClass("otw-show-sticky");
		
		
		if( jQuery(this).parent().hasClass( 'otw-first-show-sticky') ){
			jQuery(this).parent().removeClass( 'otw-first-show-sticky');
		}
	});
	jQuery('.otw-show-label').click(function(){
		
		if( jQuery(this).parent().hasClass( 'otw-bottom-sticky' ) && jQuery(this).parent().hasClass( 'scrolling-position' ) ){
		
			var node = jQuery( this );
			
			node.parent().find( '.otw-show-label' ).slideDown({
				duration: 1000,
				easing: "easeInQuad",
				complete: function(){
					
				}
			});
			setTimeout( function(){
				node.parent().find( '.otw-show-label' ).hide();
				node.parent().css( 'top', jQuery(document).scrollTop() + jQuery( window ).height() - node.parent().height() );
				node.parent().css( 'position', 'absolute' );
				
			
			}, 1000 );
		}
		if( jQuery(this).parent().hasClass( 'otw-right-sticky' ) && jQuery(this).parent().hasClass( 'scrolling-position' ) ){
			
			var node = jQuery( this );
			
			node.parent().css( 'position', 'fixed' );
			
			if( typeof( node.parent()[0].initial_top ) == 'undefined' ){
				node.parent()[0].initial_top = node.parent().position().top;
			}
			
			setTimeout( function(){
				node.parent().css( 'top', jQuery(document).scrollTop() + node.parent().position().top );
				node.parent().css( 'position', 'absolute' );
			
			}, 1000 );
		}
		
		jQuery(this).parent().toggleClass("otw-hide-sticky");
		jQuery(this).parent().toggleClass("otw-show-sticky");
	});
	 
	//open events
	if( jQuery( '.otw-first-show-sticky' ).size() ){
	
		jQuery( '.otw-first-show-sticky' ).each( function(){
			
			var node = jQuery( this );
			
			if( node.parents( '.otw-show-sticky-delay' ).size() ){
				
				setTimeout( function(){ 
					
					node.parents( '.otw-show-sticky-delay' ).removeClass( 'otw-show-sticky-delay' );
					node.find( '.otw-show-label' ).click();
					node.find( '.otw-hide-label' ).click( function(){
						jQuery( this ).parents( '.otw-first-show-sticky' ).first().removeClass( 'otw-first-show-sticky' );
					} );
				}, node.parents( '.otw-show-sticky-delay' ).attr( 'data-param' ) );
				
			}else if( node.parents( '.otw-show-sticky-loads' ).size() ){
				
				var parent_node = node.parents( '.otw-show-sticky-loads' ).first();
				
				jQuery.ajax({
					
					type: 'post',
					data: { 
						otw_overlay_action: 'otw-overlay-tracking',
						method: 'open_loads',
						overlay_id: parent_node.find( '.otw-sticky' ).attr( 'id' )
					},
					success: function( response ){
					
						if( response == 'open' ){
							node.find( '.otw-show-label' ).click();
							parent_node.removeClass( 'otw-show-sticky-loads' );
						}
					}
				});
			}else if( node.hasClass( 'otw-show-sticky-mouse' ) ){
				
				node.find( '.otw-hide-label' ).click( function(){
					jQuery( this ).parents( '.otw-first-show-sticky' ).first().removeClass( 'otw-first-show-sticky' );
					jQuery( this ).parents( '.otw-show-sticky-mouse' ).first().removeClass( 'otw-show-sticky-mouse' );
				} );
			}else if( node.hasClass( 'otw-show-sticky-link' ) ){
				node.find( '.otw-hide-label' ).click( function(){
					jQuery( this ).parents( '.otw-first-show-sticky' ).first().removeClass( 'otw-first-show-sticky' );
					jQuery( this ).parents( '.otw-show-sticky-link' ).first().removeClass( 'otw-show-sticky-link' );
				} );
			}else{
				node.find( '.otw-show-label' ).click();
				node.find( '.otw-hide-label' ).click( function(){
					jQuery( this ).parents( '.otw-first-show-sticky' ).first().removeClass( 'otw-first-show-sticky' );
				} );
			}
		} );
	}
	
	if( jQuery( '.otw-show-sticky-mouse' ).size() || jQuery( '.lh-otw-show-sticky-mouse' ).size() ){
		
		jQuery( document ).mouseout( function( e ){
			
			if( e.relatedTarget == null ){
				
				jQuery( '.otw-show-sticky-mouse' ).each( function(){
					
					if( !jQuery( this ).hasClass( 'otw-show-sticky') ){
						
						var node = jQuery( this );
						
						if( node.hasClass( 'otw-first-show-sticky' ) ){
							node.find( '.otw-show-label' ).click();
						}else{
							node.removeClass( 'otw-show-sticky-mouse' );
						}
						
						jQuery.ajax({
						
							type: 'post',
							data: { 
								otw_overlay_action: 'otw-overlay-tracking',
								method: 'open_mouse',
								overlay_id: jQuery( this ).attr( 'id' )
							}
						});
					};
				});
				
				jQuery( '.lh-otw-show-sticky-mouse' ).each( function(){
					
					if( jQuery( this ).hasClass( 'mfp-hide') ){
						
						otwOpenMagnificPopup( jQuery( this ) );
						
						jQuery.ajax({
						
							type: 'post',
							data: { 
								otw_overlay_action: 'otw-overlay-tracking',
								method: 'open_mouse',
								overlay_id: jQuery( this ).attr( 'id' )
							}
						});
						jQuery( this ).removeClass( 'lh-otw-show-sticky-mouse' );
						
					};
				});
			};
		} );
	};
	
	//close forever events
	if( jQuery( '.otw-close-forever' ).size() ){
	
		jQuery( '.otw-close-forever' ).each( function(){
		
			var node = jQuery( this );
			if( jQuery( this ).find( '.otw-hide-label' ).size() ){
				
				jQuery( this ).find( '.otw-hide-label' ).click( function(){
					
					node.find( '.otw-show-label' ).hide();
					
					jQuery.ajax({
							
						type: 'post',
						data: { 
							otw_overlay_action: 'otw-overlay-tracking',
							method: 'close_forever',
							overlay_id: node.attr( 'id' )
						}
					});
				} );
			};
		} );
	};
	
	//close for number of page loads
	if( jQuery( '.otw-close-loads' ).size() ){
	
		jQuery( '.otw-close-loads' ).each( function(){
		
			var node = jQuery( this );
			jQuery( this ).find( '.otw-hide-label' ).click( function(){
				
				node.find( '.otw-show-label' ).hide();
				
				jQuery.ajax({
						
					type: 'post',
					data: { 
						otw_overlay_action: 'otw-overlay-tracking',
						method: 'close_loads',
						overlay_id: node.attr( 'id' )
					}
				});
			} );
		} );
	};
	
	//close for number of days
	if( jQuery( '.otw-close-days' ).size() ){
	
		jQuery( '.otw-close-days' ).each( function(){
		
			var node = jQuery( this );
			jQuery( this ).find( '.otw-hide-label' ).click( function(){
				
				node.find( '.otw-show-label' ).hide();
				
				jQuery.ajax({
						
					type: 'post',
					data: { 
						otw_overlay_action: 'otw-overlay-tracking',
						method: 'close_days',
						overlay_id: node.attr( 'id' )
					}
				});
			} );
		} );
	};
	
	//decriment page loads session
	if( jQuery( '.otcl_track' ).size() ){
	
		jQuery( '.otcl_track' ).each( function(){
		
			var matches = false;
			
			if( matches = this.id.match( /^(ovcl|ovcf)_otw\-(.*)$/ ) ){
			
				var track_method = '';
				
				switch( matches[1] ){
					case 'ovcl':
							track_method = 'close_loaded';
						break;
					case 'ovcf':
							track_method = 'close_forever';
						break;
				}
				
				jQuery.ajax({
						
					type: 'post',
					data: { 
						otw_overlay_action: 'otw-overlay-tracking',
						method: track_method,
						overlay_id: 'otw-' + matches[2]
					}
				});
			}
		} );
	}
	
	//close until page reload
	if( jQuery( '.otw-close-page' ).size() ){
	
		jQuery( '.otw-close-page' ).each( function(){
		
			var node = jQuery( this );
			jQuery( this ).find( '.otw-hide-label' ).click( function(){
				
				node.find( '.otw-show-label' ).hide();
				
			} );
		} );
	};
	
	//open after click on link
	if( jQuery( '.otw-open-overlay' ).size() ){
	
		jQuery( '.otw-open-overlay' ).click( function(){
			
			var ov_id = jQuery( this ).attr( 'href' );
			
			var overlay = jQuery( ov_id );
			
			if( overlay.size() ){
				
				if( overlay.hasClass( 'otw-first-show-sticky' ) ){
					overlay.addClass( 'otw-link-opened' );
					overlay.find( '.otw-show-label' ).click();
				}else if( overlay.hasClass( 'otw-link-opened' ) ){
					overlay.find( '.otw-show-label' ).click();
				}else{
					overlay.addClass( 'otw-link-opened' );
					overlay.removeClass( 'otw-show-sticky-link' );
				}
			};
		} );
	}
	
	//open after page loads
	if( jQuery( '.otw-show-sticky-loads' ).size() ){
	
		jQuery( '.otw-show-sticky-loads' ).each( function(){
		
			var node = jQuery( this );
			
			if( node.find( '.otw-first-show-sticky' ).size() > 0 ){
				return;
			}
			
			jQuery.ajax({
				
				type: 'post',
				data: { 
					otw_overlay_action: 'otw-overlay-tracking',
					method: 'open_loads',
					overlay_id: node.find( '.otw-sticky' ).attr( 'id' )
				},
				success: function( response ){
				
					if( response == 'open' ){
						node.removeClass( 'otw-show-sticky-loads' );
					}
				}
			});
		} );
	};
	
	//open after page delay
	if( jQuery( '.otw-show-sticky-delay' ).size() ){
	
		jQuery( '.otw-show-sticky-delay' ).each( function(){
			
			var node = jQuery( this );
			setTimeout( function(){ node.removeClass( 'otw-show-sticky-delay' ); }, node.attr( 'data-param' ) );
		} );
	};
	
	//open light boxes
	if( jQuery( '.lh-otw-show-sticky-load' ).size() ){
	
		jQuery( '.lh-otw-show-sticky-load' ).each( function(){
			
			var node = jQuery( this );
			otwOpenMagnificPopup( node )
			
		} );
	};
	
	//lilght open after page delay
	if( jQuery( '.lh-otw-show-sticky-delay' ).size() ){
	
		jQuery( '.lh-otw-show-sticky-delay' ).each( function(){
			
			var node = jQuery( this );
			
			setTimeout( function(){ 
				node.removeClass( 'lh-otw-show-sticky-delay' );
				otwOpenMagnificPopup( node );
			}, node.attr( 'data-param' ) );
		} );
	};
	
	//open light box after page loads
	if( jQuery( '.lh-otw-show-sticky-loads' ).size() ){
	
		jQuery( '.lh-otw-show-sticky-loads' ).each( function(){
		
			var node = jQuery( this );
			jQuery.ajax({
				
				type: 'post',
				data: { 
					otw_overlay_action: 'otw-overlay-tracking',
					method: 'open_loads',
					overlay_id: node.attr( 'id' )
				},
				success: function( response ){
				
					if( response == 'open' ){
						otwOpenMagnificPopup( node );
					}
				}
			});
		} );
	};
	
	
	
	//open from links
	// Inline popups
	jQuery('.otw-display-overlay').click( function(){
		
		var ov_id = jQuery( this ).attr( 'href' ).replace( /^#/, '' );
		
		if( jQuery( '#' + ov_id ).size() == 0 ){
			
			jQuery.ajax({
				
				type: 'post',
				data: '&otwcalloverlay=' + ov_id,
				
				success: function( response ){
					
					jQuery( 'body' ).append( response );
					
					jQuery( '#' + ov_id ).find('.otw-hide-label').click(function(){
						jQuery(this).parent().toggleClass("otw-hide-sticky");
						jQuery(this).parent().toggleClass("otw-show-sticky");
					});
					jQuery( '#' + ov_id ).find('.otw-show-label').click(function(){
						jQuery(this).parent().toggleClass("otw-hide-sticky");
						jQuery(this).parent().toggleClass("otw-show-sticky");
					});
					
					otw_overlay_with_admin_bar();
					
					setTimeout( function(){
						jQuery( '#' + ov_id ).find('.otw-show-label').click();
					}, 100 );
				}
			});
		}else{
			jQuery( '#' + ov_id ).find( '.otw-show-label' ).click();
		}
	});
	
	jQuery('.otw-display-popup-link').each( function(){
	
		var ov_id = jQuery( this ).attr( 'href' ).replace( /^#/, '' );
		
		var effects = jQuery( this ).attr( 'data-effect' );
		
		if( jQuery( '#' + ov_id ).size() ){
			jQuery( this ).magnificPopup({
				callbacks: {
					beforeOpen: function() {
						this.st.mainClass = this.st.el.attr('data-effect');
					},
					open: function(){
						if( this.content.hasClass( 'hide-overlay-for-small' ) ){
							this.bgOverlay.addClass( 'hide-overlay-for-small' );
							this.container.addClass( 'hide-overlay-for-small' );
							this.container.parents('.mfp-wrap').addClass( 'hide-overlay-for-small' );
						}
					}
				},
				removalDelay: 500, //delay removal by X to allow out-animation
				midClick: true
			});
		}else{
			jQuery( this ).magnificPopup({
				type: 'ajax',
				ajax: {
					settings: {
						method: 'post',
						data: '&otwcalloverlay=' + ov_id
					}
				},
				callbacks: {
					ajaxContentAdded: function(){
						
						if( jQuery( this.content[2] ).hasClass( 'hide-overlay-for-small' ) ){
							this.bgOverlay.addClass( 'hide-overlay-for-small' );
							this.container.addClass( 'hide-overlay-for-small' );
							this.container.parents('.mfp-wrap').addClass( 'hide-overlay-for-small' );
						}
					}
				},
				mainClass: effects,
				removalDelay: 500, //delay removal by X to allow out-animation
				midClick: true
			});
		};
	} );
	
	jQuery('.otw-display-hinge').each( function(){
	
		var ov_id = jQuery( this ).attr( 'href' ).replace( /^#/, '' );
		
		var effects = 'mfp-with-fade';
		
		if( jQuery( this ).attr( 'data-effect' ).length ){
			effects = effects + ' '+ jQuery( this ).attr( 'data-effect' );
		}
		
		if( jQuery( '#' + ov_id ).size() ){
			jQuery( this ).magnificPopup({
				mainClass: 'mfp-with-fade',
				callbacks: {
					beforeOpen: function() {
						this.st.mainClass = this.st.el.attr('data-effect');
					},
					beforeClose: function() {
						this.content.addClass('hinge');
					},
					close: function() {
						this.content.removeClass('hinge');
					},
					open: function(){
						if( this.content.hasClass( 'hide-overlay-for-small' ) ){
							this.bgOverlay.addClass( 'hide-overlay-for-small' );
							this.container.addClass( 'hide-overlay-for-small' );
							this.container.parents('.mfp-wrap').addClass( 'hide-overlay-for-small' );
						}
					}
				},
				removalDelay: 1000, //delay removal by X to allow out-animation
				midClick: true
			});
		}else{
			jQuery( this ).magnificPopup({
				type: 'ajax',
				ajax: {
					settings: {
						method: 'post',
						data: '&otwcalloverlay=' + ov_id
					}
				},
				mainClass: effects,
				removalDelay: 1000, //delay removal by X to allow out-animation
				callbacks: {
					ajaxContentAdded: function(){
						
						if( jQuery( this.content[2] ).hasClass( 'hide-overlay-for-small' ) ){
							this.bgOverlay.addClass( 'hide-overlay-for-small' );
							this.container.addClass( 'hide-overlay-for-small' );
							this.container.parents('.mfp-wrap').addClass( 'hide-overlay-for-small' );
						}
					},
					beforeClose: function() {
						this.content.addClass('hinge');
					},
					close: function() {
						this.content.removeClass('hinge');
					}
				},
				midClick: true
			});
		}
	} );
	
	otw_overlay_with_admin_bar();
	
	
	
} );



otw_set_full_bar_height = function(){
	
	jQuery( '.otw-full-bar' ).each( function(){
	
		var node = jQuery( this );
		
		if( node.hasClass( 'otw-left-sticky' ) ||  node.hasClass( 'otw-right-sticky' ) ){
			
			var new_height = jQuery( 'body' ).height();
			
			node.css( 'height', new_height + 'px' );
		};
	});
};

otw_overlay_with_admin_bar = function(){

	var admin_bar = jQuery( '#wpadminbar' );
	
	if( admin_bar.size() ){
		
		jQuery( '.otw-sticky.otw-top-sticky, .otw-sticky.otw-left-sticky, .otw-sticky.otw-right-sticky ' ).each( function(){
			
		//	if( jQuery( 'body' ).css( 'position') != 'relative' ){
				if( typeof( this.wpadminbar_fixed ) == 'undefined' ){
					jQuery( this ).css( 'top',  jQuery( this ).position().top + admin_bar.height() );
					
					this.wpadminbar_fixed = 1;
				}
		//	}
		} )
	
	}
}

otwOpenMagnificPopup = function( node ){
	
	var is_hinge = false;
	var close_delay = 500;
	var main_class = '';
	
	if( node.attr( 'data-ceffect' ) && ( node.attr( 'data-ceffect' ) == 'hinge' ) ){
		is_hinge = true;
	}
	
	var ppCallbacks = {
		beforeOpen: function() {
			this.st.mainClass = node.attr('data-effect'); 
		},
		open: function(){
		
			otw_init_magnificPopup( this );
		}
	};
	
	if( is_hinge ){
		
		ppCallbacks.beforeClose = function() {
			this.content.addClass('hinge');
		}
		ppCallbacks.close = function() {
			this.content.removeClass('hinge');
		}
		close_delay = 1000;
		main_class = 'mfp-with-fade';
	};
	
	jQuery.magnificPopup.open({
		mainClass: main_class,
		removalDelay: close_delay,
		items: {
			src: node,
			type: 'inline'
		},
		callbacks: ppCallbacks,
		midClick: true
	});
}

otw_init_magnificPopup = function( popupObject ){
	
	if( popupObject.content.attr( 'data-index' ) && Number( popupObject.content.attr( 'data-index' ) ) > 0 ){
		
		popupObject.bgOverlay.zIndex( Number( popupObject.content.attr( 'data-index' ) ) );
		popupObject.container.parents( '.mfp-wrap' ).zIndex( Number( popupObject.content.attr( 'data-index' ) ) + 1 );
	}
	if( popupObject.content.hasClass( 'otw-close-forever' ) ){
	
		if( popupObject.content.find( '.mfp-close' ).size() ){
		
			popupObject.content.find( '.mfp-close' ).click( function(){
				jQuery.ajax({
					type: 'post',
					data: { 
						otw_overlay_action: 'otw-overlay-tracking',
						method: 'close_forever',
						overlay_id: popupObject.content.attr( 'id' )
					}
				});
			});
		}
	}else if( popupObject.content.hasClass( 'otw-close-loads' ) ){
		
		if( popupObject.content.find( '.mfp-close' ).size() ){
		
			popupObject.content.find( '.mfp-close' ).click( function(){
				jQuery.ajax({
					type: 'post',
					data: { 
						otw_overlay_action: 'otw-overlay-tracking',
						method: 'close_loads',
						overlay_id: popupObject.content.attr( 'id' )
					}
				});
			});
		}
	}else if( popupObject.content.hasClass( 'otw-close-days' ) ){
		
		if( popupObject.content.find( '.mfp-close' ).size() ){
		
			popupObject.content.find( '.mfp-close' ).click( function(){
				jQuery.ajax({
					type: 'post',
					data: { 
						otw_overlay_action: 'otw-overlay-tracking',
						method: 'close_days',
						overlay_id: popupObject.content.attr( 'id' )
					}
				});
			});
		}
	}
	if( popupObject.content.hasClass( 'hide-overlay-for-small' ) ){
		popupObject.bgOverlay.addClass( 'hide-overlay-for-small' );
		popupObject.container.addClass( 'hide-overlay-for-small' );
		popupObject.container.parents('.mfp-wrap').addClass( 'hide-overlay-for-small' );
	}
	
	if( popupObject.content.attr( 'data-style' ) && popupObject.content.attr( 'data-style' ).length ){
		popupObject.bgOverlay.css( 'cssText', popupObject.content.attr( 'data-style' ) );
	}
}