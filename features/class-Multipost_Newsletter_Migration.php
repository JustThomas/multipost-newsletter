<?php
/**
 * Feature Name:	Multipost Newsletter Migration Page
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com
 * Licence:			GPLv3
 */

if ( ! class_exists( 'Multipost_Newsletter_Migration' ) ) {

	class Multipost_Newsletter_Migration extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @var		NULL | Multipost_Newsletter_Migration
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @return	Multipost_Newsletter_Migration
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
			
			// Check if the migration is already done
			// if not, do it
			if ( 'true' != get_option( 'mp-newsletter-migration' ) ) {
				// Create Recipient Table
				global $wpdb;
				$query = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "mpnl_recipients` (
	  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	  `email` varchar(255) NOT NULL,
	  `registered` datetime NOT NULL,
	  `type` varchar(255) NOT NULL,
	  `groups` text NOT NULL,
	  `subjectareas` text NOT NULL,
	  `key` varchar(255) NOT NULL,
	  `activated` int(1) NOT NULL DEFAULT '0',
	  `unsubkey` varchar(255) NOT NULL,
	  `unsubsend` int(1) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `id` (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
				$wpdb->query( $query );
				
				// Migrate groups
				$groups = get_option( 'mp-newsletter-groups' );
				if ( ! empty( $groups ) ) {
					
					$new_groups = array();
					foreach ( $groups as $group ) {
						if ( ! is_array( $group ) )
							$new_groups[] = array( $group, 'off' );
						else
							$new_groups[] = $group;
					}
					$new_groups[] = array( $_POST[ 'new_group' ], $private_public_key );
					
					update_option( 'mp-newsletter-groups', $new_groups );
				}
				
				// Get all the users with the profile settings
				$args = array(
					'meta_key' => 'newsletter_receive',
					'meta_value' => 'on',
					'meta_compare' => '=',
				);
				$users = new WP_User_Query( $args );
				if ( ! empty( $users->results ) ) {
					foreach ( $users->results as $user ) {
						// Check if mail exists
						if ( ! function_exists( 'get_recipient_by_email' ) )
							require_once dirname( __FILE__ ) . '/../features/class-Multipost_Newsletter_Recipients.php';
							
						$recipient = get_recipient_by_email( $user->user_email );
						if ( ! empty( $recipient ) )
							continue;
						else
							$recipient = array();
						
						// Set types and groups
						$newsletter_type = get_user_meta( $user->ID, 'newsletter_type', TRUE );
						if ( ! is_array( $newsletter_type ) )
							$newsletter_type = array();
						
						$newsletter_groups = get_user_meta( $user->ID, 'newsletter_groups', TRUE );
						if ( ! is_array( $newsletter_groups ) )
							$newsletter_groups = array();
						
						if ( ! empty( $newsletter_type ) )
							$recipient[ 'type' ] = implode( ',', $newsletter_type );
						else
							$recipient[ 'type' ] = 'text';
						
						if ( TRUE == self::$is_pro && ! empty( $newsletter_groups ) )
							$recipient[ 'groups' ] = implode( ',', $newsletter_groups );
						else
							$recipient[ 'groups' ] = '';
						
						// Set defaults
						$recipient[ 'email' ] = $user->user_email;
						$recipient[ 'registered' ] = date( 'Y-m-d H:i:s' );
						$recipient[ 'key' ] = md5( wp_generate_password( 32 ) );
						$recipient[ 'activated' ] = '1';
						$recipient[ 'unsubkey' ] = md5( wp_generate_password( 32 ) );
						$recipient[ 'subjectareas' ] = 'all';
						
						// Insert the recipient
						global $wpdb;
						$wpdb->insert( $wpdb->prefix . 'mpnl_recipients', $recipient );
						
						// Remove all the users metas
						delete_user_meta( $user->ID, 'newsletter_receive' );
						delete_user_meta( $user->ID, 'newsletter_type' );
						delete_user_meta( $user->ID, 'newsletter_groups' );
					}
				}
				
				// Flush the rewrite rules
				add_filter( 'init', array( $this, 'flush_rules' ) );
				
				// Done!
				update_option( 'mp-newsletter-migration', 'true' );
			}
			
		}
		
		/**
		 * Flushes the rewrite rules
		 *
		 * @since	0.1
		 * @return	void
		 */
		public function flush_rules() {
			global $wp_rewrite;
			$wp_rewrite->flush_rules( TRUE );
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_Migration::get_instance();
}