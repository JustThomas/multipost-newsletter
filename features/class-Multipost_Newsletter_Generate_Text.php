<?php
/**
 * Feature Name:	Multipost Newsletter Generate Text
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

if ( ! class_exists( 'Multipost_Newsletter_Generate_Text' ) ) {

	class Multipost_Newsletter_Generate_Text extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @access	private
		 * @static
		 * @var		NULL | Multipost_Newsletter_Generate_Text
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @return	Multipost_Newsletter_Generate_Text
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
		 * @return	void
		 */
		public function __construct () {
			
		}
		
		/**
		 * Generates the text newsletter
		 *
		 * @since	0.1
		 * @static
		 * @access	public
		 * @param	string $edition current edition
		 * @uses	get_option, get_term_by, date_i18n, __, get_the_time, get_post, get_the_title,
		 * 			get_permalink, has_post_thumbnail, get_post_thumbnail_id, get_the_post_thumbnail,
		 * 			get_post_meta
		 * @return	string text newsletter 
		 */
		public static function generate_text( $edition, $letter_slug ) {
			
			// Implement HTML2Text Engine
			require_once dirname( __FILE__ ) . '/../inc/html2text/html2text.php';
			
			// Get Newsletter
			$newsletter = get_option( 'newsletter_' . $letter_slug );
				
			// Get HTML Settings
			$text_main = get_option( 'mp-newsletter-text-main' );
			$text_post = get_option( 'mp-newsletter-text-post' );
			$text_params = get_option( 'mp-newsletter-text-params' );
			
			// Fetching general params
			$params = get_option( 'mp-newsletter-template-params' );
				
			// Generate Main Template
			$text = $text_main;
				
			// Get Name of Newsletter
			$full_edition = get_term_by( 'slug', $edition, 'newsletter' );
			$text = str_replace( '%NAME%', $full_edition->name, $text );
				
			// Replace Date
			$text = str_replace( '%DATE%', date_i18n( get_option( 'date_format' ) ), $text );
				
			// Replace Header
			$text_header = new html2text( $params[ 'header' ] );
			$params[ 'header' ] = $text_header->get_text();
			$text = str_replace( '%HEADER%', nl2br( $params[ 'header' ] ), $text );
				
			// Replace Footer
			if ( isset( $params[ 'footer' ] ) ) {
				$text_footer = new html2text( $params[ 'footer' ] );
				$params[ 'footer' ] = $text_footer->get_text();
				$text = str_replace( '%FOOTER%', nl2br( $params[ 'footer' ] ), $text );
			}
				
			// Generate Contents
			if ( isset( $params[ 'contents' ] ) && 'on' != $params[ 'contents' ] ) {
				$text = str_replace( '%CONTENTS%', '', $text );
			} else {
				

				$contents  = "\n\n";
				foreach ( $newsletter as $post ) {
					if ( ! isset( $post[ 'show_in_contents' ] ) )
						$post[ 'show_in_contents' ] = 'on';
					
					if ( 'spacer' == $post[ 'type' ] && 'off' == $post[ 'show_in_contents' ] )
						continue;
						
					if ( 'post' == $post[ 'type' ] )
						$contents .= get_the_title( $post[ 'id' ] ) . "\n";
						
					if ( 'spacer' == $post[ 'type' ] && isset( $post[ 'content_text' ] ) )
						$contents .= $post[ 'content_text' ];
				}
				$text = str_replace( '%CONTENTS%', $contents, $text );
			}
				
			// Generate Body
			$body = '';
			foreach ( $newsletter as $post ) {
				
				if ( ! isset( $post[ 'show_in_contents' ] ) )
					$post[ 'show_in_contents' ] = 'off';
			
				$post_body = $text_post;
				if ( 'spacer' == $post[ 'type' ] && 'on' == $post[ 'show_in_contents' ] )
					continue;
			
				// Spacer
				if ( 'spacer' == $post[ 'type' ] && isset( $post[ 'content_text' ] ) )
					$body .= $post[ 'content_text' ];
			
				// The Post
				if ( 'post' == $post[ 'type' ]  ) {
						
					// The Post
					$the_post = get_post( $post[ 'id' ] );
						
					// Replace date
					$post_body = str_replace( '%DATE%', get_the_time( get_option( 'date_format' ), $post[ 'id' ] ), $post_body );
						
					// Replace Author
					$author = get_userdata( $the_post->post_author );
					$post_body = str_replace( '%AUTHOR%', $author->data->display_name, $post_body );
						
					// Replace title
					if ( 'on' == get_post_meta( $post[ 'id' ], 'donot_show_title' ) )
						$post_body = str_replace( '%TITLE%', '', $post_body );
					else
						$post_body = str_replace( '%TITLE%', get_the_title( $post[ 'id' ] ), $post_body );
						
					// Replace Content
					if ( isset( $params[ 'excerpt' ] ) && 'on' == $params[ 'excerpt' ] ) {
						$show_content = 'off';
						$show_excerpt = 'on';
						$show_link = 'off';
					} else {
						$show_content = 'on';
						$show_excerpt = 'off';
						$show_link = 'off';
					}
						
					if ( '' != get_post_meta( $post[ 'id' ], 'show_content', TRUE ) ) {
						if ( 'full' == get_post_meta( $post[ 'id' ], 'show_content', TRUE ) ) {
							$show_content = 'on';
							$show_excerpt = 'off';
							$show_link = 'off';
						} else if ( 'excerpt' == get_post_meta( $post[ 'id' ], 'show_content', TRUE ) ) {
							$show_content = 'off';
							$show_excerpt = 'on';
							$show_link = 'off';
						} else if ( 'link' == get_post_meta( $post[ 'id' ], 'show_content', TRUE ) ) {
							$show_content = 'off';
							$show_excerpt = 'off';
							$show_link = 'on';
						}
					}
					
					if ( 'on' == $show_content ) {
						$text_content = new html2text( strip_shortcodes( $the_post->post_content ) );
						$post_body = str_replace( '%CONTENT%', $text_content->get_text(), $post_body );
					} else if ( 'on' == $show_excerpt ) {
						$text_content = new html2text( $the_post->post_excerpt );
						$post_body = str_replace( '%CONTENT%', $text_content->get_text(), $post_body );
					} else if ( 'on' == $show_link ) {
						$post_body = str_replace( '%CONTENT%', get_permalink( $post[ 'id' ] ), $post_body );
						$post_body = str_replace( '%LINK%', '', $post_body );
					} else {
						$post_body = str_replace( '%CONTENT%', '', $post_body );
					}
					
					// Replace Link
					$post_body = str_replace( '%LINK%', get_permalink( $post[ 'id' ] ), $post_body );
					
					// Replace Custom Fields
					preg_match_all( '~%CUSTOM_FIELD\s*\[key=([\'"])(?P<key>[^\1]*)\1\s*label=([\'"])(?P<label>[^\3]*)\3\s*\]~Ui', $post_body, $matches );
					foreach ( $matches[ 0 ] as $key => $string_to_replace ) {
					
						// Get postmeta
						$meta = get_post_meta( $post[ 'id' ], $matches[ 'key' ][ $key ], TRUE );
						if ( $meta )
							$string = "\n\r" . $matches[ 'label' ][ $key ] . $meta;
						else
							$string = '';
					
						$post_body = str_replace( $string_to_replace . '%', $string, $post_body );
					}
					
					$body .= $post_body;
				}
			}
			$text = str_replace( '%BODY%', $body, $text );
				
			return $text;
		}
	}
}