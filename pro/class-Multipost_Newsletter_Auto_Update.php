<?php
/**
 * Feature Name:	Multipost Newsletter Market Auto Update
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com
 * Licence:			GPLv3
 */

require_once( ABSPATH . 'wp-includes/pluggable.php' );

class Multipost_Newsletter_Auto_Update extends Multipost_Newsletter {
	
	/**
	 * Instance holder
	 *
	 * @since	0.1
	 * @var		NULL | Multipost_Newsletter_Auto_Update
	 */
	private static $instance = NULL;
	
	/**
	 * Check if the plugin comes from marketpress
	 * dashboard
	 *
	 * @since	0.1
	 * @var		NULL | Multipost_Newsletter_Auto_Update
	 */
	private static $is_marketpress = FALSE;
	
	/**
	 * The name of the parent class
	 * 
	 * @since	0.1
	 * @var		string
	 */
	public static $parent_class = '';
	
	/**
	 * The URL for the update check
	 *
	 * @since	0.1
	 * @var		string
	 */
	public static $url_update_check = '';
	
	/**
	 * The URL for the update package
	 *
	 * @since	0.1
	 * @var		string
	 */
	public static $url_update_package = '';
	
	/**
	 * The holder for all our licenses
	 *
	 * @since	0.1
	 * @var		array
	 */
	public static $licenses = '';
	
	/**
	 * The license key
	 *
	 * @since	0.1
	 * @var		array
	 */
	public static $key = '';
	
	/**
	 * The parent plugin name
	 *
	 * @since	0.1
	 * @var		array
	 */
	public static $plugin_name = '';
	
	/**
	 * The URL for the key check
	 *
	 * @since	0.1
	 * @var		string
	 */
	public static $url_key_check = '';
	
	/**
	 * Method for ensuring that only one instance of this object is used
	 *
	 * @since	0.1
	 * @return	Multipost_Newsletter_Auto_Update
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
	 * @uses	sanitize_title_with_dashes, wp_safe_redirect, wp_next_scheduled, wp_schedule_event,
	 * 			apply_filters, get_option
	 * @return	void
	 */
	public function __construct() {
		
		// Setting up Plugin identifier
		self::$plugin_name = sanitize_title_with_dashes( parent::$plugin_name );
		
		// Get all our licenses
		$this->get_key();
		
		// Setting up the license checkup URL
		self::$url_key_check = 'http://marketpress.com/mp-key/' . self::$key . '/' . self::$plugin_name . '/' . sanitize_title_with_dashes( network_site_url() );
		self::$url_update_check = 'http://marketpress.com/mp-version/' . self::$key . '/' . self::$plugin_name . '/' . sanitize_title_with_dashes( network_site_url() );
		self::$url_update_package = 'http://marketpress.com/mp-download/' . self::$key . '/' . self::$plugin_name . '/' . sanitize_title_with_dashes( network_site_url() );
		
		// Parse the plugin Row Stuff
		add_filter( 'after_plugin_row_' . parent::$plugin_base_name , array( $this, 'license_row' ) );

		// Add Admin Notice for the MarketPress Dashboard
		add_filter( 'admin_notices', array( $this, 'marketpress_dashboard_notice' ) );
		
		// Due to we cannot update a form inside of a form
		// we need to redirect the update license request to the needed form
		if ( isset( $_REQUEST[ 'license_key_' . self::$plugin_name ] ) && $_REQUEST[ 'license_key_' . self::$plugin_name ] != '' )
			wp_safe_redirect( admin_url( 'admin-post.php?action=update_license_key_' . $this->get_textdomain() . '&key=' . $_REQUEST[ 'license_key_' . self::$plugin_name ] ) );
		
		// Add Set License Filter
		add_filter( 'admin_post_update_license_key_' . $this->get_textdomain(), array( $this, 'update_license' ) );
		
		// Remove Key Filter
		add_filter( 'admin_post_remove_license_key_' . $this->get_textdomain(), array( $this, 'remove_license_key' ) );
		
		// Add Download Filter
		add_filter( 'admin_post_install_marketpress_dashboard', array( $this, 'install_marketpress_dashboard' ) );
		
		// add scheduled event for the key checkup
		add_filter( $this->get_textdomain() . '_license_key_checkup', array( $this, 'license_key_checkup' ) );
		if ( ! wp_next_scheduled( $this->get_textdomain() . '_license_key_checkup' ) )
			wp_schedule_event( time(), 'daily', $this->get_textdomain() . '_license_key_checkup' );
		
		// Add Filter for the license check ( the cached state of the checkup )
		add_filter( $this->get_textdomain() . '_license_check', array( $this, 'license_check' ) );
		
		// Version Checkup
		if ( self::$is_marketpress == TRUE ) {
			$user_data = get_option( 'marketpress_user_data' );
			if ( isset( $user_data[ self::$plugin_name ] ) && $user_data[ self::$plugin_name ] == 'false' )
				add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_plugin_version' ) );
		} else {
			$license_check = apply_filters( $this->get_textdomain() . '_license_check', FALSE );
			if ( $license_check )
				add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_plugin_version' ) );
		}
			
	}
	
	/**
	 * Setting up the key
	 *
	 * @since	0.1
	 * @uses	get_option
	 * @return	void
	 */
	public function get_key() {
		
		// Check if theres a key in the config
		if ( defined( 'MARKETPRESS_KEY' ) && MARKETPESS_KEY != '' )
			self::$key = MARKETPESS_KEY;
		
		// MarketPress Key
		if ( self::$key == '' && get_option( 'marketpress_license' ) != '' )
			self::$key = get_option( 'marketpress_license' );
		
		// Check if the plugin is valid
		$user_data = get_option( 'marketpress_user_data' );
		if ( isset( $user_data[ self::$plugin_name ] ) && $user_data[ self::$plugin_name ]->valid == 'false' ) {
			self::$key = '';
		} else if ( isset( $user_data[ self::$plugin_name ] ) && $user_data[ self::$plugin_name ]->valid == 'true' ) {
			self::$key = '';
			self::$is_marketpress = TRUE;
		}
		
		// Get all our licenses
		self::$licenses = get_option( 'inpsyde_licenses' );
		if ( isset( self::$licenses[ self::$plugin_name . '_license' ] ) ) {
			self::$key = self::$licenses[ self::$plugin_name . '_license' ];
			self::$is_marketpress = FALSE;
		}
	}
	
	/**
	 * Checks over the transient-update-check for plugins if new version of
	 * this plugin os available and is it, shows a update-message into
	 * the backend and register the update package in the transient object
	 *
	 * @since	0.1
	 * @param	object $transient
	 * @uses	wp_remote_get, wp_remote_retrieve_body, get_option,
	 * 			get_site_transient, set_site_transient
	 * @return	object $transient
	 */
	public function check_plugin_version( $transient ) {
		
		if ( empty( $transient->checked ) )
			return $transient;
		
		$response = $this->license_key_checkup();
		if ( $response != 'true' ) {
			if ( isset( $transient->response[ parent::$plugin_base_name ] ) )
				unset( $transient->response[ parent::$plugin_base_name ] );
			
			return $transient;
		}
		
		// Connect to our remote host
		$remote = wp_remote_get( self::$url_update_check );
			
		// If the remote is not reachable or any other errors occured,
		// we have to break up
		if ( is_wp_error( $remote ) ) {
			if ( isset( $transient->response[ parent::$plugin_base_name ] ) )
				unset( $transient->response[ parent::$plugin_base_name ] );
			
			return $transient;
		}
			
		$response = json_decode( wp_remote_retrieve_body( $remote ) );
		if ( $response->status != 'true' ) {
			if ( isset( $transient->response[ parent::$plugin_base_name ] ) )
				unset( $transient->response[ parent::$plugin_base_name ] );
			
			return $transient;
		}
		
		$version = $response->version;
		$current_version = $this->get_plugin_header( 'Version' );
		
		// Yup, insert the version
		if ( version_compare( $version, $current_version ) ) {
			$hashlist	= get_site_transient( 'update_hashlist' );
			$hash		= crc32( get_parent_class( $this ) . $version );
			$hashlist[]	= $hash;
			set_site_transient( 'update_hashlist' , $hashlist );
	
			$info				= new stdClass();
			$info->url			= parent::$plugin_url;
			$info->slug			= parent::$plugin_base_name;
			$info->package		= self::$url_update_package;
			$info->new_version	= $version;
			
			$transient->response[ parent::$plugin_base_name ] = $info;
			
			return $transient;
		}
		
		// Always return a transient object
		if ( isset( $transient->response[ parent::$plugin_base_name ] ) )
			unset( $transient->response[ parent::$plugin_base_name ] );
		
		return $transient;
	}
	
	/**
	 * Disables the checkup
	 *
	 * @since	0.1
	 * @param	object $transient
	 * @return	object $transient
	 */
	public function dont_check_plugin_version( $transient ) {
		
		unset( $transient->response[ parent::$plugin_base_name ] );
		
		return $transient;
	}
	
	/**
	 * Added the Fields for licensing this after Plugin-Row.
	 *
	 * @since	0.1
	 * @uses	is_network_admin, current_user_can, network_admin_url, get_option
	 * @return	void
	 */
	public function license_row() {
			
		// Security Check
		if ( function_exists( 'is_network_admin' ) && is_network_admin() ) {
			if ( ! current_user_can( 'manage_network_plugins' ) )
				return false;
		} else if ( function_exists( 'current_user_can' ) && ! current_user_can( 'activate_plugins' ) )
			return false;
		
		?>
		<tr class="plugin-update-tr">
			<td colspan="4">
				<?php
				if ( self::$is_marketpress == TRUE && is_admin() && current_user_can( 'manage_options' ) ) {
					
					$user_data = get_option( 'marketpress_user_data' );
					if ( isset( $user_data[ self::$plugin_name ] ) && $user_data[ self::$plugin_name ] == 'false' ) {
						?>
						<div class="update-message" style="background-color: #FFEBE8; border-color: #C00;">
							<?php _e( 'Your license for the plugin ' . parent::$plugin_name . ' is not valid. The Auto-Update has been deactivated. Please insert a valid key in the MarketPress Dashboard. Or if you want to add an other valid code use the form below.', $this->get_textdomain() ); ?>
						</div>
						<?php
					} else {
						?>
						<div class="update-message" style="background-color: #90ee90; border-color: #008000;">
							<?php echo sprintf( __( 'You are currently using a valid key for this plugin. You are able to renew the key in the MarketPress Dashboard. Or if you want to add an other valid code use the form below.', $this->get_textdomain() ), admin_url( 'admin-post.php?action=remove_license_key_' . $this->get_textdomain() ) ); ?>
						</div>
						<?php
					}
				} else {
					$license_check = apply_filters( $this->get_textdomain() . '_license_check', FALSE );
					if ( $license_check == 'false' ) {
						?>
						<div class="update-message" style="background-color: #FFEBE8; border-color: #C00;">
							<?php _e( 'Your license for the plugin ' . parent::$plugin_name . ' is not valid. The Auto-Update has been deactivated.', $this->get_textdomain() ); ?>
						</div>
						<?php
					} else {
						?>
						<div class="update-message" style="background-color: #90ee90; border-color: #008000;">
							<?php echo sprintf( __( 'You are currently using a valid key for this plugin. You are able to renew the key below or you can delete the key by <a href="%s">clicking here</a>.', $this->get_textdomain() ), admin_url( 'admin-post.php?action=remove_license_key_' . $this->get_textdomain() ) ); ?>
						</div>
						<?php
					}
				}
				?>
				<div class="update-message">
					<strong><label for="license_key_<?php echo parent::$plugin_name; ?>"><?php _e( 'License Key', $this->get_textdomain() ); ?></label></strong>
					<input type="text" name="license_key_<?php echo self::$plugin_name; ?>" id="license_key_<?php echo self::$plugin_name; ?>" value="<?php echo self::$is_marketpress == FALSE ? self::$key : ''; ?>" class="regular-text code" />

					<input type="submit" name="submit" value="<?php echo __( 'Activate', $this->get_textdomain() ); ?>" class="button-primary action" />
				</div>
			</td>
		</tr>
		<?php
	}
	
	/**
	 * Updates and inserts the license
	 *
	 * @since	0.1
	 * @uses	wp_safe_redirect, admin_url
	 * @return	boolean
	 */
	public function update_license() {
		
		if ( $_REQUEST[ 'key' ] == '' )
			wp_safe_redirect( admin_url( 'plugins.php?message=marketpress_wrong_key' ) );
		
		$response = $this->license_key_checkup( $_REQUEST[ 'key' ] );
		if ( $response == 'true' )
			wp_safe_redirect( admin_url( 'plugins.php?message=marketpress_plugin_activated' ) );
		else if ( $response == 'wrongkey' )
			wp_safe_redirect( admin_url( 'plugins.php?message=marketpress_wrong_key' ) );
		else if ( $response == 'wronglicense' )
			wp_safe_redirect( admin_url( 'plugins.php?message=marketpress_wrong_license' ) );
		else if ( $response == 'wrongurl' )
			wp_safe_redirect( admin_url( 'plugins.php?message=marketpress_wrong_url' ) );
		else
			wp_safe_redirect( admin_url( 'plugins.php?message=marketpress_wrong_anything' ) );
		
		exit;
	}
	
	/**
	 * Check the license-key and caches the returned value
	 * in an option
	 *
	 * @since	0.1
	 * @uses	wp_remote_retrieve_body, wp_remote_get, update_option, is_wp_error,
	 * 			delete_option
	 * @return	boolean
	 */
	public function license_key_checkup( $key = '' ) {
		
		// Request Key
		if ( $key != '' )
			self::$key = $key;
		
		// Check if there's a key
		if ( self::$key == '' ) {
			// Deactivate Plugin first
			update_option( 'inpsyde_license_status_' . self::$plugin_name, 'false' );
			return 'wrongkey';
		}
		
		// Update URL Key Checker
		self::$url_key_check = 'http://marketpress.com/mp-key/' . self::$key . '/' . self::$plugin_name . '/' . sanitize_title_with_dashes( network_site_url() );
		
		// Connect to our remote host
		$remote = wp_remote_get( self::$url_key_check );
		
		// If the remote is not reachable or any other errors occured,
		// we believe in the goodwill of the user and return true
		if ( is_wp_error( $remote ) ) {
			self::$licenses[ self::$plugin_name . '_license' ] = self::$key;
			update_option( 'inpsyde_licenses' , self::$licenses );
			update_option( 'inpsyde_license_status_' . self::$plugin_name, 'true' );
			return 'true';
		}
		
		// Okay, get the response
		$response = json_decode( wp_remote_retrieve_body( $remote ) );
		if ( ! isset( $response ) || $response == '' ) {
			// Deactivate Plugin first
			delete_option( 'inpsyde_license_status_' . self::$plugin_name );
		
			if ( isset( self::$licenses[ self::$plugin_name . '_license' ] ) ) {
				unset( self::$licenses[ self::$plugin_name . '_license' ] );
				update_option( 'inpsyde_licenses' , self::$licenses );
			}
		
			return 'wronglicense';
		}
		
		// Okay, get the response
		$response = json_decode( wp_remote_retrieve_body( $remote ) );
		
		if ( $response->status == 'noproducts' ) {
			// Deactivate Plugin first
			delete_option( 'inpsyde_license_status_' . self::$plugin_name );
			
			if ( isset( self::$licenses[ self::$plugin_name . '_license' ] ) ) {
				unset( self::$licenses[ self::$plugin_name . '_license' ] );
				update_option( 'inpsyde_licenses' , self::$licenses );
			}
			
			return 'wronglicense';
		}
		
		if ( $response->status == 'wronglicense' ) {
			// Deactivate Plugin first
			delete_option( 'inpsyde_license_status_' . self::$plugin_name );
				
			if ( isset( self::$licenses[ self::$plugin_name . '_license' ] ) ) {
				unset( self::$licenses[ self::$plugin_name . '_license' ] );
				update_option( 'inpsyde_licenses' , self::$licenses );
			}
				
			return 'wronglicense';
		}
		
		if ( $response->status == 'urllimit' ) {
			// Deactivate Plugin first
			delete_option( 'inpsyde_license_status_' . self::$plugin_name );
		
			if ( isset( self::$licenses[ self::$plugin_name . '_license' ] ) ) {
				unset( self::$licenses[ self::$plugin_name . '_license' ] );
				update_option( 'inpsyde_licenses' , self::$licenses );
			}
		
			return 'wrongurl';
		}
		
		if ( $response->status == 'true' ) {
			
			// Activate Plugin first
			self::$licenses[ self::$plugin_name . '_license' ] = self::$key;
			update_option( 'inpsyde_licenses' , self::$licenses );
			update_option( 'inpsyde_license_status_' . self::$plugin_name, 'true' );
			
			return 'true';
		}
		
		exit;
	}
	
	/**
	 * Checks the cached state of the license checkup
	 * 
	 * @since	0.1
	 * @uses	get_option
	 * @return	boolean
	 */
	public function license_check() {
		
		return get_option( 'inpsyde_license_status_' . self::$plugin_name );
	}
	
	/**
	 * Checks if the plugin "MarketPress Dashboard" exists.
	 * If not, present a link to download it
	 * 
	 * @since	0.1
	 * @uses	get_plugins, sanitize_title_with_dashes, get_option, __
	 * @return	boolean
	 */
	public function marketpress_dashboard_notice() {
		
		$plugs = array();
		$plugins = get_plugins();
		foreach ( $plugins as $installed_plugin )
			$plugs[] = sanitize_title_with_dashes( $installed_plugin[ 'Name' ] );
		
		if ( isset( $_GET[ 'message' ] ) ) {
			switch ( $_GET[ 'message' ] ) {
				case 'license_deleted':
					?>
					<div class="updated"><p>
						<?php echo __( 'The License has been deleted.', $this->get_textdomain() ); ?>
					</p></div>
					<?php
					break;
				case 'marketpress_plugin_activated':
					?>
					<div class="updated"><p>
						<?php echo __( 'Plugin successfully activated.', $this->get_textdomain() ); ?>
					</p></div>
					<?php
					break;
				case 'marketpress_wrong_key':
					?>
					<div class="error"><p>
						<?php echo __( 'The entered license key is wrong.', $this->get_textdomain() ); ?>
					</p></div>
					<?php
					break;
				case 'marketpress_wrong_url':
					?>
					<div class="error"><p>
						<?php echo __( 'You have reached the limit of urls. Please update your license at <a href="http://marketpress.com">marketpress.com</a>.', $this->get_textdomain() ); ?>
					</p></div>
					<?php
					break;
				case 'marketpress_wrong_anything':
					?>
					<div class="error"><p>
						<?php echo __( 'Something went wrong. Please try again later or contact the <a href="http://marketpress.com/support/">marketpress team</a>.', $this->get_textdomain() ); ?>
					</p></div>
					<?php
					break;
				case 'marketpress_wrong_license':
					?>
					<div class="error"><p>
						<?php echo __( 'Due to a wrong license you are not allowed to activate this plugin. Please update your license at <a href="http://marketpress.com">marketpress.com</a>.', $this->get_textdomain() ); ?>
					</p></div>
					<?php
					break;
			}
		}
	}
	
	/**
	 * Downloads the MarketPress Dashboard
	 *
	 * @since	0.1
	 * @uses	WP_Upgrader, wp_safe_redirect, admin_url
	 * @return	void
	 */
	public function install_marketpress_dashboard() {
			
		// Download
		$package = 'http://marketpress.com/mpdash.zip';
		$upgrader = new WP_Upgrader( new AU_Install_Skin() );
			
		// File System Connect
		$res = $upgrader->fs_connect( array( WP_CONTENT_DIR, WP_PLUGIN_DIR ) );
			
		// Download Package
		$download = $upgrader->download_package( $package );
			
		// Don't delete local files
		$delete_package = ( $download != $package );
			
		// Unpack the package
		$working_dir = $upgrader->unpack_package( $download, $delete_package );
			
		// Install the package
		$result = $upgrader->install_package( array(
			'source'			=> $working_dir,
			'destination'		=> WP_PLUGIN_DIR,
			'clear_destination'	=> FALSE,
			'clear_working'		=> TRUE,
			'hook_extra'		=> array()
		) );
		
		// Redirect
		wp_safe_redirect( admin_url( 'plugins.php?message=marketpress_installed' ) );
	}
	
	/**
	 * Removes the plugins key from the licenses
	 *
	 * @since	0.1
	 * @uses	update_option, wp_safe_redirect, admin_url
	 * @return	void
	 */
	public function remove_license_key() {
		
		if ( isset( self::$licenses[ self::$plugin_name . '_license' ] ) )
			unset( self::$licenses[ self::$plugin_name . '_license' ] );
		
		update_option( 'inpsyde_licenses' , self::$licenses );
		
		self::$key = '';
		
		// Renew License Check
		$this->license_key_checkup();
		
		// Redirect
		wp_safe_redirect( admin_url( 'plugins.php?message=license_deleted' ) );
	}
}


global $pagenow;

if ( ! class_exists( 'AU_Install_Skin' ) && $pagenow != 'update-core.php' ) {
	// Need this class :(
	if ( ! class_exists( 'WP_Upgrader_Skin' ) )
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	
	class AU_Install_Skin extends WP_Upgrader_Skin {
	
		/**
		 * Enforce the Feedback to nothing
		 * @see WP_Upgrader_Skin::feedback()
		 */
		public function feedback() {
				
			return NULL;
		}
	}
}

// Kickoff
if ( function_exists( 'add_filter' ) )
	Multipost_Newsletter_Auto_Update::get_instance();