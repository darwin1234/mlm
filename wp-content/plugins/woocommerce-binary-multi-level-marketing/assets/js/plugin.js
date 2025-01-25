/**
 * Front js
 */
"use strict";
var bmlm = jQuery.noConflict();
bmlm(document).ready(function () {
   bmlm(".bmlm-tooltip-btn").on("click", (e) => {
      var copyText = bmlm(e.target).closest("td").find(".bmlm-input");
      copyText.select();
      copyText[0].setSelectionRange(0, 99999);
      document.execCommand("copy");
      var tooltip = bmlm(e.target).find(".bmlm-tooltiptext");
      tooltip.html("Copied: " + copyText.val());
   });
   bmlm(".bmlm-tooltip-btn").on("mouseout", (e) => {
      var tooltip = bmlm(e.target).find(".bmlm-tooltiptext");
      tooltip.html("Copy to clipboard");
   });
   if (bmlm(".bmlm-share-btn").length) {
      bmlm(".bmlm-share-btn").on("click", function (evt) {
         evt.preventDefault();
         bmlm(".bmlm-share-brick").remove();
         bmlm(evt.target).next(".bmlm-share-box").addClass("active");
         let url = bmlm(evt.target).data("url");
         let _sharetemplate = wp.template("bmlm-share-template");
         let data = {};
         data.url = url;
         bmlm(evt.target).next(".bmlm-share-box").append(_sharetemplate(data));
      });

      bmlm(document).mouseup(function (e) {
         var container = bmlm(".bmlm-share-brick");

         // if the target of the click isn't the container nor a descendant of the container
         if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.remove();
            bmlm(".bmlm-share-box").removeClass("active");
         }
      });
   }
});
