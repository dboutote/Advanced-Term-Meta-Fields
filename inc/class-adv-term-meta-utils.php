<?php

// No direct access
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Group of utility methods for use by Advanced_Term_Meta_Fields
 *
 * All methods are static, this is basically a namespacing class wrapper.
 *
 * @since 0.1.0
 *
 */
class ATMF_Utils {

	/**
	 * @var string $plugin_name name of the plugin
	 * @static
	 */
	public static $plugin_name = 'Advanced Term Meta Fields';


	/**
	 * @var string $required_version minimum version required for this plugin
	 * @static
	 */
	public static $required_version = '4.4';



	static function compatibility_check()
	{
		if ( ! self::compatible_version() ) {
			add_action( 'admin_init', array(__CLASS__, '_plugin_deactivate') );
			add_action( 'admin_notices', array(__CLASS__, '_plugin_admin_notice') );
		}
	}


	static function _plugin_deactivate() {
		deactivate_plugins( plugin_basename( ADV_TERM_META_FILE ) );
	}


	static function _plugin_admin_notice() {
		echo '<div class="error"><p>'
			. sprintf(
				__( '%1$s requires WordPress %2$s to function correctly. Unable to activate at this time.', 'wptt' ),
				'<strong>' . esc_html( self::$plugin_name ) . '</strong>',
				'<strong>' . esc_html( self::$required_version ) . '</strong>'
				)
			. '</p></div>';

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}


	static function compatible_version()
	{
		if ( version_compare( $GLOBALS['wp_version'], self::$required_version, '>=' ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Return the taxonomies used by this plugin
	 *
	 * @since 0.1.0
	 *
	 * @param array $args
	 * @param $meta_key The meta key for the meta function making the request
	 * 
	 * @return array
	 */
	static function get_taxonomies( $args = array() , $meta_key = '' ) {

		$defaults = apply_filters( "advanced_term_meta_fields_get_taxonomies_args", array( 'show_ui' => true ) );
		$defaults = apply_filters( "advanced_term_meta_fields_{$meta_key}_get_taxonomies_args", $defaults );

		$r = wp_parse_args( $args, $defaults );

		$taxonomies = get_taxonomies( $r );

		return apply_filters('advanced_term_meta_fields_taxonomies', $taxonomies);
	}

}