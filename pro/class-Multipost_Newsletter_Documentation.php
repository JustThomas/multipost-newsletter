<?php
/**
 * Feature Name:	Documentation
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com
 * Licence:			GPLv3
 */

require_once( ABSPATH . 'wp-includes/pluggable.php' );

class Multipost_Newsletter_Documentation extends Multipost_Newsletter {
	
	/**
	 * Instance holder
	 *
	 * @since	0.1
	 * @var		NULL | __CLASS__
	 */
	private static $instance = NULL;
	
	/**
	 * The parent plugin name
	 *
	 * @since	0.1
	 * @var		array
	 */
	public static $plugin_name = '';
	
	/**
	 * The URL for the menu
	 *
	 * @since	0.1
	 * @var		string
	 */
	public static $url_menu = '';
	
	/**
	 * The URL for the content
	 *
	 * @since	0.1
	 * @var		string
	 */
	public static $url_content = '';
	
	/**
	 * Method for ensuring that only one instance of this object is used
	 *
	 * @since	0.1
	 * @return	__CLASS__
	 */
	public static function get_instance() {
		
		if ( ! self::$instance )
			self::$instance = new self;
		
		return self::$instance;
	}
	
	/**
	 * Setting up some data, all vars and start the hooks
	 *
	 * @since	0.1
	 * @uses	sanitize_title_with_dashes, add_filter
	 * @return	void
	 */
	public function __construct() {
		
		// Setting up Plugin identifier
		self::$plugin_name = sanitize_title_with_dashes( parent::$plugin_name );
		
		$locale = get_locale();
		switch( $locale ) {
			case 'de_DE':
				$domain = 'de';
				break;
			default:
				$domain = 'com';
				break;
		}
		
		// Setting up the license checkup URL
		self::$url_menu = 'http://marketpress.' . $domain . '/mp-doc/' . self::$plugin_name . '/menu/';
		self::$url_content = 'http://marketpress.' . $domain . '/mp-doc/' . self::$plugin_name;
		
		// Adding Menu
		add_filter( 'admin_menu', array( $this, 'admin_menu' ) );
		
		// Load Menu Ajax
		add_filter( 'wp_ajax_mpnl_load_documentation_menu', array( $this, 'load_documentation_menu' ) );
		// Load Content Ajax
		add_filter( 'wp_ajax_mpnl_load_documentation_content', array( $this, 'load_documentation_content' ) );
	}
	
	/**
	 * Adds the submenupage
	 *
	 * @since	0.1
	 * @uses	add_submenu_page, __
	 * @return	void
	 */
	public function admin_menu() {
		
		add_submenu_page( 'mpnl_options', __( 'Multipost Newsletter Documentation', parent::$textdomain ), __( 'Documentation', parent::$textdomain ), 'manage_options', 'mpnl_documentation', array( $this, 'documentation_page' ) );
	}
	
	/**
	 * Shows the documentation page
	 *
	 * @since	0.1
	 * @uses	_e
	 * @return	void
	 */
	public function documentation_page() {
		?>
		<div class="wrap">
			<?php screen_icon( parent::$textdomain ); ?>
			<h2><?php printf( __( '%s Documentation', parent::$textdomain ), parent::$plugin_name ); ?></h2>
			
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				
					<div id="side-info-column" class="inner-sidebar">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<div id="inpsyde" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Powered by', parent::$textdomain ); ?></span></h3>
								<div class="inside">
									<p style="text-align: center;"><a href="http://inpsyde.com"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../images/inpsyde_logo.png'; ?>" style="border: 7px solid #fff;" /></a></p>
									<p><?php _e( 'This plugin is powered by <a href="http://inpsyde.com">Inpsyde.com</a> - Your expert for WordPress, BuddyPress and bbPress.', parent::$textdomain ); ?></p>
								</div>
							</div>
							
							<div id="documentation" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Documentation', parent::$textdomain ); ?></span></h3>
								<div id="documentation_menu" class="inside">
									<?php $this->build_menu(); ?>
								</div>
							</div>
						</div>
					</div>
					
					<div id="post-body">
						<div id="post-body-content">
							<div id="documentation_content">
								<?php $this->build_content(); ?>
							</div>
						</div>
					</div>
				
				</div>
		</div>
		<?php
	}
	
	/**
	 * Builds the menu
	 *
	 * @since	0.1
	 * @uses	admin_url, _e
	 * @return	void
	 */
	public function build_menu() {
		?>
		<p><img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" class="alignleft" style="margin-right: 5px;" /><?php _e( 'Loading Menu ...', parent::$textdomain ); ?></p>
		
		<script type="text/javascript">
		( function( $ ) {
			var documentation = {
				init : function () {
					documentation.load_menu();
				},

				load_menu : function() {
					var post_vars = {
						action: 'mpnl_load_documentation_menu'
					};
					
					$.post( ajaxurl, post_vars, function( response ) {
						$( '#documentation_menu' ).html( response ); }
					);
				},
			};
			$( document ).ready( function( $ ) {
				documentation.init();
			} );
		} )( jQuery );
		</script>
		<?php	
	}
	
	/**
	 * Loads the documentation menu and
	 * parses the json to html
	 *
	 * @since	0.1
	 * @uses	
	 * @return	void
	 */
	public function load_documentation_menu() {
		
		// Connect to our remote host
		$remote = wp_remote_get( self::$url_menu );
		if ( is_wp_error( $remote ) ) {
			_e( 'Could not connect to remote host. Please try again later.', parent::$textdomain );
			exit;
		}
		
		// Load the response
		$response = json_decode( wp_remote_retrieve_body( $remote ) );
		if ( ! empty( $response ) )
			$this->parse_menu( $response );
		
		exit;
	}
	
	/**
	 * Parses the json to HTML
	 *
	 * @since	0.1
	 * @uses
	 * @return	void
	 */
	public function parse_menu( $menu ) {
		
		?>
		<ul style="margin: 0; padding: 0; list-style: disc;">
			<?php foreach ( $menu as $menu_point ) { ?>
				<li style="margin: 0 0 0 20px; padding: 3px;">
					<a href="#" class="load-documentation" pageid="<?php echo $menu_point->id; ?>">
						<?php echo $menu_point->title; ?>
					</a>
				
					<?php if ( isset( $menu_point->sub ) && ! empty( $menu_point->sub ) ) { ?>
						<?php $this->parse_menu( $menu_point->sub ); ?>
					<?php } ?>
				</li>
			<?php } ?>
		</ul>
		<?php
	}
	
	/**
	 * Builds the content
	 *
	 * @since	0.1
	 * @uses	admin_url, _e
	 * @return	void
	 */
	public function build_content() {
		?>
		<p><img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" class="alignleft" style="margin-right: 5px;" /><?php _e( 'Loading Content ...', parent::$textdomain ); ?></p>
		
		<script type="text/javascript">
		( function( $ ) {
			var documentation_content = {
				init : function () {
					documentation_content.load_content( 0 );
					$( '.load-documentation' ).live( 'click', function() {
						$( '#documentation_content' ).html( '<p><img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" class="alignleft" style="margin-right: 5px;" /><?php _e( 'Loading Content ...', parent::$textdomain ); ?></p>' );
						documentation_content.load_content( $( this ).attr( 'pageid' ) );
						return false;
					} );
				},

				load_content : function( pageid ) {
					var post_vars = {
						pageid: pageid,
						action: 'mpnl_load_documentation_content'
					};
					
					$.post( ajaxurl, post_vars, function( response ) {
						$( '#documentation_content' ).html( response ); }
					);
				},
			};
			$( document ).ready( function( $ ) {
				documentation_content.init();
			} );
		} )( jQuery );
		</script>
		<?php	
	}
	
	/**
	 * Loads the documentation menu and
	 * parses the json to html
	 *
	 * @since	0.1
	 * @uses
	 * @return	void
	 */
	public function load_documentation_content() {
		
		// Check Pageid
		if ( isset( $_REQUEST[ 'pageid' ] ) && $_REQUEST[ 'pageid' ] != 0 )
			self::$url_content .= '/' . $_REQUEST[ 'pageid' ] . '/';
		
		// Connect to our remote host
		$remote = wp_remote_get( self::$url_content );
		if ( is_wp_error( $remote ) ) {
			_e( 'Could not connect to remote host. Please try again later.', parent::$textdomain );
			exit;
		}
	
		// Load the response
		$response = wp_remote_retrieve_body( $remote );
		echo $response;
	
		exit;
	}
}

// Kickoff
if ( function_exists( 'add_filter' ) )
	add_filter( 'plugins_loaded' ,  array( 'Multipost_Newsletter_Documentation', 'get_instance' ), 11 );