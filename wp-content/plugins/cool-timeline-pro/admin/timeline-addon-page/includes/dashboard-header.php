<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This php file renders HTML header for addons dashboard page
 */
if ( ! isset( $this->main_menu_slug ) ) {
	return;
}

$cool_plugins_docs      = esc_url( 'https://cooltimeline.com/docs/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_pro' );
$cool_plugins_more_info = esc_url( CTP_DEMO_URL );
?>

<div id="cool-plugins-container" class="<?php echo esc_attr( $this->main_menu_slug ); ?>">
	<div class="cool-header">
		<h2><?php echo esc_html( $this->dashboard_page_heading ); ?></h2>
		<a href="<?php echo $cool_plugins_docs; ?>" target="_docs" class="button"><?php esc_html_e( 'Docs', 'cool-timeline' ); ?></a>
		<a href="<?php echo $cool_plugins_more_info; ?>" target="_info" class="button"><?php esc_html_e( 'Demos', 'cool-timeline' ); ?></a>
	</div>
