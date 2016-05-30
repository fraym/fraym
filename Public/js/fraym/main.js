/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
var Fraym = {
    
    locales: [],

    getAjaxRequestUri: function() {
	    return '/ajax';
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
        Fraym.Block.init();
    } else {
	    Fraym.Admin.iFrameInit();
    }
    Fraym.Admin.init();
});
