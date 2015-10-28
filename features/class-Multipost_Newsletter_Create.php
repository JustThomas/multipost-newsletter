<?php
/**
 * Feature Name:	Multipost Newsletter Create
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com * Licence:			GPLv3
 * 
 * Changelog
 *
 * 0.1
 * - Initial Commit
 */

if ( ! class_exists( 'Multipost_Newsletter_Create' ) ) {

	class Multipost_Newsletter_Create extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @access	private
		 * @static
		 * @var		NULL | Multipost_Newsletter_Create
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @return	Multipost_Newsletter_Create
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
		public function __construct () {
			
			// Adding ajax send stuff
			add_filter( 'wp_ajax_send_newsletter', array( $this, 'send_newsletter' ) );
		}
		
		/**
		 * Display the newsletter generation page
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, screen_icon, _e, get_terms, 
		 * @return	void
		 */
		public function create_newsletter_page() {
			
			$self = self::get_instance();
			?>
			<div class="wrap">
				<?php screen_icon( parent::$textdomain ); ?>
				<h2><?php _e( 'Newsletter', parent::$textdomain ); ?> - <?php _e( 'Create Newsletter', parent::$textdomain ); ?></h2>
				
				<div id="poststuff" class="metabox-holder has-right-sidebar">
				
					<div id="side-info-column" class="inner-sidebar">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<div id="mp-newsletter-inpsyde" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Powered by', parent::$textdomain ); ?></span></h3>
								<div class="inside">
									<p style="text-align: center;"><a href="http://inpsyde.com"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../images/inpsyde_logo.png'; ?>" style="border: 7px solid #fff;" /></a></p>
									<p><?php _e( 'This plugin is powered by <a href="http://inpsyde.com">Inpsyde.com</a> - Your expert for WordPress, BuddyPress and bbPress.', parent::$textdomain ); ?></p>
								</div>
							</div>
						</div>
					</div>
					
					<div id="post-body">
						<div id="post-body-content">
							<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							
								<?php
								if ( isset( $_POST[ 'send_test_newsletter' ] ) || isset( $_POST[ 'send_newsletter' ] ) ) {
								
									if ( '' == $_POST[ 'edition' ] ) {
										?>
										<div class="error">
											<p><?php _e( 'You have to choose an edition to send the newsletter', parent::$textdomain ); ?></p>
										</div>
										<?php
									} else {
										return $self->send_test_newsletter( $_POST[ 'edition' ], $_POST[ 'subject_area' ] );
									}
								}
								?>
							
								<div id="settings" class="postbox">
									<h3 class="hndle"><span><?php _e( 'Choose Edition', parent::$textdomain ); ?></span></h3>
									<div class="inside">
										<form action="admin.php?page=mpnl_create" method="post">
										
											<select name="edition">
												<option value="0"><?php _e( 'Choose Edition', parent::$textdomain ) ?></option>
												<?php
													$args = array(
														'hide_empty'	=> FALSE,
														'orderby'		=> 'term_id',
														'order'			=> 'DESC'
													);
													$editions = get_terms( 'newsletter', $args );
													foreach ( $editions as $edition ) {
														if ( 0 != $edition->parent )
															continue;
														
														?>
														<option value="<?php echo $edition->slug; ?>"><?php echo $edition->name; ?></option>
														<?php
														$children_args = array(
															'hide_empty'	=> FALSE,
															'orderby'		=> 'term_id',
															'order'			=> 'DESC',
															'child_of'		=> $edition->term_id
														);
														$children = get_terms( 'newsletter', $children_args );
														if ( 0 < count( $children ) ) {
															?>
															<optgroup label="<?php echo $edition->name; ?>">
																<?php
																foreach ( $children as $child ) {
																	?>
																	<option value="<?php echo $child->slug; ?>"><?php echo $child->name; ?></option>
																	<?php
																}
																?>
															</optgroup>
														<?php
														}
													}
												?>
											</select>
											
											<?php
											$subject_area_args = array(
												'hide_empty'	=> FALSE,
												'orderby'		=> 'term_id',
												'order'			=> 'DESC'
											);
											$subject_areas = get_terms( 'subject-area', $subject_area_args );
											if ( ! empty( $subject_areas ) ) {
												?>
												<select name="subject_area">
													<option value="all"><?php _e( 'All Subject Areas', parent::$textdomain ) ?></option>
													<?php
														foreach ( $subject_areas as $subject_area ) {
															if ( 0 != $subject_area->parent )
																continue;
															
															?>
															<option value="<?php echo $subject_area->slug; ?>"><?php echo $subject_area->name; ?></option>
															<?php
															$children_args = array(
																'hide_empty'	=> FALSE,
																'orderby'		=> 'term_id',
																'order'			=> 'DESC',
																'child_of'		=> $subject_area->term_id
															);
															$children = get_terms( 'subject-area', $children_args );
															if ( 0 < count( $children ) ) {
																?>
																<optgroup label="<?php echo $subject_area->name; ?>">
																	<?php
																	foreach ( $children as $child ) {
																		?>
																		<option value="<?php echo $child->slug; ?>"><?php echo $child->name; ?></option>
																		<?php
																	}
																	?>
																</optgroup>
															<?php
															}
														}
													?>
												</select>
												<?php
											} else {
												?>
												<input type="hidden" name="subject_area" value="all" />
												<?php
											}
											?>
											
											<input name="send_test_newsletter" type="submit" class="button-primary" tabindex="6" value="<?php _e( 'Send Test Newsletter', parent::$textdomain ); ?>" style="float: right;" />
											<br class="clear" />
										</form>
									</div>
								</div>
								
							</div>
						</div>
					</div>
				
				</div>
			</div>
			<?php
		}
		
		/**
		 * Sends the testmail, this goes over mail()
		 *
		 * @since	0.1
		 * @access	public
		 * @param	string $edition
		 * @uses	get_option, Multipost_Newsletter_Generate, get_terms, __, _e
		 * @return	void
		 */
		public function send_test_newsletter( $edition, $subject_area ) {
			
			// Sett Letter Slug
			$letter_slug = $edition . '-' . $subject_area;
			
			// Check Edition
			$full_edition = get_terms( 'newsletter', array( 'slug' => $edition ) );
			// Abort if there is no edition
			if ( ! isset( $full_edition ) ) {
				$html = '<div class="updated"><p>
				' . __( 'You didnot choose an edition!', parent::$textdomain ) . '
				</p></div>';
				return $html;
			}
			$full_edition = $full_edition[ 0 ];
			
			// Get Params
			$params = get_option( 'mp-newsletter-params' );
			
			// Generate PDF
			if ( TRUE == parent::$is_pro ) {
				$pdf_options = get_option( 'mp-newsletter-pdf' );
				if ( ! isset( $pdf_options[ 'dont_generate_pdf' ] ) || $pdf_options[ 'dont_generate_pdf' ] != 'on' )
					$pdf = Multipost_Newsletter_Generate_PDF::generate_pdf( $edition, $letter_slug );
			}
				
			// Generate Text
			$text = Multipost_Newsletter_Generate_Text::generate_text( $edition, $letter_slug );
			
			if ( TRUE == parent::$is_pro ) {
				
				// Generate HTML
				$html = Multipost_Newsletter_Generate_HTML::generate_html( $edition, $letter_slug );
				
				if ( ! isset( $pdf_options[ 'dont_generate_pdf' ] ) || $pdf_options[ 'dont_generate_pdf' ] != 'on' ) {
					// Replace PDF Link
					$html = str_replace( '%PDF_LINK%', $pdf->pdf_link, $html );
					$text = str_replace( '%PDF_LINK%', $pdf->pdf_link, $text );
				}
			}
			
			// Subject
			$subject = __( 'Testmail: ', parent::$textdomain ) . $full_edition->name;
			
			// From
			$from = $params[ 'from_name' ] . '<' . $params[ 'from_mail' ] . '>';
			
			// Set Headers
			if ( TRUE == parent::$is_pro ) {
				$boundary = md5( $html . $text );
				$headers  = "MIME-Version: 1.0\r\n";
				$headers .= "From: " . $from . "\r\n";
				$headers .= "Content-Type: multipart/alternative; boundary = " . $boundary . "\r\n";
				
				// text version
				$body  = "\r\n--" . $boundary . "\r\n";
				$body .= "Content-Type: text/plain; charset=" . get_bloginfo( 'charset' ) . "\r\n";
				$body .= $text;
				
				// html version
				$body .= "\r\n--" . $boundary . "\r\n";
				$body .= "Content-Type: text/html; charset=" . get_bloginfo( 'charset' ) . "\r\n";
				$body .= $html;
				
			} else {
				$body = $text;
			}

			// Get Test-Recipients
			$test_recipients = explode( ',', $params[ 'to_test' ] );
			foreach ( $test_recipients as $email )
				mail( $email, $subject, $body, $headers );
			
			// Display Message
			$smtp_options = get_option( 'mp-newsletter-smtp' );
			?>
			
			<div class="updated"><p>
				<?php _e( 'Testmail has been send. If you want to send another one, just reload this page!', parent::$textdomain ); ?>
			</p></div>
				
			<div id="send_newsletter_box" class="postbox">
				<h3 class="hndle"><span><?php _e( 'Send Newsletter', parent::$textdomain ); ?></span></h3>
				<div class="inside">
					<form action="admin.php?page=mpnl_create" method="post">
						<input type="hidden" name="edition" id="edition" value="<?php echo $edition; ?>" />
						<input type="hidden" name="letter_slug" id="letter_slug" value="<?php echo $letter_slug; ?>" />
						<input type="hidden" name="subject_area" id="subject_area" value="<?php echo $subject_area; ?>" />
						<table class="form-table">
							<?php if ( TRUE == parent::$is_pro && ! empty( $smtp_options ) ) { ?>
							<tr>
								<th><label for="groups"><?php _e( 'Choose Sending API', parent::$textdomain ); ?></label></th>
								<td>
									<select name="api" id="api">
										<option value="wp"><?php _e( 'Send over WordPress', parent::$textdomain ) ?></option>
										<option value="smtp"><?php _e( 'Send over SMTP', parent::$textdomain ) ?></option>
									</select><br />
									<span class="description"><?php _e( 'Please chose an API to send the newsletter. If you really don\'t know what to chose, select <code>Send over WordPress</code>.', parent::$textdomain ); ?></span>
								</td>
							</tr>
							<?php } else { ?>
								<input type="hidden" name="api" id="api" value="wp" />
							<?php } ?>
							<?php
							$groups = get_option( 'mp-newsletter-groups', FALSE );
							if ( TRUE == parent::$is_pro && is_array( $groups ) && 0 < count( $groups ) ) { ?>
							<tr>
								<th><label for="groups"><?php _e( 'Recipient Groups', parent::$textdomain ); ?></label></th>
								<td>
									<select data-placeholder="<?php _e( 'Maybe chose recipient groups', parent::$textdomain ) ?>" id="groups" name="groups[]" style="width: 250px;" multiple class="chzn-select">
										<?php foreach ( $groups as $group ) { ?>
											<option value="<?php echo $group[ 0 ]; ?>"><?php echo $group[ 0 ]; ?></option>
										<?php } ?>
									</select><br />
									<span class="description"><?php _e( 'Here you are able to chose the recipient groups. The created newsletter only will be send to them.<br /><strong>Important:</strong> If you don\'t chose a group the newsletter will be send to all subscribers.', parent::$textdomain ); ?></span>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<th>&nbsp;</th>
								<td>
									<input type="submit" name="send_newsletter" id="send_newsletter" class="button-primary" value="<?php _e( 'Send Newsletter', parent::$textdomain ); ?>" />
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
			<div id="send_newsletter_box_response"></div>
			
			</div></div></div></div>
			<?php
		}
		
		/**
		 * Sends the newsletter
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, Multipost_Newsletter_Generate, get_terms,
		 * 			ABSPATH, WPINC, get_user_meta, 
		 * @global	$phpmailer The PHP Mailer
		 * @global	$wpdb WordPress Database Wrapper
		 * @return	void
		 */
		public function send_newsletter() {
			global $phpmailer, $wpdb;
			
			// POST
			$edition = $_POST[ 'edition' ];
			$letter_slug = $_POST[ 'letter_slug' ];
			
			// Offset
			$offset = $_POST[ 'offset' ];
			if ( ! isset( $offset ) )
				$offset = 0;
			
			// Get Options
			$params = get_option( 'mp-newsletter-params' );
			$smtp_options = get_option( 'mp-newsletter-smtp' );
				
			// Generate PDF
			if ( TRUE == parent::$is_pro )
				$pdf_options = get_option( 'mp-newsletter-pdf' );
				if ( ! isset( $pdf_options[ 'dont_generate_pdf' ] ) || $pdf_options[ 'dont_generate_pdf' ] != 'on' )
					$pdf = Multipost_Newsletter_Generate_PDF::generate_pdf( $edition, $letter_slug );
				
			// Generate Text
			$text = Multipost_Newsletter_Generate_Text::generate_text( $edition, $letter_slug );
			
			if ( TRUE == parent::$is_pro ) {
				// Generate HTML
				$html = Multipost_Newsletter_Generate_HTML::generate_html( $edition, $letter_slug );
				
				if ( ! isset( $pdf_options[ 'dont_generate_pdf' ] ) || $pdf_options[ 'dont_generate_pdf' ] != 'on' ) {
					// Replace PDF Link
					$html = str_replace( '%PDF_LINK%', $pdf->pdf_link, $html );
					$text = str_replace( '%PDF_LINK%', $pdf->pdf_link, $text );
				}
			}
			
			// SMTP and WP Mail
			if ( in_array( $_POST[ 'api' ], array( 'wp', 'smtp' ) ) ) {
				
				// Prepare Editiom
				$full_edition = get_terms( 'newsletter', array( 'slug' => $edition ) );
				$full_edition = $full_edition[ 0 ];
				
				// Groups query
				$groups_query = array();
				if ( $_POST[ 'groups' ] != 'null' )
					foreach ( $_POST[ 'groups' ] as $group )
						$groups_query[] = '`groups` LIKE "%' . $group . '%"';
				
				$groups_query_string = '';
				if ( ! empty( $groups_query ) ) {
					$groups_query_string .= 'AND (';
					$groups_query_string .= implode( ' OR ', $groups_query );
					$groups_query_string .= ')';
				}
				
				// Subject Area Query
				if ( $_POST[ 'subject_area' ] != 'all' )
					$subject_area_query = 'AND ( `subjectareas` LIKE "%' . $_POST[ 'subject_area' ] . '%" )';
				else
					$subject_area_query = '';
				
				// Recipient Queue
				$query = 'SELECT SQL_CALC_FOUND_ROWS ' . $wpdb->prefix . 'mpnl_recipients.*
							FROM ' . $wpdb->prefix . 'mpnl_recipients
							WHERE 1=1 AND (
								`activated` = "1"
								' . $subject_area_query . '
								' . $groups_query_string . '
							) ORDER BY id ASC
							LIMIT ' . $offset . ', 25';
				
				$recipients = $wpdb->get_results( $query );
				$found = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
				
				if ( count( $recipients ) > 0 ) {
					
					echo json_encode( array( 'found' => $found, 'offset' => $offset, 'count' => count( $recipients ) ) );
					
					foreach ( $recipients as $recipient ) {
						
						// Replace Unsubscribe Link
						$html = str_replace( '%UNSUBSCRIBELINK%', get_bloginfo( 'url' ) . '/mpnl-unsubscribe/?key=' . $recipient->key, $html );
						$text = str_replace( '%UNSUBSCRIBELINK%', get_bloginfo( 'url' ) . '/mpnl-unsubscribe/?key=' . $recipient->key, $text );
						
						// Prepare PHPMail
						if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
							require_once ABSPATH . WPINC . '/class-phpmailer.php';
							require_once ABSPATH . WPINC . '/class-smtp.php';
						}
						$phpmailer = new PHPMailer( true );
						$phpmailer->CharSet = 'UTF-8';
						
						// Send over SMTP?
						if ( TRUE == parent::$is_pro && 'smtp' == $_POST[ 'api' ] ) {
							$phpmailer->IsSMTP();
							$phpmailer->SMTPAuth = true;
							$phpmailer->Host     = $smtp_options[ 'host' ];
							$phpmailer->Port 	 = $smtp_options[ 'port' ];
							$phpmailer->Username = $smtp_options[ 'user' ];
							$phpmailer->Password = $smtp_options[ 'pass' ];
						} else {
							// Set to use PHP's mail()
							$phpmailer->IsMail();
						}
						
						// Add Recipient
						$phpmailer->AddAddress( $recipient->email );
						
						// From
						$phpmailer->From     = $params[ 'from_mail' ];
						$phpmailer->FromName = $params[ 'from_name' ];
						
						// Subject
						$phpmailer->Subject = $full_edition->name;
						
						// HTML or Text mail?
						$newsletter_types = explode( ',', $recipient->type );
						if ( TRUE == parent::$is_pro && in_array( 'html', $newsletter_types ) ) {
							
							// Content Type
							$phpmailer->ContentType = 'text/html';
							$phpmailer->IsHTML( true );
							
							$phpmailer->Body = $html;
							$phpmailer->AltBody = $text;
						} else {
							
							// Content Type
							$phpmailer->ContentType = 'text/plain';
							
							// Body
							$phpmailer->Body = $text;
						}
						
						// Send!
						if ( ! $phpmailer->Send() )
							echo 0;
					}
				} else {
					echo 1;
				}
			}
			
			// API Mails
			die;
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_Create::get_instance();
}