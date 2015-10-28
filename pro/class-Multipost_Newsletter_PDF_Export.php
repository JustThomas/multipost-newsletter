<?php
/**
 * Feature Name:	Multipost Newsletter PDF Exporter
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

if ( ! class_exists( 'Multipost_Newsletter_PDF_Export' ) ) {
	
	class Multipost_Newsletter_PDF_Export extends Multipost_Newsletter {

		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @access	private
		 * @static
		 * @var		NULL | Multipost_Newsletter_PDF_Export
		 */
		private static $instance = NULL;

		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @return	Multipost_Newsletter_PDF_Export
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
		public function __construct() {
			
		}
		
		/**
		 * Parse content html
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option
		 * @return	void
		 */
		public function parsehtml( $pdf_object, $content ) {
			
			// Options
			$pdf_options = get_option( 'mp-newsletter-pdf' );
			
			$p = split( '<', $content );
			for ( $i = 0; $i < count( $p ); $i++ ) {
				if ( isset( $p[ $i ][ 0 ] ) ) {
					switch( $p[ $i ][ 0 ] ) {
						case 'a':
							$act_tag = 'a';
							$pdf_object->pdf->SetTextColor( $pdf_options[ 'link_color_red' ], $pdf_options[ 'link_color_green' ], $pdf_options[ 'link_color_blue' ] );
							$pos = stripos( $p[ $i ], 'href="' );
							$pos2 = stripos( $p[ $i ], '"', $pos + 8 );	
							$link = substr( $p[ $i ], $pos + 6, $pos2 - ( $pos + 6 ) );
							$pos = stripos( $p[ $i ], '>' );
							$tmp = substr( $p[ $i ], $pos + 1 );
							$pdf_object->pdf->Write( 9, $tmp, $link );
							$pdf_object->pdf->SetTextColor( $pdf_options[ 'content_font_color_red' ], $pdf_options[ 'content_font_color_green' ], $pdf_options[ 'content_font_color_blue' ] );
							break;
						case '/':
							$pos = strpos( $p[ $i ], '>' );
							$tmp = substr( $p[ $i ], $pos + 1 );
							if ( strlen( $tmp ) > 0 )
								$pdf_object->pdf->Write( 9, $tmp );
							break;
						default:
							$pdf_object->pdf->Write( 9, $p[ $i ] );
							break;
					}
				}
			}
		}
		
		/**
		 * Adds an article to the pdf
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, __
		 * @return	void
		 */
		public function add_article( $pdf_object, $args = array() ) {
			
			// Options
			$pdf_options = get_option( 'mp-newsletter-pdf' );
			$params = get_option( 'mp-newsletter-template-params' );
			
			$pos_y = $pdf_object->pdf->GetY();
			if ( $pos_y > 250 ) {
				$pdf_object->pdf->AddPage();
				$pos_y = $pdf_object->pdf->GetY() + 10;
			}
			$pdf_object->pdf->SetXY( 10, $pos_y );
			
			if ( ( isset( $args[ 'title' ] ) && '' != $args[ 'title' ] ) || ( isset( $args[ 'type' ] ) && 'post' == $args[ 'type' ] ) ) {
				// Title
				$title = utf8_decode( $args[ 'title' ] );
				$title = html_entity_decode( $title );
				$pdf_object->pdf->SetFont( $pdf_options[ 'content_headline_font' ], '', $pdf_options[ 'content_headline_font_size' ] );
				$pdf_object->pdf->SetTextColor( $pdf_options[ 'content_headline_font_color_red' ], $pdf_options[ 'content_headline_font_color_green' ], $pdf_options[ 'content_headline_font_color_blue' ] );
				$pdf_object->pdf->Write( 12, $title );
				$pdf_object->pdf->LN();
			}
			
			// Add the post thumbnail
			if ( isset( $params[ 'post_thumbnails' ] ) && 'on' == $params[ 'post_thumbnails' ] && isset( $args[ 'thumbnail' ] ) && '' != $args[ 'thumbnail' ] ) {
					
				$pos_y = $pdf_object->pdf->GetY();
				if ( $pos_y > 210 ) {
					$pdf_object->pdf->AddPage();
					$pos_y = $pdf_object->pdf->GetY();
				}
				$pdf_object->pdf->Image( $args[ 'thumbnail' ], 11, $pos_y + 2, 0, 50 );
				$pdf_object->pdf->SetXY( 10, $pos_y + 55 );
			}
			
			// Prepare Content
			$content = utf8_decode( $args[ 'content' ] );
			$content = html_entity_decode( $content, ENT_QUOTES );
			$content = strip_shortcodes( $content );
			$content = str_replace( '<!--more-->', '', $content );
			$content = wp_kses( $content, array( 'a' => array( 'href' => array (), 'title' => array () ) ) );
			
			$pdf_object->pdf->SetFont( $pdf_options[ 'content_font' ], '', $pdf_options[ 'content_font_size' ] );
			$pdf_object->pdf->SetTextColor( $pdf_options[ 'content_font_color_red' ], $pdf_options[ 'content_font_color_green' ], $pdf_options[ 'content_font_color_blue' ] );
			$this->parsehtml( $pdf_object, $content );
			
			if ( isset( $params[ 'post_thumbnails' ] ) && 'on' == $params[ 'post_thumbnails' ] && isset( $args[ 'thumbnail' ] ) && '' != $args[ 'thumbnail' ] ) {
				if ( isset( $args[ 'thumbnail_source' ] ) && '' != $args[ 'thumbnail_source' ] ) {
					$pdf_object->pdf->SetFont( $pdf_options[ 'content_footer_font' ], '', $pdf_options[ 'content_footer_font_size' ] );
					$pdf_object->pdf->SetTextColor( $pdf_options[ 'content_footer_font_color_red' ], $pdf_options[ 'content_footer_font_color_green' ], $pdf_options[ 'content_footer_font_color_blue' ] );
					$pdf_object->pdf->Write( 8, $args[ 'thumbnail_source' ] );
					$pdf_object->pdf->LN();
				}
			}
			
			// Subtext
			if ( isset( $pdf_options[ 'content_footer_show' ] ) && 'on' == $pdf_options[ 'content_footer_show' ] && 'post' == $args[ 'type' ] ) {
				$pdf_object->pdf->LN();
				$pdf_object->pdf->SetFont( $pdf_options[ 'content_footer_font' ], '', $pdf_options[ 'content_footer_font_size' ] );
				$pdf_object->pdf->SetTextColor( $pdf_options[ 'content_footer_font_color_red' ], $pdf_options[ 'content_footer_font_color_green' ], $pdf_options[ 'content_footer_font_color_blue' ] );
				$pdf_object->pdf->Write( 8, utf8_decode( __( 'Written by ' , parent::$textdomain ) ) . $args[ 'author' ] . utf8_decode( __( ' published at ', parent::$textdomain ) ) . $args[ 'date' ] );
				$pdf_object->pdf->LN();
			}
			
			// Set positions
			$pos_y = $pdf_object->pdf->GetY() + 10;
			$pdf_object->pdf->SetY( $pos_y );
		}
		
		/**
		 * Save the pdf
		 *
		 * @since	0.1
		 * @access	public
		 * @return	void
		 */
		public function save( $pdf_object, $filename ) {
			
			$pdf_object->pdf->Output( $filename, 'F' );
			$pdf_object->pdf->Close();
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_PDF_Export::get_instance();
}

if ( ! class_exists( 'Multipost_Newsletter_PDF' ) ) {
	
	// First, we need FPDF
	if ( file_exists( dirname( __FILE__ ) . '/../inc/fpdf/fpdf.php' ) )
		require_once dirname( __FILE__ ) . '/../inc/fpdf/fpdf.php';
	
	class Multipost_Newsletter_PDF extends FPDF {
		
		/**
		 * First Page Holder
		 *
		 * @since	0.1
		 * @access	public
		 * @var		boolean
		 */
		public $first_page = TRUE;
		
		/**
		 * Header Method to set the header of the pdf
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, date_i18n
		 * @return	void
		 */
		public function Header() {
			
			// Options
			$pdf_options = get_option( 'mp-newsletter-pdf' );
			
			if ( TRUE == $this->first_page ) {
				
				// Logo
				if ( isset( $pdf_options[ 'logo' ] ) && '' != $pdf_options[ 'logo' ] )
					$this->Image( $pdf_options[ 'logo' ], $pdf_options[ 'logo_x' ], $pdf_options[ 'logo_y' ] );
				
				// Headline
				$this->SetXY( $pdf_options[ 'headline_x' ], $pdf_options[ 'headline_y' ] );
				$this->SetFont( $pdf_options[ 'headline_font' ], '', $pdf_options[ 'headline_font_size' ] );
				$this->SetTextColor( $pdf_options[ 'headline_font_color_red' ], $pdf_options[ 'headline_font_color_green' ], $pdf_options[ 'headline_font_color_blue' ] );
				$this->Cell( 0, 21.8, get_bloginfo( 'name' ) );
				$this->LN();
				
				// Description
				if ( isset( $pdf_options[ 'description_show' ] ) && 'on' == $pdf_options[ 'description_show' ] ) {
					$this->SetXY( $pdf_options[ 'description_x' ], $pdf_options[ 'description_y' ] );
					$this->SetFont( $pdf_options[ 'description_font' ], '', $pdf_options[ 'description_font_size' ] );
					$this->SetTextColor( $pdf_options[ 'description_font_color_red' ], $pdf_options[ 'description_font_color_green' ], $pdf_options[ 'description_font_color_blue' ] );
					$this->Cell( 0, 6, get_bloginfo( 'description' ), 0, 0, 'L' );
				}
				
				// Subline
				if ( isset( $pdf_options[ 'subline_show' ] ) && 'on' == $pdf_options[ 'subline_show' ] ) {
					$this->SetXY( $pdf_options[ 'subline_x' ], $pdf_options[ 'subline_y' ] );
					$this->SetFont( $pdf_options[ 'subline_font' ], '', $pdf_options[ 'subline_font_size' ] );
					$this->SetTextColor( $pdf_options[ 'subline_font_color_red' ], $pdf_options[ 'subline_font_color_green' ], $pdf_options[ 'subline_font_color_blue' ] );
					$this->Cell( 50, 6, date_i18n( get_option( 'date_format' ) ), 0, 0, 'C' );
				}
				
				// New Line
				$this->LN();
				$this->SetLeftMargin( 10 );
				$this->SetXY( 10, 40 );
			} else {
				// Watermark
				if ( isset( $pdf_options[ 'watermark' ] ) && '' != $pdf_options[ 'watermark' ] )
					$this->Image( $pdf_options[ 'watermark' ], $pdf_options[ 'watermark_x' ], $pdf_options[ 'watermark_y' ] );
				
				// Top Bar
				$this->SetFillColor( $pdf_options[ 'header_subpages_red' ], $pdf_options[ 'header_subpages_green' ], $pdf_options[ 'header_subpages_blue' ] );
				$this->Rect( 0, 0, 250, $pdf_options[ 'header_subpages_height' ], 'F' );
			}
		}
		
		/**
		 * Footer Method to set the footer of the pdf
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option
		 * @return	void
		 */
		public function Footer() {
			
			// Options
			$pdf_options = get_option( 'mp-newsletter-pdf' );
			
			// Page Numbers
			$this->SetFont( $pdf_options[ 'page_numbers_font' ], '', $pdf_options[ 'page_numbers_font_size' ] );
			$this->SetTextColor( $pdf_options[ 'page_numbers_font_color_red' ], $pdf_options[ 'page_numbers_font_color_green' ], $pdf_options[ 'page_numbers_font_color_blue' ] );
			
			// Even pages
			if( $this->PageNo() % 2 ) {
				
				// Corner on right
				if ( isset( $pdf_options[ 'corner_bottom_left' ] ) && '' != $pdf_options[ 'corner_bottom_left' ] )
					$this->Image( $pdf_options[ 'corner_bottom_left' ], $pdf_options[ 'corner_bottom_left_x' ], $pdf_options[ 'corner_bottom_left_y' ] );
				
				// Show Page Number
				$this->SetXY( 195, 285 );
				$this->Cell( 0, 10, $this->PageNo() );
			} else {
				// Corner on left
				if ( isset( $pdf_options[ 'corner_bottom_right' ] ) && '' != $pdf_options[ 'corner_bottom_right' ] )
					$this->Image( $pdf_options[ 'corner_bottom_right' ], $pdf_options[ 'corner_bottom_right_x' ], $pdf_options[ 'corner_bottom_right_y' ] );
				
				$this->SetXY( 8,285 );
				$this->Cell( 0, 10, $this->PageNo() );
			}
			// Yeah, first page is ready
			$this->first_page = FALSE;
			
			// Reset Font Color
			$this->SetTextColor(256,256,256);
		}
	}
}