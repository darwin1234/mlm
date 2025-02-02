<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://www.ibsofts.com
 * @since      2.0.2
 *
 * @package    GHLCONNECTPRO
 * @subpackage GHLCONNECTPRO/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      2.0.2
 * @package    GHLCONNECTPRO
 * @subpackage GHLCONNECTPRO/includes
 * @author     iB Softs <ibsofts@gmail.com>
 */
class GHLCONNECTPRO_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    2.0.2
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ghl-connect-pro',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
