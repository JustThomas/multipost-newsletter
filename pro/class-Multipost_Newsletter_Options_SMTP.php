<?php
/**
 * Feature Name:	Multipost Newsletter Options Page
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com
 * Licence:			GPLv3
 * 
 * Changelog
 *
 * 0.1
 * - Initial Commit
 */

if ( ! class_exists( 'Multipost_Newsletter_Options_SMTP' ) ) {

	class Multipost_Newsletter_Options_SMTP extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @access	private
		 * @static
		 * @var		NULL | Multipost_Newsletter_Options_SMTP
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @return	Multipost_Newsletter_Options_SMTP
		 */
		public static function get_instance() {
			
			if ( ! self::$instance )
				self::$instance = new self;
			return self::$instance;
		}
		
		/**
		 * Setting up some data, initialize translations and start the hooks
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option
		 * @return	void
		 */
		public function __construct() {
			
			// Load the Options
			$this->smtp = get_option( 'mp-newsletter-smtp' );
		}
		
		/**
		 * The SMTP Settings Tab
		 *
		 * @access	public
		 * @since	0.1
		 * @static
		 * @uses	get_option, _e
		 * @return	void
		 */
		public static function smtp_tab() {
				
			// Getting the class object
			$self = self::get_instance();
			
			if ( isset( $_POST[ 'save_settings' ] ) ) {
					
				// insert the options
				update_option( 'mp-newsletter-smtp', $_POST[ 'smtp' ] );
					
				// Replace POST array
				$self->smtp = $_POST[ 'smtp' ];
					
				?>
				<div class="updated">
					<p>
						<?php _e( 'Options have been saved.', parent::$textdomain ); ?>
					</p>
				</div>
				<?php
			}
			?>
			<form action="admin.php?page=mpnl_options&tab=smtp" method="post">
				<div id="settings" class="postbox">
					<h3 class="hndle"><span><?php _e( 'General Settings', parent::$textdomain ); ?></span></h3>
					<div class="inside">
						<p><?php _e( 'The Multipost Newsletter offers a way to send the newsletter over SMTP. Just insert your credentials. Leave the values blank to delete the credentials. If you don\'t want to send the newsletter over SMTP just leave the following fields empty.', parent::$textdomain ); ?></p>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="smtp[host]"><?php _e( 'SMTP Host', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="smtp[host]" name="smtp[host]" type="text" value="<?php echo $self->smtp[ 'host' ]; ?>" tabindex="1" class="regular-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="smtp[port]"><?php _e( 'SMTP Port', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="smtp[port]" name="smtp[port]" type="text" value="<?php echo $self->smtp[ 'port' ]; ?>" tabindex="1" class="regular-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="smtp[user]"><?php _e( 'Username', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="smtp[user]" name="smtp[user]" type="text" value="<?php echo $self->smtp[ 'user' ]; ?>" tabindex="4" class="regular-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="smtp[pass]"><?php _e( 'Password', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="smtp[pass]" name="smtp[pass]" type="password" value="<?php echo $self->smtp[ 'pass' ]; ?>" tabindex="5" class="regular-text" />
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<input name="save_settings" type="submit" class="button-primary" tabindex="6" value="<?php _e( 'Save Changes', parent::$textdomain ); ?>" style="float: right;" />
				<br class="clear" />
			</form>
			<?php
		}
	}
}