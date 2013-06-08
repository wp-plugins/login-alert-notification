<?php

add_action('admin_init', 'wpla_register_settings');
function wpla_register_settings() {
	register_setting('login_alerts_settings_group', 'login_alerts_settings');
}

/*
 * Option panel on WP Dashboard
 */

function wpla_admin(){

	global $login_alerts_options;
	
	ob_start(); ?>
	<div class="wrap">
	<div id="icon-options-general" class="icon32">
	<br>
	</div>
	<h2>Login Alert Notification Options</h2>
	<form method="post" action="options.php">
	
		<?php settings_fields('login_alerts_settings_group'); ?>
	
	<p>Notify login alerts to your Windows, Mac, Tablet PC and Smartphones via Email and push notification services such as <a href="http://www.prowlapp.com/" target="_blank">Prowl</a>, <a href="http://www.notifymyandroid.com/" target=_blank>Notify My Android</a>, <a href="http://im.kayac.com" target=_blank>im.kayac.com</a> and <a href="https://pushover.net/" target=_blank>Pushover</a> if detected someone trying to your WordPress login page.</p>
		<h3>Redirection</h3>
		<p><input id="login_alerts_settings[failredirect_enable]" type="checkbox" name="login_alerts_settings[failredirect_enable]" value="1" <?php checked(1, isset( $login_alerts_options['failredirect_enable'] ) ); ?> />
		<label class="description" for="login_alerts_settings[failredirect_enable]">Automatically redirect to "<?php echo get_option('home'); ?>" after login attempt failed</label></p>

		<h3>Don't disturb options</h3>
		<p><input id="login_alerts_settings[excludeadmin_enable]" type="checkbox" name="login_alerts_settings[excludeadmin_enable]" value="1" <?php checked(1, isset( $login_alerts_options['excludeadmin_enable'] ) ); ?> />
		<label class="description" for="login_alerts_settings[excludeadmin_enable]">Ignore 'admin' user's attempt (Recommended if you've already removed default 'admin' user)</label></p>
		<p><input id="login_alerts_settings[excludereach_enable]" type="checkbox" name="login_alerts_settings[excludereach_enable]" value="1" <?php checked(1, isset( $login_alerts_options['excludereach_enable'] ) ); ?> />
		<label class="description" for="login_alerts_settings[excludereach_enable]">Excluding alerts of login form just opened</label></p>
		<p><input id="login_alerts_settings[excludefail_enable]" type="checkbox" name="login_alerts_settings[excludefail_enable]" value="1" <?php checked(1, isset( $login_alerts_options['excludefail_enable'] ) ); ?> />
		<label class="description" for="login_alerts_settings[excludefail_enable]">Notify successful logged-in event only (NOT recommended)</label></p>

		<h3>Email</h3>
		<p><input id="login_alerts_settings[email_enable]" type="checkbox" name="login_alerts_settings[email_enable]" value="1" <?php checked(1, isset( $login_alerts_options['email_enable'] ) ); ?> />
		<label class="description" for="login_alerts_settings[email_enable]">Enable Email Notification to the blog administrator "<?php echo get_option('admin_email'); ?>". If you fail to receive email, please try another sender's address of blog administrator.</label></p>
		<p><label class="description" for="login_alerts_settings[emailfrom]">Sender's address (Optional)</label>
		<input size="40" type="text" id="login_alerts_settings[emailfrom]" name="login_alerts_settings[emailfrom]" value="<?php echo $login_alerts_options['emailfrom']; ?>" /></p>

		<h3>im.kayac.com</h3>
		<p><input id="login_alerts_settings[imkayac_enable]" type="checkbox" name="login_alerts_settings[imkayac_enable]" value="1" <?php checked(1, isset( $login_alerts_options['imkayac_enable'] ) ); ?> />
		<label class="description" for="login_alerts_settings[imkayac_enable]">Enable im.kayac.com Notification</label></p>
		<p><label class="description" for="login_alerts_settings[username]">Username (required)</label>
		<input type="text" id="login_alerts_settings[username]" name="login_alerts_settings[username]" value="<?php echo $login_alerts_options['username']; ?>" /></p>
		<p><label class="description" for="login_alerts_settings[secretkey]">Secret key (recommended)</label>
		<input type="text" size="60" id="login_alerts_settings[secretkey]" name="login_alerts_settings[secretkey]" value="<?php echo $login_alerts_options['secretkey']; ?>" /></p>

		<h3>Prowl for iOS</h3>
		<p><input id="login_alerts_settings[prowl_enable]" type="checkbox" name="login_alerts_settings[prowl_enable]" value="1" <?php checked(1, isset( $login_alerts_options['prowl_enable'] ) ); ?> />
		<label class="description" for="login_alerts_settings[prowl_enable]">Enable Prowl Notification</label></p>
		<p><label class="description" for="login_alerts_settings[prowlapikey]">API Key (required)</label>
		<input type="text" size="60" id="login_alerts_settings[prowlapikey]" name="login_alerts_settings[prowlapikey]" value="<?php echo $login_alerts_options['prowlapikey']; ?>" /></p>

		<h3>Notify My Android</h3>
		<p><input id="login_alerts_settings[nma_enable]" type="checkbox" name="login_alerts_settings[nma_enable]" value="1" <?php checked(1, isset( $login_alerts_options['nma_enable'] ) ); ?> />
		<label class="description" for="login_alerts_settings[nma_enable]">Enable NMA Notification</label></p>
		<p><label class="description" for="login_alerts_settings[nmaapikey]">API Key (required)</label>
		<input type="text" size="60" id="login_alerts_settings[nmaapikey]" name="login_alerts_settings[nmaapikey]" value="<?php echo $login_alerts_options['nmaapikey']; ?>" /></p>

		<h3>Pushover(iOS, Android)</h3>
		<p><input id="login_alerts_settings[po_enable]" type="checkbox" name="login_alerts_settings[po_enable]" value="1" <?php checked(1, isset( $login_alerts_options['po_enable'] ) ); ?> />
		<label class="description" for="login_alerts_settings[po_enable]">Enable Pushover Notification</label></p>
		<p><label class="description" for="login_alerts_settings[poapptoken]">Application Token (required)</label>
		<input type="text" size="60" id="login_alerts_settings[poapptoken]" name="login_alerts_settings[poapptoken]" value="<?php echo $login_alerts_options['poapptoken']; ?>" /></p>
		<p><label class="description" for="login_alerts_settings[poapikey]">User Key (required)</label>
		<input type="text" size="60" id="login_alerts_settings[poapikey]" name="login_alerts_settings[poapikey]" value="<?php echo $login_alerts_options['poapikey']; ?>" /></p>

		<p class="submit">
			<input type="submit" class="button-primary" value="Save Settings" />
		</p>

	</form>
	
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}