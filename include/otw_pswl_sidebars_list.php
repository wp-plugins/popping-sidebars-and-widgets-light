<?php
/** List with all available otw sitebars
  *
  *
  */
global $_wp_column_headers;

$_wp_column_headers['toplevel_page_otw-pswl'] = array(
	'title' => __( 'Title', 'otw_pswl' ),
	'description' => __( 'Description', 'otw_pswl' ),
	'status' => __( 'Status', 'otw_pswl' )

);

$otw_sidebar_list = get_option( 'otw_sidebars' );

$message = '';
$massages = array();
$messages[1] = __( 'Sidebar saved.', 'otw_pswl' );
$messages[2] = __( 'Sidebar deleted.', 'otw_pswl' );
$messages[3] = __( 'Sidebar activated.', 'otw_pswl' );
$messages[4] = __( 'Sidebar deactivated.', 'otw_pswl' );


if( isset( $_GET['message'] ) && isset( $messages[ $_GET['message'] ] ) ){
	$message .= $messages[ $_GET['message'] ];
}


?>
<?php if ( $message ) : ?>
<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br/></div>
	<h2>
		<?php _e('Available Custom Sidebars', 'otw_pswl') ?>
		<a class="preview button" href="admin.php?page=otw-pswl-sidebars-manage"><?php _e('Add New', 'otw_pswl') ?></a>
	</h2>
	<?php include_once( 'otw_pswl_help.php' );?>
	<form class="search-form" action="" method="get">
	</form>
	
	<br class="clear" />
	<?php if( is_array( $otw_sidebar_list ) && count( $otw_sidebar_list ) ){?>
	<table class="widefat fixed" cellspacing="0">
		<thead>
			<tr>
				<?php foreach( $_wp_column_headers['toplevel_page_otw-pswl'] as $key => $name ){?>
					<th><?php echo $name?></th>
				<?php }?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<?php foreach( $_wp_column_headers['toplevel_page_otw-pswl'] as $key => $name ){?>
					<th><?php echo $name?></th>
				<?php }?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach( $otw_sidebar_list as $sidebar_item ){?>
				<tr>
					<?php foreach( $_wp_column_headers['toplevel_page_otw-pswl'] as $column_name => $column_title ){
						
						$edit_link = admin_url( 'admin.php?page=otw-pswl-sidebars-manage&amp;action=edit&amp;sidebar='.$sidebar_item['id'] );
						$delete_link = admin_url( 'admin.php?page=otw-pswl-sidebars-action&amp;sidebar='.$sidebar_item['id'].'&amp;action=delete' );
						$status_link = '';
						switch( $sidebar_item['status'] ){
							case 'active':
									$status_link = admin_url( 'admin.php?page=otw-pswl-sidebars-action&amp;sidebar='.$sidebar_item['id'].'&amp;action=deactivate' );
									$status_link_name = __( 'Deactivate', 'otw_pswl' );
								break;
							case 'inactive':
									$status_link = admin_url( 'admin.php?page=otw-pswl-sidebars-action&amp;sidebar='.$sidebar_item['id'].'&amp;action=activate' );
									$status_link_name = __( 'Activate', 'otw_pswl' );
								break;
						}
						switch($column_name) {

							case 'cb':
									echo '<th scope="row" class="check-column"><input type="checkbox" name="itemcheck[]" value="'. esc_attr($sidebar_item['id']) .'" /></th>';
								break;
							case 'title':
									echo '<td><strong><a href="'.$edit_link.'" title="'.esc_attr(sprintf(__('Edit &#8220;%s&#8221;', 'otw_pswl'), $sidebar_item['title'])).'">'.$sidebar_item['title'].'</a></strong><br />';
									
									echo '<div class="row-actions">';
									echo '<a href="'.$edit_link.'">' . __('Edit', 'otw_pswl') . '</a>';
									echo ' | <a href="'.$delete_link.'">' . __('Delete', 'otw_pswl'). '</a>';
									if( $status_link ){
									echo ' | <a href="'.$status_link.'">' . $status_link_name. '</a>';
									}
									echo '</div>';
									
									echo '</td>';
								break;
							case 'description':
									echo '<td>'.$sidebar_item['description'].'</td>';
								break;
							case 'status':
									switch( $sidebar_item['status'] ){
										case 'active':
												echo '<td class="sidebar_active">'.__( 'Active', 'otw_pswl' ).'</td>';
											break;
										case 'inactive':
												echo '<td class="sidebar_inactive">'.__( 'Inactive', 'otw_pswl' ).'</td>';
											break;
										default:
												echo '<td>'.__( 'Unknown', 'otw_pswl' ).'</td>';
											break;
									}
								break;
						}
					}?>
				</tr>
			<?php }?>
		</tbody>
	</table>
	<?php }else{ ?>
		<p><?php _e('No custom sidebars found.', 'otw_pswl')?></p>
	<?php } ?>
</div>
