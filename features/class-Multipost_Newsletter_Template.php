<?php
/**
 * Feature Name:	Multipost Newsletter Template Page
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com
 * Licence:			GPLv3
 * 
 * TODO: Change save methods to admin posts (restore, save, spacers, etc.)
 */

if ( ! class_exists( 'Multipost_Newsletter_Template' ) ) {

	class Multipost_Newsletter_Template extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @access	private
		 * @static
		 * @since	0.1
		 * @var		NULL | Multipost_Newsletter_Template
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @access	public
		 * @static
		 * @since	0.1
		 * @return	Multipost_Newsletter_Template
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
		 * @uses	get_option, get_magic_quotes_gpc
		 * @return	void
		 */
		public function __construct() {
			
			// Fetching current text template
			$this->text_main = get_option( 'mp-newsletter-text-main' );
			$this->text_post = get_option( 'mp-newsletter-text-post' );
			$this->text_params = get_option( 'mp-newsletter-text-params' );
			
			// Fetching general params
			$this->params = get_option( 'mp-newsletter-template-params' );
			
			// strip slashes so HTML won't be escaped
			$this->text_main = stripslashes_deep( $this->text_main );
			$this->text_post = stripslashes_deep( $this->text_post );
			$this->text_params = array_map( 'stripslashes_deep', $this->text_params );
			
			if ( is_array( $this->params ) )
				$this->params = array_map( 'stripslashes_deep', $this->params );
		}
		
		/**
		 * Setting up the option page
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	__, _e, get_option, sanitize_title_with_dashes, update_option
		 * @return	void
		 */
		public function template_page() {
			
			// Getting the class object
			$self = self::get_instance();
			
			// Devine the tabs
			$tabs = array(
				'general'	=> __( 'General', parent::$textdomain ),
				'html'		=> __( 'HTML', parent::$textdomain ),
				'text'		=> __( 'Text', parent::$textdomain ),
				'pdf'		=> __( 'PDF', parent::$textdomain ),
				'spacer'	=> __( 'Spacer', parent::$textdomain ),
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
					_e( 'Template ', parent::$textdomain );

					foreach( $tabs as $tab_handle => $tabname ) {
						// set the url to the tab
						$url = admin_url( 'admin.php?page=mpnl_template&tab=' . $tab_handle );
						// check, if this is the current tab
						$active = ( $current_tab == $tab_handle ) ? ' nav-tab-active' : '';
						printf( '<a href="%s" class="nav-tab%s">%s</a>', $url, $active, $tabname );
					}
				?></h2>
				
				<?php
				// Restore Point Stuff
				if ( isset( $_POST[ 'create_restore_point' ] ) ) {
					
					$settings = array();
					
					// Fetching current html template
					$settings[ 'html_main' ] = get_option( 'mp-newsletter-html-main' );
					$settings[ 'html_post' ] = get_option( 'mp-newsletter-html-post' );
					$settings[ 'html_params' ] = get_option( 'mp-newsletter-html-params' );
					
					// Fetching current text template
					$settings[ 'text_main' ] = get_option( 'mp-newsletter-text-main' );
					$settings[ 'text_post' ] = get_option( 'mp-newsletter-text-post' );
					$settings[ 'text_params' ] = get_option( 'mp-newsletter-text-params' );
					
					// Fetching PDF
					$settings[ 'pdf' ] = get_option( 'mp-newsletter-pdf' );
					
					// Fetching Spacer
					$settings[ 'spacers' ] = get_option( 'mp-newsletter-spacers' );
						
					// Fetching general params
					$settings[ 'params' ] = get_option( 'mp-newsletter-template-params' );
					
					// Generate Name
					if ( '' == $_POST[ 'point_name' ] )
						$point_name = __( 'Restore Point at ', parent::$textdomain ) . date( 'Y-m-d H:i:s' );
					else
						$point_name = $_POST[ 'point_name' ];
					$sanitized_point = sanitize_title_with_dashes( $point_name );
					
					// Prepare
					$restore_point = array(
						'name'		=> $point_name,
						'settings'	=> $settings
					);
					
					// Insert point into the existing one
					$restore_points = get_option( 'mp-newsletter-restore-points' );
					if ( ! is_array( $restore_points ) )
						$restore_points = array();
					
					$restore_points[ $sanitized_point ] = $restore_point;
					
					update_option( 'mp-newsletter-restore-points', $restore_points );
					?>
					<div class="updated"><p>
						<?php _e( 'Restore Point has been saved', parent::$textdomain ); ?>
					</p></div>
					<?php
				}
				
				if ( isset( $_POST[ 'restore_template_settings' ] ) && '' != $_POST[ 'restore_point' ] ) {
					
					// Get Points
					$restore_points = get_option( 'mp-newsletter-restore-points' );
					
					// Current Point
					$point_to_restore = $restore_points[ $_POST[ 'restore_point' ] ];
					
					// Update the options
					update_option( 'mp-newsletter-html-main', $point_to_restore[ 'settings' ][ 'html_main' ] );
					update_option( 'mp-newsletter-html-post', $point_to_restore[ 'settings' ][ 'html_post' ] );
					update_option( 'mp-newsletter-html-params', $point_to_restore[ 'settings' ][ 'html_params' ] );
					update_option( 'mp-newsletter-text-main', $point_to_restore[ 'settings' ][ 'text_main' ] );
					update_option( 'mp-newsletter-text-post', $point_to_restore[ 'settings' ][ 'text_post' ] );
					update_option( 'mp-newsletter-text-params', $point_to_restore[ 'settings' ][ 'text_params' ] );
					update_option( 'mp-newsletter-template-params', $point_to_restore[ 'settings' ][ 'params' ] );
					update_option( 'mp-newsletter-pdf', $point_to_restore[ 'settings' ][ 'pdf' ] );
					update_option( 'mp-newsletter-spacers', $point_to_restore[ 'settings' ][ 'spacers' ] );
					
					// Refresh page
					?>
					<div class="updated"><p>
						<?php _e( 'Restore Settings. Please wait ...', parent::$textdomain ); ?>
					</p></div>
					<meta http-equiv="refresh" content="2" />
					<?php
					return;
				}
				?>
				
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
							
							<div id="mp-newsletter-create-restore-point" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Create Restore Point', parent::$textdomain ); ?></span></h3>
								<div class="inside">
									<p><?php _e( 'This newsletter plugin is able to save the template settings into a restore point. With that you can restore the settings.', parent::$textdomain ); ?></p>
									<form action="" method="post">
										<p><label for="point_name"><strong><?php _e( 'Name of Restore Point', parent::$textdomain ); ?></strong></label></p>
										<p><input type="text" name="point_name" id="point_name" class="large-text" /><br /><span class="description"><?php _e( 'Leave empty and the name will be generated automatically', parent::$textdomain ); ?></span></p>
										<p><input type="submit" name="create_restore_point" value="<?php _e( 'Create Restore Point', parent::$textdomain ); ?>" class="button-secondary" /></p>
									</form>
								</div>
							</div>
							
							<div id="mp-newsletter-restore-points" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Restore Template Settings', parent::$textdomain ); ?></span></h3>
								<div class="inside">
									<?php
									$restore_points = get_option( 'mp-newsletter-restore-points' );
									if ( is_array( $restore_points ) && ! empty( $restore_points ) ) :
									?>
										<form action="" method="post">
											<p>
												<select name="restore_point">
													<option value="0"><?php _e( 'Chose Restore Point', parent::$textdomain ); ?></option>
													<?php foreach ( $restore_points as $name => $point ) { ?>
														<option value="<?php echo $name; ?>"><?php echo $point[ 'name' ]; ?></option>
													<?php } ?>
												</select>
											</p>
											<p><input type="submit" name="restore_template_settings" value="<?php _e( 'Restore Template Settings', parent::$textdomain ); ?>" class="button-secondary" /></p>
										</form>
									<?php endif; ?>
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
		 * The Spacer Template Tab
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, _e, __, sanitize_title_with_dashes, update_option
		 * @return	void
		 */
		public function spacer_tab() {
				
			$self = self::get_instance();
			
			if ( isset( $_GET[ 'delete_spacer' ] ) ) {
				
				$current_spacers = get_option( 'mp-newsletter-spacers' );
				
				unset( $current_spacers[ $_GET[ 'delete_spacer' ] ] );
				
				
				update_option( 'mp-newsletter-spacers', $current_spacers );
				?>
				<div class="updated"><p>
					<?php _e( 'Spacer has been deleted', parent::$textdomain ); ?>
				</p></div>
				<?php
			}
			
			if ( isset( $_POST[ 'save_new_spacer' ] ) ) {
				
				$current_spacers = get_option( 'mp-newsletter-spacers' );
				if ( ! is_array( $current_spacers ) )
					$current_spacers = array();
				
				$sanitized_title = sanitize_title_with_dashes( $_POST[ 'spacer' ][ 'title' ] );
				
				$current_spacers[ $sanitized_title ] = $_POST[ 'spacer' ];
				
				update_option( 'mp-newsletter-spacers', $current_spacers );
				?>
				<div class="updated"><p>
					<?php _e( 'Spacer has been saved', parent::$textdomain ); ?>
				</p></div>
				<?php
			}
			
			if ( isset( $_POST[ 'save_spacer' ] ) ) {
				
				$_POST = array_map( 'stripslashes_deep', $_POST );
			
				$current_spacers = get_option( 'mp-newsletter-spacers' );
				
				unset( $current_spacers[ $_POST[ 'sanitized_title' ] ] );
				
				$sanitized_title = sanitize_title_with_dashes( $_POST[ 'spacer' ][ 'title' ] );
				$current_spacers[ $sanitized_title ] = $_POST[ 'spacer' ];
				update_option( 'mp-newsletter-spacers', $current_spacers );
				?>
				<div class="updated"><p>
					<?php _e( 'Spacer has been saved', parent::$textdomain ); ?>
				</p></div>
				<?php
			}
			
			$spacers = get_option( 'mp-newsletter-spacers' );
			if ( is_array( $spacers ) )
				$spacers = array_map( 'stripslashes_deep', $spacers );
			?>
			<div id="settings" class="postbox">
				<div class="handlediv" title="<?php _e( 'Click to toggle', parent::$textdomain ); ?>"><br /></div>
				<h3 class="hndle"><span><?php _e( 'Add Spacer Template', parent::$textdomain ); ?></span></h3>
				<div class="inside" style="display: none">
					<form action="admin.php?page=mpnl_template&tab=spacer" method="post">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="spacer[title]"><?php _e( 'Spacer Title', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="spacer[title]" name="spacer[title]" type="text" tabindex="1" class="large-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="spacer[html]"><?php _e( 'HTML Content', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<textarea id="spacer[html]" name="spacer[html]" tabindex="2" rows="10" class="large-text"></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="spacer[text]"><?php _e( 'Text Content', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<textarea id="spacer[text]" name="spacer[text]" tabindex="2" rows="10" class="large-text"></textarea>
									</td>
								</tr>
							</tbody>
						</table>
						<input name="save_new_spacer" type="submit" class="button-primary" tabindex="3" value="<?php _e( 'Add New Spacer', parent::$textdomain ); ?>" style="float: right;" />
						<br class="clear" />
					</form>
				</div>
			</div>
			<h4><?php _e( 'Currently available spacers', parent::$textdomain ); ?></h4>
			
			<?php
			if ( is_array( $spacers ) && ! empty( $spacers ) ) {
				foreach ( $spacers as $title => $spacer ) {
					?>
					<div id="settings" class="postbox">
						<div class="handlediv" title="<?php _e( 'Click to toggle', parent::$textdomain ); ?>"><br /></div>
						<h3 class="hndle"><span><?php echo $spacer[ 'title' ]; ?></span></h3>
						<div class="inside" style="display: none">
							<form action="admin.php?page=mpnl_template&tab=spacer" method="post">
								<table class="form-table">
									<tbody>
										<tr valign="top">
											<th scope="row">
												<label for="spacer[title]"><?php echo $spacer[ 'title' ]; ?>:</label>
											</th>
											<td>
												<input id="spacer[title]" name="spacer[title]" type="text" tabindex="1" class="large-text" value="<?php echo $spacer[ 'title' ]; ?>" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row">
												<label for="spacer[html]"><?php _e( 'HTML Content', parent::$textdomain ); ?>:</label>
											</th>
											<td>
												<textarea id="spacer[html]" name="spacer[html]" tabindex="2" rows="10" class="large-text"><?php echo $spacer[ 'html' ]; ?></textarea>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row">
												<label for="spacer[text]"><?php _e( 'Text Content', parent::$textdomain ); ?>:</label>
											</th>
											<td>
												<textarea id="spacer[text]" name="spacer[text]" tabindex="2" rows="10" class="large-text"><?php echo $spacer[ 'text' ]; ?></textarea>
											</td>
										</tr>
									</tbody>
								</table>
								<input type="hidden" name="sanitized_title" value="<?php echo $title; ?>" />
								<span class="submitbox"><a href="admin.php?page=mpnl_template&tab=spacer&delete_spacer=<?php echo $title; ?>" class="submitdelete"><?php _e( 'Delete Spacer', parent::$textdomain ) ?></a></span>
								<input name="save_spacer" type="submit" class="button-primary" tabindex="3" value="<?php _e( 'Edit Spacer', parent::$textdomain ); ?>" style="float: right;" />
								<br class="clear" />
							</form>
						</div>
					</div>
					<?php
				}
			}
		}
		
		/**
		 * The General Template Tab
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, _e, __, update_option
		 * @return	void
		 */
		public function general_tab() {
			
			$self = self::get_instance();
			
			if ( isset( $_POST[ 'save_general_template' ] ) ) {
				
				// strip slashes so HTML won't be escaped
				if ( get_magic_quotes_gpc() ) {
					$_POST      = array_map( 'stripslashes_deep', $_POST );
					$_GET       = array_map( 'stripslashes_deep', $_GET );
					$_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
				}
				
				// insert the options
				update_option( 'mp-newsletter-template-params', $_POST[ 'general' ] );
					
				// Replace POST array
				$self->params = $_POST[ 'general' ];
					
				?>
				<div class="updated">
					<p>
						<?php _e( 'Template has been saved.', parent::$textdomain ); ?>
					</p>
				</div>
				<?php
			}
			
			?>
			<form action="admin.php?page=mpnl_template&tab=general" method="post">
				<div id="settings" class="postbox">
					<h3 class="hndle"><span><?php _e( 'General Template', parent::$textdomain ); ?></span></h3>
					<div class="inside">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="general[header]"><?php _e( 'Intro-Text', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<textarea id="general[header]" name="general[header]" tabindex="12" rows="20" class="large-text"><?php echo $self->params[ 'header' ]; ?></textarea><br />
										<span class="description">
											Tags:<br />
											%PDF_LINK% // <?php _e( 'Display the PDF Link', parent::$textdomain ); ?><br />
											%UNSUBSCRIBELINK% // <?php _e( 'Display the link so that a recipient is able to unsubscribe from the newsletter. It just provides the http-address like <code>http://yourblog.com/unsubscribe...</code>.', parent::$textdomain ); ?>
										</span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="general[footer]"><?php _e( 'Footer-Text', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<textarea id="general[footer]" name="general[footer]" tabindex="12" rows="20" class="large-text"><?php echo $self->params[ 'footer' ]; ?></textarea><br />
										<span class="description">
											Tags:<br />
											%PDF_LINK% // <?php _e( 'Display the PDF Link', parent::$textdomain ); ?><br />
											%UNSUBSCRIBELINK% // <?php _e( 'Display the link so that a recipient is able to unsubscribe from the newsletter. It just provides the http-address like <code>http://yourblog.com/unsubscribe...</code>.', parent::$textdomain ); ?>
										</span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="general[contents]"><?php _e( 'Display contents', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="general[contents]" name="general[contents]" type="checkbox" tabindex="3" <?php if ( isset( $self->params[ 'contents' ] ) && 'on' == $self->params[ 'contents' ] ) { echo 'checked="checked"'; } ?> />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="general[excerpt]"><?php _e( 'Content as Excerpt', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="general[excerpt]" name="general[excerpt]" type="checkbox" tabindex="3" <?php if ( isset( $self->params[ 'excerpt' ] ) && 'on' == $self->params[ 'excerpt' ] ) { echo 'checked="checked"'; } ?> />
									</td>
								</tr>
                     			<tr valign="top">
									<th scope="row">
										<label for="general[post_thumbnails]"><?php _e( 'Use Post Thumbnails', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="general[post_thumbnails]" name="general[post_thumbnails]" type="checkbox" tabindex="3" <?php if ( isset( $self->params[ 'post_thumbnails' ] ) && 'on' == $self->params[ 'post_thumbnails' ] ) { echo 'checked="checked"'; } ?> />
									</td>
                     			</tr>
							</tbody>
						</table>
					</div>
				</div>
				<input name="save_general_template" type="submit" class="button-primary" tabindex="3" value="<?php _e( 'Save Changes', parent::$textdomain ); ?>" style="float: right;" />
				<br class="clear" />
			</form>
			<?php
		}
		
		/**
		 * The HTML Template Tab
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, _e, __, update_option
		 * @return	void
		 */
		public function html_tab() {
			
			if ( TRUE == parent::$is_pro )
				Multipost_Newsletter_Template_HTML::html_tab();
			else {
				echo '<p>';
				_e( 'You have to purchase the pro-version of this plugin to generate an HTML-Newsletter', parent::$textdomain );
				echo '</p>';
			}
		}
		
		/**
		 * The PDF Template Tab
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, _e, __, update_option
		 * @return	void
		 */
		public function pdf_tab() {
			
			if ( TRUE == parent::$is_pro )
				Multipost_Newsletter_Template_PDF::pdf_tab();
			else {
				echo '<p>';
				_e( 'You have to purchase the pro-version of this plugin to generate a PDF-Newsletter', parent::$textdomain );
				echo '</p>';
			}
		}
		
		/**
		 * The Text Template Tab
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, _e, __, update_option
		 * @return	void
		 */
		public function text_tab() {
			
			$self = self::get_instance();
			
			if ( isset( $_POST[ 'save_text_template' ] ) ) {
					
				// strip slashes so HTML won't be escaped
				if ( get_magic_quotes_gpc() ) {
					$_POST      = array_map( 'stripslashes_deep', $_POST );
					$_GET       = array_map( 'stripslashes_deep', $_GET );
					$_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
				}
				
				// insert the options
				update_option( 'mp-newsletter-text-main', $_POST[ 'text_main' ] );
				update_option( 'mp-newsletter-text-post', $_POST[ 'text_post' ] );
				update_option( 'mp-newsletter-text-params', $_POST[ 'text_params' ] );
				
				// Replace POST array
				$self->text_params = $_POST[ 'text_params' ];
				$self->text_main = $_POST[ 'text_main' ];
				$self->text_post = $_POST[ 'text_post' ];
				
				?>
				<div class="updated">
					<p>
						<?php _e( 'Template has been saved.', parent::$textdomain ); ?>
					</p>
				</div>
				<?php
			}
				
			?>
			<form action="admin.php?page=mpnl_template&tab=text" method="post">
				<div id="settings" class="postbox">
					<h3 class="hndle"><span><?php _e( 'Text Template', parent::$textdomain ); ?></span></h3>
					<div class="inside">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="text_main"><?php _e( 'Newsletter Template', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<textarea id="text_main" name="text_main" tabindex="5" rows="20" class="large-text"><?php echo $self->text_main; ?></textarea><br />
										<span class="description">
											Tags:<br />
											%NAME% // <?php _e( 'Name of the newsletter', parent::$textdomain ); ?><br />
											%HEADER% // <?php _e( 'Displays the Intro-Text', parent::$textdomain ); ?><br />
											%DATE% // <?php _e( 'Date of the Newsletter', parent::$textdomain ); ?><br />
											%CONTENTS% // <?php _e( 'Displays the contents if needed', parent::$textdomain ); ?><br />
											%FOOTER% // <?php _e( 'Displays the footer', parent::$textdomain ); ?><br />
											%BODY% // <?php _e( 'Displays the Posts', parent::$textdomain ); ?><br />
											%PDF_LINK% // <?php _e( 'Display the PDF URL', parent::$textdomain ); ?><br />
											%UNSUBSCRIBELINK% // <?php _e( 'Display the link so that a recipient is able to unsubscribe from the newsletter. It just provides the http-address like <code>http://yourblog.com/unsubscribe...</code>.', parent::$textdomain ); ?>
										</span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="text_post"><?php _e( 'Single Post Template', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<textarea id="text_post" name="text_post" tabindex="6" rows="20" class="large-text"><?php echo $self->text_post; ?></textarea><br />
										<span class="description">
											Tags:<br />
											%TITLE% // <?php _e( 'Post Title', parent::$textdomain ); ?><br />
											%CONTENT% // <?php _e( 'Post Content', parent::$textdomain ); ?><br />
											%THUMBNAIL% // <?php _e( 'Post Thumbnail', parent::$textdomain ); ?><br />
											%DATE% // <?php _e( 'Post Date', parent::$textdomain ); ?><br />
											%AUTHOR% // <?php _e( 'Post Author', parent::$textdomain ); ?><br />
											%LINK% // <?php _e( 'The permalink of the post', parent::$textdomain ); ?><br />
											%CUSTOM_FIELD[key="fieldname" label="<?php _e( 'Your label here', parent::$textdomain ); ?>"]%  // <?php _e( 'Display a custom field', parent::$textdomain ); ?>
										</span>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<input name="save_text_template" type="submit" class="button-primary" tabindex="7" value="<?php _e( 'Save Changes', parent::$textdomain ); ?>" style="float: right;" />
				<br class="clear" />
			</form>
			<?php
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_Template::get_instance();
}