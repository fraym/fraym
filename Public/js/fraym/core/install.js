/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */

Fraym.Install = {
	init: function () {
		$('form').submit(function (e) {
			$.ajax({
				url: '/install.php',
				dataType: 'json',
				data: $(this).serialize() + '&cmd=checkDatabase',
				type: 'post',
				async: false,
				success: function (data, textStatus, jqXHR) {
					if (typeof data.error != 'undefined') {
						e.preventDefault();
						alert(data.error);
						return false;
					}
				}
			});
			return true;
		});
	}
};

$(function () {
	Fraym.Install.init();
});