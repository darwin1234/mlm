/**
 * Admin js.
 */
"use strict";
var bmlm = jQuery.noConflict();
bmlm(document).ready(function () {
   if (bmlm(".bmlm_upload_badge").length) {
      bmlm(".bmlm_upload_badge").on("click", function (e) {
         var bannerUpload;
         e.preventDefault();
         var bannerUpload = wp
            .media({
               title: "Banner",
               button: {
                  text: "Upload",
               },
               multiple: false,
            })
            .on("select", function () {
               var attachments = bannerUpload
                  .state()
                  .get("selection")
                  .first()
                  .toJSON();
               bmlm(".bmlm-badge-img").attr("src", attachments.url);
               bmlm("#bmlm-badge-image").val(attachments.id);
            })
            .open();
      });
   }
   bmlm(".sponsor-commission-rule-table").on("click", ".action-delete", (e) => {
      bmlm(e.target).closest("tr").remove();
   });
   bmlm("#addToEndBtn").on("click", (e) => {
      let _length = bmlm(".sponsor-commission-rule-table tbody tr").length;
      let data = {};
      data.key = _length;
      let loaderElm = wp.template("table_row_template");
      bmlm(".sponsor-commission-rule-table tbody").append(loaderElm(data));
   });
   bmlm(".visiblity-action").on("change", (e) => {
      let _selectedValue = bmlm(e.target).val();
      if (_selectedValue == 1) {
         bmlm("tr.bmlm-visiblity").removeClass("hide");
      } else {
         bmlm("tr.bmlm-visiblity").removeClass("hide").addClass("hide");
      }
   });

   // wallet search.
   if (bmlm("#wallet-customer").length) {
      bmlm("#wallet-customer").select2({
         ajax: {
            method: "GET",
            url: bmlm_vars.ajax.ajaxUrl, // AJAX URL is predefined in WordPress admin
            dataType: "json",
            delay: 250, // delay in ms while typing when to perform a AJAX search
            data: function (params) {
               return {
                  query: params.term, // search query
                  action: "bmlm_wallet_get_sponsors_list",
                  security: bmlm_vars.ajax.ajaxNonce,
               };
            },
            processResults: function (response) {
               var options = [];
               if (!response.error && response.data != null) {
                  // users is the array of objects, and each of them contains value and the Label of the option
                  response.data.forEach((user) => {
                     options.push({
                        id: user.ID,
                        text: user.user_email + " ( " + user.user_login + " ) ",
                     });
                  });
               }
               return {
                  results: options,
               };
            },
            cache: true,
         },
         width: "350px",
         multiple: true,
         minimumInputLength: 3, // the minimum of symbols to input before perform a search
         language: {
            inputTooShort: function (args) {
               let remain = args.minimum - args.input.length;
               return bmlm_vars.ajax.i18n_input_too_short_n.replace(
                  "%qty%",
                  remain,
               );
            },
            noResults: function () {
               return bmlm_vars.ajax.i18n_no_matches;
            },
         },
      });
   }

    if (bmlm('select[name="role"]').length) {
        let _selectedValue = bmlm('select[name="role"]').val();
        if ("bmlm_sponsor" === _selectedValue) {
           var data = {};
           data.id = "refferal";
           let sponsorElm = wp.template("sponsor_template");
           bmlm("#createuser table tbody").append(sponsorElm(data));
        }
        bmlm('select[name="role"]').on("change", (e) => {
         if ("bmlm_sponsor" === bmlm(e.target).val()) {
            var data = {};
            data.id = "refferal";
            let sponsorElm = wp.template("sponsor_template");
            bmlm("#createuser table tbody").append(sponsorElm(data));
         } else {
             bmlm("tr#refferal").remove();
         }
      });
   }
    // Pay commission amount.
    bmlm(".bmlm-pay-commission").on("click", function (e) {
        e.preventDefault();
        var cid = bmlm(e.target).data("cid");
        if (cid) {
            bmlm.ajax({
               url: bmlm_vars.ajax.ajaxUrl,
               type: "POST",
               beforeSend: function () {
                  // setting a timeout.
                  bmlm(e.target).addClass("disabled");
               },
               data: {
                  action: "bmlm_pay_commission",
                  cid: cid,
                  nonce: bmlm_vars.ajax.ajaxNonce,
               },
               success: function (response) {
                   alert(response.message);
                   location.reload();
               },
            });
        }
    });
});

bmlm(function(){
    var dtToday = new Date();
    var month = dtToday.getMonth() + 1;
    var day = dtToday.getDate();
    var year = dtToday.getFullYear();
    if(month < 10)
        month = '0' + month.toString();
    if(day < 10)
        day = '0' + day.toString();

    var maxDate = year + '-' + month + '-' + day;
    bmlm('#bmlm-commission-to-date').attr('max', maxDate);
});
