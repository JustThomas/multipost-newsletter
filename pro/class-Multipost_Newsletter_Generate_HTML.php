<?php
/**
 * Feature Name:	Multipost Newsletter Generate
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

if ( ! class_exists( 'Multipost_Newsletter_Generate_HTML' ) ) {

	class Multipost_Newsletter_Generate_HTML extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @access	private
		 * @static
		 * @var		NULL | Multipost_Newsletter_Generate_HTML
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @return	Multipost_Newsletter_Generate_HTML
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
		 * Generates the html newsletter
		 *
		 * @since	0.1
		 * @static
		 * @access	public
		 * @param	string $edition current edition
		 * @uses	get_option, get_term_by, date_i18n, __, get_the_time, get_post, get_the_title,
		 * 			get_permalink, has_post_thumbnail, get_post_thumbnail_id, get_the_post_thumbnail,
		 * 			get_post_meta 
		 * @return	string html newsletter
		 */
		public static function generate_html( $edition, $letter_slug ) {
			
			// Get Newsletter
			$newsletter = get_option( 'newsletter_' . $letter_slug );
			
			// Get HTML Settings
			$html_main = get_option( 'mp-newsletter-html-main' );
			$html_post = get_option( 'mp-newsletter-html-post' );
			$html_params = get_option( 'mp-newsletter-html-params' );
			
			if ( ! isset( $_POST[ 'generate_newsletter' ] ) ) {
				$html_head = $html_params[ 'html_head' ];
				$html_footer = $html_params[ 'html_footer' ];
			} else {
				$html_head = '';
				$html_footer = '';
			}
				
			// Fetching general params
			$params = get_option( 'mp-newsletter-template-params' );
			
			// Generate Main Template
			$html = $html_head . $html_main . $html_footer;
			
			// Get Name of Newsletter
			$full_edition = get_term_by( 'slug', $edition, 'newsletter' );
			$html = str_replace( '%NAME%', $full_edition->name, $html );
			
			// Replace Date
			$html = str_replace( '%DATE%', date_i18n( get_option( 'date_format' ) ), $html );
			
			// Replace Header
			$html = str_replace( '%HEADER%', stripslashes( nl2br( $params[ 'header' ] ) ), $html );
			
			// Replace Footer
			$html = str_replace( '%FOOTER%', stripslashes( nl2br( $params[ 'footer' ] ) ), $html );
			
			// Generate Contents
			if ( ! isset( $params[ 'contents' ] ) || 'on' != $params[ 'contents' ] ) {
				$html = str_replace( '%CONTENTS%', '', $html );
			} else {
				
				$contents = '<ul>';
				foreach ( $newsletter as $post ) {
					if ( 'spacer' == $post[ 'type' ] && 'off' == $post[ 'show_in_contents' ] )
						continue;
					
					if ( 'post' == $post[ 'type' ] )
						$contents .= '<li><a href="#' . $post[ 'id' ] . '">' . get_the_title( $post[ 'id' ] ) . '</a></li>';
					
					if ( 'spacer' == $post[ 'type' ] )
						$contents .= $post[ 'content_html' ];
				}
				$contents .= '</ul>';
				$html = str_replace( '%CONTENTS%', $contents, $html );
			}
			
			// Generate Body
			$body = '';
			foreach ( $newsletter as $post ) {
				
				if ( ! isset( $post[ 'show_in_contents' ] ) )
					$post[ 'show_in_contents' ] = 'off';
				
				$post_body = $html_post;
				if ( 'spacer' == $post[ 'type' ] && 'on' == $post[ 'show_in_contents' ] )
					continue;
				
				// Spacer
				if ( 'spacer' == $post[ 'type' ] && isset( $post[ 'content_html' ] ) )
					$body .= $post[ 'content_html' ];
				
				// The Post
				if ( 'post' == $post[ 'type' ]  ) {
					
					// The Post
					$the_post = get_post( $post[ 'id' ] );
					
					// Replace date
					$post_body = str_replace( '%DATE%', get_the_time( get_option( 'date_format' ), $post[ 'id' ] ), $post_body );
					
					// Replace Link
					$post_body = str_replace( '%LINK%', get_permalink( $post[ 'id' ] ), $post_body );
					
					// Replace Link name
					$post_body = str_replace( '%LINK_NAME%', '<a name="' . $post[ 'id' ] . '"></a>', $post_body );
					
					// Replace color
					if ( isset( $color ) && $color == 'even' ) {
						$the_color = $html_params[ 'color_even' ];
						$color = 'odd';
					} else {
						$the_color = $html_params[ 'color_odd' ];
						$color = 'even';
					}
					$post_body = str_replace( '%COLOR%', $the_color, $post_body );
					
					// Replace Author
					$author = get_userdata( $the_post->post_author );
					$post_body = str_replace( '%AUTHOR%', $author->data->display_name, $post_body );
					
					// Replace title
					if ( 'on' == get_post_meta( $post[ 'id' ], 'donot_show_title' ) )
						$post_body = str_replace( '%TITLE%', '', $post_body );
					else
						$post_body = str_replace( '%TITLE%', get_the_title( $post[ 'id' ] ), $post_body );
					
					// Replace Post Thumbnail
					if ( isset( $params[ 'post_thumbnails' ] ) )
						$show_post_thumbnail = $params[ 'post_thumbnails' ];
					else
						$show_post_thumbnail = '';
						
					if ( '' != get_post_meta( $post[ 'id' ], 'show_post_thumbnail', TRUE ) )
						$show_post_thumbnail = get_post_meta( $post[ 'id' ], 'show_post_thumbnail', TRUE );
					
					if ( 'on' == $show_post_thumbnail && has_post_thumbnail( $post[ 'id' ] ) ) {
						$thumbnail_id = get_post_thumbnail_id( $post[ 'id' ] );
						$post_body = str_replace( '%THUMBNAIL%', get_the_post_thumbnail( $post[ 'id' ], 'thumbnail', array( 'align' => 'left', 'vspace' => '5', 'hspace' => '5' ) ), $post_body );
					} else {
						$post_body = str_replace( '%THUMBNAIL%', '', $post_body );
					}
					
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
						$post_body = str_replace( '%CONTENT%', wpautop( do_shortcode( $the_post->post_content ) ), $post_body );
					} else if ( 'on' == $show_excerpt ) {
						$post_body = str_replace( '%CONTENT%', $the_post->post_excerpt, $post_body );
					} else if ( 'on' == $show_link ) {
						$post_body = str_replace( '%CONTENT%', '<a href="' . get_permalink( $post[ 'id' ] ) . '">' . __( 'Read the post online', parent::$textdomain ) . '</a>', $post_body );
					} else {
						$post_body = str_replace( '%CONTENT%', '', $post_body );
					}

					// Replace Custom Fields
					preg_match_all( '~%CUSTOM_FIELD\s*\[key=([\'"])(?P<key>[^\1]*)\1\s*label=([\'"])(?P<label>[^\3]*)\3\s*\]~Ui', $post_body, $matches );
					foreach ( $matches[ 0 ] as $key => $string_to_replace ) {
						
						// Get postmeta
						$meta = get_post_meta( $post[ 'id' ], $matches[ 'key' ][ $key ], TRUE );
						if ( $meta )
							$string = '<br />' . $matches[ 'label' ][ $key ] . $meta;
						else
							$string = '';
						
						$post_body = str_replace( $string_to_replace . '%', $string, $post_body );
					}
					
					$body .= $post_body;
				}
			}
			$html = str_replace( '%BODY%', $body, $html );
			
			return $html;
		}
	}
}