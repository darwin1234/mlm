=== Wallet Submodule ===
Contributors: Webkul
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.6
Requires PHP: 7.4
Tested up to PHP: 8.2
WC requires at least: 5.0
WC tested up to: 8.5
WPML Compatible: yes
Multisite Compatible: yes

Tags: woocommerce, wallet, currency, virtual currency, wallet gateway.

License: license.txt file included with plugin
License URI: https://store.webkul.com/license.html

It is a submodule and will be pulled into our other modules like 'Wallet System', 'WC Group Buy', 'MP Group Buy', and 'Binary MLM' to include the same feature in each module and avoid repeated work.

WooCommerce Wallet Submodule adds wallet related features like, wallet gateway, customer wallet, OTP verification etc.

== Installation ==

1. Add submodule to the plugin.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to the Customer Wallet

== Frequently Asked Questions ==
No questions asked yet

== Feel free to do so. ==
For any Query please generate a ticket at https://webkul.com/ticket/

== 1.0.6 (23-12-01) ==
Fixed: Wallet amount is not clearing on un-checking the wallet checkbox on checkout.
Fixed: Session getting expired on checkout while selecting wallet payment method.

== 1.0.5 (23-10-31) ==
Added: Background processing for migrating existing data from previous version of the module.
Added: Common OTP setting under wallet gateway for each module.
Added: WC-8 HPOS feature compatibility.
Fixed: Transaction filters and sorting were not working properly.
Fixed: OTP type setting not working properly.
Removed: Reference column from transaction listing pages as we now utilizing this data internally for differentiating transactions from different modules.

== 1.0.3 (23-07-13) ==
Added: Filter hooks for showing the OTP on our demo stores.
Added: Mail notification on each wallet transaction.
Fixed: Full payment checkout with wallet not working.
Fixed: Issues on sorting transaction table in admin.
Fixed: Credit/debit and store limit were not working.
Migrated: User's phone number for SMS OTP verification from Wallet system module to new key.

== 1.0.0 ==
Initial setup by extracting code form wc wallet system module.
