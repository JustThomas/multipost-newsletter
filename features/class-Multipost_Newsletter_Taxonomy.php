<?php
/**
 * Feature Name:	Multipost Newsletter Taxonomy
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

if ( ! class_exists( 'Multipost_Newsletter_Taxonomy' ) ) {

	class Multipost_Newsletter_Taxonomy extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @access	private
		 * @static
		 * @var		NULL | Multipost_Newsletter_Taxonomy
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @return	Multipost_Newsletter_Taxonomy
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
		 * @uses	add_filter
		 * @return	void
		 */
		public function __construct() {
			
			// Init Taxonomy for the newsletter
			add_filter( 'init', array( $this, 'init_taxonomy' ) );
			
			// Remove menu item
			add_filter( 'admin_menu', array( $this, 'remove_custom_taxonomy_menu' ) );
			
			// Post Type
			$post_types = get_option( 'mp-newsletter-post-types' );
			
			// Custom Columns
			if ( is_array( $post_types ) ) {
				foreach ( $post_types as $post_type ) {
					
					if ( isset( $_GET[ 'post_type' ] ) && $post_type != $_GET[ 'post_type' ] )
						continue;
					
					if ( 'post' == $post_type || 'page' == $post_type )
						$post_type = $post_type . 's';
					
					add_filter( 'manage_' . $post_type . '_columns', array( $this, 'custom_column_head' ) );
					add_filter( 'manage_' . $post_type . '_custom_column', array( $this, 'custom_column_content' ) );
				}
			}
		}
		
		/**
		 * Add Costum Collumn Head
		 * 
		 * @since	0.1
		 * @access	public
		 * @param	array $defaults current default headers
		 * @uses	__
		 * @return	array $defaults modified headers
		 */
		public function custom_column_head( $defaults ) {
			
			$defaults[ 'newsletter' ] = __( 'Newsletter', parent::$textdomain );
			$defaults[ 'subject-area' ] = __( 'Subject Area', parent::$textdomain );
			return $defaults;
		}
		
		/**
		 * Add Costum Collumn Content
		 * 
		 * @since	0.1
		 * @access	public
		 * @param	string $column_name
		 * @global	$post the current post object
		 * @uses	get_the_term_list
		 * @return	void
		 */
		public function custom_column_content( $column_name ) {
			global $post;
			
			if ( 'newsletter' == $column_name )
				echo get_the_term_list( $post->ID, 'newsletter', '', ', ', '' );
			
			if ( 'subject-area' == $column_name )
				echo get_the_term_list( $post->ID, 'subject-area', '', ', ', '' );
		}
		
		/**
		 * Removes the menu entry of the registered custom taxonomy
		 * 
		 * @since	0.1
		 * @access	public
		 * @global	$submenu the current submenu object
		 * @return	void
		 */
		public function remove_custom_taxonomy_menu() {
			global $submenu;
			
			foreach ( $submenu as $entry => $values ) {
				if ( 'mpnl_options' != $entry ) {
					foreach ( $values as $key => $value ) {
						if ( in_array( __( 'Editions', parent::$textdomain ), $value ) )
							unset( $submenu[ $entry ][ $key ] );
						
						if ( in_array( __( 'Subject Areas', parent::$textdomain ), $value ) )
							unset( $submenu[ $entry ][ $key ] );
					}
				}
			}
		}
		
		/**
		 * Initialize Taxonomy
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	register_taxonomy, __
		 * @return	void
		 */
		public function init_taxonomy() {
			
			$labels = array(
				'name'				=> __( 'Editions', parent::$textdomain ),
				'all_items'			=> __( 'All Editions', parent::$textdomain ),
				'edit_item'			=> __( 'Edit Edition', parent::$textdomain ),
				'parent_item'		=> __( 'Parent Edition', parent::$textdomain ),
				'update_item'		=> __( 'Update Edition', parent::$textdomain ),
				'search_items'		=> __( 'Search Editions', parent::$textdomain ),
				'add_new_item'		=> __( 'Add Edition', parent::$textdomain ),
				'singular_name'		=> __( 'Edition', parent::$textdomain ),
				'new_item_name'		=> __( 'Add Edition', parent::$textdomain ),
				'popular_items'		=> __( 'Popular Editions', parent::$textdomain ),
				'parent_item_colon'	=> __( 'Parent Edition:', parent::$textdomain ),
			);
			
			$taxonomy_args = array(
				'public'			=> TRUE,
				'query_var'			=> 'newsletter',
				'show_ui'			=> TRUE,
				'show_tagcloud'		=> FALSE,
				'hierarchical'		=> TRUE,
				'show_in_nav_menus'	=> TRUE,
				'labels'			=> $labels,
			);
			
			$post_types = get_option( 'mp-newsletter-post-types' );
			
			register_taxonomy( 'newsletter', $post_types, $taxonomy_args );
			
			$labels = array(
				'name'				=> __( 'Subject Areas', parent::$textdomain ),
				'all_items'			=> __( 'All Subject Areas', parent::$textdomain ),
				'edit_item'			=> __( 'Edit Subject Area', parent::$textdomain ),
				'parent_item'		=> __( 'Parent Subject Area', parent::$textdomain ),
				'update_item'		=> __( 'Update Subject Area', parent::$textdomain ),
				'search_items'		=> __( 'Search Subject Areas', parent::$textdomain ),
				'add_new_item'		=> __( 'Add Subject Area', parent::$textdomain ),
				'singular_name'		=> __( 'Subject Area', parent::$textdomain ),
				'new_item_name'		=> __( 'Add Subject Area', parent::$textdomain ),
				'popular_items'		=> __( 'Popular Subject Areas', parent::$textdomain ),
				'parent_item_colon'	=> __( 'Parent Subject Area:', parent::$textdomain ),
			);
				
			$taxonomy_args = array(
				'public'			=> TRUE,
				'query_var'			=> 'subject-area',
				'show_ui'			=> TRUE,
				'show_tagcloud'		=> FALSE,
				'hierarchical'		=> TRUE,
				'show_in_nav_menus'	=> TRUE,
				'labels'			=> $labels,
			);
				
			$post_types = get_option( 'mp-newsletter-post-types' );
				
			register_taxonomy( 'subject-area', $post_types, $taxonomy_args );
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_Taxonomy::get_instance();
}