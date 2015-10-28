<?php
// **********************************************************
// If you want to have an own template for the confirmation
// just copy the following code to your
// confirmunsubscription.php in your template

if ( get_query_var( 'mpnlkey' ) != '' )
	mpnl_confirm_unsubscription( get_query_var( 'mpnlkey' ) );
else
	wp_die( __( 'Sorry, you have to give us a key to work with.', get_mpnl_textdomain() ) );

// Okay, stop copying here. If you want to add a message to
// the current user, do it below like I do
// **********************************************************
?>

<html>
	<head>
		<title><?php _e( 'Unsubscribtion', get_mpnl_textdomain() ); ?> &raquo; <?php bloginfo( 'name' ) ?></title>
		<meta name="robots" content="noindex">
		<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
		<?php wp_head(); ?>
	</head>
	<body>
		<?php get_header(); ?>
		<div id="content">
			<p><?php _e( 'You successfully unsubscribed from our newsletter.', get_mpnl_textdomain() ); ?></p>
			<p><?php echo sprintf( __( 'The %s Team', get_mpnl_textdomain() ), get_bloginfo( 'name' ) ); ?></p>
		</div>
		<?php get_footer(); ?>
		<?php wp_footer(); ?>
	</body>
</html>