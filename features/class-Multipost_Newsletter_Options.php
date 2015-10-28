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

if ( ! class_exists( 'Multipost_Newsletter_Options' ) ) {

	class Multipost_Newsletter_Options extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @access	private
		 * @static
		 * @var		NULL | Multipost_Newsletter_Options
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @return	Multipost_Newsletter_Options
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
			$this->options = get_option( 'mp-newsletter-params' );
			$this->post_types = get_option( 'mp-newsletter-post-types' );
			
			if ( ! is_array( $this->post_types ) )
				$this->post_types = array();
		}
		
		/**
		 * Setting up the option page
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	__, _e, screen_icon
		 * @return	void
		 */
		public function options_page() {
			
			// Getting the class object
			$self = self::get_instance();
			
			// Devine the tabs
			$tabs = array(
				'general'	=> __( 'General', parent::$textdomain ),
				'smtp'		=> __( 'SMTP', parent::$textdomain ),
			);
			
			// set the current tab to the first element, if no tab is in request
			if ( isset( $_REQUEST[ 'tab' ] ) && array_key_exists( $_REQUEST[ 'tab' ], $tabs ) ) {
				$current_tab = $_REQUEST[ 'tab' ];
				$current_tabname = $tabs[ $current_tab ];
			} else {
				$current_tab = current( array_keys( $tabs ) );
				$current_tabname = $tabs[ $current_tab ];
			}
			
			?>
			<div class="wrap">
				<?php screen_icon( parent::$textdomain ); ?>
				<h2 class="nav-tab-wrapper"><?php
					_e( 'Newsletter Options ', parent::$textdomain );

					foreach( $tabs as $tab_handle => $tabname ) {
						// set the url to the tab
						$url = admin_url( 'admin.php?page=mpnl_options&tab=' . $tab_handle );
						// check, if this is the current tab
						$active = ( $current_tab == $tab_handle ) ? ' nav-tab-active' : '';
						printf( '<a href="%s" class="nav-tab%s">%s</a>', $url, $active, $tabname );
					}
				?></h2>
				
				<div id="poststuff" class="metabox-holder has-right-sidebar">
				
					<div id="side-info-column" class="inner-sidebar">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<div id="mp-newsletter-inpsyde" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Powered by', parent::$textdomain ); ?></span></h3>
								<div class="inside">
									<p style="text-align: center;"><a href="http://inpsyde.com"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../images/inpsyde_logo.png'; ?>" style="border: 7px solid #fff;" /></a></p>
									<p><?php _e( 'This plugin is powered by <a href="http://inpsyde.com">Inpsyde.com</a> - Your expert for WordPress, BuddyPress and bbPress.', parent::$textdomain ); ?></p>
								</div>
							</div>
						</div>
					</div>
					
					<div id="post-body">
						<div id="post-body-content">
							<div id="normal-sortables" class="meta-box-sortables ui-sortable">
								<?php $self->show_tab( array( $self , $current_tab . '_tab' ), $current_tabname ); ?>
							</div>
						</div>
					</div>
				
				</div>
			</div>
			<?php
		}
		
		/**
		 * Shows the tab, and calls the function for the content of the tab
		 *
		 * @since	0.1
		 * @access	private
		 * @param	string $tab_function function to call for tab content
		 * @param	string $title title of the tab
		 * @return	void
		 */
		private function show_tab( $tab_function, $title ) {
			if ( is_callable( $tab_function ) )
				call_user_func( $tab_function );
		}
		
		/**
		 * The SMTP Settings Tab
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	get_option, _e
		 * @return	void
		 */
		public function smtp_tab() {
			
			if ( TRUE == parent::$is_pro )
				Multipost_Newsletter_Options_SMTP::smtp_tab();
			else {
				echo '<p>';
				_e( 'You have to purchase the pro-version of this plugin to send a newsletter over SMTP', parent::$textdomain );
				echo '</p>';
			}
		}
		
		/**
		 * The General Settings Tab
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	get_option, _e
		 * @return	void
		 */
		public function general_tab() {
			
			// Getting the class object
			$self = self::get_instance();
			
			$post_types = $self->get_post_types();
			
			if ( isset( $_POST[ 'save_settings' ] ) ) {
					
				// insert the options
				update_option( 'mp-newsletter-params', $_POST[ 'options' ] );
				update_option( 'mp-newsletter-post-types', $_POST[ 'post_types' ] );
					
				// Replace POST array
				$self->options = $_POST[ 'options' ];
				$self->post_types = $_POST[ 'post_types' ];
					
				?>
				<div class="updated">
					<p>
						<?php _e( 'Options have been saved.', parent::$textdomain ); ?>
					</p>
				</div>
				<?php
			}
			
			?>
			<form action="admin.php?page=mpnl_options" method="post">
				<div id="posttypes" class="postbox">
					<h3 class="hndle"><span><?php _e( 'Supported Post Types', parent::$textdomain ); ?></span></h3>
					<div class="inside">
						<p><?php _e( 'Here you are able to enable different post types for the newsletter. If you enable one you will have the possibility to add their content to an edition.', parent::$textdomain ); ?></p>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="post_types"><?php _e( 'Support Post Types', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<?php foreach ( $post_types as $post_type => $data ) { ?>
											<input id="post_types_<?php echo $post_type; ?>" name="post_types[]" type="checkbox" value="<?php echo $post_type; ?>" <?php if ( in_array( $post_type, $self->post_types ) ) echo 'checked="checked"'; ?> /> <label for="post_types_<?php echo $post_type; ?>"><?php echo $data->labels->name; ?></label><br />
										<?php } ?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			
				<div id="settings" class="postbox">
					<h3 class="hndle"><span><?php _e( 'Sender E-Mail Settings', parent::$textdomain ); ?></span></h3>
					<div class="inside">
						<p><?php _e( 'Setup your sender options here. The newsletter definitly needs a sender name and a sender email. If you send the newsletter to your recipients, they will be displayed in the "From"-section. You also have to set the recipient for the testmail to check the newsletter before it will be published.', parent::$textdomain ); ?></p>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="options[from_name]"><?php _e( 'Sender Name', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="options[from_name]" name="options[from_name]" type="text" value="<?php echo $self->options[ 'from_name' ]; ?>" tabindex="2" class="regular-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[from_mail]"><?php _e( 'Sender E-Mail', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="options[from_mail]" name="options[from_mail]" type="text" value="<?php echo $self->options[ 'from_mail' ]; ?>" tabindex="3" class="regular-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[to_test]"><?php _e( 'Recipient of Testmail', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="options[to_test]" name="options[to_test]" type="text" value="<?php echo $self->options[ 'to_test' ]; ?>" tabindex="4" class="regular-text" />
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
		
		/**
		 * Get all custom post types
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_post_types
		 * @return	void
		 */
		public function get_post_types() {
		
			// Get all registered post types
			$args = array( 'public' => TRUE );
			$output = 'objects';
			$post_types = get_post_types( $args, $output );
			$types = array();
		
			// We do not want to display these standard post types
			$exclude = array(
				'attachment'
			);
		
			foreach ( $post_types as $cpt => $params ) {
				if ( in_array( $cpt, $exclude ) )
					continue;
					
				$types[ $cpt ] = $params;
			}
			
			return $types;
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_Options::get_instance();
}