<?php
/**
 * Feature Name:	Multipost Newsletter Template Page
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

if ( ! class_exists( 'Multipost_Newsletter_Template_HTML' ) ) {

	class Multipost_Newsletter_Template_HTML extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @access	private
		 * @static
		 * @since	0.1
		 * @var		NULL | Multipost_Newsletter_Template_HTML
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @access	public
		 * @static
		 * @since	0.1
		 * @return	Multipost_Newsletter_Template_HTML
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
			
			// Fetching current html template
			$this->html_main = get_option( 'mp-newsletter-html-main' );
			$this->html_post = get_option( 'mp-newsletter-html-post' );
			$this->html_params = get_option( 'mp-newsletter-html-params' );
			
			// strip slashes so HTML won't be escaped
			$this->html_main = stripslashes_deep( $this->html_main );
			$this->html_post = stripslashes_deep( $this->html_post );
			$this->html_params = array_map( 'stripslashes_deep', $this->html_params );
		}
		
		/**
		 * The HTML Template Tab
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @uses	get_option, _e, __, update_option
		 * @return	void
		 */
		public static function html_tab() {
			
			$self = self::get_instance();
			
			if ( isset( $_POST[ 'save_html_template' ] ) ) {
					
				// strip slashes so HTML won't be escaped
				$_POST      = array_map( 'stripslashes_deep', $_POST );
				$_GET       = array_map( 'stripslashes_deep', $_GET );
				$_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
				
				// insert the options
				update_option( 'mp-newsletter-html-main', $_POST[ 'html_main' ] );
				update_option( 'mp-newsletter-html-post', $_POST[ 'html_post' ] );
				update_option( 'mp-newsletter-html-params', $_POST[ 'html_params' ] );
				
				// Replace POST array
				$self->html_params = $_POST[ 'html_params' ];
				$self->html_main = $_POST[ 'html_main' ];
				$self->html_post = $_POST[ 'html_post' ];
				
				?>
				<div class="updated">
					<p>
						<?php _e( 'Template has been saved.', parent::$textdomain ); ?>
					</p>
				</div>
				<?php
			}
				
			?>
			<form action="admin.php?page=mpnl_template&tab=html" method="post">
				<div id="settings" class="postbox">
					<h3 class="hndle"><span><?php _e( 'HTML Template', parent::$textdomain ); ?></span></h3>
					<div class="inside">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="html_params[color_even]"><?php _e( 'Background-Color (even)', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="html_params[color_even]" name="html_params[color_even]" type="text" value="<?php echo $self->html_params[ 'color_even' ]; ?>" tabindex="1" class="regular-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="html_params[color_odd]"><?php _e( 'Background-Color (odd)', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="html_params[color_odd]" name="html_params[color_odd]" type="text" value="<?php echo $self->html_params[ 'color_odd' ]; ?>" tabindex="2" class="regular-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="html_params[html_head]"><?php _e( 'HTML Header Template', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<textarea id="html_params[html_head]" name="html_params[html_head]" tabindex="7" rows="10" class="large-text"><?php echo $self->html_params[ 'html_head' ]; ?></textarea><br />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="html_params[html_footer]"><?php _e( 'HTML Footer Template', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<textarea id="html_params[html_footer]" name="html_params[html_footer]" tabindex="7" rows="10" class="large-text"><?php echo $self->html_params[ 'html_footer' ]; ?></textarea><br />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="html_main"><?php _e( 'Newsletter Template', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<textarea id="html_main" name="html_main" tabindex="7" rows="20" class="large-text"><?php echo $self->html_main; ?></textarea><br />
										<span class="description">
											Tags:<br />
											%NAME% // <?php _e( 'Name of the newsletter', parent::$textdomain ); ?><br />
											%HEADER% // <?php _e( 'Displays the Intro-Text', parent::$textdomain ); ?><br />
											%DATE% // <?php _e( 'Date of the Newsletter', parent::$textdomain ); ?><br />
											%CONTENTS% // <?php _e( 'Displays the contents if needed', parent::$textdomain ); ?><br />
											%FOOTER% // <?php _e( 'Displays the footer', parent::$textdomain ); ?><br />
											%BODY% // <?php _e( 'Displays the Posts', parent::$textdomain ); ?><br />
											%PDF_LINK% // <?php _e( 'Display the PDF Link', parent::$textdomain ); ?><br />
											%UNSUBSCRIBELINK% // <?php _e( 'Display the link so that a recipient is able to unsubscribe from the newsletter. It just provides the http-address like <code>http://yourblog.com/unsubscribe...</code>.', parent::$textdomain ); ?>
										</span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="html_post"><?php _e( 'Single Post Template', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<textarea id="html_post" name="html_post" tabindex="8" rows="20" class="large-text"><?php echo $self->html_post; ?></textarea><br />
										<span class="description">
											Tags:<br />
											%TITLE% // <?php _e( 'Post Title', parent::$textdomain ); ?><br />
											%CONTENT% // <?php _e( 'Post Content', parent::$textdomain ); ?><br />
											%THUMBNAIL% // <?php _e( 'Post Thumbnail', parent::$textdomain ); ?><br />
											%DATE% // <?php _e( 'Post Date', parent::$textdomain ); ?><br />
											%AUTHOR% // <?php _e( 'Post Author', parent::$textdomain ); ?><br />
											%COLOR% // <?php _e( 'Displays the color setted up in the \"Background-Color\"-Section. Use it like this: &lt;div style=\"background: #%COLOR%;\"&gt; ...', parent::$textdomain ); ?><br />
											%LINK% // <?php _e( 'The permalink of the post', parent::$textdomain ); ?>
											%LINK_NAME% // <?php _e( 'Important for the Contents. It posts a &lt;a name=""&gt;&lt;/a&gt;', parent::$textdomain ); ?><br />
											%CUSTOM_FIELD[key="fieldname" label="<?php _e( 'Your label here', parent::$textdomain ); ?>"]%  // <?php _e( 'Display a custom field', parent::$textdomain ); ?>
										</span>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<input name="save_html_template" type="submit" class="button-primary" tabindex="9" value="<?php _e( 'Save Changes', parent::$textdomain ); ?>" style="float: right;" />
				<br class="clear" />
			</form>
			<?php
		}
	}
}