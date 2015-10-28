<?php
// **********************************************************
// If you want to have an own template for the unsubscription
// just copy the following code to your unsubscripe.php in
// your template

if ( isset( $_GET[ 'key' ] ) )
	mpnl_unsubscribe_recipient( $_GET[ 'key' ] );
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
			<p><?php _e( 'To unsubscribe from our newsletter, you will receive an e-mail with the confirmation link.', get_mpnl_textdomain() ); ?></p>
			<p><?php echo sprintf( __( 'The %s Team', get_mpnl_textdomain() ), get_bloginfo( 'name' ) ); ?></p>
		</div>
		<?php get_footer(); ?>
		<?php wp_footer(); ?>
	</body>
</html>