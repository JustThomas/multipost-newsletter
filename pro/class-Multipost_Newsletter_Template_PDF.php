<?php
/**
 * Feature Name:	Multipost Newsletter Template Page
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

if ( ! class_exists( 'Multipost_Newsletter_Template_PDF' ) ) {

	class Multipost_Newsletter_Template_PDF extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @access	private
		 * @static
		 * @since	0.1
		 * @var		NULL | Multipost_Newsletter_Template_PDF
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @access	public
		 * @static
		 * @since	0.1
		 * @return	Multipost_Newsletter_Template_PDF
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
		 * @uses	get_option, get_magic_quotes_gpc
		 * @return	void
		 */
		public function __construct() {
			
		}
		
		/**
		 * The PDF Template Tab
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @uses	get_option, _e, __, update_option
		 * @return	void
		 */
		public static function pdf_tab() {
			
			// Get Options
			$pdf_options = get_option( 'mp-newsletter-pdf' );
			
			// Base Font Setup
			$fonts = array(
				'arial'			=> 'Arial',
				'arialblack'	=> 'Arial Black',
				'booter'		=> 'Booter Five Zero',
				'black'			=> 'Blackbeard',
				'calligra'		=> 'Calligra',
				'couriernew'	=> 'Courier New',
				'georgia'		=> 'Georgia',
				'impact'		=> 'Impact',
				'verdana'		=> 'Verdana',
			);
			
			if ( isset( $_POST[ 'save_pdf_template' ] ) ) {
					
				update_option( 'mp-newsletter-pdf', $_POST[ 'pdf' ] );
			
				// Replace POST array
				$pdf_options = $_POST[ 'pdf' ];
			
				?>
				<div class="updated">
					<p>
						<?php _e( 'Template has been saved.', parent::$textdomain ); ?>
					</p>
				</div>
				<?php
			}
			
			$settings = array(
				'headline'			=> __( 'Headline', parent::$textdomain ),
				'description'		=> __( 'Description', parent::$textdomain ),
				'subline'			=> __( 'Subline (the date is located there)', parent::$textdomain ),
				'content_headline'	=> __( 'Content Headline', parent::$textdomain ),
				'content'			=> __( 'Content', parent::$textdomain ),
				'content_footer'	=> __( 'Content Footer (Authordata, Category, Postdate)', parent::$textdomain ),
				'page_numbers'		=> __( 'Page Numbers', parent::$textdomain )
			);
			$hidden_show = array( 'headline', 'content_headline', 'content' );
			$dont_show_positions = array( 'content_headline', 'content', 'content_footer', 'page_numbers' );
			?>
			<form action="admin.php?page=mpnl_template&tab=pdf" method="post">
				<div id="settings" class="postbox">
					<h3 class="hndle"><span><?php _e( 'PDF Template', parent::$textdomain ); ?></span></h3>
					<div class="inside">
						<p><?php _e( 'The Multipost Newsletter uses FPDF to generate the PDF dynamically. Due to this we have some restrictions to template the PDF. In future we are planning a feature to make this easier and better.', parent::$textdomain ); ?></p>
						
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[dont_generate_pdf]"><?php _e( 'Don\'t generate PDF', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[dont_generate_pdf]" name="pdf[dont_generate_pdf]" type="checkbox" tabindex="1" <?php if ( isset( $pdf_options[ 'dont_generate_pdf' ] ) && 'on' == $pdf_options[ 'dont_generate_pdf' ] ) { echo 'checked="checked"'; } ?> />
									</td>
								</tr>
							</tbody>
						</table>
						
						<?php foreach ( $settings as $setting => $label ) { ?>
						
							<h4><?php echo $label; ?></h4>
							<table class="form-table">
								<tbody>
									<?php if ( ! in_array( $setting, $hidden_show ) ) { ?>
										<tr valign="top">
											<th scope="row">
												<label for="pdf[<?php echo $setting; ?>_show]"><?php echo sprintf( __( 'Show %s', parent::$textdomain ), $label ); ?>:</label>
											</th>
											<td>
												<input id="pdf[<?php echo $setting; ?>_show]" name="pdf[<?php echo $setting; ?>_show]" type="checkbox" tabindex="3" <?php if ( isset( $pdf_options[ $setting . '_show' ] ) && 'on' == $pdf_options[ $setting . '_show' ] ) { echo 'checked="checked"'; } ?> />
											</td>
										</tr>
									<?php } ?>
									<tr valign="top">
										<th scope="row">
											<label for="pdf[<?php echo $setting; ?>_font]"><?php _e( 'Font', parent::$textdomain ); ?>:</label>
										</th>
										<td>
											<select name="pdf[<?php echo $setting; ?>_font]" id="pdf[<?php echo $setting; ?>_font]" tabindex="1">
												<?php foreach ( $fonts as $font => $name ) { ?>
													<option <?php if ( $font == $pdf_options[ $setting . '_font' ] ) echo 'selected="selected"'; ?> value="<?php echo $font; ?>"><?php echo $name; ?></option>
												<?php } ?>
											</select>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">
											<label for="pdf[<?php echo $setting; ?>_font_size]"><?php _e( 'Font Size', parent::$textdomain ); ?>:</label>
										</th>
										<td>
											<input id="pdf[<?php echo $setting; ?>_font_size]" name="pdf[<?php echo $setting; ?>_font_size]" type="text" value="<?php echo $pdf_options[ $setting . '_font_size' ]; ?>" tabindex="2" /><br />
											<span class="description"><?php _e( 'Note: FPDF uses pt', parent::$textdomain ); ?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">
											<label for="pdf[<?php echo $setting; ?>_font_color]"><?php _e( 'Font Color', parent::$textdomain ); ?>:</label>
										</th>
										<td>
											<input id="pdf[<?php echo $setting; ?>_font_color]" name="pdf[<?php echo $setting; ?>_font_color_red]" type="text" value="<?php echo $pdf_options[ $setting . '_font_color_red' ]; ?>" class="small-text" tabindex="3" /> <?php _e( 'Red', parent::$textdomain ); ?><br />
											<input id="pdf[<?php echo $setting; ?>_font_color]" name="pdf[<?php echo $setting; ?>_font_color_green]" type="text" value="<?php echo $pdf_options[ $setting . '_font_color_green' ]; ?>" class="small-text" tabindex="4" /> <?php _e( 'Green', parent::$textdomain ); ?><br />
											<input id="pdf[<?php echo $setting; ?>_font_color]" name="pdf[<?php echo $setting; ?>_font_color_blue]" type="text" value="<?php echo $pdf_options[ $setting . '_font_color_blue' ]; ?>" class="small-text" tabindex="5" /> <?php _e( 'Blue', parent::$textdomain ); ?><br />
											<span class="description"><?php _e( 'Here, we are using the RGB color model', parent::$textdomain ); ?></span>
										</td>
									</tr>
									<?php if ( ! in_array( $setting, $dont_show_positions ) ) { ?>
										<tr valign="top">
											<th scope="row">
												<label for="pdf[<?php echo $setting; ?>_x]"><?php _e( 'Position X', parent::$textdomain ); ?>:</label>
											</th>
											<td>
												<input id="pdf[<?php echo $setting; ?>_x]" name="pdf[<?php echo $setting; ?>_x]" type="text" value="<?php echo $pdf_options[ $setting . '_x' ]; ?>" tabindex="1" class="small-text" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row">
												<label for="pdf[<?php echo $setting; ?>_y]"><?php _e( 'Position Y', parent::$textdomain ); ?>:</label>
											</th>
											<td>
												<input id="pdf[<?php echo $setting; ?>_y]" name="pdf[<?php echo $setting; ?>_y]" type="text" value="<?php echo $pdf_options[ $setting . '_y' ]; ?>" tabindex="1" class="small-text" />
											</td>
										</tr>
									<?php } ?>
									
								</tbody>
							</table>
						<?php } ?>
						<p><?php _e( 'We also offer a neat way to place some decoration images in the PDF. Just insert the full path (not URL!) to the images here', parent::$textdomain ); ?>
						<h4><?php _e( 'Logo', parent::$textdomain ); ?></h4>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[logo]"><?php _e( 'Full path to logo', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[logo]" name="pdf[logo]" type="text" value="<?php echo $pdf_options[ 'logo' ]; ?>" tabindex="1" class="regular-text" /><br />
										<span class="description"><?php _e( 'You can find the path under Media -> Overview -> Edit Media File', parent::$textdomain ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[logo_x]"><?php _e( 'Position X', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[logo_x]" name="pdf[logo_x]" type="text" value="<?php echo $pdf_options[ 'logo_x' ]; ?>" tabindex="1" class="small-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[logo_y]"><?php _e( 'Position Y', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[logo_y]" name="pdf[logo_y]" type="text" value="<?php echo $pdf_options[ 'logo_y' ]; ?>" tabindex="1" class="small-text" />
									</td>
								</tr>
							</tbody>
						</table>
						
						<h4><?php _e( 'Watermark', parent::$textdomain ); ?></h4>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[watermark]"><?php _e( 'Full path to watermark', parent::$textdomain ); ?>:</label><br />
										<span class="description"><?php _e( 'This image is placed on the background on every page except the first', parent::$textdomain ); ?></span>
									</th>
									<td>
										<input id="pdf[watermark]" name="pdf[watermark]" type="text" value="<?php echo $pdf_options[ 'watermark' ]; ?>" tabindex="1" class="regular-text" /><br />
										<span class="description"><?php _e( 'You can find the path under Media -> Overview -> Edit Media File', parent::$textdomain ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[watermark_x]"><?php _e( 'Position X', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[watermark_x]" name="pdf[watermark_x]" type="text" value="<?php echo $pdf_options[ 'watermark_x' ]; ?>" tabindex="1" class="small-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[wartermark_y]"><?php _e( 'Position Y', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[watermark_y]" name="pdf[watermark_y]" type="text" value="<?php echo $pdf_options[ 'watermark_y' ]; ?>" tabindex="1" class="small-text" />
									</td>
								</tr>
							</tbody>
						</table>
						
						<h4><?php _e( 'Corner Bottom Left', parent::$textdomain ); ?></h4>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[corner_bottom_right]"><?php _e( 'Full path to corner bottom right (odd pages)', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[corner_bottom_right]" name="pdf[corner_bottom_right]" type="text" value="<?php echo $pdf_options[ 'corner_bottom_right' ]; ?>" tabindex="1" class="regular-text" /><br />
										<span class="description"><?php _e( 'You can find the path under Media -> Overview -> Edit Media File', parent::$textdomain ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[corner_bottom_right_x]"><?php _e( 'Position X', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[corner_bottom_right_x]" name="pdf[corner_bottom_right_x]" type="text" value="<?php echo $pdf_options[ 'corner_bottom_right_x' ]; ?>" tabindex="1" class="small-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[corner_bottom_right_y]"><?php _e( 'Position Y', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[corner_bottom_right_y]" name="pdf[corner_bottom_right_y]" type="text" value="<?php echo $pdf_options[ 'corner_bottom_right_y' ]; ?>" tabindex="1" class="small-text" />
									</td>
								</tr>
							</tbody>
						</table>
						
						<h4><?php _e( 'Corner Bottom Right', parent::$textdomain ); ?></h4>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[corner_bottom_left]"><?php _e( 'Full path to corner bottom left (even pages)', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[corner_bottom_left]" name="pdf[corner_bottom_left]" type="text" value="<?php echo $pdf_options[ 'corner_bottom_left' ]; ?>" tabindex="1" class="regular-text" /><br />
										<span class="description"><?php _e( 'You can find the path under Media -> Overview -> Edit Media File', parent::$textdomain ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[corner_bottom_left_x]"><?php _e( 'Position X', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[corner_bottom_left_x]" name="pdf[corner_bottom_left_x]" type="text" value="<?php echo $pdf_options[ 'corner_bottom_left_x' ]; ?>" tabindex="1" class="small-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[corner_bottom_right_y]"><?php _e( 'Position Y', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[corner_bottom_left_y]" name="pdf[corner_bottom_left_y]" type="text" value="<?php echo $pdf_options[ 'corner_bottom_left_y' ]; ?>" tabindex="1" class="small-text" />
									</td>
								</tr>
							</tbody>
						</table>
						
						<h4><?php _e( 'Other Stuff', parent::$textdomain ); ?></h4>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[header_subpages_height]"><?php _e( 'Height of the Topbar on every page except the first', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[header_subpages_height]" name="pdf[header_subpages_height]" type="text" value="<?php echo $pdf_options[ 'header_subpages_height' ]; ?>" tabindex="1" class="small-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[header_subpages]"><?php _e( 'Color of the Topbar on every page except the first', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[header_subpages]" name="pdf[header_subpages_red]" type="text" value="<?php echo $pdf_options[ 'header_subpages_red' ]; ?>" class="small-text" tabindex="3" /> <?php _e( 'Red', parent::$textdomain ); ?><br />
										<input id="pdf[header_subpages]" name="pdf[header_subpages_green]" type="text" value="<?php echo $pdf_options[ 'header_subpages_green' ]; ?>" class="small-text" tabindex="4" /> <?php _e( 'Green', parent::$textdomain ); ?><br />
										<input id="pdf[header_subpages]" name="pdf[header_subpages_blue]" type="text" value="<?php echo $pdf_options[ 'header_subpages_blue' ]; ?>" class="small-text" tabindex="5" /> <?php _e( 'Blue', parent::$textdomain ); ?><br />
										<span class="description"><?php _e( 'Here, we are using the RGB color model', parent::$textdomain ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[link_color]"><?php _e( 'Link color', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[link_color]" name="pdf[link_color_red]" type="text" value="<?php echo $pdf_options[ 'link_color_red' ]; ?>" class="small-text" tabindex="3" /> <?php _e( 'Red', parent::$textdomain ); ?><br />
										<input id="pdf[link_color]" name="pdf[link_color_green]" type="text" value="<?php echo $pdf_options[ 'link_color_green' ]; ?>" class="small-text" tabindex="4" /> <?php _e( 'Green', parent::$textdomain ); ?><br />
										<input id="pdf[link_color]" name="pdf[link_color_blue]" type="text" value="<?php echo $pdf_options[ 'link_color_blue' ]; ?>" class="small-text" tabindex="5" /> <?php _e( 'Blue', parent::$textdomain ); ?><br />
										<span class="description"><?php _e( 'Here, we are using the RGB color model', parent::$textdomain ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="pdf[excerpt]"><?php _e( 'Content as Excerpt', parent::$textdomain ); ?>:</label>
									</th>
									<td>
										<input id="pdf[excerpt]" name="pdf[excerpt]" type="checkbox" tabindex="3" <?php if ( isset( $pdf_options[ 'excerpt' ] ) && 'on' == $pdf_options[ 'excerpt' ] ) { echo 'checked="checked"'; } ?> />
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<input name="save_pdf_template" type="submit" class="button-primary" tabindex="9" value="<?php _e( 'Save Changes', parent::$textdomain ); ?>" style="float: right;" />
				<br class="clear" />
			</form>
			<?php
		}
	}
}