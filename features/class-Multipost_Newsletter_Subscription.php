<?php
/**
 * Feature Name:	Multipost Newsletter Subscription Page
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com
 * Licence:			GPLv3
 */

if ( ! class_exists( 'Multipost_Newsletter_Subscription' ) ) {

	class Multipost_Newsletter_Subscription extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @var		NULL | Multipost_Newsletter_Subscription
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @return	Multipost_Newsletter_Subscription
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
			
			add_filter( 'query_vars', array( $this, 'query_vars' ) );
			add_filter( 'template_include', array( $this, 'template_include' ) );
			add_filter( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );
		}
		
		/**
		 * Included the needed templates
		 *
		 * @access	public
		 * @uses	get_query_var, get_query_template
		 * @since	0.1
		 * @param	mixed $template The current queried template
		 * @return	mixed $template the new template
		 */
		function template_include( $template ) {
		
			$templates = array(
				'activation'			=> 'activation',
				'unsubscribe'			=> 'unsubscribe',
				'confirmunsubscription'	=> 'confirmunsubscription'
			);
			
			// Check if theere is a user template for this
			if ( isset( $templates[ get_query_var( 'mpnl' ) ] ) )
				$new_template = get_query_template( $templates[ get_query_var( 'mpnl' ) ] );
			else
				$new_template = '';
			
			if ( '' != $new_template )
				return $new_template;
			
			// Use our own template
			$new_template = plugin_dir_path( __FILE__ ) . '../templates/' . get_query_var( 'mpnl' ) . '.php';
			if ( file_exists( $new_template ) )
				return $new_template;
			else
				return $template;
		}
		
		/**
		 * Generate the rewrite rules
		 *
		 * @since	0.1
		 * @uses	get_query_var, get_query_template
		 * @param	object $wp_rewrite
		 * @return	void
		 */
		public function generate_rewrite_rules ( $wp_rewrite ) {
		
			$rules = array(
				'mpnl-activation/?([A-Za-z0-9-_.,]+)?'	=> 'index.php?mpnl=activation&mpnlkey=$matches[1]',
				'mpnl-unsubscribe/?([A-Za-z0-9-_.,]+)?'	=> 'index.php?mpnl=unsubscribe&mpnlkey=$matches[1]',
				'mpnl-confirm-unsubscription/?([A-Za-z0-9-_.,]+)?'	=> 'index.php?mpnl=confirmunsubscription&mpnlkey=$matches[1]',
			);
		
			foreach ( $rules as $query => $target ) {
				$wp_rewrite->rules = array(
					$query => $target,
				) + $wp_rewrite->rules;
			}
		}
		
		/**
		 * Register our query var
		 *
		 * @since	0.1
		 * @param	array $qvars The query vars
		 * @return	array $qvars The query vars
		 */
		public function query_vars( $qvars ){
			$qvars[] = 'mpnl';
			$qvars[] = 'mpnlkey';
			return $qvars;
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_Subscription::get_instance();
}