<?php
/**
 * Feature Name:	Multipost Newsletter Groups
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com
 * Licence:			GPLv3
 * 
 * TODO: Change save methods to admin post
 */

if ( ! class_exists( 'Multipost_Newsletter_Groups' ) ) {

	class Multipost_Newsletter_Groups extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @access	private
		 * @static
		 * @var		NULL | Multipost_Newsletter_Groups
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @return	Multipost_Newsletter_Groups
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
			
		}
		
		/**
		 * Displays the group management
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, update_option, _e, __
		 * @return	void
		 */
		public function groups_page() {
			
			// Getting the instance
			$self = self::get_instance();
			
			$groups = get_option( 'mp-newsletter-groups' );
			
			// Adds a group
			if ( isset( $_POST[ 'new_group' ] ) && '' != $_POST[ 'new_group' ] ) {
					
				if ( ! is_array( $groups ) )
					$groups = array();
				
				// Migrate old groups
				$new_groups = array();
				foreach ( $groups as $group ) {
					if ( ! is_array( $group ) )
						$new_groups[] = array( $group, 'off' );
					else
						$new_groups[] = $group;
				}
				
				if ( isset( $_POST[ 'private_group' ] ) )
					$private_public_key = 'on';
				else
					$private_public_key = 'off';
					
				$new_groups[] = array( $_POST[ 'new_group' ], $private_public_key );
				
				update_option( 'mp-newsletter-groups', $new_groups );
				?>
				<div class="updated">
					<p><?php _e( 'Group has been added', parent::$textdomain ); ?></p>
				</div>
				<?php
			}
			
			// privates a group
			if ( isset( $_GET[ 'private' ] ) && '' != $_GET[ 'private' ] ) {
					
				$new = array();
				foreach( $groups as $group ) {
						
					if ( $group[ 0 ] == $_GET[ 'private' ] )
						$new[] = array( $group[ 0 ], 'on' );
					else
						$new[] = $group;
				}
				update_option( 'mp-newsletter-groups', $new );
				?>
				<div class="updated">
					<p><?php _e( 'Group has been setted to private.', parent::$textdomain ); ?></p>
				</div>
				<?php
			}
			
			// publics a group
			if ( isset( $_GET[ 'public' ] ) && '' != $_GET[ 'public' ] ) {
					
				$new = array();
				foreach( $groups as $group ) {
			
					if ( $group[ 0 ] == $_GET[ 'public' ] )
						$new[] = array( $group[ 0 ], 'off' );
					else
						$new[] = $group;
				}
				update_option( 'mp-newsletter-groups', $new );
				?>
				<div class="updated">
					<p><?php _e( 'Group has been setted to public.', parent::$textdomain ); ?></p>
				</div>
				<?php
			}
				
			// Delete a group
			if ( isset( $_GET[ 'del' ] ) && '' != $_GET[ 'del' ] ) {
					
				$new = array();
				foreach( $groups as $group ) {
			
					if ( $group[ 0 ] != $_GET[ 'del' ] )
						$new[] = $group;
				}
				$groups = $new;
				update_option( 'mp-newsletter-groups', $groups );
				?>
				<div class="error">
					<p><?php _e( 'Group has been deleted', parent::$textdomain ); ?></p>
				</div>
				<?php
			}
			$groups = get_option( 'mp-newsletter-groups', FALSE );
			?>
			<div class="wrap">
				<?php screen_icon( parent::$textdomain ); ?>
				<h2><?php _e( 'Newsletter', parent::$textdomain ); ?> - <?php _e( 'Groups', parent::$textdomain ); ?></h2>
				
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
							
							<div id="new_group" class="postbox">
									<h3 class="hndle"><span><?php _e( 'Add New Group', parent::$textdomain ); ?></span></h3>
									<div class="inside">
										<form action="admin.php?page=mpnl_groups" method="post">
											<table class="form-table">
												<tbody>
													<tr valign="top">
														<th scope="row">
															<label for="new_group"><?php _e( 'Group Name', parent::$textdomain ); ?>:</label>
														</th>
														<td>
															<input id="new_group" name="new_group" type="text" value="" tabindex="1" class="regular-text" /><br />
														</td>
													</tr>
													<tr valign="top">
														<th scope="row">
															<label for="private_group"><?php _e( 'Private Group', parent::$textdomain ); ?>:</label>
														</th>
														<td>
															<input id="private_group" name="private_group" type="checkbox" tabindex="2" /><br />
															<span class="description"><?php _e( 'Enable this checkbox to set this group to private. The recipient will not be able to see this group in the widget.', parent::$textdomain ); ?></span>
														</td>
													</tr>
													<tr valign="top">
														<th scope="row">&nbsp;</th>
														<td>
															<input name="save_group" type="submit" class="button-primary" tabindex="3" value="<?php _e( 'Add Group', parent::$textdomain ); ?>" />
														</td>
													</tr>
												</tbody>
											</table>
										</form>
									</div>
								</div>
							
								<table class="wp-list-table widefat fixed posts">
									<thead>
										<tr>
											<th><?php _e( 'Name' ); ?></th>
											<th><?php _e( 'Privacy', parent::$textdomain ); ?></th>
											<th><?php _e( 'Actions' ); ?></th>
										</tr>
									</thead>
									
									<tfoot>
										<tr>
											<th><?php _e( 'Name' ); ?></th>
											<th><?php _e( 'Privacy', parent::$textdomain ); ?></th>
											<th><?php _e( 'Actions' ); ?></th>
										</tr>
									</tfoot>
									
									<tbody>
										<?php
										if ( is_array( $groups ) && 0 < count( $groups ) ) {
											$i = 1;
											foreach ( $groups as $group ) {
												$i++;
												?>
												<tr valign="top" <?php echo ( ( $i % 2 ) != 1 ? 'class="alt"' : '' ); ?>>
													<th scope="row">
														<strong><?php echo $group[ 0 ]; ?></strong><br />
													</th>
													<td>
														<?php
														if ( $group[ 1 ] == 'on' ) {
															echo __( 'Private', parent::$textdomain );
														} else {
															echo __( 'Public', parent::$textdomain );
														}
														?>
													</td>
													<td>
														<?php
														if ( $group[ 1 ] == 'on' ) {
															echo '<a href="admin.php?page=mpnl_groups&public=' . $group[ 0 ] . '">' . __( 'Change to public', parent::$textdomain ) . '</a> | ';
														} else {
															echo '<a href="admin.php?page=mpnl_groups&private=' . $group[ 0 ] . '">' . __( 'Change to private', parent::$textdomain ) . '</a> | ';
														}
														?>
														<span class='submitdelete trash'><a class='delete-tag' href="admin.php?page=mpnl_groups&del=<?php echo $group[ 0 ]; ?>"><?php _e( 'Delete' ) ?></a></span>
													</td>
												</tr>
												<?php
											}
										} else {
											?>
											<tr valign="top">
												<th scope="row">
													<?php _e( 'There are no groups yet', parent::$textdomain ); ?>
												</th>
											</tr>
											<?php
										} ?>
										
									</tbody>
								</table>
							
							</div>
						</div>
					</div>
				
				</div>
			</div>
			<?php
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_Groups::get_instance();
}