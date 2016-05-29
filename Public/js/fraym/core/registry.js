/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
Core.Registry = {
	init: function () {

		$('body').on('click', '#registry-extensions [data-install]', Core.Registry.installExtension);
		$('body').on('click', '#registry-extensions [data-uninstall]', Core.Registry.uninstallExtension);
		$('body').on('click', '#registry-extensions [data-remove]', Core.Registry.removeExtension);
		$('body').on('click', '#registry-extensions [data-download]', Core.Registry.downloadExtension);
		$('body').on('click', '#registry-extensions [data-update]', Core.Registry.updateExtension);
		$('body').on('submit', '#registry-extensions #repository-form', Core.Registry.repositorySearch);
	},

	repositorySearch: function (e) {
		e.preventDefault();
		var term = $('[name="extension_term"]').val();
		Core.Registry.request(
			{
				cmd: 'repositorySearch',
				term: $.trim(term)
			},
			function (response) {
				$('#repositoryResult').html(response.responseText);
				$('body').unmask();
			});
		return false;
	},

	installExtension: function (e) {
		e.preventDefault();
		var repositoryKey = $(this).data('install');
		Core.Registry.request(
			{
				cmd: 'installExtension',
				repositoryKey: repositoryKey
			},
			function () {
				window.location.reload();
			});
	},

	uninstallExtension: function (e) {
		e.preventDefault();
		var extensionId = $(this).data('uninstall');
		Core.Registry.request(
			{
				cmd: 'uninstallExtension',
				extensionId: extensionId
			},
			function () {
				window.location.reload();
			});
	},

	removeExtension: function (e) {
		e.preventDefault();
		var repositoryKey = $(this).data('remove');
		Core.Registry.request(
			{
				cmd: 'removeExtension',
				repositoryKey: repositoryKey
			},
			function () {
				window.location.reload();
			});
	},

	downloadExtension: function (e) {
		e.preventDefault();
		var repositoryKey = $(this).data('download');
		Core.Registry.request(
			{
				cmd: 'downloadExtension',
				repositoryKey: repositoryKey
			},
			function () {
				window.location.reload();
			});
	},


	updateExtension: function (e) {
		e.preventDefault();
		var repositoryKey = $(this).data('update');
		Core.Registry.request(
			{
				cmd: 'downloadExtension',
				repositoryKey: repositoryKey
			},
			function () {
				Core.Registry.request(
					{
						cmd: 'updateExtension',
						repositoryKey: repositoryKey
					},
					function () {
						window.location.reload();
					});
			});
	},

	request: function (options, successCallback, errorCallback) {

		$('body').mask({
			spinner: { lines: 10, length: 5, width: 3, radius: 10}
		});
		$.ajax({
			url: window.location.href,
			data: options,
			type: 'post',
			complete: function (jqXHR, textStatus) {
				if(successCallback) {
					successCallback(jqXHR, textStatus);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				if(errorCallback) {
					errorCallback(jqXHR, textStatus, errorThrown);
				}
				$('body').unmask();
			}
		});
	}
};

$(function () {
	Core.Registry.init();
});