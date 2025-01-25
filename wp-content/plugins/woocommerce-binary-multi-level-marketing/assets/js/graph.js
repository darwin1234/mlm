/**
 * Front Graph js.
 */
"use strict";
var bmlm = jQuery.noConflict();
document.addEventListener("DOMContentLoaded", function (event) {
   // Your code to run since DOM is loaded and ready
    let gross_report = JSON.parse(window.chart);
    let sale_report = gross_report.sale;
    let joining_report = gross_report.joining;
    let levelup_report = gross_report.levelup;
    let bonus_report = gross_report.bonus;
    var grossCTX = document.getElementById("grosshistogram").getContext("2d");
    var salesCTX = document
       .getElementById("bmlm-sales-graph")
        .getContext("2d");
    var joiningCTX = document
       .getElementById("bmlm-joining-graph")
       .getContext("2d");
    var levelupCTX = document
       .getElementById("bmlm-levelup-graph")
       .getContext("2d");
    var bonusCTX = document.getElementById("bmlm-bonus-graph").getContext("2d");
    let mixedDataset = [
       {
          label: "Sales",
          data: sale_report,
          backgroundColor: "rgba(153,255,51,0.6)",
       },
       {
          label: "Joining",
          data: joining_report,
          backgroundColor: "rgba(255,153,0,0.6)",
       },
       {
          label: "Levelup",
          data: levelup_report,
          backgroundColor: "rgb(255,0,132,0.6)",
       },
       {
          label: "Bonus",
          data: bonus_report,
          backgroundColor: "rgba(0,140,255,0.6)",
       },
    ];
    let saleDataset = [
       {
          label: "Sales",
          data: sale_report,
          backgroundColor: "rgba(153,255,51,0.6)",
       }
    ];
    let joiningDataset = [
       {
          label: "Joining",
          data: joining_report,
          backgroundColor: "rgba(255,153,0,0.6)",
       },
    ];
    let levelupDataset = [
       {
          label: "Level Up",
          data: levelup_report,
          backgroundColor: "rgb(255,0,132,0.6)",
       },
    ];
    let bonusDataset = [
       {
          label: "Bonus",
          data: bonus_report,
          backgroundColor: "rgba(0,140,255,0.6)",
       },
    ];
    generate_graph(grossCTX, "line", mixedDataset);
    generate_graph(salesCTX, "line", saleDataset);
    generate_graph(joiningCTX, "line", joiningDataset);
    generate_graph(levelupCTX, "line", levelupDataset);
    generate_graph(bonusCTX, "line", bonusDataset);

    function generate_graph(graph, type, dataset) {
        var myChart = new Chart(graph, {
           type: type,
           data: {
              datasets: dataset,
           },
           options: {
              responsive: true,
              scales: {
                 xAxes: [
                    {
                       display: true,
                       type: "time",
                       time: {
                          tooltipFormat: "ll HH:mm",
                          unit: "day",
                          unitStepSize: 4,
                          displayFormats: {
                             month: "MMM DD",
                          },
                       },
                    },
                 ],
              },
              legend: {
                 display: true,
              },
              animation: {
                 duration: 0,
              },
              hover: {
                 animationDuration: 0,
              },
              responsiveAnimationDuration: 0,
           },
        });
    }
});
