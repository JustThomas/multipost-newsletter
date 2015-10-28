<?php
/**
 * Plugin Name:	Multipost Newsletter
 * Plugin URI:	http://marketpress.com/product/multipost-newsletter/
 * Description:	The Multi Post Newsletter is a simple plugin, which provides to link several posts to a newsletter. This procedure is similar to the categories. Within the flexible configuration and templating, you're able to set the newsletters appearance to your requirement.
 * Version:		1.1
 * Author:		Inpsyde GmbH
 * Author URI:	http://inpsyde.com
 * Licence:		GPLv3
 * Text Domain:	multipost-newsletter
 * Domain Path:	/language
 *
 * The Multi Post Newsletter is a simple plugin, which provides
 * to link several posts to a newsletter. This procedure is
 * similar to the categories. Within the flexible configuration
 * and templating, you're able to set the newsletters appearance
 * to your requirement.
 *
 * Changelog
 * 
 * 1.1
 * - Feature: Changed the user subscriptions including widget etc.
 * - Feature: Added Double Opt-In Form to the widget
 * - Feature: Added activation and unsubscription theme templates for double opt-in
 * - Feature: Choose more than one user group on the submission
 * - Feature: Added Import dialog to import subscribers
 * - Feature: Introduced %UNSUBSCRIBELINK% for the templates
 * - Feature: Added option not to generate the PDF
 * - Feature: Introduced Subject Areas
 * - Feature: New Standard Template
 * - Feature: Added Documentation
 * - Code & UI: Improved sending process
 * - Code & UI: Improved prepare process
 * - Code: Minor changes due to some performance issues
 * - Code: Changed Menu Position
 * - Code: Several Codex Optimizations
 * - Code: Changed request methods to core standards
 * - Code: Added local logo
 * - Code: Fixed some minor warnings and notices
 * 
 * 1.0.6
 * - Code: Updated Auto Updater
 * - Version: Version Hopping due to some Auto Update issues
 * 
 * 1.0.4
 * - Code: Fixed fatal error on widget
 * 
 * 1.0.3
 * - Code: Fixed several Warnings and Notices
 * - Code: Fixed SMTP Connection
 * - Code: Fixed article remove from an edition
 * - Code: Added target="_blank" at PDF-Link in Preview
 * 
 * 1.0.2
 * - Code: Fixed Warning in Prepare Dialog
 * - Code: Fixed Warning in PDF Preview
 * - Code: Fixed Warning while check for the pro folder
 * - Code: Fixed Warning in Auto Update
 * - Code: Fixed Warning in Template
 * - Code: Fixed unnecessary type in user and widget
 * 
 * 1.0.1
 * - Code: Fixed several Notices
 * - Code: Fixed Charset problems
 * - Code: Fixed phpmailer recipient problems
 * - Code: Fixed bug in checkboxes on post preview
 * 
 * 1.0
 * - License: Changed to GPLv3
 * - Version: Hopped Version due to too many changes
 * - Code: Complete new Codebase
 * - Feature: Automattic PDF Export
 * - Feature: Templating for Text-Mail
 * - Feature: Support Article Pictures
 * - Feature: Improved UI
 * - Feature: One Default Template
 * - Feature: Add Custom Fields to the template
 * - Feature: Fixed translations
 * - Feature: Mass-Mailing-Feature
 * - Feature: Support for custom post types
 * - Feature: Widget for Subscription
 * - Feature: Send mail to specific reciptions (groups)
 * 
 * 0.5.5.5
 * - Feature: URL-Shortener is.gd for text-mail
 * - Code: Several Fixes for the text-mail
 * - Code: Language Check-Ups
 * 
 * 0.5.5.4
 * - Feature: Frontend-Templating
 * 
 * 0.5.5.3
 * - Code: Fixed broken full page view
 * - Code: Fixed a "\" bug in the template options
 * 
 * 0.5.5.2
 * - Code: Styling
 * - Code: Fixed a ' bug in the template options
 * 
 * 0.5.5.1
 * - Code: Fixed annoying Bug in "sending the main newsletter"
 * 
 * 0.5.5
 * - Code: Fixed Doubled Mail Problem
 * - Code: Fixed Encoding Issues
 * 
 * 0.5.4
 * - Code: Fixed limit of posts
 * - Code: Fixed Encoding Issues
 * 
 * 0.5.3
 * - Code: Several fixes through the display of the newsletter
 * - Code: Added a new tag %LINK_NAME%
 * 
 * 0.5.2
 * - Code: Merged txt and html loop
 * - Code: Fixed Boundary and Headers
 * - Code: Some usability fixes
 * - Code: Fix in title/link conflict
 * 
 * 0.5.1
 * - Code: Fix in Contents ( Text-Version )
 * - Code: %LINK% now just gives the permalink
 * - Code: Language Fixes
 * 
 * 0.5
 * - Version: Hopping because of many changes
 * - Code: New improved Code
 * - Code: Several Checks
 * - Misc: Custom Collumn "Newsletter" in Article Overview
 * - Feature: text/plain mail
 * - Feature: New and better option pages
 * - Feature: New workflow to generate a Newsletter
 * - Feature: AJAX-functionalities for sortable Articles
 * 
 * 0.2
 * - Feature: Option to choose excerpt
 * - Feature: Option to choose display contents
 * - Feature: Added %LINK% in Template
 * - Code: Fixed Capabilities
 * - Code: Fixed i18n
 * - Code: Styling
 * 
 * 0.1.1
 * - Code: Clean Ups, added comments and some other stuff
 *
 * 0.1
 * - Initial Release
 */

if ( ! class_exists( 'Multipost_Newsletter' ) ) {
	
	if ( function_exists( 'add_filter' ) )
		add_filter( 'plugins_loaded' ,  array( 'Multipost_Newsletter', 'get_instance' ) );
	
	class Multipost_Newsletter {
		
		/**
		* The plugins textdomain
		*
		* @since	0.6
		* @access	public
		* @static
		* @var		string
		*/
		public static $textdomain = '';
		
		/**
		 * Instance holder
		 *
		 * @since	0.6
		 * @access	private
		 * @static
		 * @var		NULL | Multipost_Newsletter
		 */
		private static $instance = NULL;
		
		/**
		 * The plugins Name
		 *
		 * @since 	1.0
		 * @static
		 * @access	public
		 * @var 	string
		 */
		public static $plugin_name = '';
		
		/**
		 * The plugins plugin_base
		 *
		 * @since 	1.0
		 * @access	public
		 * @static
		 * @var 	string
		 */
		public static $plugin_base_name = '';
		
		/**
		 * The plugins URL
		 *
		 * @since 	1.0
		 * @access	public
		 * @static
		 * @var 	string
		 */
		public static $plugin_url = '';
		
		/**
		 * Checks if plugin is pro
		 *
		 * @since 	1.0
		 * @access	public
		 * @static
		 * @var 	boolean
		 */
		public static $is_pro = FALSE;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.6
		 * @access	public
		 * @static
		 * @return	Multipost_Newsletter
		 */
		public static function get_instance() {
			
			if ( ! self::$instance )
				self::$instance = new self;
			return self::$instance;
		}
		
		/**
		 * Setting up some data, initialize localization and load
		 * the features
		 * 
		 * @since	0.6
		 * @access	public
		 * @return	void
		 */
		public function __construct () {
			
			// Textdomain
			self::$textdomain = $this->get_textdomain();
			// Initialize the localization
			$this->load_plugin_textdomain();
			
			// The Plugins Basename, URL and Name
			self::$plugin_base_name = plugin_basename( __FILE__ );
			self::$plugin_url = $this->get_plugin_header( 'PluginURI' );
			self::$plugin_name = $this->get_plugin_header( 'Name' );
			
			// Load the features
			$this->load_features();
		}
		
		/**
		 * Get a value of the plugin header
		 *
		 * @since	0.5
		 * @access	protected
		 * @param	string $value
		 * @uses	get_plugin_data, ABSPATH
		 * @return	string The plugin header value
		 */
		protected function get_plugin_header( $value = 'TextDomain' ) {
			
			// Generate and check Cache
			static $plugin_data = array();
			if ( isset ( $plugin_data[ $value ] ) )
				return $plugin_data[ $value ];
			
			// Get Plugin Header functions
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

			// Get the value
			$plugin_data = get_plugin_data( __FILE__ );
			if ( isset( $plugin_data[ $value ] ) )
				$plugin_value = $plugin_data[ $value ];
			else
				$plugin_value = '';

			return $plugin_value;
		}
		
		/**
		 * Get the Textdomain
		 *
		 * @since	0.5
		 * @access	public
		 * @return	string The plugins textdomain
		 */
		public function get_textdomain() {
			
			return $this->get_plugin_header( 'TextDomain' );
		}
		
		/**
		 * Load the localization
		 *
		 * @since	0.5
		 * @access	public
		 * @uses	load_plugin_textdomain, plugin_basename
		 * @return	void
		 */
		public function load_plugin_textdomain() {
			
			load_plugin_textdomain( self::$textdomain, FALSE, dirname( plugin_basename( __FILE__ ) ) . $this->get_plugin_header( 'DomainPath' ) );
		}
		
		/**
		 * Returns array of features, also
		 * Scans the plugins subfolder "/features"
		 *
		 * @since	0.5
		 * @access	protected
		 * @return	void
		 */
		protected function load_features() {
			
			// Load Pro-Features
			if ( is_dir( dirname( __FILE__ ) . '/pro' ) ) {
				self::$is_pro = TRUE;
				foreach ( glob( dirname( __FILE__ ) . '/pro/class-*.php' ) as $class )
					require_once $class;
			}
		
			// load all files with the pattern class-*.php from the directory inc
			foreach ( glob( dirname( __FILE__ ) . '/features/class-*.php' ) as $class )
				require_once $class;
		}
	}
	
	if ( ! function_exists( 'p' ) ) {
		/**
		 * This helper function outputs a given string,
		 * object or array
		 *
		 * @since	0.1
		 * @param 	mixed $output
		 * @return	void
		 */
		function p( $output ) {
			print '<br /><br /><br /><pre>';
			print_r( $output );
			print '</pre>';
		}
	}
	
	if ( ! function_exists( 'get_mpnl_textdomain' ) ) {
	
		/**
		 * This helper function loads the textdomain
		 *
		 * @since	0.1
		 * @return	string the textdomain
		 */
		function get_mpnl_textdomain() {
				
			$instance = Multipost_Newsletter::get_instance();
			return $instance::$textdomain;
		}
	}
}