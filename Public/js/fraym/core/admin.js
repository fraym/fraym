/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */

Fraym.Admin = {
	PERMISSION_DENIED: '',
	BLOCK_EDIT_SRC: '',
	BLOCK_MENU_SRC: '',
	EDIT_MODE: false,

	init: function() {
		Fraym.Admin.initPanel();
		$('body').on('mousemove', '[data-toggle="tooltip"]', function(){
			$(this).tooltip();
		});
		$('#clearcache').click(function (e) {
			e.preventDefault();
			$.ajax({
				url:parent.window.Fraym.getAjaxRequestUri(),
				dataType:'json',
				data:{cmd:'clearCache'},
				type:'post',
				success:function (data, textStatus, jqXHR) {
					Fraym.Notification.show('success', 'Cache cleared!');
				}
			});
		});
	},

	iFrameInit: function() {
		var parentWindow = Fraym.getBaseWindow();

		if(parentWindow.Fraym.Block.dialogWithIframe) {

			$(Fraym.$.BLOCK_CURRENT_INPUT).val(parentWindow.Fraym.Block.dialogBlockId);
			$(Fraym.$.BLOCK_CURRENT_VIEW).html(parentWindow.Fraym.Block.dialogBlockId);
			$('#selected-content-id').html(parentWindow.Fraym.Block.dialogContentId);
			$('input[name=contentId]').val(parentWindow.Fraym.Block.dialogContentId);
			$('input[name=contentId]').val(parentWindow.Fraym.Block.dialogContentId);
			$('input[name="location"]').val(parentWindow.location.href.substring(parentWindow.location.protocol.length+2));

			$('input[name=menuId]').val(parentWindow.menu_id);
			$(Fraym.$.BLOCK_MENU_TRANSLATION_ID).val(window.parent.menu_translation_id);
			Fraym.Block.initIframeContent();
		}
	},

	isMobile: function() {
		if(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
			return true;
		}
		return false;
	},

	isPanelOpen: false,

	initPanel:function () {

		$('body').prepend($('#blockConfigMenu'));
		$('[data-url]').click(function (e) {
			e.preventDefault();
			parent.window.Fraym.Block.showDialog({title: $(this).find('span.title').html()}, $(this).data('url'));
			if(!$("#navigation").hasClass('collapsed')) {
				$(".sidebar-collapse a").click();
			}
		});

		$('[data-id="block-edit-mode"]').click(function (e) {
			e.preventDefault();
			var editMode = $(this).attr('data-editmode') == '1' ? 0 : 1;
			Fraym.Admin.setEditMode(editMode);
		});


		$(".sidebar-collapse a").on("click", function () {
			$("#navigation").toggleClass("collapsed");
			$(".sidebar-collapse").toggleClass("active");
			if($("#navigation").hasClass('collapsed')) {
				Fraym.Admin.closePanel();
			} else {
				Fraym.Admin.openPanel();
			}
		});

		var $adminPanelIframe = $(Fraym.$.BLOCK_CONFIG_MENU).find('iframe');
		if($adminPanelIframe.length) {
			$adminPanelIframe.slimScroll({width: '250px', height: $(window).height()});
			$adminPanelIframe.load(function(){
				$adminPanelIframe.show();
				var height = $adminPanelIframe.contents().find('body').height();
				$adminPanelIframe.height(height);
				$adminPanelIframe.css({'max-height': $(document).height()});
			});
		}
	},

	openPanel: function() {
		Fraym.Admin.isPanelOpen = true;
		Fraym.getBaseWindow().$(Fraym.$.BLOCK_CONFIG_MENU).show().animate({width: '250'}, 100);
	},

	closePanel: function() {
		Fraym.Admin.isPanelOpen = false;
		Fraym.getBaseWindow().$(Fraym.$.BLOCK_CONFIG_MENU).animate({width: '41'}, 100);
	},

	setEditMode: function(active) {
		if(typeof active == 'undefined') {
			active = '';
		}

		$('[data-id="block-edit-mode"]').attr('disabled', 'disabled');
		$.ajax({
			url:parent.window.Fraym.getAjaxRequestUri(),
			dataType:'json',
			data:{cmd:'setEditMode', value:active},
			type:'post',
			success:function (data, textStatus, jqXHR) {
				parent.window.location.reload();
			}
		});
	}
};
