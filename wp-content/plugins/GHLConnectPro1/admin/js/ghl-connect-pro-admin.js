jQuery(document).ready(function ($) {
  $.ajax({
    url: ghl_woo_sync.ajaxurl,
    type: "POST",
    data: {
      action: "ghl_check_sync_data",
    },
    success: function (response) {
      if (response.data.success === true) {
        if (response.data.sync_status === "yes") {
          // If sync_status is 'yes', change the description-ghl text
          $("p.description-ghl").text("All users are synced in GHL");
        } else {
          // Otherwise, show progress
          $("p.description-ghl").text("Sync Progress...");
        }
      }
    },
  });
});
