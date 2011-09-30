<div class="wrap">
	<h2>Facebook Events Management</h2>
	<form method="POST" action="options.php">
<?php
	if ( function_exists( 'settings_field' ) ) {
		settings_field( 'facebookevents-options' );
	} else {
		wp_nonce_field( 'update-options' );
?>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="facebookevents-appid,facebookevents-appsecret" />
<?php
	}
?>
	<div id="poststuff">
	<div class="postbox open">
		<h3><?php echo __('<strong>Facebook Settings:</strong> ', 'event-espresso'); ?></h3>
		<div class="inside">
		<table class="form-table">
		<tbody>
		<tr>
			<th><label for="appid"><strong>App ID</strong></label></th>
			<td><input type="text" id="appid" value="<?php echo $app_id; ?>" name="facebookevents-appid" /></td>
		</tr>
		<tr>
			<th><label for="appsecret"><strong>App Secret</strong></label></th>
			<td><input type="text" id="appsecret" value="<?php echo $app_secret; ?>" name="facebookevents-appsecret" /></td>
		</tr>
		</tbody>
		</table>
		</div>
	</div>
	</div>
	<p class="submit" style="clear:both;">
		<input type="submit" name="Submit"  class="button-primary" value="<?php _e('Save Settings', 'event-espresso') ?>" />
	</p>
	</form>
<?php if ( $session ) { ?>
	<h3>Scheduled Events Via Facebook</h3>
	<table id="table" class="widefat fixed" width="100%"> 
	<thead>
		<tr>
			<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:4%;">ID</th>
			<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:15%;">Name</th>
			<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:4%;">Attending</th>
			<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:4%;">Declined</th>
			<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:4%;">Maybe</th>
			<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:4%;">No Reply</th>
			<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:15%;">Shortcode</th>
			<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:15%;">Facebook Link</th>
		</tr>
	</thead>
	<tbody>
	<?php espresso_fb_eventstable(); ?>
	</tbody>
	</table>
	<?php } ?>
</div>