=== WKWC Addons ===
Contributors: Webkul
Tested up to: 6.4
Stable tag: 1.1.7
Requires PHP: 7.4
Tested up to PHP: 8.3
WC tested up to: 8.5
WPML Compatible: yes
Multisite Compatible: yes

Tags: webkul addons, wc addons, global menu.

It adds a global admin menu as 'Webkul WC Addos' and facilitates all Webkul WC addons to added under this menu as a menu.

== Installation ==

1. Open terminal for the root of the plugin in which you are adding this submodule.
2. Add submodule using git command. `git submodule add {repo link}`
3. Copy the autoload file from any implemented module like Group Buy, Wallet system etc.
4. Add two plugin headers * WKWC_Addons: {latest version} & * WKWC_Settings: {setting page slug}
5. Go to the Webkul WC Addons menu to access the settings.

== Frequently Asked Questions ==
No questions asked yet

== Feel free to do so. ==
For any Query please generate a ticket at https://webkul.com/ticket/

== 1.1.7 (24-01-11)==
Added: Notice wrapper function for using new wp_add_notice function.
Updated: WordPress Coding standard according to phpcs-3.8.0

== 1.1.5 (23-12-20)==
Added: Last working date and time info in footer on param.
Fixed: WP Coding standard issues using code sniffer tools.

== 1.1.4 (23-11-24)==
Updated: 'WKWC Addons' menu page priority from 55 to arbitrary fractions number 54.978 to avoid collision with other plugin like WCFM also sets at 55.

== 1.1.3 (23-10-27)==
Updated: autoload file to load the submodule from root file of the main module using filter.
Updated: Latest PHP (8.3), WP (6.4) and WC(8.3) version tested up to values.

== 1.1.2 (23-10-11)==
Added: JS file to trigger click on WooCommerce Addons tab on extensions page loaded.

== 1.1.0 (23-09-21)==
Added: Function to create tabs for each modules.
Updated: Card layout with icons and responsive design.

== 1.0.0 (23-09-05)==
Initial release
