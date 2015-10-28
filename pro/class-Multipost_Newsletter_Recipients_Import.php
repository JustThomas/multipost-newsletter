<?php
/**
 * Feature Name:	Multipost Newsletter Recipients Page
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com
 * Licence:			GPLv3
 */

if ( ! class_exists( 'Multipost_Newsletter_Recipients_Import' ) ) {

	class Multipost_Newsletter_Recipients_Import extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @var		NULL | Multipost_Newsletter_Recipients_Import
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @return	Multipost_Newsletter_Recipients_Import
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
			
			// Adding Save Hooks
			add_filter( 'admin_post_mpnl_import_recipients', array( $this, 'import_recipients' ) );
		}
		
		/**
		 * The Import Tab
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, _e, __, update_option
		 * @return	void
		 */
		public function import_tab() {
			
			?>
			<div id="mp-newsletter-inpsyde" class="postbox">
				<h3 class="hndle"><span><?php _e( 'Import Recipients', parent::$textdomain ); ?></span></h3>
				<div class="inside">
					<form action="<?php echo admin_url( 'admin-post.php?action=mpnl_import_recipients' ); ?>" method="post">
						<?php wp_nonce_field( 'mpnl_import_recipients' ); ?>
						<table class="form-table">
							<tr>
								<th><label for="emails"><?php _e( 'E-Mails', parent::$textdomain ); ?></label></th>
								<td>
									<textarea id="emails" name="emails" class="large-text" rows="10"></textarea>
									<span class="description"><?php _e( 'Insert the e-mails of the recipients seperated by comma.', parent::$textdomain ); ?></span>
								</td>
							</tr>
							<tr>
								<th><?php _e( 'Newsletter Type', parent::$textdomain ); ?></th>
								<td>
									<label for="html"><input type="checkbox" id="html" name="type[]" value="html" /> <?php _e( 'HTML', parent::$textdomain ); ?></label><br />
									<label for="text"><input type="checkbox" id="text" name="type[]" value="text" /> <?php _e( 'Text', parent::$textdomain ); ?></label><br />
									<span class="description"><?php _e( 'If you don\'t chose a type of the newsletter <code>text</code> will be setted for each user.', parent::$textdomain ); ?></span>
								</td>
							</tr>
							<?php
							// Load Groups
							$groups = get_option( 'mp-newsletter-groups' );
							if ( TRUE == parent::$is_pro && is_array( $groups ) && 0 < count( $groups ) ) { ?>
							<tr>
								<th><label for="groups"><?php _e( 'Groups', parent::$textdomain ); ?></label></th>
								<td>
									<select data-placeholder="<?php _e( 'Chose some groups', parent::$textdomain ); ?>" id="groups" name="groups[]" style="width: 350px;" multiple class="chzn-select">
										<?php foreach ( $groups as $group ) { ?>
											<option value="<?php echo $group; ?>"><?php echo $group; ?></option>
										<?php } ?>
									</select><br />
									<span class="description"><?php _e( 'If you don\'t chose a group the recipient only will receive the global newsletters.', parent::$textdomain ); ?></span>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<th>&nbsp;</th>
								<td>
									<input type="submit" name="submit" id="submit" value="<?php _e( 'Import Recipients', parent::$textdomain ); ?>" class="button-primary" />
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
			<?php
		}
		
		/**
		 * Edits the recipients data
		 *
		 * @since	0.1
		 * @return	void
		 */
		public function import_recipients() {
				
			// Check Nonce
			check_admin_referer( 'mpnl_import_recipients' );
				
			// Is email valid?
			if ( '' == trim( $_POST[ 'emails' ] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mpnl_recipients&tab=import&message=nomails' ) );
				exit;
			}
			
			// Set standards
			if ( isset( $_POST[ 'type' ] ) )
				$type = implode( ',', $_POST[ 'type' ] );
			else
				$type = 'text';
				
			if ( TRUE == parent::$is_pro && isset( $_POST[ 'groups' ] ) )
				$groups = implode( ',', $_POST[ 'groups' ] );
			else
				$groups = '';
			
			// Check the mails and import each one
			$reccount = 0;
			$emails = explode( ',', $_POST[ 'emails' ] );
			foreach ( $emails as $mail ) {
				
				// Validate
				if ( ! filter_var( $mail, FILTER_VALIDATE_EMAIL ) )
					continue;
				
				// Check if mail exists
				$recipient = get_recipient_by_email( $mail );
				if ( ! empty( $recipient ) )
					continue;
				else
					$recipient = array();
				
				// Set defaults
				$recipient[ 'email' ] = $mail;
				$recipient[ 'registered' ] = date( 'Y-m-d H:i:s' );
				$recipient[ 'type' ] = $type;
				$recipient[ 'groups' ] = $groups;
				$recipient[ 'key' ] = md5( wp_generate_password( 32 ) );
				$recipient[ 'activated' ] = '1';
				$recipient[ 'unsubkey' ] = md5( wp_generate_password( 32 ) );
				
				// Insert recipient
				global $wpdb;
				$wpdb->insert( $wpdb->prefix . 'mpnl_recipients', $recipient );
				
				// Update count
				$reccount++;
			}
			
			wp_safe_redirect( admin_url( 'admin.php?page=mpnl_recipients&tab=import&message=imported&reccount=' . $reccount ) );
			exit;
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_Recipients_Import::get_instance();
}