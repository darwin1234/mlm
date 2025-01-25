/**
 * WC Addons JS.
 */
"use strict";
var wkwcaddJQ = jQuery.noConflict();

wkwcaddJQ(document).ready(function () {
    // Displaying WooCommerce addons.
    if (wkwcaddJQ('.__wk_ext-extension-body').length) {
        console.log('loaded');
        setTimeout(() => {
            wkwc_trigger_wc_addon_click(1000);
        }, 1000);
    }

    function wkwc_trigger_wc_addon_click(time) {
        let interval = 1000;
        if (wkwcaddJQ(".__wk_ext-active-tab").length) {
            wkwcaddJQ('.__wk_ext-border-color ul li:nth-child(4)').trigger('click');
        } else {
            setTimeout(() => {
                if (time < 10000) {
                    time = time + interval;
                    wkwc_trigger_wc_addon_click(time);
                }
            }, interval);
         }
    }
});

