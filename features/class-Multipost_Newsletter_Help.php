<?php
/**
 * Feature Name:	Multipost Newsletter Help
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com
 * Licence:			GPLv3
 */

if ( ! class_exists( 'Multipost_Newsletter_Help' ) ) {

	class Multipost_Newsletter_Help extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @var		NULL | Multipost_Newsletter_Help
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @return	Multipost_Newsletter_Help
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
		 * @uses	
		 * @return	void
		 */
		public function __construct () {
			
		}
		
		/**
		 * Adds the options help tabs
		 *
		 * @since	0.1
		 * @global	$mpnl_options_page
		 * @uses	__
		 * @return	void
		 */
		public static function mpnl_options_page_help() {
			global $mpnl_options_page;
			
			$screen = get_current_screen();
			if ( $screen->id != $mpnl_options_page )
				return;
			
			$general_content  = '<p>' . __( 'In order to the KISS-Principles we donot want to offer many options to set.', parent::$textdomain ) . '</p>';
			$general_content .= '<h3>' . __( 'Sender Options', parent::$textdomain ) . '</h3>';
			$general_content .= '<p>' . __( 'The newsletter definitly needs a sender name and a sender email. If you send the newsletter to your recipients, they will be displayed in the "From"-section. You also have to set the recipient for the testmail to check the newsletter before it will be published.', parent::$textdomain ) . '</p>';
			$general_content .= '<h3>' . __( 'Custom Post Types', parent::$textdomain ) . '</h3>';
			$general_content .= '<p>' . __( 'The Multipost Newsletter provides a support for all post types. Just activate the post types here to enable the functionality to set the edition for the types.', parent::$textdomain ) . '</p>';
			
			$screen->add_help_tab( array(
				'id'	=> 'mpnl_options_page_general',
				'title'	=> __( 'General Options', parent::$textdomain ),
				'content'	=> $general_content,
			) );
			
			$smtp_content  = '<p>' . __( 'In order to the KISS-Principles we donot want to offer many options to set.', parent::$textdomain ) . '</p>';
			$smtp_content .= '<h3>' . __( 'SMTP Settings', parent::$textdomain ) . '</h3>';
			$smtp_content .= '<p>' . __( 'The Multipost Newsletter offers a way to send the newsletter over SMTP. Just insert your credentials. Leave the values blank to delete the credentials.', parent::$textdomain ) . '</p>';
				
			$screen->add_help_tab( array(
				'id'	=> 'mpnl_options_page_smtp',
				'title'	=> __( 'SMTP Settings', parent::$textdomain ),
				'content'	=> $smtp_content,
			) );
		}
		
		/**
		 * Adds the template help tabs
		 *
		 * @since	0.1
		 * @global	$mpnl_template_page
		 * @uses
		 * @return	void
		 */
		public static function mpnl_template_page_help() {
			global $mpnl_template_page;
			
			// No need for this here
			return;
				
			$screen = get_current_screen();
			if ( $screen->id != $mpnl_template_page )
				return;
				
		}
		
		/**
		 * Adds the groups help tabs
		 *
		 * @since	0.1
		 * @global	$mpnl_groups_page
		 * @uses
		 * @return	void
		 */
		public static function mpnl_groups_page_help() {
			global $mpnl_groups_page;
			
			// No need for this here
			return;
		
			$screen = get_current_screen();
			if ( $screen->id != $mpnl_groups_page )
				return;
		
		}
		
		/**
		 * Adds the prepare help tabs
		 *
		 * @since	0.1
		 * @global	$mpnl_prepare_page
		 * @uses
		 * @return	void
		 */
		public static function mpnl_prepare_page_help() {
			global $mpnl_prepare_page;
			
			// No need for this here
			return;
		
			$screen = get_current_screen();
			if ( $screen->id != $mpnl_prepare_page )
				return;
		
		}
		
		/**
		 * Adds the create help tabs
		 *
		 * @since	0.1
		 * @global	$mpnl_create_page
		 * @uses
		 * @return	void
		 */
		public static function mpnl_create_page_help() {
			global $mpnl_create_page;
			
			// No need for this here
			return;
		
			$screen = get_current_screen();
			if ( $screen->id != $mpnl_create_page )
				return;
		
		}
		
		/**
		 * Adds the recipients help tabs
		 *
		 * @since	0.1
		 * @global	$mpnl_create_page
		 * @uses
		 * @return	void
		 */
		public static function mpnl_recipients_page_help() {
			global $mpnl_recipients_page;
			
			// No need for this here
			return;
		
			$screen = get_current_screen();
			if ( $screen->id != $mpnl_recipients_page )
				return;
		
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_Help::get_instance();
}