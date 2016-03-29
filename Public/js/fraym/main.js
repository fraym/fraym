/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
var Core = {

    getAjaxRequestUri: function() {
	    return base_path + menu_path + (menu_path.charAt(menu_path.length-1) == '/' ? '' : '/') + 'ajax';
    },

    reloadPage: function() {
        var baseWindow = Core.getBaseWindow();
        $.ajax({
            url: baseWindow.location.href,
            dataType:'html',
            beforeSend: function( xhr ) {
                xhr.setRequestHeader('X-Requested-With', {toString: function(){ return ''; }});
            },
            success: function( data ) {
	            var doc = document.implementation.createHTMLDocument('');
	            doc.open();
	            doc.write(data);
	            doc.close();

                // Destroy context menu
                baseWindow.$.contextMenu('destroy');

                // Remove all text nodes
	            baseWindow.$('body').contents()
                .filter(function() {
                    return this.nodeType == 3;
                }).remove();

                // Remove body content, prevent block-dialog and the admin panel
	            baseWindow.$('body > :not(.block-dialog):not(#blockConfigMenu)').remove();
                // Remove admin panel
                $('body', doc).find('#blockConfigMenu').remove();
                var headHtml = $('head', doc).html();

                // Replace head data
                baseWindow.$('head:first').html(headHtml);
                // Add the new body content to the current body
	            baseWindow.$('body:first').prepend($('body', doc).html());
            }
        });
    },

    encodeQueryData: function (data)
    {
        var ret = [];
        for (var d in data)
            ret.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
        return ret.join("&");
    },

    getBaseWindow: function() {
        if(parent.window) {
            return parent.window;
        }
        return window;
    },
    
    getUniqueId:function (obj) {
        var knownObjects = [];

        for (var i = knownObjects.length; i--;) {
            if (knownObjects[i][0] === obj) return knownObjects[i][1];
        }
        var uid = 'x' + (+('' + Math.random()).substring(2)).toString(32);
        knownObjects.push([obj, uid]);
        return uid;
    },

    location:function (path) {
        if (path.substr(0, 4).toLowerCase() == 'http' || path.substr(0, 5).toLowerCase() == 'https') {
            window.location = path;
        }
        else {
            window.location = base_path + menu_path + (menu_path.charAt(menu_path.length - 1) == '/' ? '' : '/') + path;
        }
    },

    showMessage:function (msg) {
        alert(msg);
    },

    initLabels:function () {
        $.each($('input').parent().children(),
            function () {
                if ($(this).attr('title') != '' && ($(this).attr('type') == 'text' || $(this).attr('type') == 'password')) {
                    var new_div = document.createElement('div');
                    var new_label = document.createElement('label');
                    $(new_label).html($(this).attr('title'));


                    $(new_label).css({opacity:'0.8', position:'absolute', top:'5px', left:'8px', color:'#3c3c3c', cursor:'text', zIndex:'1'});

                    $(this).focus(function () {
                        if ($(this).val() == '') {
                            $(new_label).fadeTo(300, '0.4');
                        }
                    });

                    $(new_label).click(function () {
                        $(this).siblings(':first').focus();
                    });

                    $(this).keydown(function () {
                        $(new_label).hide();
                    });

                    $(this).blur(function () {
                        if ($(this).val() == '') {
                            $(new_label).show();
                            $(new_label).fadeTo(300, '0.8');
                        }
                    });

                    if ($(this).val() != '') {
                        $(new_label).hide();
                    }

                    $(new_div).append(new_label);
                    $(new_div).css({position:'relative', display:'inline-block'});

                    $(this).parent().append(new_div);
                    $(new_div).append($(this));
                }
                else {
                    $(this).parent().append($(this));
                }
            }
        );
    },

	inArray: function (needle, haystack, argStrict) {
	  var key = '', strict = !! argStrict;
	  if (strict) {
	    for (key in haystack) {
	      if (haystack[key] === needle) {
	        return true;
	      }
	    }
	  } else {
	    for (key in haystack) {
	      if (haystack[key] == needle) {
	        return true;
	      }
	    }
	  }
	  return false;
	},

    urlEncode: function (str) {
        str = (str + '').toString();
        return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
    }
}

$(function () {
    if (self == top) {
        Core.Block.init();
    } else {
	    Core.Admin.iFrameInit();
    }
    Core.Admin.init();
});
