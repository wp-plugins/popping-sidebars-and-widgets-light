<!-- Overlay -->
<div id="<?php echo $overlay_vars['id'];?>" class="<?php echo $overlay_vars['class'];?>"<?php echo $overlay_vars['data_param']?><?php echo $overlay_vars['data-style']?><?php echo $overlay_vars['data-effect']?><?php echo $overlay_vars['data-close-effect']?><?php echo $overlay_vars['style']?><?php echo $overlay_vars['data_index']?>>
	<div class="otw-popup-content-inner"<?php echo $overlay_vars['content_inner_style']?>>
		<?php echo $overlay_vars['content']; ?>
		<?php if( strlen( $overlay_vars['affiliate_username'] ) ){?>
			<div class="otw-overlay-affiliate">
				<?php echo $this->get_label( 'Powered by' )?> <a href="http://codecanyon.net/item/popping-sidebars-and-widgets-for-wordpress/8688220?ref=<?php echo $overlay_vars['affiliate_username'] ?>" target="_blank"><?php echo $this->get_label( 'Popping Sidebars and Widgets.')?></a>
			</div>
		<?php }?>
 	</div>
</div>