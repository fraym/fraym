/**
 * Copyright (c) 2009 Sergiy Kovalchuk (serg472@gmail.com)
 *
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Following code is based on Element.mask() implementation from ExtJS framework (http://extjs.com/)
 *
 */
(function($){

    /**
     * Displays loading mask over selected element(s). Accepts both single and multiple selectors.
     *
     * @param label Text message that will be displayed on top of the mask besides a spinner (optional).
     * 				If not provided only mask will be displayed without a label or a spinner.
     * @param delay Delay in milliseconds before element is masked (optional). If unmask() is called
     *              before the delay times out, no mask is displayed. This can be used to prevent unnecessary
     *              mask display for quick processes.
     */
    $.fn.mask = function(label, options) {
        options = $.extend({'delay': 0}, options)

        return $(this).each(function() {
            var element = $(this);

            if(options.delay > 0) {
                element.data("_mask_timeout", setTimeout(function() { $.maskElement(element, options)}, options.delay));
            } else {
                $.maskElement(element, label);
            }
        });
    };

    /**
     * Removes mask from the element(s). Accepts both single and multiple selectors.
     */
    $.fn.unmask = function(){
        return $(this).each(function() {
            $.unmaskElement($(this));
        });
    };

    /**
     * Checks if a single element is masked. Returns false if mask is delayed or not displayed.
     */
    $.fn.isMasked = function(){
        return this.hasClass("masked");
    };

    $.maskElement = function(element, options) {
        options = $.extend({
            spinner: { lines: 10, length: 4, width: 2, radius: 5},
            spinnerPadding: 5,
            label: "",

            overlayOpacity: 0.75,
            overlaySize: false
        }, options)

        //if this element has delayed mask scheduled then remove it and display the new one
        if (element.data("_mask_timeout") !== undefined) {
            clearTimeout(element.data("_mask_timeout"));
            element.removeData("_mask_timeout");
        }

        if(element.isMasked()) {
            $.unmaskElement(element);
        }

        if(element.css("position") == "static") {
            element.addClass("masked-relative");
        }

        element.addClass("masked");

        var maskDiv = $('<div class="loadmask"></div>').css({opacity: 0 });

        if(options.overlaySize !== false) {
            if(options.overlaySize.height !== undefined)
                maskDiv.height(options.overlaySize.height)

            if(options.overlaySize.width !== undefined)
                maskDiv.width(options.overlaySize.width)
        }

        // auto height fix for IE
        if(navigator.userAgent.toLowerCase().indexOf("msie") > -1){
            maskDiv.height(element.height() + parseInt(element.css("padding-top")) + parseInt(element.css("padding-bottom")));
            maskDiv.width(element.width() + parseInt(element.css("padding-left")) + parseInt(element.css("padding-right")));
        }

        // fix for z-index bug with selects in IE6
        if(navigator.userAgent.toLowerCase().indexOf("msie 6") > -1){
            element.find("select").addClass("masked-hidden");
        }

        element.append(maskDiv);

        if(options.label.length > 0 || options.spinner !== false) {
            var maskMsgDiv = $('<div class="loadmask-msg" style="display:none;"></div>').css({'opacity':0});

            if(options.spinner !== false)
                maskMsgDiv.append(new Spinner(options.spinner).spin().el);

            if(options.label.length > 0) {
                var label = $('<div class="loadmask-label">' + options.label + '</div>')
                maskMsgDiv.append(label)
            }

            element.append(maskMsgDiv);

            // calculate center position
            // TODO there must be cleaner way to do this; check newer jQuery methods and see if anything will fit
            maskMsgDiv.css("top", Math.round(maskDiv.height() / 2 - (maskMsgDiv.height() - parseInt(maskMsgDiv.css("padding-top")) - parseInt(maskMsgDiv.css("padding-bottom"))) / 2)+"px");
            maskMsgDiv.css("left", Math.round(maskDiv.width() / 2 - (maskMsgDiv.width() - parseInt(maskMsgDiv.css("padding-left")) - parseInt(maskMsgDiv.css("padding-right"))) / 2)+"px");
            maskMsgDiv.show();

            if(options.spinner !== false && options.label.length > 0) {
                var spinnerSquare = options.spinner.radius * 2 + (options.spinner.width + options.spinner.length) * 2

                // The center of the spinner is positioned at the top left corner of this DIV
                // center the label text vertically, and align the label to the right of the spinner
                label.css({
                    'margin-left': spinnerSquare / 2 + options.spinnerPadding,
                    'margin-top': -label.height() / 2
                })
            }

            maskMsgDiv.fadeTo('slow', 1)
        }

        // TODO this should be customizable
        maskDiv.fadeTo('slow', options.overlayOpacity);
    };

    $.unmaskElement = function(element){
        //if this element has delayed mask scheduled then remove it
        if (element.data("_mask_timeout") !== undefined) {
            clearTimeout(element.data("_mask_timeout"));
            element.removeData("_mask_timeout");
        }

        element.find(".loadmask-msg,.loadmask").remove();
        element.removeClass("masked");
        element.removeClass("masked-relative");
        element.find("select").removeClass("masked-hidden");
    };

})(jQuery);