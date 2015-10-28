<?php
/**
 * Feature Name:	Multipost Newsletter Prepare
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

if ( ! class_exists( 'Multipost_Newsletter_Prepare' ) ) {

	class Multipost_Newsletter_Prepare extends Multipost_Newsletter {
		
		/**
		 * Instance holder
		 *
		 * @since	0.1
		 * @access	private
		 * @static
		 * @var		NULL | Multipost_Newsletter_Prepare
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since	0.1
		 * @access	public
		 * @static
		 * @return	Multipost_Newsletter_Prepare
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
		 * @uses	get_option, add_filter
		 * @return	void
		 */
		public function __construct () {
			
			// Load options
			$this->options = get_option( 'mp-newsletter-params' );
			
			// Register Ajax function
			add_filter( 'wp_ajax_save_post_order', array( $this, 'ajax_save_post_order' ) );
			
			// Register Spacer Code
			add_filter( 'wp_ajax_get_spacer_code', array( $this, 'get_spacer_code' ) );
			
			// Remove Spacer
			add_filter( 'wp_ajax_remove_spacer', array( $this, 'remove_spacer' ) );
			
			// Save Spacer
			add_filter( 'wp_ajax_save_spacer', array( $this, 'save_spacer' ) );
			
			// save_post_settings
			add_filter( 'wp_ajax_save_post_settings', array( $this, 'save_post_settings' ) );
			
			// Remove post from edition
			add_filter( 'wp_ajax_remove_post_from_edition', array( $this, 'remove_post_from_edition' ) );
			
			// generate ajax pdf
			if ( TRUE == parent::$is_pro )
				add_filter( 'wp_ajax_generate_pdf', array( $this, 'ajax_generate_pdf' ) );
		}
		
		/**
		 * Display the newsletter generation page
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	screen_icon, _e, __, get_terms
		 * @return	void
		 */
		public function generate_newsletter_page() {
			
			$self = self::get_instance();
			
			?>
			<div class="wrap">
				<?php screen_icon( parent::$textdomain ); ?>
				<h2><?php _e( 'Newsletter', parent::$textdomain ); ?> - <?php _e( 'Prepare Newsletter', parent::$textdomain ); ?></h2>
				
				<?php
				if ( isset( $_POST[ 'generate_newsletter' ] ) || isset( $_POST[ 'generate_preview' ] ) ) {
					if ( '' == $_POST[ 'edition' ] ) {
						?>
						<div class="error">
							<p><?php _e( 'You have to choose an edition to generate the newsletter', parent::$textdomain ); ?></p>
						</div>
						<?php
					}
				}
				?>
				
				<div id="ajax_response"></div>
				
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
							
							<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							
								<?php
								if ( isset( $_POST[ 'generate_newsletter' ] ) || isset( $_POST[ 'generate_preview' ] ) ) {
								
									if ( '' == $_POST[ 'edition' ] ) {
										// Do nothing
									} else {
										$self->generate_sort_preview( $_POST[ 'edition' ], $_POST[ 'subject_area' ] );
									}
								}
								?>
							</div>
						</div>
					</div>
					
					<div id="post-body">
						<div id="post-body-content">
						
							<?php
							if ( ! isset( $_POST[ 'edition' ] ) || ( isset( $_POST[ 'edition' ] ) && '' == $_POST[ 'edition' ] ) ) {
								?>
								<div id="settings" class="postbox">
									<h3 class="hndle"><span><?php _e( 'Choose Edition', parent::$textdomain ); ?></span></h3>
									<div class="inside">
										<form action="admin.php?page=mpnl_generate" method="post">
										
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
											
											<input name="generate_newsletter" type="submit" class="button-primary" tabindex="6" value="<?php _e( 'Prepare Newsletter', parent::$textdomain ); ?>" style="float: right;" />
											<br class="clear" />
										</form>
									</div>
								</div>
								<?php
							} else {
								// Do Preview
								$self->generate_preview( $_POST[ 'edition' ], $_POST[ 'subject_area' ] );
							}
							?>
						</div>
					</div>
				
				</div>
			</div>
			<?php
		}
		
		/**
		 * Sorting the newsletter
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, _e, __, WP_Query, update_option, get_post_meta
		 * @return	void
		 */
		public function generate_sort_preview( $edition, $subject_area ) {
			
			$self = self::get_instance();
			
			// Set edition
			$letter_slug = $edition . '-' . $subject_area;
			$spacers = get_option( 'mp-newsletter-spacers' );
			?>
			<div id="ajax_response"></div>
			
			<form action="admin.php?page=mpnl_generate" method="post">
			
				<div id="settings" class="postbox">
					<h3 class="hndle"><span><?php _e( 'Prepare Posts', parent::$textdomain ); ?></span></h3>
					<div class="inside">
					
						<input type="hidden" id="edition" name="edition" value="<?php echo $edition; ?>" />
						<input type="hidden" id="letter_slug" name="letter_slug" value="<?php echo $letter_slug; ?>" />
						<a href="#" id="add_spacer" class="button-secondary" style="float: right; margin-bottom: 10px;"><?php _e( 'Add', parent::$textdomain ); ?></a>
						<select name="spacer_input" id="spacer_input" style="float: right; margin: 0 5px 0 0;">
							<option value="0"><?php _e( 'Choose Spacer', parent::$textdomain ); ?></option>
							<option value="0"><?php _e( 'Empty Spacer', parent::$textdomain ); ?></option>
							<?php foreach ( $spacers as $title => $spacer ) { ?>
								<option value="<?php echo $title; ?>"><?php echo $spacer[ 'title' ]; ?></option>
							<?php } ?>
						</select>
						<br class="clear" />
						
						<div id="posts" class="sortable-holder">
								
							<?php
							$current_newsletter = get_option( 'newsletter_' . $letter_slug );
							$current_newsletter_ids = array();
							if ( is_array( $current_newsletter ) ) {
								foreach ( $current_newsletter as $nl_posts ) {
									$current_newsletter_ids[] = $nl_posts[ 'id' ];
								}
							} else {
								$current_newsletter = array();
							}
							
							// Check for new articles
							$query_args = array(
								'orderby'			=> 'menu_order',
								'order'				=> 'ASC',
								'post_status'		=> 'publish',
								'posts_per_page'	=> -1
							);
							$query_args[ 'tax_query' ][ 'relation' ] = 'AND';
							$query_args[ 'tax_query' ][] = array(
								'taxonomy'	=> 'newsletter',
								'field'		=> 'slug',
								'terms'		=> $_POST[ 'edition' ]
							);
							if ( $subject_area != 'all' )
								$query_args[ 'tax_query' ][] = array(
									'taxonomy'	=> 'subject-area',
									'field'		=> 'slug',
									'terms'		=> $_POST[ 'subject_area' ]
								);
							$custom_query = new WP_Query( $query_args );
							
							$i = 0;
							$posts = array();
							if ( $custom_query->have_posts() ) {
								while ( $custom_query->have_posts() ) {
									$i++;
									$custom_query->the_post();
									
									// Does post id exists in current newsletter?
									if ( ! in_array( get_the_ID(), $current_newsletter_ids ) ) {
										$posts[ $i ] = array(
											'type'	=> 'post',
											'id'	=> get_the_ID()
										);
									}
								}
							}
							
							// Update current Newsletter
							if ( 0 < count( $posts ) ) {
								foreach ( $posts as $new_post ) {
									$new_newsletter[] = $new_post;
								}
								foreach ( $current_newsletter as $old_posts ) {
									$new_newsletter[] = $old_posts;
								}
							} else if ( 0 == count( $current_newsletter ) ) {
								$new_newsletter = $posts;
							} else {
								$new_newsletter = $current_newsletter;
							}
							
							update_option( 'newsletter_' . $letter_slug, $new_newsletter );
							
							// Load Current Newsletter
							$current_newsletter = get_option( 'newsletter_' . $letter_slug );
							
							// Fix Notice
							if ( ! isset( $current_newsletter ) )
								$current_newsletter = array();
							
							$standard_template = get_option( 'mp-newsletter-template-params' );
							$pdf_options = get_option( 'mp-newsletter-pdf' );
							
							foreach ( $current_newsletter as $position => $post ) {
								if ( 'spacer' == $post[ 'type' ] ) {
									?>
									<div id="<?php echo $post[ 'id' ]; ?>" class="stuffbox post-sortable" style="background: #fff;">
										<div class="handlediv" title="<?php _e( 'Click to toggle', parent::$textdomain ); ?>"><br /></div>
										<h3 class="hndle">
											<?php _e( 'Spacer', parent::$textdomain ); ?>
										</h3>
										<div class="inside" style="display: none;">
										
											<?php
											if ( ! isset( $post[ 'content_html' ] ) )
												$post[ 'content_html' ] = '';
											if ( ! isset( $post[ 'content_text' ] ) )
												$post[ 'content_text' ] = '';
											?>
										
											<label for="html_<?php echo $post[ 'id' ] ?>"><?php _e( 'HTML', parent::$textdomain ); ?></label><br />
											<textarea id="html_<?php echo $post[ 'id' ] ?>" rows="5" class="large-text"><?php echo $post[ 'content_html' ]; ?></textarea>
											
											<label for="text_<?php echo $post[ 'id' ] ?>"><?php _e( 'Text', parent::$textdomain ); ?></label><br />
											<textarea id="text_<?php echo $post[ 'id' ] ?>" rows="5" class="large-text"><?php echo $post[ 'content_text' ]; ?></textarea>
											
											<input <?php if ( isset( $post[ 'show_in_contents' ] ) && 'on' == $post[ 'show_in_contents' ] ) echo 'checked="checked"' ?> id="show_in_contents_<?php echo $post[ 'id' ]; ?>" name="show_in_contents_<?php echo $post[ 'id' ]; ?>" type="checkbox" /> <label for="show_in_contents_<?php echo $post[ 'id' ]; ?>"><?php _e( 'Show Spacer in Contents only', parent::$textdomain ); ?></label><br />
											
											<input <?php if ( isset( $post[ 'dont_show_in_pdf' ] ) && 'on' == $post[ 'dont_show_in_pdf' ] ) echo 'checked="checked"' ?> id="dont_show_in_pdf_<?php echo $post[ 'id' ]; ?>" name="dont_show_in_pdf_<?php echo $post[ 'id' ]; ?>" type="checkbox" /> <label for="dont_show_in_pdf_<?php echo $post[ 'id' ]; ?>"><?php _e( 'Don\'t show this spacer in PDF', parent::$textdomain ); ?></label><br /><br />
											
											<a id="save_spacer" href="<?php echo $post[ 'id' ] ?>" class="button-secondary"><?php _e( 'Save Spacer', parent::$textdomain ); ?></a><br /><br />
											<span class="submitbox"><a href="<?php echo $post[ 'id' ] ?>" id="remove_spacer" class="submitdelete"><?php _e( 'Delete Spacer', parent::$textdomain ) ?></a></span>
										</div>
									</div>
									<?php
								} else {
									?>
									<div id="<?php echo $post[ 'id' ]; ?>" class="stuffbox post-sortable" style="background: #fff;">
										<div class="handlediv" title="<?php _e( 'Click to toggle', parent::$textdomain ); ?>"><br /></div>
										<h3 class="hndle"><?php echo get_the_title( $post[ 'id' ] ); ?></span></h3>
										<div class="inside" style="display: none;">
											<?php
												// Get Standard-Settings
												if ( isset( $standard_template[ 'post_thumbnails' ] ) )
													$show_post_thumbnail = $standard_template[ 'post_thumbnails' ];
												else
													$show_post_thumbnail = FALSE;
												
												if ( isset( $standard_template[ 'excerpt' ] ) && 'on' == $standard_template[ 'excerpt' ] ) {
													$show_content = 'off';
													$show_excerpt = 'on';
													$show_link = 'off';
												} else {
													$show_content = 'on';
													$show_excerpt = 'off';
													$show_link = 'off';
												}
												
												// Compare with post meta
												if ( '' != get_post_meta( $post[ 'id' ], 'show_post_thumbnail', TRUE ) )
													$show_post_thumbnail = get_post_meta( $post[ 'id' ], 'show_post_thumbnail', TRUE );
												
												if ( '' != get_post_meta( $post[ 'id' ], 'donot_show_title', TRUE ) )
													$donot_show_title = get_post_meta( $post[ 'id' ], 'donot_show_title', TRUE );
												else
													$donot_show_title = 'off';
												
												if ( '' != get_post_meta( $post[ 'id' ], 'dont_show_in_pdf', TRUE ) )
													$dont_show_in_pdf = get_post_meta( $post[ 'id' ], 'dont_show_in_pdf', TRUE );
												else
													$dont_show_in_pdf = 'off';
												
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
												
												// PDF Content
												if ( isset( $pdf_options[ 'excerpt' ] ) && 'on' == $pdf_options[ 'excerpt' ] ) {
													$show_pdf_content = 'off';
													$show_pdf_excerpt = 'on';
												} else {
													$show_pdf_content = 'on';
													$show_pdf_excerpt = 'off';
												}
												
												if ( '' != get_post_meta( $post[ 'id' ], 'show_pdf_content', TRUE ) ) {
													if ( 'full' == get_post_meta( $post[ 'id' ], 'show_pdf_content', TRUE ) ) {
														$show_pdf_content = 'on';
														$show_pdf_excerpt = 'off';
													} else if ( 'excerpt' == get_post_meta( $post[ 'id' ], 'show_pdf_content', TRUE ) ) {
														$show_pdf_content = 'off';
														$show_pdf_excerpt = 'on';
													}
												}
											?>
											
											<strong><?php _e( 'Additional Options', parent::$textdomain ); ?></strong><br />
											<input <?php if ( 'on' == $show_post_thumbnail ) echo 'checked="checked"' ?> id="show_post_thumbnail_<?php echo $post[ 'id' ]; ?>" name="show_post_thumbnail" type="checkbox" /> <label for="show_post_thumbnail_<?php echo $post[ 'id' ]; ?>"><?php _e( 'Show Post Thumbnail', parent::$textdomain ); ?></label><br /><br />
											
											<strong><?php _e( 'Title', parent::$textdomain ); ?></strong><br />
											<input <?php if ( isset( $donot_show_title ) && 'on' == $donot_show_title ) echo 'checked="checked"' ?> id="donot_show_title_<?php echo $post[ 'id' ]; ?>" name="donot_show_title_<?php echo $post[ 'id' ]; ?>" type="checkbox" /> <label for="donot_show_title_<?php echo $post[ 'id' ]; ?>"><?php _e( 'Donot show Title', parent::$textdomain ); ?></label><br /><br />
											
											<strong><?php _e( 'Content Options', parent::$textdomain ); ?></strong><br />
											<input id="show_content_<?php echo $post[ 'id' ]; ?>_content" name="show_content_<?php echo $post[ 'id' ]; ?>" type="radio" value="content" <?php if ( 'on' == $show_content ) echo 'checked="checked"' ?> /> <label for="show_content_<?php echo $post[ 'id' ]; ?>_content"><?php _e( 'Show full content', parent::$textdomain ); ?></label><br />
											<input id="show_content_<?php echo $post[ 'id' ]; ?>_excerpt" name="show_content_<?php echo $post[ 'id' ]; ?>" type="radio" value="excerpt"  <?php if ( 'on' == $show_excerpt ) echo 'checked="checked"' ?>/> <label for="show_content_<?php echo $post[ 'id' ]; ?>_excerpt"><?php _e( 'Only show excerpt', parent::$textdomain ); ?></label><br />
											<input id="show_content_<?php echo $post[ 'id' ]; ?>_link" name="show_content_<?php echo $post[ 'id' ]; ?>" type="radio" value="link" <?php if ( 'on' == $show_link ) echo 'checked="checked"' ?> /> <label for="show_content_<?php echo $post[ 'id' ]; ?>_link"><?php _e( 'Only show the link', parent::$textdomain ); ?></label><br /><br />
											
											<strong><?php _e( 'PDF Content Options', parent::$textdomain ); ?></strong><br />
											<input id="show_pdf_content_<?php echo $post[ 'id' ]; ?>_content" name="show_pdf_content_<?php echo $post[ 'id' ]; ?>" type="radio" value="content" <?php if ( 'on' == $show_pdf_content ) echo 'checked="checked"' ?> /> <label for="show_pdf_content_<?php echo $post[ 'id' ]; ?>_content"><?php _e( 'Show full content', parent::$textdomain ); ?></label><br />
											<input id="show_pdf_content_<?php echo $post[ 'id' ]; ?>_excerpt" name="show_pdf_content_<?php echo $post[ 'id' ]; ?>" type="radio" value="excerpt"  <?php if ( 'on' == $show_pdf_excerpt ) echo 'checked="checked"' ?>/> <label for="show_pdf_content_<?php echo $post[ 'id' ]; ?>_excerpt"><?php _e( 'Only show excerpt', parent::$textdomain ); ?></label><br /><br />
											
											<strong><?php _e( 'PDF Settings', parent::$textdomain ); ?></strong><br />
											<input <?php if ( isset( $dont_show_in_pdf ) && 'on' == $dont_show_in_pdf ) echo 'checked="checked"' ?> id="dont_show_in_pdf_<?php echo $post[ 'id' ]; ?>" name="dont_show_in_pdf_<?php echo $post[ 'id' ]; ?>" type="checkbox" /> <label for="dont_show_in_pdf_<?php echo $post[ 'id' ]; ?>"><?php _e( 'Donot show this post in pdf', parent::$textdomain ); ?></label><br /><br />
											
											<a id="save_post" href="<?php echo $post[ 'id' ] ?>" edition="<?php echo $edition; ?>" letter_slug="<?php echo $letter_slug; ?>" class="button-secondary"><?php _e( 'Save Post Settings', parent::$textdomain ); ?></a><br /><br />
											<span class="submitbox"><a href="#" class="submitdelete remove-post-from-edition" edition="<?php echo $edition; ?>" post="<?php echo $post[ 'id' ] ?>"><?php _e( 'Remove Post from edition', parent::$textdomain ); ?></a>
										</div>
									</div>
									<?php
								}
							}
							?>
						</div>
					</div>
				</div>
			</form>
			
			<?php
		}
		
		/**
		 * Save the post order
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, update_option
		 * @return	void
		 */
		public function ajax_save_post_order() {
			
			$posts = explode( ',',  $_POST[ 'order' ][ 'posts' ] );
			$new_newsletter = array();
			$current_newsletter = get_option( 'newsletter_' . $_POST[ 'letter_slug' ] );
			
			foreach ( $posts as $post_id )
				foreach ( $current_newsletter as $position => $post_vars )
					if ( $post_id == $post_vars[ 'id' ] )
						$new_newsletter[] = $post_vars;
			
			update_option( 'newsletter_' . $_POST[ 'letter_slug' ], $new_newsletter );
			
			// Rebuild Preview
			$text = nl2br( Multipost_Newsletter_Generate_Text::generate_text( $_POST[ 'edition' ], $_POST[ 'letter_slug' ] ) );
			$mpnl = Multipost_Newsletter::get_instance();
			if ( TRUE == $mpnl::$is_pro )
				$html = Multipost_Newsletter_Generate_HTML::generate_html( $_POST[ 'edition' ], $_POST[ 'letter_slug' ] );
			else
				$html = __( 'You have to purchase the pro-version of this plugin to generate an HTML-Newsletter', parent::$textdomain );
			$response = array( 'text' => $text, 'html' => $html );
			echo json_encode( $response );
			
			exit;
		}
		
		/**
		 * Retrive the spacer code
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, _e, __, update_option
		 * @return	void
		 */
		public function get_spacer_code() {
			
			$html = '';
			$text = '';
			
			if ( '' != $_POST[ 'spacer' ] ) {
				$spacers = get_option( 'mp-newsletter-spacers' );
				
				if ( isset( $spacers[ $_POST[ 'spacer' ] ][ 'html' ] ) )
					$html = $spacers[ $_POST[ 'spacer' ] ][ 'html' ];
				else
					$html = '';
				
				if ( isset( $spacers[ $_POST[ 'spacer' ] ][ 'text' ] ) )
					$text = $spacers[ $_POST[ 'spacer' ] ][ 'text' ];
				else
					$text = '';
			}
			?>
			<div id="<?php echo $_POST[ 'id' ]; ?>" class="stuffbox post-sortable" style="display: none; background: #fff;">
				<div class="handlediv" title="<?php _e( 'Click to toggle', parent::$textdomain ); ?>"><br /></div>
				<h3 class="hndle">
					<?php _e( 'Spacer', parent::$textdomain ); ?>
				</h3>
				<div class="inside">
					
					<label for="html_<?php echo $_POST[ 'id' ] ?>"><?php _e( 'HTML', parent::$textdomain ); ?></label><br />
					<textarea id="html_<?php echo $_POST[ 'id' ] ?>" rows="5" class="large-text"><?php echo $html; ?></textarea>
					
					<label for="text_<?php echo $_POST[ 'id' ] ?>"><?php _e( 'Text', parent::$textdomain ); ?></label><br />
					<textarea id="text_<?php echo $_POST[ 'id' ] ?>" rows="5" class="large-text"><?php echo $text; ?></textarea>
					
					<input id="show_in_contents_<?php echo $_POST[ 'id' ]; ?>" name="show_in_contents_<?php echo $_POST[ 'id' ]; ?>" type="checkbox" /> <label for="show_in_contents_<?php echo $_POST[ 'id' ]; ?>"><?php _e( 'Show Spacer in Contents only', parent::$textdomain ); ?></label><br />
					
					<input id="dont_show_in_pdf_<?php echo $_POST[ 'id' ]; ?>" name="dont_show_in_pdf_<?php echo $_POST[ 'id' ]; ?>" type="checkbox" /> <label for="dont_show_in_pdf_<?php echo $_POST[ 'id' ]; ?>"><?php _e( 'Don\'t show this spacer in PDF', parent::$textdomain ); ?></label><br />
				
					<a id="save_spacer"  href="<?php echo $_POST[ 'id' ] ?>" class="button-secondary" style="float: right;"><?php _e( 'Save Spacer', parent::$textdomain ); ?></a><br /><br />
					<span class="submitbox"><a href="<?php echo $_POST[ 'id' ] ?>" id="remove_spacer" class="submitdelete"><?php _e( 'Delete Spacer', parent::$textdomain ) ?></a></span>
				</div>
			</div>
			<?php
			
			$current_newsletter = get_option( 'newsletter_' . $_POST[ 'letter_slug' ] );
			$new_newsletter[] = array(
				'type'	=> 'spacer',
				'id'	=> $_POST[ 'id' ]
			);
			foreach ( $current_newsletter as $position => $post )
				$new_newsletter[] = $post;

			update_option( 'newsletter_' . $_POST[ 'letter_slug' ], $new_newsletter );
			exit;
		}
		
		/**
		 * Removes a spacer from the current edition
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, update_option
		 * @return	void
		 */
		public function remove_spacer() {
			
			$current_newsletter = get_option( 'newsletter_' . $_POST[ 'letter_slug' ] );
			foreach ( $current_newsletter as $position => $post )
				if ( $post[ 'id' ] == $_POST[ 'id' ] )
					unset( $current_newsletter[ $position ] );
				
			update_option( 'newsletter_' . $_POST[ 'letter_slug' ], $current_newsletter );
			
			// Rebuild Preview
			$text = nl2br( Multipost_Newsletter_Generate_Text::generate_text( $_POST[ 'edition' ], $_POST[ 'letter_slug' ] ) );
			$mpnl = Multipost_Newsletter::get_instance();
			if ( TRUE == $mpnl::$is_pro )
				$html = Multipost_Newsletter_Generate_HTML::generate_html( $_POST[ 'edition' ], $_POST[ 'letter_slug' ] );
			else
				$html = __( 'You have to purchase the pro-version of this plugin to generate an HTML-Newsletter', parent::$textdomain );
			$response = array( 'text' => $text, 'html' => $html );
			echo json_encode( $response );
			exit;
		}
		
		/**
		 * Saves a spacer
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, update_option, _e
		 * @return	void
		 */
		public function save_spacer() {
			
			$_POST = array_map( 'stripslashes_deep', $_POST );

			$current_newsletter = get_option( 'newsletter_' . $_POST[ 'letter_slug' ] );
			foreach ( $current_newsletter as $position => $post )
				if ( $post[ 'id' ] == $_POST[ 'id' ] )
					$current_newsletter[ $position ] = array(
						'type'				=> 'spacer',
						'id'				=> $_POST[ 'id' ],
						'show_in_contents'	=> $_POST[ 'show_in_contents' ],
						'dont_show_in_pdf'	=> $_POST[ 'dont_show_in_pdf' ],
						'content_html'		=> $_POST[ 'content_html' ],
						'content_text'		=> $_POST[ 'content_text' ],
					);
			
			update_option( 'newsletter_' . $_POST[ 'letter_slug' ], $current_newsletter );
			
			// Rebuild Preview
			$text = nl2br( Multipost_Newsletter_Generate_Text::generate_text( $_POST[ 'edition' ], $_POST[ 'letter_slug' ] ) );
			$mpnl = Multipost_Newsletter::get_instance();
			if ( TRUE == $mpnl::$is_pro )
				$html = Multipost_Newsletter_Generate_HTML::generate_html( $_POST[ 'edition' ], $_POST[ 'letter_slug' ] );
			else
				$html = __( 'You have to purchase the pro-version of this plugin to generate an HTML-Newsletter', parent::$textdomain );
			$response = array( 'text' => $text, 'html' => $html );
			echo json_encode( $response );
			
			exit;
		}
		
		/**
		 * Saves the post settings
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	update_post_meta, _e
		 * @return	void
		 */
		public function save_post_settings() {
			
			if ( ! isset( $_POST[ 'donot_show_title' ] ) )
				$_POST[ 'donot_show_title' ] = FALSE;
			if ( ! isset( $_POST[ 'show_post_thumbnail' ] ) )
				$_POST[ 'show_post_thumbnail' ] = FALSE;
			if ( ! isset( $_POST[ 'content' ] ) )
				$_POST[ 'content' ] = FALSE;
			if ( ! isset( $_POST[ 'dont_show_in_pdf' ] ) )
				$_POST[ 'dont_show_in_pdf' ] = FALSE;
			if ( ! isset( $_POST[ 'show_pdf_content' ] ) )
				$_POST[ 'show_pdf_content' ] = FALSE;
			
			update_post_meta( $_POST[ 'id' ], 'donot_show_title', $_POST[ 'donot_show_title' ] );
			update_post_meta( $_POST[ 'id' ], 'show_post_thumbnail', $_POST[ 'show_post_thumbnail' ] );
			update_post_meta( $_POST[ 'id' ], 'show_content', $_POST[ 'content' ] );
			update_post_meta( $_POST[ 'id' ], 'dont_show_in_pdf', $_POST[ 'dont_show_in_pdf' ] );
			update_post_meta( $_POST[ 'id' ], 'show_pdf_content', $_POST[ 'show_pdf_content' ] );
			
			// Rebuild Preview
			$text = nl2br( Multipost_Newsletter_Generate_Text::generate_text( $_POST[ 'edition' ], $_POST[ 'letter_slug' ] ) );
			$mpnl = Multipost_Newsletter::get_instance();
			if ( TRUE == $mpnl::$is_pro )
				$html = Multipost_Newsletter_Generate_HTML::generate_html( $_POST[ 'edition' ], $_POST[ 'letter_slug' ] );
			else
				$html = __( 'You have to purchase the pro-version of this plugin to generate an HTML-Newsletter', parent::$textdomain );
			$response = array( 'text' => $text, 'html' => $html );
			echo json_encode( $response );
			exit;
		}
		
		/**
		 * Removes a post from an edition
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	get_option, update_option
		 * @return	void
		 */
		public function remove_post_from_edition() {
			
			// Remove from the current object list
			$current_newsletter = get_option( 'newsletter_' . $_POST[ 'letter_slug' ] );
			foreach ( $current_newsletter as $position => $post )
				if ( $post[ 'id' ] == $_POST[ 'post_id' ] )
					unset( $current_newsletter[ $position ] );
			
			update_option( 'newsletter_' . $_POST[ 'letter_slug' ], $current_newsletter );
			
			// Remove the object term
			global $wpdb;
			$term = get_term_by( 'slug', $_POST[ 'edition' ], 'newsletter' );
			$delete_term_relationship = $wpdb->prepare( 'DELETE FROM ' . $wpdb->term_relationships . ' WHERE `object_id` = %d AND `term_taxonomy_id` = %d', $_POST[ 'post_id' ], $term->term_taxonomy_id );
			$decrease_count = $wpdb->prepare( 'UPDATE ' . $wpdb->term_taxonomy . ' SET count = count - 1 WHERE `term_taxonomy_id` = %d', $term->term_taxonomy_id );
			$wpdb->query( $delete_term_relationship );
			$wpdb->query( $decrease_count );
			
			// Rebuild Preview
			$text = nl2br( Multipost_Newsletter_Generate_Text::generate_text( $_POST[ 'edition' ], $_POST[ 'letter_slug' ] ) );
			$mpnl = Multipost_Newsletter::get_instance();
			if ( TRUE == $mpnl::$is_pro )
				$html = Multipost_Newsletter_Generate_HTML::generate_html( $_POST[ 'edition' ], $_POST[ 'letter_slug' ] );
			else
				$html = __( 'You have to purchase the pro-version of this plugin to generate an HTML-Newsletter', parent::$textdomain );
			$response = array( 'text' => $text, 'html' => $html );
			echo json_encode( $response );
			exit;
		}
		
		/**
		 * Generate the pdf, the ajax way
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	Multipost_Newsletter_Generate, __
		 * @return	void
		 */
		public function ajax_generate_pdf() {
				
			$pdf = Multipost_Newsletter_Generate_PDF::generate_pdf( $_POST[ 'edition' ], $_POST[ 'letter_slug' ] );
			
			echo '<a href="' . $pdf->pdf_link . '" target="_blank">' . __( 'Download PDF', parent::$textdomain ) . '</a>';
			
			exit;
		}
		
		
		/**
		 * Generates the preview
		 *
		 * @since	0.1
		 * @access	public
		 * @uses	__, _e, Multipost_Newsletter_Generate
		 * @return	void
		 */
		public function generate_preview( $edition, $subject_area ) {
			
			// Set Edition
			$letter_slug = $edition . '-' . $subject_area;
			
			// Get Instance
			$self = self::get_instance();
			?>
			<div class="updated">
				<p><?php _e( '<strong>Important Note:</strong> The settings made here will be automatically saved. If you finished the prepare process, you can head to Newsletter Create.', parent::$textdomain ); ?></p>
			</div>
			
			<div id="menu-management" style="position: absolute; margin-top: -27px;">
			
				<div class="nav-tabs">
					<a href="#" class="nav-tab hide-if-no-js nav-tab-active" id="link_text_preview"><?php _e( 'Text-Preview', parent::$textdomain ) ?></a>
					<a href="#" class="nav-tab hide-if-no-js" id="link_html_preview"><?php _e( 'HTML-Preview', parent::$textdomain ) ?></a>
					<?php
					$pdf_options = get_option( 'mp-newsletter-pdf' );
					if ( FALSE == parent::$is_pro )
						$pdf_options[ 'dont_generate_pdf' ] = '';
					
					if ( ! isset( $pdf_options[ 'dont_generate_pdf' ] ) || $pdf_options[ 'dont_generate_pdf' ] != 'on' ) { ?>
						<a href="#" class="nav-tab hide-if-no-js" id="link_pdf_preview"><?php _e( 'Download-PDF', parent::$textdomain ) ?></a>
					<?php } ?>
				</div>
			</div>
			
			<div id="settings" class="stuffbox" style="clear: both; margin: 25px 0 10px;">
				<h3 class="hndle" style="cursor: default;"><span><?php _e( 'Newsletter Preview', parent::$textdomain ); ?></span></h3>
				<div id="text-preview" class="inside">
					<?php echo nl2br( Multipost_Newsletter_Generate_Text::generate_text( $edition, $letter_slug ) ); ?>
				</div>
				<div id="html-preview" class="inside" style="display: none;">
					<?php
						if ( TRUE == parent::$is_pro )
							echo Multipost_Newsletter_Generate_HTML::generate_html( $edition, $letter_slug );
						else
							_e( 'You have to purchase the pro-version of this plugin to generate an HTML-Newsletter', parent::$textdomain );
					?>
				</div>
				<div id="pdf-preview" class="inside" style="display: none;">
					<?php
						if ( TRUE == parent::$is_pro )
							_e( 'Generate PDF, please wait', parent::$textdomain );
						else
							_e( 'You have to purchase the pro-version of this plugin to generate a PDF-Newsletter', parent::$textdomain );
					?>
				</div>
			</div>
			<?php
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		Multipost_Newsletter_Prepare::get_instance();
}