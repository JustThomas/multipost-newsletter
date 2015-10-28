<?php
/**
 * Feature Name:	Multipost Newsletter Widget
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

if ( ! class_exists( 'Multipost_Newsletter_Widget' ) ) {
	
	if ( function_exists( 'add_filter' ) )
		add_filter( 'widgets_init', array( 'Multipost_Newsletter_Widget', 'register' ) );

	class Multipost_Newsletter_Widget extends WP_Widget {

		/**
		 * The plugins textdomain
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @var		string
		 */
		public static $textdomain = '';
		
		/**
		 * Checks if Plugin is a pro
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @var		boolean
		 */
		public static $is_pro = '';
		
		/**
		 * constructor
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	__
		 * @return	void
		 */
		public function __construct() {
			
			self::$textdomain = Multipost_Newsletter::$textdomain;
			self::$is_pro = Multipost_Newsletter::$is_pro;
			
			// Add Lost Password Link
			add_filter( 'login_form_bottom', array( $this, 'login_form_bottom' ), 10, 2 );
			
			parent::__construct(
				'multipost-newsletter-widget',
				'Multipost Newsletter Widget',
				array(
					'description' => __( 'Use this widget to display the register formular.', self::$textdomain )
				)
			);
		}
		
		/**
		 * Add Lost Password Link
		 *
		 * @since	0.1
		 * @access	public
		 * @param	string $foo nothing
		 * @param	array $args
		 * @uses	
		 * @return	$string
		 */
		public function login_form_bottom( $foo = '', $args ) {
			
			return '<a href="' . get_bloginfo( 'url' ) . '/wp-login.php?action=lostpassword">' . __( 'Lost Password' ) . '</a>';
		}
		
		/**
		 * displays the widget in frontend
		 *
		 * @since	0.1
		 * @access	public
		 * @param	array $args the widget arguments
		 * @param	array $instance current instance
		 * @uses	apply_filters, get_option, is_email_address_unsafe, filter_var, FILTER_VALIDATE_EMAIL,
		 * 			__, get_site_option, email_exists, wp_insert_user, update_user_meta, sanitize_title_with_dashes,
		 * 			wp_new_user_notification, is_user_logged_in
		 * @return	void
		 */
		public function widget( $args, $instance ) {
			
			extract( $args );
			
			echo $before_widget;
			
			$title = '';
			if ( isset( $instance[ 'title' ] ) )
				$title = $instance[ 'title' ];
			
			$title = apply_filters( 'widget_title', $title );
			
			if ( '' != $title )
				echo $before_title . $title . $after_title;
			
			// Load Groups
			$groups = array();
			$my_groups = get_option( 'mp-newsletter-groups' );
			foreach ( $my_groups as $group )
				if ( $group[ 1 ] == 'off' )
					$groups[] = $group;
			
			// Set index
			$success = FALSE;
			$my_errors = array();
			
			if ( isset( $_POST[ 'register' ] ) ) {
				
				$error = FALSE;
				// Is email valid?
				if ( '' == trim( $_POST[ 'email' ] ) || ! filter_var( $_POST[ 'email' ], FILTER_VALIDATE_EMAIL ) ) {
					?><div class="error"><p><?php _e( 'Please enter a valid e-mail address!', self::$textdomain ); ?></p></div><?php
					$error = TRUE;
				}
				// Check if the mail is in the system
				$recipient = get_recipient_by_email( $_POST[ 'email' ] );
				if ( ! empty( $recipient ) ) {
					?><div class="error"><p><?php _e( 'Sorry, this recipient already exists!', self::$textdomain ); ?></p></div><?php
					$error = TRUE;
				}
				
				if ( $error == FALSE ) {
					// Okay, prepare the post array
					unset( $_POST[ 'register' ] );
					if ( isset( $_POST[ 'type' ] ) )
						$_POST[ 'type' ] = implode( ',', $_POST[ 'type' ] );
					else
						$_POST[ 'type' ] = 'text';
						
					if ( TRUE == self::$is_pro && isset( $_POST[ 'groups' ] ) )
						$_POST[ 'groups' ] = implode( ',', $_POST[ 'groups' ] );
					else
						$_POST[ 'groups' ] = '';
					
					if ( isset( $_POST[ 'subjectareas' ] ) )
						$_POST[ 'subjectareas' ] = implode( ',', $_POST[ 'subjectareas' ] );
					else
						$_POST[ 'subjectareas' ] = '';
					
					// registered
					$_POST[ 'registered' ] = date( 'Y-m-d H:i:s' );
					
					// Generate the keys
					$_POST[ 'key' ] = md5( wp_generate_password( 32 ) );
					$_POST[ 'unsubkey' ] = md5( wp_generate_password( 32 ) );
					
					// Prepare the mail
					$to = $_POST[ 'email' ];
					$subject = '[' . get_bloginfo( 'name' ) . '] ' . __( 'Request to subscribe to our newsletter', get_mpnl_textdomain() );
					$message  = __( 'Hello,', get_mpnl_textdomain() ) . "\n\n";
					$message .= __( 'We got a request that you want to subscribe to our newsletter.', get_mpnl_textdomain() ) . "\n";
					$message .= __( 'If you really want to do that, click the following link.', get_mpnl_textdomain() ) . "\n";
					$message .= __( 'If this mail is a mistake, please ignore it.', get_mpnl_textdomain() ) . "\n\n";
					$message .= get_bloginfo( 'url' ) . '/mpnl-activation/' . $_POST[ 'key' ] . "\n\n";
					$message .= __( 'Best regards,', get_mpnl_textdomain() ) . "\n";
					$message .= sprintf( __( 'The %s Team', get_mpnl_textdomain() ), get_bloginfo( 'name' ) ) . "\n";
					
					// Send the mail
					mail( $to, $subject, $message );
					
					// Insert the recipient
					global $wpdb;
					$wpdb->insert( $wpdb->prefix . 'mpnl_recipients', $_POST );
					
					// Success!!
					$success = TRUE;
				}
			}
			
			// Output
			if ( TRUE == $success ) {
				?>
				<div class="updated"><p>
					<?php echo sprintf( __( 'You have been registered successfully. You\'ll now get an email to activate your subscription.', self::$textdomain ), wp_login_url() ); ?>
				</p></div>
				<?php
			} else {
				?>
				<form action="" method="post" id="register_form">
					<p>
						<input type="text" name="email" value="<?php if ( isset( $_POST[ 'email' ] ) ) echo $_POST[ 'email' ]; ?>" placeholder="<?php _e( 'E-mail' ); ?>" id="email" />
					</p>
					<?php if ( TRUE == self::$is_pro ) { ?>
					<p>
						<input id="newsletter_type_html" name="type[]" value="html" type="checkbox" <?php if ( isset( $_POST[ 'newsletter_type' ] ) && in_array( 'html', $_POST[ 'newsletter_type' ] ) ) echo 'checked="checked"'; ?> /> <label for="newsletter_type_html"><?php _e( 'HTML', self::$textdomain ); ?></label>
						<input id="newsletter_type_text" name="type[]" value="text" type="checkbox" <?php if ( isset( $_POST[ 'newsletter_type' ] ) && in_array( 'text', $_POST[ 'newsletter_type' ] ) ) echo 'checked="checked"'; ?> /> <label for="newsletter_type_text"><?php _e( 'Text', self::$textdomain ); ?></label><br />
						<span class="description"><?php _e( 'If you don\'t chose a type, text will be setted automatically.', self::$textdomain ); ?></span>
					</p>
					<?php } else {
						?><input name="newsletter_type[]" value="text" type="hidden" /><?php
					} ?>
					<?php if ( TRUE == self::$is_pro && is_array( $groups ) && 0 < count( $groups ) ) { ?>
					<p>
						<select data-placeholder="<?php _e( 'Chose some groups', self::$textdomain ); ?>" id="groups" name="groups[]" style="width: 100%;" multiple class="chzn-select">
							<?php foreach ( $groups as $group ) { ?>
								<option value="<?php echo $group[ 0 ]; ?>"><?php echo $group[ 0 ]; ?></option>
							<?php } ?>
						</select>
					</p>
					<?php } ?>
					
					<?php
					$subject_area_args = array(
						'hide_empty'	=> FALSE,
						'orderby'		=> 'term_id',
						'order'			=> 'DESC'
					);
					$subject_areas = get_terms( 'subject-area', $subject_area_args );
					if ( ! empty( $subject_areas ) ) {
						?>
						<p>
						<select name="subjectareas[]" id="subjectareas" data-placeholder="<?php _e( 'Chose some subject areas', self::$textdomain ); ?>" style="width: 100%;" multiple class="chzn-select">
							<option value="all" selected="selected"><?php _e( 'All Subject Areas', self::$textdomain ) ?></option>
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
						</p>
						<?php
					} else {
						?>
						<input type="hidden" name="subject_area" value="all" />
						<?php
					}
					?>
					
					<p><input type="submit" id="submit_register" name="register" class="button-primary" value="<?php _e( 'Subscribe' ); ?>" /></p>
					<p><a href="#" id="already_registered"><?php _e( 'Want to unsubscribe?', self::$textdomain ) ?></a></p>
				</form>
				<div id="login_form">
					<form action="<?php bloginfo( 'url' ); ?>/mpnl-unsubscribe/" method="get">
						<p><?php _e( 'To unsubscribe please enter your key. If you don\'t have one, please contact the webmaster.', self::$textdomain ); ?></p>
						<p><input type="text" name="key" style="width:99%;" value="" placeholder="<?php _e( 'Please enter your key' ); ?>" id="key" /></p>
						<p><input type="submit" id="submit_unsubscribe" class="button-primary" value="<?php _e( 'Unsubscribe' ); ?>" /></p>
						<p><a href="#" id="register_for_newsletter"><?php _e( 'Register for newsletter', self::$textdomain ) ?></a></p>
					</form>
				</div>
				<?php
			}
			
			echo $after_widget;
		}
		
		/**
		 * process the options-updateing
		 *
		 * @since	0.1
		 * @access	public
		 * @param	array $new_instance
		 * @param	array $old_instance
		 * @return	array
		 */
		public function update( $new_instance, $old_instance ) {

			$instance = $old_instance;
			$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
			return $instance;
		}

		/**
		 * the backend options form
		 *
		 * @since	0.1
		 * @access	public
		 * @param	array $instance
		 * @uses	_e, esc_attr
		 * @return	string
		 */
		public function form( $instance ) {

			$title = '';

			if ( isset( $instance[ 'title' ] ) )
				$title = esc_attr( $instance[ 'title' ] );

			?>
			<p>
				<label for="<?php $this->get_field_id( 'title' );?>">
					<?php _e( 'Title:', self::$textdomain );?>
				</label><br />
				<input type="text" id="<?php echo $this->get_field_id( 'title' );?>" name="<?php echo $this->get_field_name( 'title' );?>" value="<?php echo $title; ?>" />
			</p>
			<?php
			return TRUE;
		}

		/**
		 * register
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @uses	register_widget
		 * @return	void
		 */
		public static function register() {
			register_widget( __CLASS__ );
		}
	}
}