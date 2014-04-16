/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */

Core.Admin = {
    PERMISSION_DENIED: '',
    BLOCK_EDIT_SRC: '',
    BLOCK_MENU_SRC: '',

    init: function() {
        Core.Admin.initPanel();
	    $('body').on('mousemove', '[data-toggle="tooltip"]', function(){
		    $(this).tooltip();
	    });
    },

	iFrameInit: function() {
		var parentWindow = Core.getBaseWindow();

		if(parentWindow.Core.Block.dialogWithIframe) {

			$(Core.$.BLOCK_CURRENT_INPUT).val(parentWindow.Core.Block.dialogBlockId);
	        $(Core.$.BLOCK_CURRENT_VIEW).html(parentWindow.Core.Block.dialogBlockId);
			$('#selected-content-id').html(parentWindow.Core.Block.dialogContentId);
			$('input[name=contentId]').val(parentWindow.Core.Block.dialogContentId);
			$('input[name=contentId]').val(parentWindow.Core.Block.dialogContentId);
			$('input[name="location"]').val(parentWindow.location.href.substring(parentWindow.location.protocol.length+2));

			$('input[name=menuId]').val(parentWindow.menu_id);
			$(Core.$.BLOCK_MENU_TRANSLATION_ID).val(window.parent.menu_translation_id);
			Core.Block.initIframeContent();
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

        $('[data-url]').click(function (e) {
            e.preventDefault();
            parent.window.Core.Block.showDialog({title: $(this).find('span').html()}, $(this).data('url'));

        });

        $('[data-id="block-edit-mode"]').click(function (e) {
            e.preventDefault();
            var editMode = $(this).attr('data-editmode') == '1' ? 0 : 1;
			Core.Admin.setEditMode(editMode);
        });

        $(document).mousemove(function(e) {
            if(e.shiftKey == false && e.clientX <= 100 && Core.Admin.isPanelOpen == false) {
                Core.Admin.openPanel();
            } else if(e.clientX > 222 && Core.Admin.isPanelOpen == true) {
                Core.Admin.closePanel();
            }
        });

	    var $adminPanelIframe = $(Core.$.BLOCK_CONFIG_MENU).find('iframe');
        if($adminPanelIframe.length) {
	        $adminPanelIframe.slimScroll({width: '185px', height: $(window).height()});
	        $adminPanelIframe.load(function(){
		        $adminPanelIframe.show();
		        var height = $adminPanelIframe.contents().find('body').height();
		        $adminPanelIframe.height(height);
		        $adminPanelIframe.css({'max-height': $(document).height()});
	        });
        }
    },

	openPanel: function() {
		Core.Admin.isPanelOpen = true;
		$(Core.$.BLOCK_CONFIG_MENU).show().animate({left: '0'}, 100);
	},

	closePanel: function() {
		Core.Admin.isPanelOpen = false;
        $(Core.$.BLOCK_CONFIG_MENU).animate({left: '-222'}, 100);
	},

	setEditMode: function(active) {
		if(typeof active == 'undefined') {
			active = '';
		}

		$('[data-id="block-edit-mode"]').attr('disabled', 'disabled');
		$.ajax({
		      url:parent.window.Core.getAjaxRequestUri(),
		      dataType:'json',
		      data:{cmd:'setEditMode', value:active},
		      type:'post',
		      success:function (data, textStatus, jqXHR) {
		          parent.window.location.reload();
		      }
		 });
	}
};
