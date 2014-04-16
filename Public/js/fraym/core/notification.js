/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
Core.Notification = {
    show: function(type, title, message, timeout) {
        var timeout = typeof timeout != 'undefined' ? timeout : 4000;
        var $notification = $('<div class="notification"><h3><i class="icon-remove icon-white"></i>' + title + '</h3></div>');
        if(typeof message != 'undefined') {
            $notification.append('<p>' + message + '</p>');
        }
        $notification.addClass(type);
        $('body').append($notification);
        $notification.fadeIn();
        setTimeout(function(){ $notification.fadeOut('fast', function(){$(this).remove()}); }, timeout);
    }
}