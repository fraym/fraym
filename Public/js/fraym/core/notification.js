/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
Core.Notification = {
    show: function(type, title, message, timeout) {
        var timeout = typeof timeout != 'undefined' ? timeout : 4000;
        var $notification = $('<div class="notification"><h3><i class="close fa-times fa"></i>' + title + '</h3></div>');
        $notification.find('.close').click(function(){
            $notification.fadeOut('fast', function(){$(this).remove()});
        });
        if(typeof message != 'undefined') {
            $notification.append('<p>' + message + '</p>');
        }
        $notification.addClass(type);
        $('body').append($notification);
        $notification.fadeIn();
        setTimeout(function(){ $notification.fadeOut('fast', function(){$(this).remove()}); }, timeout);
    }
};