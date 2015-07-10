/*
Originaly made from:
Uniform v2.1.0
Copyright © 2009 Josh Pyles / Pixelmatrix Design LLC
MIT License
*/

(function ($, undef) {
	"use strict";

	/**
	 * Use .prop() if jQuery supports it, otherwise fall back to .attr()
	 *
	 * @param jQuery $el jQuery'd element on which we're calling attr/prop
	 * @param ... All other parameters are passed to jQuery's function
	 * @return The result from jQuery
	 */
	function attrOrProp($el) {
		var args = Array.prototype.slice.call(arguments, 1);

		if ($el.prop) {
			// jQuery 1.6+
			return $el.prop.apply($el, args);
		}

		// jQuery 1.5 and below
		return $el.attr.apply($el, args);
	}

	/**
	 * For backwards compatibility with older jQuery libraries, only bind
	 * one thing at a time.  Also, this function adds our namespace to
	 * events in one consistent location, shrinking the minified code.
	 *
	 * The properties on the events object are the names of the events
	 * that we are supposed to add to.  It can be a space separated list.
	 * The namespace will be added automatically.
	 *
	 * @param jQuery $el
	 * @param Object options Uniform options for this element
	 * @param Object events Events to bind, properties are event names
	 */
	function bindMany($el, options, events) {
		var name, namespaced;

		for (name in events) {
			if (events.hasOwnProperty(name)) {
				namespaced = name.replace(/ |$/g, options.eventNamespace);
				$el.bind(namespaced, events[name]);
			}
		}
	}

	/**
	 * Bind the active, focus, and blur UI updates
	 *
	 * @param jQuery $el Original element
	 * @param jQuery $target Target for the events (our div/span)
	 * @param Object options Uniform options for the element $target
	 */
	function bindUi($el, $target, options) {
		bindMany($el, options, {
			focus: function () {
				$target.addClass(options.focusClass);
			},
			blur: function () {
				$target.removeClass(options.focusClass);
				$target.removeClass(options.activeClass);
			},
			"mousedown touchbegin": function () {
				if (!$el.is(":disabled")) {
					$target.addClass(options.activeClass);
				}
			},
			"mouseup touchend mouseleave": function () {
				$target.removeClass(options.activeClass);
			}
		});
	}

	/**
	 * Remove the hover, focus, active classes.
	 *
	 * @param jQuery $el Element with classes
	 * @param Object options Uniform options for the element
	 */
	function classClearStandard($el, options) {
		$el.removeClass(options.hoverClass + " " + options.focusClass + " " + options.activeClass);
	}

	/**
	 * Add or remove a class, depending on if it's "enabled"
	 *
	 * @param jQuery $el Element that has the class added/removed
	 * @param String className Class or classes to add/remove
	 * @param Boolean enabled True to add the class, false to remove
	 */
	function classUpdate($el, className, enabled) {
		if (enabled) {
			$el.addClass(className);
		} else {
			$el.removeClass(className);
		}
	}

	/**
	 * Set or remove the "disabled" class for disabled elements, based on
	 * if the 
	 *
	 * @param jQuery $tag Our Uniform span/div
	 * @param jQuery $el Original form element
	 * @param Object options Uniform options for this element
	 */
	function classUpdateDisabled($tag, $el, options) {
		classUpdate($tag, options.disabledClass, $el.is(":disabled"));
	}

	/**
	 * Wrap an element inside of a container or put the container next
	 * to the element.  See the code for examples of the different methods.
	 *
	 * Returns the container that was added to the HTML.
	 *
	 * @param jQuery $el Element to wrap
	 * @param jQuery $container Add this new container around/near $el
	 * @param String method One of "after", "before" or "wrap"
	 * @return $container after it has been cloned for adding to $el
	 */
	function divSpanWrap($el, $container, method) {
		switch (method) {
		case "after":
			// Result:  <element /> <container />
			$el.after($container);
			return $el.next();
		case "before":
			// Result:  <container /> <element />
			$el.before($container);
			return $el.prev();
		case "wrap":
			// Result:  <container> <element /> </container>
			$el.wrap($container);
			return $el.parent();
		}

		return null;
	}


	/**
	 * Create a div/span combo for uniforming an element
	 *
	 * @param jQuery $el Element to wrap
	 * @param Object options Options for the element, set by the user
	 * @param Object divSpanConfig Options for how we wrap the div/span
	 * @return Object Contains the div and span as properties
	 */
	function divSpan($el, options, divSpanConfig) {
		var $div, $span, id;

		if (!divSpanConfig) {
			divSpanConfig = {};
		}

		divSpanConfig = $.extend({
			bind: {},
			divClass: null,
			divWrap: "wrap",
			spanClass: null,
			spanHtml: null,
			spanWrap: "wrap"
		}, divSpanConfig);

		$div = $('<div />');
		$span = $('<span />');

		// Automatically hide this div/span if the element is hidden.
		// Do not hide if the element is hidden because a parent is hidden.
		if (options.autoHide && $el.is(':hidden') && $el.css('display') === 'none') {
			$div.hide();
		}

		if (divSpanConfig.divClass) {
			$div.addClass(divSpanConfig.divClass);
		}

		if (options.wrapperClass) {
			$div.addClass(options.wrapperClass);
		}

		if (divSpanConfig.spanClass) {
			$span.addClass(divSpanConfig.spanClass);
		}

		id = attrOrProp($el, 'id');

		if (options.useID && id) {
			attrOrProp($div, 'id', options.idPrefix + '-' + id);
		}

		if (divSpanConfig.spanHtml) {
			$span.html(divSpanConfig.spanHtml);
		}

		$div = divSpanWrap($el, $div, divSpanConfig.divWrap);
		$span = divSpanWrap($el, $span, divSpanConfig.spanWrap);
		classUpdateDisabled($div, $el, options);
		return {
			div: $div,
			span: $span
		};
	}


	/**
	 * Wrap an element with a span to apply a global wrapper class
	 *
	 * @param jQuery $el Element to wrap
	 * @param object options
	 * @return jQuery Wrapper element
	 */
	function wrapWithWrapperClass($el, options) {
		var $span;

		if (!options.wrapperClass) {
			return null;
		}

		$span = $('<span />').addClass(options.wrapperClass);
		$span = divSpanWrap($el, $span, "wrap");
		return $span;
	}


	/**
	 * Change text into safe HTML
	 *
	 * @param String text
	 * @return String HTML version
	 */
	function htmlify(text) {
		if (!text) {
			return "";
		}

		return $('<span />').text(text).html();
	}

	/**
	 * Test if the element is a multiselect
	 *
	 * @param jQuery $el Element
	 * @return boolean true/false
	 */
	function isMultiselect($el) {
		var elSize;

		if ($el[0].multiple) {
			return true;
		}

		elSize = attrOrProp($el, "size");

		if (!elSize || elSize <= 1) {
			return false;
		}

		return true;
	}

	/**
	 * Meaningless utility function.  Used mostly for improving minification.
	 *
	 * @return false
	 */
	function returnFalse() {
		return false;
	}

	/**
	 * noSelect plugin, very slightly modified
	 * http://mths.be/noselect v1.0.3
	 *
	 * @param jQuery $elem Element that we don't want to select
	 * @param Object options Uniform options for the element
	 */
	function noSelect($elem, options) {
		var none = 'none';
		bindMany($elem, options, {
			'selectstart dragstart mousedown': returnFalse
		});

		$elem.css({
			MozUserSelect: none,
			msUserSelect: none,
			webkitUserSelect: none,
			userSelect: none
		});
	}


	/**
	 * Function from jQuery to swap some CSS values, run a callback,
	 * then restore the CSS.  Modified to pass JSLint and handle undefined
	 * values with 'use strict'.
	 *
	 * @param jQuery $el Element
	 * @param object newCss CSS values to swap out
	 * @param Function callback Function to run
	 */
	function swap($elements, newCss, callback) {
		var restore, item;

		restore = [];

		$elements.each(function () {
			var name;

			for (name in newCss) {
				if (Object.prototype.hasOwnProperty.call(newCss, name)) {
					restore.push({
						el: this,
						name: name,
						old: this.style[name]
					});

					this.style[name] = newCss[name];
				}
			}
		});

		callback();

		while (restore.length) {
			item = restore.pop();
			item.el.style[item.name] = item.old;
		}
	}


	/**
	 * The browser doesn't provide sizes of elements that are not visible.
	 * This will clone an element and add it to the DOM for calculations.
	 *
	 * @param jQuery $el
	 * @param String method
	 */
	function sizingInvisible($el, callback) {
		var targets;

		// We wish to target ourselves and any parents as long as
		// they are not visible
		targets = $el.parents();
		targets.push($el[0]);
		targets = targets.not(':visible');
		swap(targets, {
			visibility: "hidden",
			display: "block",
			position: "absolute"
		}, callback);
	}


	/**
	 * Standard way to unwrap the div/span combination from an element
	 *
	 * @param jQuery $el Element that we wish to preserve
	 * @param Object options Uniform options for the element
	 * @return Function This generated function will perform the given work
	 */
	function unwrapUnwrapUnbindFunction($el, options) {
		return function () {
			$el.unwrap().unwrap().unbind(options.eventNamespace);
		};
	}

	var uniformHandlers = [  // Objects that take care of "unification"
			{
				// Select lists, but do not style multiselects here
				match: function ($el) {
					if ($el.is("select") && !isMultiselect($el)) {
						return true;
					}

					return false;
				},
				apply: function ($el, options) {
					var ds, $div, $span, origElemWidth;

					if (options.selectAutoWidth) {
						sizingInvisible($el, function () {
							origElemWidth = $el.width();
						});
					}

					ds = divSpan($el, options, {
						divClass: $el.prop('class') + ' ' + options.selectClass,
						spanHtml: ($el.find(":selected:first") || $el.find("option:first")).html(),
						spanWrap: "before"
					});
					$div = ds.div;
					$span = ds.span;

					if (options.selectAutoWidth) {
						// Use the width of the select and adjust the
						// span and div accordingly
						sizingInvisible($el, function () {
							// Force "display: block" - related to bug #287
							swap($([ $span[0], $div[0] ]), {
								display: "block"
							}, function () {
								$div.width(origElemWidth);
							});
						});
					} else {
						// Force the select to fill the size of the div
						$div.addClass('fixedWidth');
					}

					// Take care of events
					bindUi($el, $div, options);
					bindMany($el, options, {
						change: function () {
							$span.html($el.find(":selected").html());
							$div.removeClass(options.activeClass);
						},
						"click touchend": function () {
							// IE7 and IE8 may not update the value right
							// until after click event - issue #238
							var selHtml = $el.find(":selected").html();

							if ($span.html() !== selHtml) {
								// Change was detected
								// Fire the change event on the select tag
								$el.trigger('change');
							}
						},
						keyup: function () {
							$span.html($el.find(":selected").html());
						}
					});
					noSelect($span, options);
					return {
						remove: function () {
							// Remove sibling span
                            console.log('remove');
							$span.remove();

							// Unwrap parent div
							$el.unwrap().unbind(options.eventNamespace);
							return $el;
						},
						update: function () {
							if (options.selectAutoWidth) {
								// Easier to remove and reapply formatting
								$.uniform.restore($el);
								$el.uniform(options);
							} else {
								classClearStandard($div, options);

								// Reset current selected text
								$span.html($el.find(":selected").html());
								classUpdateDisabled($div, $el, options);
							}
						}
					};
				}
			}/*,
			{
				// Select lists - multiselect lists only
				match: function ($el) {
					if ($el.is("select") && isMultiselect($el)) {
						return true;
					}

					return false;
				},
				apply: function ($el, options) {
					var $wrapper;

					$el.addClass(options.selectMultiClass);
					$wrapper = wrapWithWrapperClass($el, options);
					bindUi($el, $el, options);

					return {
						remove: function () {
							$el.removeClass(options.selectMultiClass);

							if ($wrapper) {
								$el.unwrap();
							}
						},
						update: returnFalse
					};
				}
			}*/
		];

	$.uniform = {
		// Default options that can be overridden globally or when uniformed
		// globally:  $.uniform.defaults.fileButtonHtml = "Pick A File";
		// on uniform:  $('input').uniform({fileButtonHtml: "Pick a File"});
		defaults: {
			activeClass: "active",
			autoHide: true,
			disabledClass: "disabled",
			eventNamespace: ".uniform",
			focusClass: "focus",
			hoverClass: "hover",
			idPrefix: "uniform",
			inputAddTypeAsClass: true,
			inputClass: "uniform-input",
			resetDefaultHtml: "Reset",
			resetSelector: false,  // We'll use our own function when you don't specify one
			selectAutoWidth: true,
			selectClass: "selector",
			selectMultiClass: "uniform-multiselect",
			submitDefaultHtml: "Submit",  // Only text allowed
			useID: true,
			wrapperClass: null
		},

		// All uniformed elements - DOM objects
		elements: []
	};

	$.fn.uniform = function (options) {
		var el = this;
		options = $.extend({}, $.uniform.defaults, options);
		// Code for specifying a reset button
		if (options.resetSelector) {
			$(options.resetSelector).mouseup(function () {
				window.setTimeout(function () {
					$.uniform.update(el);
				}, 10);
			});
		}

		return this.each(function () {
			var $el = $(this), i, handler, callbacks;

			// Avoid uniforming elements already uniformed - just update
			if ($el.data("uniformed")) {
				$.uniform.update($el);
				return;
			}

			// See if we have any handler for this type of element
			for (i = 0; i < uniformHandlers.length; i = i + 1) {
				handler = uniformHandlers[i];

				if (handler.match($el, options)) {
					callbacks = handler.apply($el, options);
					$el.data("uniformed", callbacks);

					// Store element in our global array
					$.uniform.elements.push($el.get(0));
					return;
				}
			}

			// Could not style this element
		});
	};

	$.uniform.restore = $.fn.uniform.restore = function (elem) {
        console.log(elem.length);
		if (elem === undef) {
			elem = $.uniform.elements;
		}

		$(elem).each(function () {
			var $el = $(this), index, elementData;
			elementData = $el.data("uniformed");

            console.log($el.html());

			// Skip elements that are not uniformed
			if (!elementData) {
				return;
			}

			// Unbind events, remove additional markup that was added
			elementData.remove();

			// Remove item from list of uniformed elements
			index = $.inArray(this, $.uniform.elements);

			if (index >= 0) {
				$.uniform.elements.splice(index, 1);
			}

			$el.removeData("uniformed");
		});
	};

	$.uniform.update = $.fn.uniform.update = function (elem) {
		if (elem === undef) {
			elem = $.uniform.elements;
		}

		$(elem).each(function () {
			var $el = $(this), elementData;
			elementData = $el.data("uniformed");

			// Skip elements that are not uniformed
			if (!elementData) {
				return;
			}

			elementData.update($el, elementData.options);
		});
	};
}(jQuery));
