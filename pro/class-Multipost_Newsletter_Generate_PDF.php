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

if ( ! class_exists( 'Multipost_Newsletter_Generate_PDF' ) ) {

	class Multipost_Newsletter_Generate_PDF extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @access	private
		 * @static
		 * @var		NULL | Multipost_Newsletter_Generate_PDF
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @return	Multipost_Newsletter_Generate_PDF
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
		 * Generate the pdf
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @param	string $edition current edition
		 * @uses	Multipost_Newsletter_PDF, get_option, get_term_by, date_i18n, __,
		 * 			get_permalink, has_post_thumbnail, get_post_thumbnail_id, get_the_post_thumbnail,
		 * 			get_post_meta, get_the_time, get_post, get_the_title,
		 * @return	object $pdf The PDF object
		 */
		public static function generate_pdf( $edition, $letter_slug ) {
			
			// Options
			$pdf_options = get_option( 'mp-newsletter-pdf' );
			$params = get_option( 'mp-newsletter-template-params' );
			$text_params = get_option( 'mp-newsletter-text-params' );
			
			// Set fpdf
			$pdf->pdf = new Multipost_Newsletter_PDF( 'P','mm','A4' );
			$pdf->pdf->source_path = dirname( __FILE__ ) . '/../inc/fpdf/fpdf.php';

			// Add fonts
			$pdf->pdf->AddFont( 'arial', '', dirname( __FILE__ ) . '/../inc/fpdf/arial.php' );
			$pdf->pdf->AddFont( 'arialblack', '', dirname( __FILE__ ) . '/../inc/fpdf/arialblack.php' );
			$pdf->pdf->AddFont( 'booter', '', dirname( __FILE__ ) . '/../inc/fpdf/booter.php' );
			$pdf->pdf->AddFont( 'black', '', dirname( __FILE__ ) . '/../inc/fpdf/black.php' );
			$pdf->pdf->AddFont( 'calligra', '', dirname( __FILE__ ) . '/../inc/fpdf/calligra.php' );
			$pdf->pdf->AddFont( 'couriernew', '', dirname( __FILE__ ) . '/../inc/fpdf/couriernew.php' );
			$pdf->pdf->AddFont( 'georgia', '', dirname( __FILE__ ) . '/../inc/fpdf/georgia.php' );
			$pdf->pdf->AddFont( 'impact', '', dirname( __FILE__ ) . '/../inc/fpdf/impact.php' );
			$pdf->pdf->AddFont( 'verdana', '', dirname( __FILE__ ) . '/../inc/fpdf/verdana.php' );
			$pdf->pdf->AddPage();
			
			// Get PDF Class
			$pdf_export = Multipost_Newsletter_PDF_Export::get_instance();
			
			// Generate Content
			$newsletter = get_option( 'newsletter_' . $letter_slug );
			
			// Add Intro
			$post_args = array(
				'type'		=> 'misc',
				'content'	=> $params[ 'header' ]
			);
			$pdf_export->add_article( $pdf, $post_args );
			
			// Generate Contents
			if ( isset( $params[ 'contents' ] ) && 'on' == $params[ 'contents' ] ) {
			
				$contents  = "\n\n" . __( 'Contents', parent::$textdomain ) . "\n\n";
				foreach ( $newsletter as $post ) {
					
					if ( 'spacer' == $post[ 'type' ] && 'off' == $post[ 'show_in_contents' ] )
						continue;
					
					if ( 'spacer' == $post[ 'type' ] && 'on' == $post[ 'dont_show_in_pdf' ] )
						continue;
					
					if ( 'on' == get_post_meta( $post[ 'id' ], 'dont_show_in_pdf', TRUE ) )
						continue;
			
					if ( 'post' == $post[ 'type' ] )
						$contents .= '   ' . get_the_title( $post[ 'id' ] ) . "\n";
			
					if ( 'spacer' == $post[ 'type' ] )
						$contents .= '   ' . $post[ 'content_text' ];
				}
				
				// Add Intro
				$post_args = array(
					'type'		=> 'misc',
					'content'	=> html_entity_decode( $contents )
				);
				$pdf_export->add_article( $pdf, $post_args );
			}
			
			foreach ( $newsletter as $post ) {
				
				$post_args = array();
				
				// Spacer
				if ( 'spacer' == $post[ 'type' ] && isset( $post[ 'show_in_contents' ] ) && 'on' == $post[ 'show_in_contents' ] )
					continue;
				
				if ( 'spacer' == $post[ 'type' ] && isset( $post[ 'dont_show_in_pdf' ] ) && 'on' == $post[ 'dont_show_in_pdf' ] )
					continue;
				
				if ( 'on' == get_post_meta( $post[ 'id' ], 'dont_show_in_pdf', TRUE ) )
					continue;
					
				if ( 'spacer' == $post[ 'type' ] && isset( $post[ 'content_html' ] ) ) {
					$post_args[ 'type' ] = 'spacer';
					$post_args[ 'content' ] = $post[ 'content_html' ];
				} else {
					
					$post_args[ 'type' ] = 'post';
					
					$the_post = get_post( $post[ 'id' ] );
					
					// Title
					if ( 'on' == get_post_meta( $post[ 'id' ], 'donot_show_title' ) )
						$post_args[ 'title' ] = '';
					else
						$post_args[ 'title' ] = get_the_title( $post[ 'id' ] );
					
					// Content
					if ( isset( $pdf_options[ 'excerpt' ] ) && 'on' == $pdf_options[ 'excerpt' ] ) {
						$show_content = 'off';
						$show_excerpt = 'on';
					} else {
						$show_content = 'on';
						$show_excerpt = 'off';
					}
					
					if ( '' != get_post_meta( $post[ 'id' ], 'show_pdf_content', TRUE ) ) {
						if ( 'full' == get_post_meta( $post[ 'id' ], 'show_pdf_content', TRUE ) ) {
							$show_content = 'on';
							$show_excerpt = 'off';
						} else if ( 'excerpt' == get_post_meta( $post[ 'id' ], 'show_pdf_content', TRUE ) ) {
							$show_content = 'off';
							$show_excerpt = 'on';
						}
					}
					
					if ( isset( $show_content ) && 'on' == $show_content ) {
						$post_args[ 'content' ] = $the_post->post_content;
					} else if ( isset( $show_excerpt ) && 'on' == $show_excerpt ) {
						$post_args[ 'content' ] = $the_post->post_excerpt;
					} else {
						$post_args[ 'content' ] = '';
					}
					
					// add post thumbnail (PATH!) with source
					if ( isset( $params[ 'post_thumbnails' ] ) )
						$show_post_thumbnail = $params[ 'post_thumbnails' ];
					else
						$show_post_thumbnail = 'off';
						
					if ( '' != get_post_meta( $post[ 'id' ], 'show_post_thumbnail', TRUE ) )
						$show_post_thumbnail = get_post_meta( $post[ 'id' ], 'show_post_thumbnail', TRUE );
						
					if ( 'on' == $show_post_thumbnail && has_post_thumbnail( $post[ 'id' ] ) ) {
						$thumbnail_id = get_post_thumbnail_id( $post[ 'id' ] );
						$attachment = get_post( $thumbnail_id );
						$post_args[ 'thumbnail' ] = get_attached_file( $thumbnail_id );
						
						// Source
						if ( '' != $attachment->post_content ) {
							$post_args[ 'thumbnail_source' ] = "\n" . __( 'Image Source: ', parent::$textdomain ) . $attachment->post_content;
						}
					}
					
					// Other Stuff
					$author = get_userdata( $the_post->post_author );
					$post_args[ 'date' ] = get_the_time( get_option( 'date_format' ), $post[ 'id' ] );
					$post_args[ 'author' ] = $author->data->display_name;
				}
				
				$pdf_export->add_article( $pdf, $post_args );
			}
			
			// Add Footer
			$post_args = array(
				'type'		=> 'misc',
				'content'	=> $params[ 'footer' ]
			);
			$pdf_export->add_article( $pdf, $post_args );
			
			// Upload Path
			$upload_dir = wp_upload_dir();
			$path = $upload_dir[ 'path' ];
			$url = $upload_dir[ 'url' ];
			
			// Cache
			if ( ! isset( $_POST[ 'send_newsletter' ] ) )
				$filename = $edition . '-' . time() . '.pdf';
			else
				$filename = $edition . '.pdf';
			$filepath = $path . '/' . $filename;
			$fileurl = $url . '/' . $filename;
			
			if ( ! isset( $_POST[ 'send_newsletter' ] ) ) {
				$cached_files = get_option( 'mp-newsletter-cached-files' );
				if ( ! is_array( $cached_files ) )
					$cached_files = array();
				$cached_files[] = $filepath;
				update_option( 'mp-newsletter-cached-files', $cached_files );
			} else {
				$cached_files = get_option( 'mp-newsletter-cached-files' );
				foreach ( $cached_files as $file ) {
					@unlink( $file );
				}
			}
			
			$pdf_export->save( $pdf, $filepath );
			
			// Generate Link
			$pdf->pdf_link = $fileurl;
			
			return $pdf;
		}
	}
}