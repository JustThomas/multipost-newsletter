<?php
/**
 * Feature Name:	Multipost Newsletter Recipients Page
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com
 * Licence:			GPLv3
 */

if ( ! class_exists( 'Multipost_Newsletter_Recipients' ) ) {

	class Multipost_Newsletter_Recipients extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @var		NULL | Multipost_Newsletter_Recipients
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @return	Multipost_Newsletter_Recipients
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
			
			// Adding Save Hooks
			add_filter( 'admin_post_mpnl_edit_recipient', array( $this, 'do_edit_recipient' ) );
		}
		
		/**
		 * Setting up the option page
		 *
		 * @since	0.1
		 * @uses	__, _e, screen_icon
		 * @return	void
		 */
		public function recipients_page() {
			
			// Getting the class object
			$self = self::get_instance();
			
			// Devine the tabs
			$tabs = array(
				'recipients'	=> __( 'Recipients', parent::$textdomain ),
				'import'		=> __( 'Import', parent::$textdomain ),
			);
			
			// set the current tab to the first element, if no tab is in request
			if ( isset( $_REQUEST[ 'tab' ] ) && array_key_exists( $_REQUEST[ 'tab' ], $tabs ) ) {
				$current_tab = $_REQUEST[ 'tab' ];
				$current_tabname = $tabs[ $current_tab ];
			} else {
				$current_tab = current( array_keys( $tabs ) );
				$current_tabname = $tabs[ $current_tab ];
			}
			?>
			<div class="wrap">
				<?php screen_icon( parent::$textdomain ); ?>
				<h2 class="nav-tab-wrapper"><?php
					_e( 'Newsletter Recipients ', parent::$textdomain );

					foreach( $tabs as $tab_handle => $tabname ) {
						// set the url to the tab
						$url = admin_url( 'admin.php?page=mpnl_recipients&tab=' . $tab_handle );
						// check, if this is the current tab
						$active = ( $current_tab == $tab_handle ) ? ' nav-tab-active' : '';
						printf( '<a href="%s" class="nav-tab%s">%s</a>', $url, $active, $tabname );
					}
				?></h2>
				
				<?php
				// Delete Recipient
				if ( isset( $_GET[ 'delete_recipient' ] ) && is_numeric( $_GET[ 'delete_recipient' ] ) )
					$self->delete_recipient( $_GET[ 'delete_recipient' ] );
				?>
				
				<?php
				// Check messages
				if ( isset( $_GET[ 'message' ] ) ) {
					switch( $_GET[ 'message' ] ) {
						case 'wrongmail':
							echo '<div class="error"><p>' . __( 'Please enter a valid e-mail address!', parent::$textdomain ) . '</p></div>';
							break;
						case 'somethingwentwrong':
							echo '<div class="error"><p>' . __( 'Something went wrong! Please consult your administrator!', parent::$textdomain ) . '</p></div>';
							break;
						case 'nomails':
							echo '<div class="error"><p>' . __( 'If you want to import e-mails you should give us some ;)', parent::$textdomain ) . '</p></div>';
							break;
						case 'edited':
							echo '<div class="updated"><p>' . __( 'Recipient has been updated.', parent::$textdomain ) . '</p></div>';
							break;
						case 'imported':
							echo '<div class="updated"><p>' . sprintf( __( '%s Recipients have been imported.', parent::$textdomain ), $_GET[ 'reccount' ] ) . '</p></div>';
							break;
					}
				}
				?>
				
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
								<?php $self->show_tab( array( $self , $current_tab . '_tab' ), $current_tabname ); ?>
							</div>
						</div>
					</div>
				
				</div>
			</div>
			<?php
		}
		
		/**
		 * Shows the tab, and calls the function for the content of the tab
		 *
		 * @since	0.1
		 * @param	string $tab_function function to call for tab content
		 * @param	string $title title of the tab
		 * @return	void
		 */
		private function show_tab( $tab_function, $title ) {
			if ( is_callable( $tab_function ) )
				call_user_func( $tab_function );
		}
		
		/**
		 * The Recipients Page with the possibility
		 * to edit the users
		 * 
		 * @since	0.1
		 * @return	void
		 */
		public function recipients_tab() {
			
			if ( isset( $_GET[ 'edit_recipient' ] ) )
				$this->edit_recipient( $_GET[ 'edit_recipient' ] );
			else
				$this->recipients_list();
		}
		
		/**
		 * Displays the form to edit
		 * the recipient
		 *
		 * @since	0.1
		 * @return	void
		 */
		public function edit_recipient( $recipient_id ) {
			
			$recipient = get_recipient( $recipient_id );
			if ( empty( $recipient ) ) {
				?>
				<div class="error"><p><?php _e( 'Recipient not found!', parent::$textdomain ); ?></p></div>
				<?php
			} else {
				// Check the type
				$types = explode( ',', $recipient->type );
				?>
				<div id="mp-newsletter-inpsyde" class="postbox">
					<h3 class="hndle"><span><?php _e( 'Edit Recipient', parent::$textdomain ); ?> <?php echo $recipient->email; ?></span></h3>
					<div class="inside">
						<form action="<?php echo admin_url( 'admin-post.php?action=mpnl_edit_recipient' ); ?>" method="post">
							<?php wp_nonce_field( 'mpnl_edit_recipient' ); ?>
							<input type="hidden" name="recipient_id" value="<?php echo $recipient_id; ?>" />
							<table class="form-table">
								<tr>
									<th><label for="email"><?php _e( 'E-Mail', parent::$textdomain ); ?></label></th>
									<td><input type="text" id="email" name="email" value="<?php echo $recipient->email; ?>" /></td>
								</tr>
								<tr>
									<th><?php _e( 'Newsletter Type', parent::$textdomain ); ?></th>
									<td>
										<label for="html"><input <?php if ( in_array( 'html', $types ) ) echo 'checked="checked"'; ?> type="checkbox" id="html" name="type[]" value="html" /> <?php _e( 'HTML', parent::$textdomain ); ?></label><br />
										<label for="text"><input <?php if ( in_array( 'text', $types ) ) echo 'checked="checked"'; ?> type="checkbox" id="text" name="type[]" value="text" /> <?php _e( 'Text', parent::$textdomain ); ?></label><br />
										<span class="description"><?php _e( 'If you or the recipient don\'t chose a type of the newsletter <code>text</code> will be setted.', parent::$textdomain ); ?></span>
									</td>
								</tr>
								<?php
								// Load Groups
								$groups = get_option( 'mp-newsletter-groups' );
								$recipient_groups = explode( ',', $recipient->groups );
								if ( TRUE == parent::$is_pro && is_array( $groups ) && 0 < count( $groups ) ) { ?>
								<tr>
									<th><label for="groups"><?php _e( 'Groups', parent::$textdomain ); ?></label></th>
									<td>
										<select data-placeholder="<?php _e( 'Chose some groups', parent::$textdomain ); ?>" id="groups" name="groups[]" style="width: 350px;" multiple class="chzn-select">
											<?php foreach ( $groups as $group ) { ?>
												<option value="<?php echo $group[ 0 ]; ?>" <?php if ( in_array( $group[ 0 ], $recipient_groups ) ) { echo 'selected="selected"'; } ?>><?php echo $group[ 0 ]; ?></option>
											<?php } ?>
										</select><br />
										<span class="description"><?php _e( 'If you or the recipient don\'t chose a group the recipient only will receive the global newsletters.', parent::$textdomain ); ?></span>
									</td>
								</tr>
								<?php } ?>
								
								<?php
								$subject_area_args = array(
									'hide_empty'	=> FALSE,
									'orderby'		=> 'term_id',
									'order'			=> 'DESC'
								);
								$subject_areas = get_terms( 'subject-area', $subject_area_args );
								if ( ! empty( $subject_areas ) ) {
									$recipient_areas = explode( ',', $recipient->subjectareas );
									?>
									<tr>
										<th><label for="subjectareas"><?php _e( 'Subject Areas', parent::$textdomain ); ?></label></th>
										<td>
											<select name="subjectareas[]" id="subjectareas" data-placeholder="<?php _e( 'Chose some subject areas', self::$textdomain ); ?>" style="width: 350px;" multiple class="chzn-select">
												<option value="all" <?php if ( in_array( 'all', $recipient_areas ) ) { echo 'selected="selected"'; } ?>><?php _e( 'All Subject Areas', self::$textdomain ) ?></option>
												<?php
													foreach ( $subject_areas as $subject_area ) {
														if ( 0 != $subject_area->parent )
															continue;
														
														?>
														<option value="<?php echo $subject_area->slug; ?>" <?php if ( in_array( $subject_area->slug, $recipient_areas ) ) { echo 'selected="selected"'; } ?>><?php echo $subject_area->name; ?></option>
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
																	<option value="<?php echo $child->slug; ?>" <?php if ( in_array( $child->slug, $recipient_areas ) ) { echo 'selected="selected"'; } ?>><?php echo $child->name; ?></option>
																	<?php
																}
																?>
															</optgroup>
														<?php
														}
													}
												?>
											</select>
										</td>
									</tr>
									<?php
								} else {
									?>
									<input type="hidden" name="subject_area" value="all" />
									<?php
								}
								?>
								
								<tr>
									<th><?php _e( 'Activated', parent::$textdomain ); ?></th>
									<td>
										<label for="activated"><input <?php if ( $recipient->activated == 1 ) echo 'checked="checked"'; ?> type="checkbox" id="activated" name="activated" value="1" /> <?php _e( 'User is activated', parent::$textdomain ); ?></label><br />
									</td>
								</tr>
								<tr>
									<th><label for="key"><?php _e( 'Key', parent::$textdomain ); ?></label></th>
									<td>
										<input type="text" id="key" name="key" value="<?php echo $recipient->key; ?>" /><br />
										<span class="description"><?php echo sprintf( __( 'This is the activation key for this recipient. If the user is not activated an heads to <code>%s</code> the recipient will be activated for your newsletter.', parent::$textdomain ), get_recipient_activation_link( $recipient_id ) ); ?></span>
									</td>
								</tr>
								<tr>
									<th>&nbsp;</th>
									<td>
										<input type="submit" name="submit" id="submit" value="<?php _e( 'Edit Recipient', parent::$textdomain ); ?>" class="button-primary" />
									</td>
								</tr>
							</table>
						</form>
					</div>
				</div>
				<?php
			}
		}
		
		/**
		 * Edits the recipients data
		 *
		 * @since	0.1
		 * @return	void
		 */
		public function do_edit_recipient() {
			
			// Check Nonce
			check_admin_referer( 'mpnl_edit_recipient' );
			
			// Is email valid?
			if ( '' == trim( $_POST[ 'email' ] ) || ! filter_var( $_POST[ 'email' ], FILTER_VALIDATE_EMAIL ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mpnl_recipients&edit_recipient=' . $_POST[ 'recipient_id' ] . '&message=wrongmail' ) );
				exit;
			}
			
			// Okay, prepare the post array
			$recipient_id = $_POST[ 'recipient_id' ];
			if ( isset( $_POST[ 'type' ] ) )
				$_POST[ 'type' ] = implode( ',', $_POST[ 'type' ] );
			else
				$_POST[ 'type' ] = 'text';
			
			if ( TRUE == parent::$is_pro && isset( $_POST[ 'groups' ] ) )
				$_POST[ 'groups' ] = implode( ',', $_POST[ 'groups' ] );
			else
				$_POST[ 'groups' ] = '';
			
			if ( isset( $_POST[ 'subjectareas' ] ) )
				$_POST[ 'subjectareas' ] = implode( ',', $_POST[ 'subjectareas' ] );
			else
				$_POST[ 'subjectareas' ] = 'all';
				
			unset( $_POST[ 'recipient_id' ] );
			unset( $_POST[ 'submit' ] );
			unset( $_POST[ '_wpnonce' ] );
			unset( $_POST[ '_wp_http_referer' ] );
			
			// Update the recipient
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'mpnl_recipients', $_POST, array( 'id' => $recipient_id ) );
			wp_safe_redirect( admin_url( 'admin.php?page=mpnl_recipients&edit_recipient=' . $recipient_id . '&message=edited' ) );
			exit;
		}
		
		/**
		 * Deletes a recipient
		 *
		 * @param	int $recipient_id
		 * @return	void
		 */
		public function delete_recipient( $recipient_id ) {
			global $wpdb;
			
			$query = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'mpnl_recipients WHERE `id` = %s', $recipient_id );
			$delete = $wpdb->query( $query );
			
			if ( $delete )
				echo '<div class="updated"><p>' . __( 'Recipient has been deleted!', parent::$textdomain ) . '</p></div>';
			else
				echo '<div class="error"><p>' . __( 'Recipient does not exists!', parent::$textdomain ) . '</p></div>';
		}
		
		/**
		 * This function loads a recipient with the given id
		 *
		 * @param	int $recipient_id
		 * @return	object the recipient
		 */
		public function get_recipient( $recipient_id ) {
			global $wpdb;
			
			$query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'mpnl_recipients WHERE `id` = %s', $recipient_id );
			$recipient = $wpdb->get_row( $query );
			
			if ( empty( $recipient ) )
				return FALSE;
			else
				return $recipient;
		}
		
		/**
		 * This function loads a recipient with the given key
		 *
		 * @param	string $key
		 * @return	object the recipient
		 */
		function get_recipient_by_key( $key ) {
			global $wpdb;
				
			$query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'mpnl_recipients WHERE `key` = "%s"', $key );
			$recipient = $wpdb->get_row( $query );
				
			if ( empty( $recipient ) )
				return FALSE;
			else
				return $recipient;
		}
		
		/**
		 * This function loads a recipient with the given unsub key
		 *
		 * @param	string $key
		 * @return	object the recipient
		 */
		public function get_recipient_by_unsubkey( $key ) {
			global $wpdb;
		
			$query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'mpnl_recipients WHERE `unsubkey` = "%s"', $key );
			$recipient = $wpdb->get_row( $query );
		
			if ( empty( $recipient ) )
				return FALSE;
			else
				return $recipient;
		}
		
		/**
		 * This function loads a recipient with the given email
		 *
		 * @param	string $email
		 *
		 * @return	object the recipient
		 */
		public function get_recipient_by_email( $email ) {
			global $wpdb;
			
			$query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'mpnl_recipients WHERE `email` = "%s"', $email );
			$recipient = $wpdb->get_row( $query );
			
			if ( empty( $recipient ) )
				return FALSE;
			else
				return $recipient;
		}
		
		/**
		 * Gets the activation link from the recipient
		 *
		 * @param	int $recipient_id
		 * @return	string
		 */
		function get_recipient_activation_link( $recipient_id ) {
		
			$recipient = get_recipient( $recipient_id );
			if ( ! empty( $recipient ) )
				return get_bloginfo( 'url' ) . '/mpnl-activation/' . $recipient->key;
			else
				return FALSE;
		}
		
		/**
		 * Shows the list of the recipients
		 *
		 * @since	0.1
		 * @return	void
		 */
		public function recipients_list() {
			global $wpdb;
				
			// Setup Where string
			$where = '';
			$where_just_s = '';
			if ( isset( $_GET[ 'show' ] ) )
				if ( $_GET[ 'show' ] == 'activated' )
				$where .= ' AND `activated` = 1';
			else if ( $_GET[ 'show' ] == 'not_activated' )
				$where .= ' AND `activated` = 0';
			
			if ( isset( $_GET[ 's' ] ) ) {
				$where .= ' AND `email` LIKE "%' . $_GET[ 's' ] . '%"';
				$where_just_s .= ' AND `email` LIKE "%' . $_GET[ 's' ] . '%"';
			}
				
			// Setup offset
			if ( isset( $_GET[ 'paged' ] ) )
				$offset = ( $_GET[ 'paged' ] - 1 ) * 25;
			else
				$offset = 0;
				
			// Query the recipients
			$recipients = $wpdb->get_results( 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . $wpdb->prefix . 'mpnl_recipients WHERE 1=1 ' . $where . ' LIMIT ' . $offset . ',25' );
			$found = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
				
			// Countings
			// TODO Make it simpler, maybe in one query
			$all_recipients = $wpdb->get_results( 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . $wpdb->prefix . 'mpnl_recipients WHERE 1=1 ' . $where_just_s . ' LIMIT 0,1' );
			$all_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			$active_recipients = $wpdb->get_results( 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . $wpdb->prefix . 'mpnl_recipients WHERE `activated` = 1 ' . $where_just_s . ' LIMIT 0,1' );
			$active_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			$inactive_recipients = $wpdb->get_results( 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . $wpdb->prefix . 'mpnl_recipients WHERE `activated` = 0 ' . $where_just_s . ' LIMIT 0,1' );
			$inactive_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			?>
			<ul class="subsubsub" style="margin-top: 0;">
				<li class="all"><a href="admin.php?page=mpnl_recipients" class="current"><?php _e( 'All', parent::$textdomain ); ?> <span class="count">(<?php echo $all_found; ?>)</span></a> |</li>
				<li class="activated"><a href="admin.php?page=mpnl_recipients&show=activated"><?php _e( 'Activated', parent::$textdomain ); ?> <span class="count">(<?php echo $active_found; ?>)</span></a> | </li>
				<li class="not_activated"><a href="admin.php?page=mpnl_recipients&show=not_activated"><?php _e( 'Not Activated', parent::$textdomain ); ?> <span class="count">(<?php echo $inactive_found; ?>)</span></a></li>
			</ul>
			
			<form action="admin.php?page=mpnl_recipients" method="get">
				<p class="search-box">
					<input type="search" id="recipients-search-input" name="s" value="<?php echo isset( $_GET[ 's' ] ) ? $_GET[ 's' ] : ''; ?>">
					<input type="hidden" name="page" value="mpnl_recipients" />
					<?php if ( isset( $_GET[ 'show' ] ) ) echo '<input type="hidden" name="show" value="' . $_GET[ 'show' ] . '" />'; ?>
					<input type="submit" name="" id="search-submit" class="button" value="<?php _e( 'Search Recipients', parent::$textdomain ); ?>">
				</p>
			</form>
			
			<table class="wp-list-table widefat fixed recipients" cellspacing="0">
				<thead>
					<tr>
						<!-- th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th -->
						<th scope="col" id="email" class="manage-column column-email"><span><?php _e( 'E-Mail', parent::$textdomain ); ?></span></th>
						<th scope="col" id="registered" class="manage-column column-registered"><?php _e( 'Registered', parent::$textdomain ); ?></th>
						<th scope="col" id="type" class="manage-column column-type"><?php _e( 'Type', parent::$textdomain ); ?></th>
						<th scope="col" id="activated" class="manage-column column-activated"><span><?php _e( 'Activated', parent::$textdomain ); ?></span></th>
						<th scope="col" id="subject_areas" class="manage-column column-subject-areas"><span><?php _e( 'Subject Areas', parent::$textdomain ); ?></span></th>
						<?php if ( parent::$is_pro == TRUE ) : ?>
							<th scope="col" id="groups" class="manage-column column-groups"><span><?php _e( 'Groups', parent::$textdomain ); ?></span></th>
						<?php endif; ?>
					</tr>
				</thead>

				<tfoot>
					<tr>
						<th scope="col" id="email" class="manage-column column-email"><span><?php _e( 'E-Mail', parent::$textdomain ); ?></span></th>
						<th scope="col" id="registered" class="manage-column column-registered"><?php _e( 'Registered', parent::$textdomain ); ?></th>
						<th scope="col" id="type" class="manage-column column-type"><?php _e( 'Type', parent::$textdomain ); ?></th>
						<th scope="col" id="activated" class="manage-column column-activated"><span><?php _e( 'Activated', parent::$textdomain ); ?></span></th>
						<th scope="col" id="subject_areas" class="manage-column column-subject-areas"><span><?php _e( 'Subject Areas', parent::$textdomain ); ?></span></th>
						<?php if ( parent::$is_pro == TRUE ) : ?>
							<th scope="col" id="groups" class="manage-column column-groups"><span><?php _e( 'Groups', parent::$textdomain ); ?></span></th>
						<?php endif; ?>
					</tr>
				</tfoot>

				<tbody id="the-list">
					<?php if ( ! empty( $recipients ) ) : foreach ( $recipients as $recipient ) : ?>
						<tr id="recipient-<?php echo $recipient->id ?>" class="" valign="top">
							<td class="recipient-email column-email"><strong><a class="row-email" href="admin.php?page=mpnl_recipients&edit_recipient=<?php echo $recipient->id; ?>" title="<?php _e( 'Edit recipient', parent::$textdomain ); ?>"><?php echo $recipient->email; ?></a></strong>
								<div class="row-actions">
									<span class="edit"><a href="admin.php?page=mpnl_recipients&edit_recipient=<?php echo $recipient->id; ?>" title="<?php _e( 'Edit recipient', parent::$textdomain ); ?>"><?php _e( 'Edit', parent::$textdomain ); ?></a> | </span>
									<span class="trash"><a class="submitdelete" title="<?php _e( 'Delete recipient', parent::$textdomain ); ?>" href="admin.php?page=mpnl_recipients&delete_recipient=<?php echo $recipient->id; ?>"><?php _e( 'Delete', parent::$textdomain ); ?></a></span>
								</div>
							</td>
							<td scope="col" class="recipient-registered column-registered"><?php echo date( get_option( 'date_format' ), strtotime( $recipient->registered ) ) . ' ' . date( get_option( 'time_format' ), strtotime( $recipient->registered ) ); ?></td>
							<td scope="col" class="recipient-type column-type"><?php echo $recipient->type; ?></td>
							<td scope="col" class="recipient-activated column-activated"><span><?php echo $recipient->activated == 0 ? __( 'not activated', parent::$textdomain ) : __( 'activated', parent::$textdomain ); ?></span></td>
							<td scope="col" class="recipient-subject-areas column-subject-areas"><span><?php echo $recipient->subjectareas; ?></span></td>
							<?php if ( parent::$is_pro == TRUE ) : ?>
								<td scope="col" class="recipient-groups column-groups"><span><?php echo $recipient->groups; ?></span></td>
							<?php endif; ?>
						</tr>
					<?php endforeach; else : ?>
						<tr id="recipient-<?php echo $recipient->id ?>" class="" valign="top">
							<td colspan="5">
								<p><?php _e( 'Sorry, no recipients found for your query.', parent::$textdomain ); ?></p>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
			
			<?php
			$overall_pages = ceil( $found / 25 );
			if ( ! empty( $recipients ) && $overall_pages > 1 ) {
			?>
			<div class="tablenav bottom">
				<?php
				// Calculate the pages
				$first_page = 1;
				$last_page = $overall_pages;

				// Check next page
				if ( isset( $_GET[ 'paged' ] ) ) {
					$current_page = $_GET[ 'paged' ];
					$next_page = $_GET[ 'paged' ] + 1;
					$prev_page = $_GET[ 'paged' ] - 1;
				} else {
					$current_page = 1;
					$next_page = 2;
				}
				
				// Set Query String
				$query_string = 'admin.php?page=mpnl_recipients';
				if ( isset( $_GET[ 's' ] ) )
					$query_string .= '&s=' . $_GET[ 's' ];
				if ( isset( $_GET[ 'show' ] ) )
					$query_string .= '&show=' . $_GET[ 'show' ];
				
				?>
				<form action="" method="get">
					<input type="hidden" name="page" value="mpnl_recipients" />
					<?php if ( isset( $_GET[ 's' ] ) ) echo '<input type="hidden" name="s" value="' . $_GET[ 's' ] . '" />'; ?>
					<?php if ( isset( $_GET[ 'show' ] ) ) echo '<input type="hidden" name="show" value="' . $_GET[ 'show' ] . '" />'; ?>
					<div class="tablenav-pages">
						<span class="displaying-num"><?php echo sprintf( __( '%s items', parent::$textdomain ), $all_found ); ?></span>
						<span class="pagination-links">
							<a class="first-page <?php if ( ! isset( $_GET[ 'paged' ] ) || $_GET[ 'paged' ] == 1 ) echo 'disabled'; ?>" title="<?php _e( 'Go to the first page', parent::$textdomain ); ?>" href="<?php echo $query_string . '&paged=' . $first_page; ?>">«</a>
							<a class="prev-page <?php if ( ! isset( $_GET[ 'paged' ] ) || $_GET[ 'paged' ] == 1 ) echo 'disabled'; ?>" title="<?php _e( 'Go to the previous page', parent::$textdomain ); ?>" href="<?php echo $query_string . '&paged=' . $prev_page; ?>">‹</a>
							<span class="paging-input">
								<input class="current-page" title="<?php _e( 'Current page', parent::$textdomain ); ?>" type="text" name="paged" value="<?php echo $current_page; ?>" size="1"> of <span class="total-pages"><?php echo $overall_pages; ?></span>
							</span>
							<a class="next-page <?php if ( isset( $_GET[ 'paged' ] ) && $last_page == $current_page ) echo 'disabled'; ?>" title="<?php _e( 'Go to the next page', parent::$textdomain ); ?>" href="<?php echo $query_string . '&paged=' . $next_page; ?>">›</a>
							<a class="last-page <?php if ( isset( $_GET[ 'paged' ] ) && $_GET[ 'paged' ] == $last_page ) echo 'disabled'; ?>" title="<?php _e( 'Go to the last page', parent::$textdomain ); ?>" href="<?php echo $query_string . '&paged=' . $last_page; ?>">»</a>
						</span>
					</div>
					<br class="clear">
				</form>
			</div>
			<?php }
		}
		
		/**
		 * The Import Tab
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, _e, __, update_option
		 * @return	void
		 */
		public function import_tab() {
				
			if ( TRUE == parent::$is_pro )
				Multipost_Newsletter_Recipients_Import::import_tab();
			else {
				echo '<p>';
				_e( 'You have to purchase the pro-version of this plugin to import subscribers!', parent::$textdomain );
				echo '</p>';
			}
		}
		
		/**
		 * Unsubscribes a recipient
		 *
		 * @param	int $key
		 * @return	void
		 */
		public function mpnl_unsubscribe_recipient( $key ) {
			
			// Check if there's a recipient with this key
			$recipient = get_recipient_by_key( $key );
			if ( empty( $recipient ) )
				wp_die( __( 'Sorry, there is no recipient with this key.', parent::$textdomain ) );
			
			// Check if the recipient already got an unsubscription mail
			if ( $recipient->unsubsend == 1 )
				wp_die( __( 'Sorry, this recipient already got an unsubscription mail.' ), parent::$textdomain );
			
			// Everything seams fine, so we prepare and send an e-mail to the user
			$to = $recipient->email;
			$subject = '[' . get_bloginfo( 'name' ) . '] ' . __( 'Request to unsubscribe from newsletter', parent::$textdomain );
			$message  = __( 'Hello,', parent::$textdomain ) . "\n\n";
			$message .= __( 'We got a request that you want to unsubscribe from our newsletter.', parent::$textdomain ) . "\n";
			$message .= __( 'If you really want to do that, click the following link.', parent::$textdomain ) . "\n";
			$message .= __( 'If this mail is a mistake, please ignore it.', parent::$textdomain ) . "\n\n";
			$message .= get_bloginfo( 'url' ) . '/mpnl-confirm-unsubscription/' . $recipient->unsubkey . "\n\n";
			$message .= __( 'Best regards,', parent::$textdomain ) . "\n";
			$message .= sprintf( __( 'The %s Team', parent::$textdomain ), get_bloginfo( 'name' ) ) . "\n";
			
			// Get Params
			$params = get_option( 'mp-newsletter-params' );
			
			// Prepare PHPMail
			if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
				require_once ABSPATH . WPINC . '/class-phpmailer.php';
				require_once ABSPATH . WPINC . '/class-smtp.php';
			}
			$phpmailer = new PHPMailer( true );
			$phpmailer->CharSet = 'UTF-8';
			
			// Set to use PHP's mail()
			$phpmailer->IsMail();
			
			// Add Recipient
			$phpmailer->AddAddress( $to );
			
			// From
			$phpmailer->From     = $params[ 'from_mail' ];
			$phpmailer->FromName = $params[ 'from_name' ];
			
			// Content Type
			$phpmailer->ContentType = 'text/plain';
			
			// Body and Title
			$phpmailer->Subject = $subject;
			$phpmailer->Body = $message;
			
			// Send the Mail
			$phpmailer->Send();
			
			// Update the unsubsend
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'mpnl_recipients', array( 'unsubsend' => '1' ), array( 'id' => $recipient->id ) );
		}
		
		/**
		 * Unsubscribes a recipient
		 *
		 * @param	int $key
		 * @return	void
		 */
		public function mpnl_confirm_unsubscription( $key ) {
		
			// Check if there's a recipient with this key
			$recipient = get_recipient_by_unsubkey( $key );
			if ( empty( $recipient ) )
				wp_die( __( 'Sorry, there is no recipient with this key.', get_mpnl_textdomain() ) );
			
			// Check if the recipient already got an unsubscription mail
			if ( $recipient->unsubsend == 0 )
				wp_die( __( 'Sorry, this recipient didn\'t got an unsubscription mail.' ), get_mpnl_textdomain() );
			
			// Everything seams fine, so we delete this subscription
			$this->delete_recipient( $recipient->id );
		}
		
		/**
		 * Activates a recipient
		 *
		 * @param	int $key
		 * @return	void
		 */
		public function mpnl_recipient_activation( $key ) {
		
			// Check if there's a recipient with this key
			$recipient = get_recipient_by_key( $key );
			if ( empty( $recipient ) )
				wp_die( __( 'Sorry, there is no recipient with this key.', get_mpnl_textdomain() ) );
			
			// Check if the recipient already got an unsubscription mail
			if ( $recipient->activated == 1 )
				wp_die( __( 'Sorry, this recipient is already activated.' ), get_mpnl_textdomain() );
			
			// Everything seams fine, so we activate the recipient
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'mpnl_recipients', array( 'activated' => '1' ), array( 'id' => $recipient->id ) );
		}
	}
	
	if ( ! function_exists( 'get_recipient' ) ) {
		/**
		 * This function loads a recipient with the given id
		 * 
		 * @see		Multipost_Newsletter_Recipients::get_recipient()
		 * 
		 * @param	int $recipient_id
		 * 
		 * @return	object the recipient
		 */
		function get_recipient( $recipient_id ) {
			
			$res = Multipost_Newsletter_Recipients::get_instance();
			return $res->get_recipient( $recipient_id );
		}
	}
	
	if ( ! function_exists( 'get_recipient_by_key' ) ) {
		/**
		 * This function loads a recipient with the given key
		 *
		 * @see		Multipost_Newsletter_Recipients::get_recipient_by_key()
		 *
		 * @param	string $key
		 *
		 * @return	object the recipient
		 */
		function get_recipient_by_key( $key ) {
				
			$res = Multipost_Newsletter_Recipients::get_instance();
			return $res->get_recipient_by_key( $key );
		}
	}
	
	if ( ! function_exists( 'get_recipient_by_unsubkey' ) ) {
		/**
		 * This function loads a recipient with the given key
		 *
		 * @see		Multipost_Newsletter_Recipients::get_recipient_by_unsubkey()
		 *
		 * @param	string $key
		 *
		 * @return	object the recipient
		 */
		function get_recipient_by_unsubkey( $key ) {
	
			$res = Multipost_Newsletter_Recipients::get_instance();
			return $res->get_recipient_by_unsubkey( $key );
		}
	}
	
	if ( ! function_exists( 'get_recipient_by_email' ) ) {
		/**
		 * This function loads a recipient with the given email
		 *
		 * @see		Multipost_Newsletter_Recipients::get_recipient_by_email()
		 *
		 * @param	string $email
		 *
		 * @return	object the recipient
		 */
		function get_recipient_by_email( $email ) {
	
			$res = Multipost_Newsletter_Recipients::get_instance();
			return $res->get_recipient_by_email( $email );
		}
	}
	
	if ( ! function_exists( 'get_recipient_activation_link' ) ) {
		/**
		 * Gets the activation link from the recipient
		 *
		 * @see		Multipost_Newsletter_Recipients::get_recipient_activation_link()
		 *
		 * @param	int $recipient_id
		 *
		 * @return	string
		 */
		function get_recipient_activation_link( $recipient_id ) {
				
			$res = Multipost_Newsletter_Recipients::get_instance();
			return $res->get_recipient_activation_link( $recipient_id );
		}
	}
	
	if ( ! function_exists( 'mpnl_unsubscribe_recipient' ) ) {
		/**
		 * Unsubscribes a recipient
		 *
		 * @see		Multipost_Newsletter_Recipients::mpnl_unsubscribe_recipient()
		 *
		 * @param	int $key
		 *
		 * @return	void
		 */
		function mpnl_unsubscribe_recipient( $key ) {
	
			$res = Multipost_Newsletter_Recipients::get_instance();
			return $res->mpnl_unsubscribe_recipient( $key );
		}
	}
	
	if ( ! function_exists( 'mpnl_confirm_unsubscription' ) ) {
		/**
		 * Unsubscribes a recipient
		 *
		 * @see		Multipost_Newsletter_Recipients::mpnl_confirm_unsubscription()
		 *
		 * @param	int $key
		 *
		 * @return	void
		 */
		function mpnl_confirm_unsubscription( $key ) {
	
			$res = Multipost_Newsletter_Recipients::get_instance();
			return $res->mpnl_confirm_unsubscription( $key );
		}
	}
	
	if ( ! function_exists( 'mpnl_recipient_activation' ) ) {
		/**
		 * Activates a recipient
		 *
		 * @see		Multipost_Newsletter_Recipients::mpnl_recipient_activation()
		 *
		 * @param	int $key
		 *
		 * @return	void
		 */
		function mpnl_recipient_activation( $key ) {
	
			$res = Multipost_Newsletter_Recipients::get_instance();
			return $res->mpnl_recipient_activation( $key );
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_Recipients::get_instance();
}