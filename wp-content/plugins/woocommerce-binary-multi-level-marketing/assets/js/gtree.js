/**
 * Gtree js.
 */
"use strict";
var bmlm = jQuery.noConflict();
    if (bmlm("#bmlmTree").length) {
       var _is_admin = bmlm_gtree.is_admin ? bmlm_gtree.is_admin : false;
       var params = {
          selector: "#bmlmTree",
          dataLoadUrl: bmlm_gtree.gtree,
          chartWidth: _is_admin
             ? window.innerWidth - 200
             : bmlm("#bmlm-full-container").innerWidth() - 100,
          chartHeight: window.innerHeight,
          funcs: {
             search: null,
             closeSearchBox: null,
             clearResult: null,
             findInTree: null,
             reflectResults: null,
             toggleFullScreen: null,
             locate: null,
          },
          data: null,
       };
       params.pristinaData = JSON.parse(bmlm_gtree.gtree);
       params.data = JSON.parse(bmlm_gtree.gtree);
       drawOrganizationChart(params);
       function drawOrganizationChart(params) {
           listen();

          params.funcs.expandAll = expandAll;
          params.funcs.collapsAll = collapsAll;
          params.funcs.search = searchUsers;
          params.funcs.closeSearchBox = closeSearchBox;
          params.funcs.findInTree = findInTree;
          params.funcs.clearResult = clearResult;
          params.funcs.reflectResults = reflectResults;
          params.funcs.toggleFullScreen = toggleFullScreen;
          params.funcs.locate = locate;

          var attrs = {
             EXPAND_SYMBOL: "\uf132",
             COLLAPSE_SYMBOL: "\uf460",
             selector: params.selector,
             root: params.data,
             width: params.chartWidth,
             height: params.chartHeight,
             index: 0,
             nodePadding: 9,
             collapseCircleRadius: 9,
             nodeHeight: 80,
             nodeWidth: 210,
             duration: 750,
             rootNodeTopMargin: 20,
             minMaxZoomProportions: [0.05, 3],
             linkLineSize: 180,
             collapsibleFontSize: "10px",
             userIcon: "\uf12e",
             nodeStroke: "#ccc",
             nodeStrokeWidth: "1px",
          };

          var dynamic = {};
          dynamic.nodeImageWidth = (attrs.nodeHeight * 100) / 140;
          dynamic.nodeImageHeight = attrs.nodeHeight - 2 * attrs.nodePadding;
          dynamic.nodeTextLeftMargin =
             attrs.nodePadding * 2 + dynamic.nodeImageWidth;
          dynamic.rootNodeLeftMargin = attrs.width / 2;
          dynamic.nodePositionNameTopMargin =
             attrs.nodePadding + 8 + (dynamic.nodeImageHeight / 4) * 1;
          dynamic.nodeChildCountTopMargin =
             attrs.nodePadding + 14 + (dynamic.nodeImageHeight / 4) * 3;

          var tree = d3.layout
             .tree()
             .nodeSize([attrs.nodeWidth + 40, attrs.nodeHeight]);
          var diagonal = d3.svg.diagonal().projection(function (d) {
             return [d.x + attrs.nodeWidth / 2, d.y + attrs.nodeHeight / 2];
          });

          var zoomBehaviours = d3.behavior
             .zoom()
             .scaleExtent(attrs.minMaxZoomProportions)
             .on("zoom", redraw);

          var svg = d3
             .select(attrs.selector)
             .append("svg")
             .attr("width", attrs.width)
             .attr("height", attrs.height)
             .call(zoomBehaviours)
             .append("g")
              .attr("transform", "translate(" + attrs.width / 3 + "," + 0 + ")");


          //necessary so that zoom knows where to zoom and unzoom from.
          zoomBehaviours.translate([
             dynamic.rootNodeLeftMargin,
             attrs.rootNodeTopMargin,
          ]);

          attrs.root.x0 = 0;
          attrs.root.y0 = dynamic.rootNodeLeftMargin;

          if (params.mode != "department") {
             // adding unique values to each node recursively.
             var uniq = 1;
             addPropertyRecursive(
                "uniqueIdentifier",
                function (v) {
                   return uniq++;
                },
                attrs.root,
             );
          }

          expand(attrs.root);
          if (attrs.root.children) {
             Object.values(attrs.root.children).forEach(collapse);
          }

          update(attrs.root);

          d3.select(attrs.selector).style("height", attrs.height);

          function update(source, param) {
             // Compute the new tree layout.
             var nodes = tree.nodes(attrs.root).reverse(),
                links = tree.links(nodes);

             // Normalize for fixed-depth.
             nodes.forEach(function (d) {
                d.y = d.depth * attrs.linkLineSize;
             });

             // Update the nodes…
             var node = svg.selectAll("g.node").data(nodes, function (d) {
                return d.id || (d.id = ++attrs.index);
             });

             // Enter any new nodes at the parent's previous position.
             var nodeEnter = node
                .enter()
                .append("g")
                .attr("class", "node bmlm-node")
                .attr("transform", function (d) {
                   return "translate(" + source.x0 + "," + source.y0 + ")";
                });

             var nodeGroup = nodeEnter.append("g").attr("class", "node-group");

             nodeGroup
                .append("rect")
                .attr("width", attrs.nodeWidth)
                 .attr("height", attrs.nodeHeight)
                 .attr("bmlm-data-sponsor-status", function (d) {
                   return (d.status==1)?'enabled':'disabled';
                })
                .attr("data-node-group-id", function (d) {
                   return d.uniqueIdentifier;
                })
                .attr("class", function (d) {
                   var res = "";
                   if (d.isLoggedUser) res += "nodeRepresentsCurrentUser ";
                   res +=
                      d._children || d.children
                         ? "nodeHasChildren"
                         : "nodeDoesNotHaveChildren";
                   return res;
                });

             var collapsiblesWrapper = nodeEnter
                .append("g")
                .attr("data-id", function (v) {
                   return v.uniqueIdentifier;
                });

            var collapsibles = collapsiblesWrapper
                .append("circle")
                .attr("class", "node-collapse bmlm-node-collapse")
                .attr("cx", attrs.nodeWidth/2)
                .attr("cy", attrs.nodeHeight - 1.5)
                .attr("", setCollapsibleSymbolProperty);

             //hide collapse rect when node does not have children.
             collapsibles
                .attr("r", function (d) {
                   if (d.children || d._children) return attrs.collapseCircleRadius;
                   return 0;
                })
                .attr("height", attrs.collapseCircleRadius);

             collapsiblesWrapper
                .append("text")
                .attr("class", "text-collapse")
                .attr("x", attrs.nodeWidth/2)
                .attr("y", attrs.nodeHeight+7)
                .attr("width", attrs.collapseCircleRadius)
                .attr("height", attrs.collapseCircleRadius)
                .style("font-family", "Dashicons")
                .style("font-size", "16px")
                .style("font-weight", "500")
                .attr("text-anchor", "middle")
                .text(function (d) {
                   return d.collapseText;
                });

             collapsiblesWrapper.on("click", click);

             nodeGroup
                .append("text")
                .attr("x", dynamic.nodeTextLeftMargin)
                .attr("y", attrs.nodePadding + 10)
                .attr("class", "bmlm-emp-name")
                .attr("text-anchor", "left")
                 .text(function (d) {
                     let dname = d.display_name.trim().toUpperCase().substring(0, 18);
                     if (dname.length < d.display_name.length) {
                      dname = dname.substring(0, 15) + "...";
                   }
                     return dname;
                })
                .call(wrap, attrs.nodeWidth);

             nodeGroup
                .append("text")
                .attr("x", dynamic.nodeTextLeftMargin)
                .attr("y", dynamic.nodePositionNameTopMargin)
                .attr("class", "bmlm-emp-position-name")
                .attr("dy", ".35em")
                .attr("text-anchor", "left")
                .text(function (d) {
                   var position = d.user_email.substring(0, 21);
                   if (position.length < d.user_email.length) {
                      position = position.substring(0, 18) + "...";
                   }
                   return position;
                });

             nodeGroup
                .append("text")
                .attr("x", dynamic.nodeTextLeftMargin)
                .attr(
                   "y",
                   attrs.nodePadding + 10 + (dynamic.nodeImageHeight / 4) * 2,
                )
                .attr("class", "bmlm-emp-registration-date")
                .attr("dy", ".35em")
                .attr("text-anchor", "left")

                .text(function (d) {
                   var mydate = new Date(d.user_registered);
                   return mydate.toLocaleString('default',{day: 'numeric', month: 'short', year: 'numeric'});
                });

             nodeGroup
                .append("text")
                .attr("x", attrs.nodeWidth-55)
                .attr("y", attrs.nodeHeight-5)
                .attr("class", "bmlm-emp-count-icon")
                .attr("text-anchor", "left")
                .style("font-family", "Dashicons")
                .text(function (d) {
                   return attrs.userIcon;
                });

             nodeGroup
                .append("text")
                .attr("x", attrs.nodeWidth - 35)
                .attr("y", attrs.nodeHeight-8)
                .attr("class", "bmlm-emp-count")
                .attr("text-anchor", "left")
                .text(function (d) {
                   return d.downline_member;
                });

             nodeGroup
                .append("defs")
                .append("svg:clipPath")
                .attr("id", "clip")
                .append("svg:rect")
                .attr("id", "clip-rect")
                .attr("rx", 3)
                .attr("x", attrs.nodePadding)
                .attr("y", 2 + attrs.nodePadding)
                .attr("width", dynamic.nodeImageWidth)
                .attr("fill", "none")
                .attr("height", dynamic.nodeImageHeight - 4);

             nodeGroup
                .append("svg:image")
                .attr("y", 2 + attrs.nodePadding)
                .attr("x", attrs.nodePadding)
                .attr("preserveAspectRatio", "none")
                .attr("width", dynamic.nodeImageWidth)
                .attr("height", dynamic.nodeImageHeight - 4)
                .attr("clip-path", "url(#clip)")
                .attr("xlink:href", function (v) {
                   return v.imageUrl;
                });

             // Transition nodes to their new position.
             var nodeUpdate = node
                .transition()
                .duration(attrs.duration)
                .attr("transform", function (d) {
                   return "translate(" + d.x + "," + d.y + ")";
                });

             // todo replace with attrs object.
             nodeUpdate
                .select("rect")
                .attr("width", attrs.nodeWidth)
                .attr("height", attrs.nodeHeight)
                .attr("rx", 3)
                .attr("stroke", function (d) {
                   if (param && d.uniqueIdentifier == param.locate) {
                      return "#a1ceed";
                   }
                   return attrs.nodeStroke;
                })
                .attr("stroke-width", function (d) {
                   if (param && d.uniqueIdentifier == param.locate) {
                      return 6;
                   }
                   return attrs.nodeStrokeWidth;
                });

             // Transition exiting nodes to the parent's new position.
             var nodeExit = node
                .exit()
                .transition()
                .duration(attrs.duration)
                .attr("transform", function (d) {
                   return "translate(" + source.x + "," + source.y + ")";
                })
                .remove();

             nodeExit
                .select("rect")
                .attr("width", attrs.nodeWidth)
                .attr("height", attrs.nodeHeight);

             // Update the links…
             var link = svg.selectAll("path.link").data(links, function (d) {
                return d.target.id;
             });

             // Enter any new links at the parent's previous position.
             link
                .enter()
                .insert("path", "g")
                .attr("class", "link bmlm-link")
                .attr("x", attrs.nodeWidth / 2)
                .attr("y", attrs.nodeHeight / 2)
                .attr("d", function (d) {
                   var o = {
                      x: source.x0,
                      y: source.y0,
                   };
                   return diagonal({
                      source: o,
                      target: o,
                   });
                });

             // Transition links to their new position.
             link.transition().duration(attrs.duration).attr("d", diagonal);

             // Transition exiting nodes to the parent's new position.
             link
                .exit()
                .transition()
                .duration(attrs.duration)
                .attr("d", function (d) {
                   var o = {
                      x: source.x,
                      y: source.y,
                   };
                   return diagonal({
                      source: o,
                      target: o,
                   });
                })
                .remove();

             // Stash the old positions for transition.
             nodes.forEach(function (d) {
                d.x0 = d.x;
                d.y0 = d.y;
             });

             if (param && param.locate) {
                var x;
                var y;

                nodes.forEach(function (d) {
                   if (d.uniqueIdentifier == param.locate) {
                      x = d.x;
                      y = d.y;
                   }
                });

                // normalize for width/height
                var new_x = -x + window.innerWidth / 6;
                var new_y = -y + window.innerHeight / 3;
                console.log(new_y);
                // move the main container g
                svg.attr("transform", "translate(" + new_x + "," + new_y + ")");
                zoomBehaviours.translate([new_x, new_y]);
                zoomBehaviours.scale(1);
             }

             if (param && param.centerMySelf) {
                var x;
                var y;

                nodes.forEach(function (d) {
                   if (d.isLoggedUser) {
                      x = d.x;
                      y = d.y;
                   }
                });

                // normalize for width/height
                var new_x = -x + window.innerWidth / 2;
                var new_y = -y + window.innerHeight / 2;

                // move the main container g
                svg.attr("transform", "translate(" + new_x + "," + new_y + ")");
                zoomBehaviours.translate([new_x, new_y]);
                zoomBehaviours.scale(1);
             }

          }

          // Toggle children on click.
          function click(d) {
             d3.select(this)
                .select("text")
                .text(function (dv) {
                   if (dv.collapseText == attrs.EXPAND_SYMBOL) {
                      dv.collapseText = attrs.COLLAPSE_SYMBOL;
                   } else {
                      if (dv.children) {
                         dv.collapseText = attrs.EXPAND_SYMBOL;
                      }
                   }
                   return dv.collapseText;
                });

             if (d.children) {
                d._children = d.children;
                d.children = null;
             } else {
                d.children = d._children;
                d._children = null;
             }
             update(d);
          }

          //########################################################

          //Redraw for zoom
          function redraw() {
             //console.log("here", d3.event.translate, d3.event.scale);
             svg.attr(
                "transform",
                "translate(" +
                   d3.event.translate +
                   ")" +
                   " scale(" +
                   d3.event.scale +
                   ")",
             );
          }

          // #############################   Function Area #######################
          function wrap(text, width) {
             text.each(function () {
                var text = d3.select(this),
                   words = text.text().split(/\s+/).reverse(),
                   word,
                   line = [],
                   lineNumber = 0,
                   lineHeight = 1.1, // ems
                   x = text.attr("x"),
                   y = text.attr("y"),
                   dy = 0, //parseFloat(text.attr("dy")),
                   tspan = text
                      .text(null)
                      .append("tspan")
                      .attr("x", x)
                      .attr("y", y)
                      .attr("dy", dy + "em");
                while ((word = words.pop())) {
                   line.push(word);
                   tspan.text(line.join(" "));
                   if (tspan.node().getComputedTextLength() > width) {
                      line.pop();
                      tspan.text(line.join(" "));
                      line = [word];
                      tspan = text
                         .append("tspan")
                         .attr("x", x)
                         .attr("y", y)
                         .attr("dy", ++lineNumber * lineHeight + dy + "em")
                         .text(word);
                   }
                }
             });
          }

          function addPropertyRecursive(
             propertyName,
             propertyValueFunction,
             element,
          ) {
             if (element[propertyName]) {
                element[propertyName] =
                   element[propertyName] + " " + propertyValueFunction(element);
             } else {
                element[propertyName] = propertyValueFunction(element);
             }

              if (element.children) {
                 Object.values(element.children).forEach(function (v) {
                   addPropertyRecursive(propertyName, propertyValueFunction, v);
                });
             }
             if (element._children) {
                element._children.forEach(function (v) {
                   addPropertyRecursive(propertyName, propertyValueFunction, v);
                });
             }
          }

          function reflectResults(results) {
              var htmlStringArray = results.map(function (result) {
                var strVar = "";
                strVar += '         <div class="list-item" status="disabled">';
                strVar += "          <div class='bmlm-list-wrap' >";
                strVar += '            <div class="image-wrapper">';
                strVar +=
                   '              <img class="image" src="' +
                   result.imageUrl +
                   '"/>';
                strVar += "            </div>";
                strVar += '            <div class="description">';
                strVar +=
                   '<p class="name">' + result.display_name.toUpperCase() + "</p>";
                strVar +=
                   '               <p class="position-name">' +
                   result.user_email +
                   "</p>";
                strVar +=
                   '               <p class="registration-date">' +
                   result.user_registered +
                   "</p>";
                strVar += "            </div></div>";
                strVar += '            <div class="buttons">';
                 if (_is_admin && '2' !== result.status) {
                    strVar +=
                       "              <a target='_blank' href='" +
                       result.profileUrl +
                       "'><button class='button button-primary btn-search-box bmlm-btn-action'>Profile</button></a>";
                 }
                strVar +=
                   "              <button class='button button-primary btn-search-box bmlm-btn-action btn-locate' onclick='params.funcs.locate(" +
                   result.uniqueIdentifier +
                   ")'>Locate </button>";
                strVar += "            </div>";
                strVar += "          </a>";
                strVar += "        </div>";

                return strVar;
             });

             var htmlString = htmlStringArray.join("");
             params.funcs.clearResult();

             var parentElement = get(".result-list");
             var old = parentElement.innerHTML;
             var newElement = htmlString + old;
             parentElement.innerHTML = newElement;
             set(
                ".bmlm-user-search-box .result-header",
                "RESULT - " + htmlStringArray.length,
             );
          }

          function clearResult() {
             set(".result-list", '<div class="buffer" ></div>');
             set(".bmlm-user-search-box .result-header", "RESULT");
          }

          function listen() {
             var input = get(".bmlm-user-search-box .search-input");
              if (input) {
                  input.addEventListener("input", function () {
                     var value = input.value ? input.value.trim() : "";
                     if (value.length < 3) {
                        params.funcs.clearResult();
                     } else {
                        var searchResult = params.funcs.findInTree(params.data, value);
                        params.funcs.reflectResults(searchResult);
                     }
                  });
              }
          }

          function searchUsers() {
             d3.selectAll(".bmlm-user-search-box")
                .transition()
                .duration(250)
                .style("width", "350px");
          }

          function closeSearchBox() {
             d3.selectAll(".bmlm-user-search-box")
                .transition()
                .duration(250)
                .style("width", "0px")
                .each("end", function () {
                   params.funcs.clearResult();
                   clear(".search-input");
                });
          }

          function findInTree(rootElement, searchText) {
             var result = [];
             // use regex to achieve case insensitive search and avoid string creation using toLowerCase method
             var regexSearchWord = new RegExp(searchText, "i");

             recursivelyFindIn(rootElement, searchText);

             return result;

             function recursivelyFindIn(user) {
                if (
                   user.display_name.match(regexSearchWord) ||
                   user.user_email.match(regexSearchWord)
                ) {
                   result.push(user);
                }

                var childUsers = user.children ? user.children : user._children;
                if (childUsers) {
                   childUsers.forEach(function (childUser) {
                      recursivelyFindIn(childUser, searchText);
                   });
                }
             }
          }

          function expandAll() {
             expand(attrs.root);
             update(attrs.root);
          }
           function collapsAll() {
               collapse(attrs.root);
                update(attrs.root);
           }

          function expand(d) {
             if (d.children) {
                Object.values(d.children).forEach(expand);
             }

             if (d._children) {
                d.children = d._children;
                d.children.forEach(expand);
                d._children = null;
             }

             if (d.children) {
                // if node has children and it's expanded, then  display -
                setToggleSymbol(d, attrs.COLLAPSE_SYMBOL);
             }
          }

          function collapse(d) {
             if (d._children) {
                d._children.forEach(collapse);
             }
             if (d.children) {
                d._children = d.children;
                d._children.forEach(collapse);
                d.children = null;
             }

             if (d._children) {
                // if node has children and it's collapsed, then  display +
                setToggleSymbol(d, attrs.EXPAND_SYMBOL);
             }
          }

          function setCollapsibleSymbolProperty(d) {
             if (d._children) {
                d.collapseText = attrs.EXPAND_SYMBOL;
             } else if (d.children) {
                d.collapseText = attrs.COLLAPSE_SYMBOL;
             }
          }

          function setToggleSymbol(d, symbol) {
             d.collapseText = symbol;
             d3.select("*[data-id='" + d.uniqueIdentifier + "']")
                .select("text")
                .text(symbol);
          }

          function locateRecursive(d, id) {
             if (d.uniqueIdentifier == id) {
                expandParents(d);
             } else if (d._children) {
                d._children.forEach(function (ch) {
                   ch.parent = d;
                   locateRecursive(ch, id);
                });
             } else if (d.children) {
                d.children.forEach(function (ch) {
                   ch.parent = d;
                   locateRecursive(ch, id);
                });
             }
          }

          /* expand current nodes collapsed parents */
          function expandParents(d) {
             while (d.parent) {
                d = d.parent;
                if (!d.children) {
                   d.children = d._children;
                   d._children = null;
                   setToggleSymbol(d, attrs.COLLAPSE_SYMBOL);
                }
             }
          }

          function toggleFullScreen() {

             if (
                (document.fullScreenElement &&
                   document.fullScreenElement !== null) ||
                (!document.mozFullScreen && !document.webkitIsFullScreen)
             ) {
                if (document.documentElement.requestFullScreen) {
                   document.documentElement.requestFullScreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                   document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullScreen) {
                   document.documentElement.webkitRequestFullScreen(
                      Element.ALLOW_KEYBOARD_INPUT,
                   );
                }
                d3.select(params.selector + " svg")
                   .attr("width", screen.width)
                   .attr("height", screen.height);
             } else {
                if (document.cancelFullScreen) {
                   document.cancelFullScreen();
                } else if (document.mozCancelFullScreen) {
                   document.mozCancelFullScreen();
                } else if (document.webkitCancelFullScreen) {
                   document.webkitCancelFullScreen();
                }
                d3.select(params.selector + " svg")
                   .attr("width", params.chartWidth)
                   .attr("height", params.chartHeight);
             }
          }

          //locateRecursive
          function locate(id) {
             /* collapse all and expand logged user nodes */
             if (!attrs.root.children) {
                if (!attrs.root.uniqueIdentifier == id) {
                   attrs.root.children = attrs.root._children;
                }
             }
             if (attrs.root.children) {
                attrs.root.children.forEach(collapse);
                attrs.root.children.forEach(function (ch) {
                   locateRecursive(ch, id);
                });
             }

             update(attrs.root, { locate: id });
          }

          function deepClone(item) {
             return JSON.parse(JSON.stringify(item));
          }

          function show(selectors) {
             display(selectors, "initial");
          }

          function hide(selectors) {
             display(selectors, "none");
          }

          function display(selectors, displayProp) {
             selectors.forEach(function (selector) {
                var elements = getAll(selector);
                elements.forEach(function (element) {
                   element.style.display = displayProp;
                });
             });
          }

          function set(selector, value) {
             var elements = getAll(selector);
             elements.forEach(function (element) {
                element.innerHTML = value;
                element.value = value;
             });
          }

          function clear(selector) {
             set(selector, "");
          }

          function get(selector) {
             return document.querySelector(selector);
          }

          function getAll(selector) {
             return document.querySelectorAll(selector);
          }
       }
    }
