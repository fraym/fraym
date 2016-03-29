/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
Core.Block = {
	dragging: false,
	url: '',
	CodeMirror: false,
	contextMenuItemsDisabled: {},
    dialogContentId: '',
    dialogBlockId: '',
    dialogWithIframe: false,

	History: {

		blocks: [],
		currentIndex: 0,

		init: function () {
			$(document).keypress(function (e) {
				if (!(event.which == 26 && event.ctrlKey) && !(event.which == 25 && event.ctrlKey) && !(event.which == 19)) {
					return true;
				}
				e.preventDefault();

				if (event.which == 26 && event.ctrlKey) {
					// ctrl + z pressed
					// TODO
					console.log('undo');
				} else {
					// ctrl + y pressed
					// TODO
					console.log('redo');
				}
			});
		},

		load: function (blockId) {
			//TODO
		}
	},

	loadDefaultConfig: function (json) {
		var $baseElement = $('body');

		$baseElement.find('#extension option[value="' + json.id + '"]').prop('selected', 'selected');
		Core.Block.getExtensionConfigView(json.id, json);

		$baseElement.find(Core.$.BLOCK_TEMPLATE_SELECTION).removeAttr('disabled');

		if (typeof json.xml != 'undefined') {
			var xmlData = json.xml;

			if (typeof xmlData.template != 'undefined' && xmlData.template['@type'] == 'string') {
				$baseElement.find('#template').children(Core.$.BLOCK_TEMPLATE_SELECTION_CUSTOM).prop('selected', 'selected');
				$baseElement.find('textarea[name=templateContent]').val(xmlData.template.$);
			} else if (typeof xmlData.template != 'undefined' && $.isNumeric(xmlData.template['@type'])) {
				$baseElement.find('#template').find('[value="' + xmlData.template['@type'] + '"]').prop('selected', 'selected');
			} else {
				$baseElement.find('#template').children('option[value=""]').prop('selected', 'selected');
				$baseElement.find('input[name="templateFile"]').val(xmlData.template.$);
				$baseElement.find('input[name="name"]').val(json.blockName);
				$baseElement.find('#template').change();
			}

			if (Core.Block.CodeMirror) {
				Core.Block.CodeMirror.setValue($("#templateContent").val());
			}

			$baseElement.find(Core.$.BLOCK_TEMPLATE_SELECTION).change();

			if (typeof xmlData.startDate != 'undefined') {
				$baseElement.find('[name=startDate]').val(xmlData.startDate);
			}

			if (typeof xmlData.endDate != 'undefined') {
				$baseElement.find('[name=endDate]').val(xmlData.endDate);
			}

			if (typeof xmlData.permissions != 'undefined') {
				$.each(xmlData.permissions, function (key, perm) {
					if ($.isArray(perm)) {
						$.each(perm, function (custKey, custPerm) {
							$baseElement.find('.permission:last option[value="' + custPerm['@identifier'] + '"]').prop('selected', 'selected');
						});
					} else {
						$baseElement.find('.permission:last option[value="' + perm['@identifier'] + '"]').prop('selected', 'selected');
					}
				});
			}

			if (typeof xmlData.excludedDevices != 'undefined') {
				$.each(xmlData.excludedDevices, function (key, devices) {
					if ($.isArray(devices)) {
						$.each(devices, function (custKey, custDevice) {
							$baseElement.find('.excludedDevices:last option[value="' + custDevice['@type'] + '"]').prop('selected', 'selected');
						});
					} else {
						$baseElement.find('.excludedDevices:last option[value="' + devices['@type'] + '"]').prop('selected', 'selected');
					}
				});
			}

			$baseElement.find('[name=active] option[value=' + xmlData.active + ']').prop('selected', 'selected');

			if (typeof xmlData.cache != 'undefined') {
				$baseElement.find('[name=cache] option[value=' + xmlData.cache + ']').prop('selected', 'selected');
			}
		}

		if (typeof json.menuItem != 'undefined' && (json.menuItem === null || json.menuItem.length === 0)) {
			$baseElement.find('#all-pages').prop('checked', 'checked');
		}

		if (typeof json.menuItemTranslation != 'undefined' && json.menuItemTranslation != null) {
			$baseElement.find('#menuTranslation').val('current');
		}

		$baseElement.find('[type="submit"]').removeAttr('disabled');
        $('select').trigger("chosen:updated");
	},

	addTab: function (title, html) {
		$(Core.$.BLOCK_TABS).tabs("destroy");
		var count = ($(Core.$.BLOCK_TABS).find('ul > li').length + 1);
		$(Core.$.BLOCK_TABS).children('ul').append('<li><a href="#block-tabs-' + count + '">' + title + '</a></li>');
		$(Core.$.BLOCK_TABS).append($('<div id="block-tabs-' + count + '" class="custom-tab-content"></div>').html(html));
		$(Core.$.BLOCK_TABS).tabs({activate: function(){$('select:not(.default)').chosen();}});
		$('[href="#block-tabs-' + count + '"]').effect('highlight', {}, 1000);
	},

	removeTabs: function () {
		$(Core.$.BLOCK_TABS).tabs("destroy");
		$(Core.$.BLOCK_TABS).find('ul > li:not(:first), .custom-tab-content').remove();
		$(Core.$.BLOCK_TABS).tabs();
	},

	init: function () {

		Core.Block.initBlockActions();
		Core.Block.History.init();

		if(typeof $.cookie != 'undefined') {
			if(typeof $.cookie('copy') != 'undefined') {
				Core.Block.copyBlock($.cookie('copy'));
			} else if(typeof $.cookie('cut') != 'undefined') {
				Core.Block.cutBlock($.cookie('cut'));
			}
		}

		$(document).keypress(function (e) {
			if (!(event.which == 5 && event.ctrlKey)) {
				return true;
			}
			e.preventDefault();
			Core.Admin.setEditMode();
		});

		$(Core.$.BLOCK_BLOCK_TO_TOP).click(function(){
			var $container = $(this).parents('.block-container-content:first, .block-container:first');
			if($(this).hasClass('active')) {
				$(this).removeClass('active');
				$container.css('z-index', '');
			} else {
				$(this).addClass('active');
				$container.css('z-index', '9000');
			}
		});

		if (Core.Admin.isMobile() == false) {
			// adding hover evects
			$('body').on('mouseenter', Core.$.BLOCK_CONTAINER, function (e) {
				if (e.shiftKey == false && Core.Block.dragging == false) {
					$(this).animate({borderColor: 'rgba(0, 137, 205, 1.0)'});
					$(this).find(Core.$.BLOCK_VIEW_CONTAINER).css({borderColor: 'rgba(0, 137, 205, 1.0)'});
					$(this).find(Core.$.BLOCK_VIEW_INFO_CONTAINER).css({opacity: '1'});
				}
			});
			$('body').on('mouseleave', Core.$.BLOCK_CONTAINER, function (e) {
				if (e.shiftKey == false && Core.Block.dragging == false) {
					$(this).animate({borderColor: 'rgba(0, 137, 205, 0.0)'});
					$(this).find(Core.$.BLOCK_VIEW_CONTAINER).css({borderColor: 'rgba(0, 137, 205, 0.0)'});
					$(this).find(Core.$.BLOCK_VIEW_INFO_CONTAINER).css({opacity: '0'});
				}
			});

			$('body').on('mouseenter', Core.$.BLOCK_HOLDER + ':not(' + Core.$.BLOCK_CONTAINER + ')', function (e) {
                if(e.shiftKey == false) {
					if($(this).hasClass('changeset')) {
						$(this).css({borderColor: 'rgba(255, 165, 0, 1.0)'});
					} else {
						$(this).css({borderColor: 'rgba(23, 184, 19, 1.0)'});
					}

                    $(this).find(Core.$.BLOCK_INFO).css({opacity: '1'}).show();
                }
			});

			$('body').on('mouseleave', Core.$.BLOCK_HOLDER + ':not(' + Core.$.BLOCK_CONTAINER + ')', function (e) {
                if(e.shiftKey == false) {
					if($(this).hasClass('changeset')) {
						$(this).css({borderColor: 'rgba(255, 165, 0, 0)'});
					} else {
						$(this).css({borderColor: 'rgba(23, 184, 19, 0)'});
					}

                    $(this).find(Core.$.BLOCK_INFO).css({opacity: '0'}).hide();
                }
			});
		} else {
			$(Core.$.BLOCK_HOLDER + ':not(' + Core.$.BLOCK_CONTAINER + ')').css({borderColor: 'rgba(23, 184, 19, 1.0)'});
			$(Core.$.BLOCK_INFO).css({opacity: '1'});
			$(Core.$.BLOCK_VIEW_CONTAINER).css({borderColor: 'rgba(0, 137, 205, 1.0)'});
			$(Core.$.BLOCK_VIEW_INFO_CONTAINER).css({opacity: '1'});
		}

		$('body').on('dblclick', Core.$.BLOCK_INFO, function () {
			Core.Block.showBlockDialog($(this).parents(Core.$.BLOCK_VIEW_CONTAINER).attr('id'), $(this).parent().data('id'));
		});
	},

	initIframeContent: function () {
		// init tabs on block dialog
		$(Core.$.BLOCK_TABS).tabs();
        $('select:not(.default)').chosen();

		$('.overlay-save').click(function (e) {
			e.preventDefault();
			$('form').data('closeonsuccess', false);
			$('form').submit();
		});

		$('.overlay-save-and-close').click(function (e) {
			e.preventDefault();
			$('form').data('closeonsuccess', true);
			$('form').submit();
		});

		FileManager.initFilePathInput();

		$('form#block-add-edit-form').formSubmit({
                url: Core.getAjaxRequestUri(),
                'beforeSubmit': function() {
                    $(Core.Block).trigger('saveBlockConfig');
                },
                'onSuccess': function (json) {

                    // For view extension reloadpage with no config.
                    // If not blocks of type content will not be rendered correctly.
                    if($(Core.$.BLOCK_TABS).find('> ul > li').length == 1) {
                        Core.getBaseWindow().Core.reloadPage();
                    }

                    $(Core.Block).trigger('blockConfigSaved');
                    if (json && json.data) {
                        if ($(Core.$.BLOCK_CURRENT_INPUT).val() == '') {
                            window.parent.$('#' + $(Core.$.BLOCK_CURRENT_CONTENTID_INPUT).val()).prepend(json.data);
                            window.parent.Core.Block.initBlockActions();
                        } else {
                            Core.Block.replaceBlock($(Core.$.BLOCK_CURRENT_INPUT).val(), json.data);
                        }
                        $(Core.$.BLOCK_CURRENT_INPUT).val(json.blockId);
                        $(Core.$.BLOCK_CURRENT_VIEW).html(json.blockId);
                    } else {
                        Block.showMessage('Error check your config');
                    }

                    if($('form').data('closeonsuccess') == true) {
                        window.parent.$(Core.$.BLOCK_OVERLAY).dialog('close');
                    }
                },
                dataType: 'json'
            });

		if ($(Core.$.BLOCK_DATETIME_INPUT).length) {
			$(Core.$.BLOCK_DATETIME_INPUT).datetimepicker({dateFormat: 'yy-mm-dd'});
		}

		$(Core.$.BLOCK_EXTENSION_INPUT).change(function () {
			// unbind all save events
			$(Core.Block).unbind('saveBlockConfig');
			$(Core.Block).unbind('blockConfigsaved');
			if ($(this).val() == '') {
				$(Core.$.BLOCK_TEMPLATE_SELECTION).attr('disabled', 'disabled');
				return;
			}
			Core.Block.loadConfig({extensionId: $(this).val()});
		});

		if ($("#templateContent").length) {
			Core.Block.CodeMirror = CodeMirror.fromTextArea($("#templateContent").get(0), {
				lineNumbers: true,
				lineWrapping: true,
				autoCloseBrackets: true,
				autoCloseTags: true,
				mode: "text/html",
				styleActiveLine: true,
				tabMode: "indent",
				matchTags: {bothTags: true},
                extraKeys: {"Ctrl-J": "toMatchingTag"}
			});
			Core.Block.CodeMirror.on("change", function(cm, change) {
				$("#templateContent").val(cm.getValue());
			});
		}

		$(Core.$.BLOCK_TEMPLATE_SELECTION).change(function () {
			if ($(this).val() == 'custom') {
				$('.template-content').show();
				$('.template-file-select').hide();
				Core.Block.CodeMirror.refresh();
			} else if($.isNumeric($(this).val())) {
				$('.template-file-select').hide();
				$('.template-content').hide();
			} else {
				$('.template-file-select').show();
				$('.template-content').hide();
			}
		}).change();

		if ($(Core.$.BLOCK_CURRENT_INPUT).length) {

			var currentBlockId = $(Core.$.BLOCK_CURRENT_INPUT).val();
			if (currentBlockId.length) {
				Core.Block.loadConfig({id: currentBlockId});
			}
		}

		var saveBlockHotKey = function (event) {
			if (!(event.which == 115 && event.ctrlKey) && !(event.which == 19)) return true;
			event.preventDefault();
			$('button[type=submit]').click();
			return false;
		};
		$(window).keypress(saveBlockHotKey);
	},


	initBlockActions: function () {

		$.each($(Core.$.BLOCK_HOLDER), function(){
			if(!$(this).hasClass('action-added')) {
				$(this).addClass('action-added');
				Core.Block.addBlockActions($(this).attr('data-id'));
			}
		});
		$.each($(Core.$.BLOCK_VIEW_CONTAINER), function(){
			if(!$(this).hasClass('action-added')) {
				$(this).addClass('action-added');
				Core.Block.addViewActions($(this).attr('id'));
			}
		});

		var start = false;
		$(Core.$.BLOCK_VIEW_CONTAINER).sortable({
			placeholder: 'draghelper',
			connectWith: Core.$.BLOCK_VIEW_CONTAINER,
			handle: Core.$.BLOCK_INFO,
			tolerance:"pointer",
			cursorAt: { top:0, left: 0 },
			start: function (ev, ui) {
				start = true;
				ev.stopPropagation();
			},
			receive: function (ev, ui) {
				ev.stopPropagation();
			},
			stop: function (ev, ui) {
				if(start) {
					ev.stopPropagation();
					start = false;

					var contentId = $(ui.item).parent().attr('id');
					var contentBlocks = [];

					$.each($(ui.item).parent().children(Core.$.BLOCK_HOLDER), function(){
						var blockElement = {contentId: contentId, blockId: $(this).data('id'), menuId: window.parent.menu_id};
						contentBlocks.push(blockElement);
					});

					if ($.trim(contentId) != '') {
						var parentWindow = Core.getBaseWindow();
						var location = parentWindow.location.href.substring(parentWindow.location.protocol.length+2);
						$.ajax({
							url:Core.getAjaxRequestUri(),
							dataType:'json',
							data:{cmd:'moveBlockToView', blocks: contentBlocks, location: location},
							type:'post',
							success:function (data, textStatus, jqXHR) {
								if (data.success == false) {
									Core.showMessage(Core.getBaseWindow().Core.Translation.Global.PermissionDenied);
								}
							}
						});
					}
				}
			}
		});



	},

	addViewActions: function (id) {
		if (id == '') {
			return;
		}

		Core.Block.contextMenuItemsDisabled['paste'] = !(typeof $.cookie('copy') != 'undefined' || typeof $.cookie('cut') != 'undefined');
		Core.Block.contextMenuItemsDisabled['pasteAsRef'] = !(typeof $.cookie('copy') != 'undefined');

		$('#' + id + '-block-container-actionbar').find('a.add').click(function(e){
			e.preventDefault();
			Core.Block.showBlockDialog(id);
		});

		if(Core.Block.contextMenuItemsDisabled['paste'] !== false) {
			$('.block-container-actionbar').find('a.paste').hide();
		}

		if(Core.Block.contextMenuItemsDisabled['pasteAsRef'] !== false) {
			$('.block-container-actionbar').find('a.pasteref').hide();
		}

		$('#' + id + '-block-container-actionbar').find('a.paste').click(function(e){
			e.preventDefault();
			Core.Block.pasteBlock(id, false);
		});

		$('#' + id + '-block-container-actionbar').find('a.pasteref').click(function(e){
			e.preventDefault();
			Core.Block.pasteBlock(id, true);
		});


		$.contextMenu( 'destroy', '.edit-view-content' );

		$.contextMenu({
			selector: '#' + id,
			callback: function (key, options) {
				switch (key) {
					case 'add':
						Core.Block.showBlockDialog(id);
						break;
					case 'paste':
						Core.Block.pasteBlock(id, false);
						break;
					case 'pasteAsRef':
						Core.Block.pasteBlock(id, true);
						break;
				}
			},
			items: {
				"add": {
					name: Core.getBaseWindow().Core.Translation.ContextMenu.AddBlock,
					icon: "edit",
					disabled: function(key, opt) {
	                    return !!Core.Block.contextMenuItemsDisabled[key];
				    }
				},
				"paste": {
					name: Core.getBaseWindow().Core.Translation.ContextMenu.PasteBlock,
					icon: "paste",
					disabled: function(key, opt) {
	                    return !!Core.Block.contextMenuItemsDisabled[key];
				    }
				},
				"pasteAsRef": {
					name: Core.getBaseWindow().Core.Translation.ContextMenu.PasteAsRefBlock,
					icon: "paste",
					disabled: function(key, opt) {
	                    return !!Core.Block.contextMenuItemsDisabled[key];
				    }
				}
			}
		});
		$('#' + id).swipe({
			swipeLeft: function (event, direction, distance, duration, fingerCount) {
				if (fingerCount === 1) {
					event.preventDefault();
					event.stopPropagation();
					$(this).contextMenu({
						x: event.changedTouches[0].screenX,
						y: event.changedTouches[0].screenY
					});
				}
			}
		});
	},

	loadConfig: function (data) {
        $('body').mask({
            spinner: { lines: 10, length: 5, width: 3, radius: 10}
        });
		$.ajax({
			url: Core.getAjaxRequestUri(),
			dataType: 'json',
			data: $.extend({cmd: 'getBlockConfig'}, data),
			type: 'post',
			success: function (json, textStatus, jqXHR) {
                $('body').unmask();

				if (json != null) {
					Core.Block.loadDefaultConfig(json);
				} else if (typeof data != 'undefined' && data.id) {
					$(Core.$.BLOCK_DIALOG + ',' + Core.$.BLOCK_OVERLAY).remove();
					Core.showMessage(Core.getBaseWindow().Core.Translation.Global.PermissionDenied);
				} else if (typeof data != 'undefined' && data.extensionId) {
					$(Core.$.BLOCK_IFRAME).contents().find('#extension option:first').prop('selected', 'selected');
					Core.showMessage(Core.getBaseWindow().Core.Translation.Global.PermissionDenied);
				}
                $('select').trigger("chosen:updated");
			}
		});
	},

	showDialog: function (dialogSettings, iframeSrc) {
		var settings = $.extend({
			dialogClass: 'block-dialog',
			title: 'Dialog',
			height: 670,
			width: 800,
			resizable: true,
			hide: {effect: "fade", duration: 200},
			show: {effect: "fade", duration: 200},
			close: function () {
				$(this).remove();
			}}, dialogSettings);

		var $newDialog = $('<div></div>');
		var $iframe = $('<iframe frameborder="0" src="about:blank" seamless></iframe>');

		$iframe.css({height: '100%', width: '100%'}).attr('src', iframeSrc);
		$newDialog.addClass(Core.$.BLOCK_OVERLAY.replace('.', ''));
		$newDialog.append($iframe);
		var dialog = $newDialog.dialog(settings);
		var titlebar = dialog.parents('.ui-dialog').find('.ui-dialog-titlebar');

		$('<div class="ui-dialog-titlebar-buttons"></div>')
			.append(titlebar.find('button'))
			.prepend($('<button class="ui-dialog-titlebar-refresh-iframe ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only"><span class="ui-button-text">refresh</span><span class="ui-button-icon-primary ui-icon ui-icon-arrowrefresh-1-w"></span></button>')
				.click(function () {
					$iframe.get(0).contentWindow.location.reload();
				})).appendTo(titlebar);

		return $newDialog;
	},

	showBlockDialog: function (contentId, currentBlockId) {
		Core.Block.dialogContentId = contentId;
		Core.Block.dialogBlockId = currentBlockId;
		$(Core.$.BLOCK_DIALOG + ',' + Core.$.BLOCK_OVERLAY).remove();
		$(Core.Block).unbind('blockConfigLoaded');
		Core.Block.dialogWithIframe = Core.Block.showDialog({title: 'Block config', dialogClass: 'block-dialog'}, Core.Admin.BLOCK_EDIT_SRC);
	},

	getExtensionConfigView: function (extensionId, extensionJsonData) {
		var blockId = $(Core.$.BLOCK_CURRENT_INPUT).val();
		$.ajax({
			url: Core.getAjaxRequestUri(),
			dataType: 'html',
			data: {cmd: 'getExtensionConfigView', id: extensionId, blockId: blockId},
			type: 'post',
			async: false,
			success: function (html) {
				Core.Block.removeTabs();
				if (html.toString().length) {
					Core.Block.addTab(extensionJsonData.name, html);
				}

				FileManager.initFilePathInput();

				Core.Block.History.load(extensionJsonData.id);
				$(Core.Block).trigger('blockConfigLoaded', [extensionJsonData]).unbind('blockConfigLoaded');
				$(Core.$.BLOCK_TEMPLATE_SELECTION).removeAttr('disabled');
			}
		});
	},

	deleteBlock: function (id) {
		var parentWindow = Core.getBaseWindow();
		var location = parentWindow.location.href.substring(parentWindow.location.protocol.length+2);
		$.ajax({
			url: Core.getAjaxRequestUri(),
			dataType: 'json',
			data: {cmd: 'deleteBlock', blockId: id, location: location},
			type: 'post',
			success: function (json, textStatus, jqXHR) {
				if (json.success == true) {
					$('[data-id="' + id + '"]').effect('explode', {}, 500, function () {
						$(this).remove();
					});
					$('[data-byRef="' + id + '"]').effect('explode', {}, 500, function () {
						$(this).remove();
					});
				} else if (typeof json.message != 'undefined') {
					Core.showMessage(json.message);
				}
			}
		});
	},

	copyBlock: function (id) {
		Core.Block.contextMenuItemsDisabled['paste'] = false;
		Core.Block.contextMenuItemsDisabled['pasteAsRef'] = false;
		$.cookie('copy', id, { path: '/' });
		$.removeCookie('cut', { path: '/' });
		$('.block-container-actionbar').find('a.paste').show();
		$('.block-container-actionbar').find('a.pasteref').show();
	},

	cutBlock: function (id) {
		Core.Block.contextMenuItemsDisabled['paste'] = false;
		$.cookie('cut', id, { path: '/' });
		$.removeCookie('copy', { path: '/' });
		$('[data-id="' + id + '"]').css('opacity', 0.5);
		$('.block-container-actionbar').find('a.paste').show();
		$('.block-container-actionbar').find('a.pasteref').hide();
	},

	pasteBlock: function (contentId, byRef) {
		var parentWindow = Core.getBaseWindow();
		var id = $.cookie('copy') || $.cookie('cut');
		var op = typeof $.cookie('copy') != 'undefined' ? 'copy' : 'cut';
		var location = parentWindow.location.href.substring(parentWindow.location.protocol.length+2);

		$.ajax({
			url: Core.getAjaxRequestUri(),
			dataType: 'json',
			data: {cmd: 'pasteBlock', contentId: contentId, blockId: id, op: op, byRef: byRef, menuId: menu_id, location: location},
			type: 'post',
			success: function (json, textStatus, jqXHR) {

				if(op === 'cut') {
					$('.block-container-actionbar').find('a.paste').hide();
					$('.block-container-actionbar').find('a.pasteref').hide();
				}
				$.removeCookie('cut', { path: '/' });

				if (json.success == true) {
					if(op === 'cut') {
						$('[data-id="' + id + '"]').effect('explode', {}, 500, function () {
							$(this).remove();
						});
					}
					$('#' + contentId).prepend(json.data);
					Core.Block.initBlockActions();
				} else if (typeof json.message != 'undefined') {
					Core.showMessage(json.message);
				}
			}
		});
	},

	replaceBlock: function (blockId, data) {
		window.parent.$('[data-id=' + blockId + ']').replaceWith(data);
		window.parent.Core.Block.initBlockActions();
	},

	addBlockActions: function (id) {
		if (id == '') {
			return;
		}

		$('[data-id=' + id + ']').find('a.edit').click(function(e){
			e.preventDefault();
			Core.Block.showBlockDialog($(this).parents(Core.$.BLOCK_VIEW_CONTAINER).attr('id'), $(this).parents(Core.$.BLOCK_HOLDER).data('id'));
		});
		$('[data-id=' + id + ']').find('a.copy').click(function(e){
			e.preventDefault();
			Core.Block.copyBlock($(this).parents(Core.$.BLOCK_HOLDER).data('id'));
		});
		$('[data-id=' + id + ']').find('a.cut').click(function(e){
			e.preventDefault();
			Core.Block.cutBlock($(this).parents(Core.$.BLOCK_HOLDER).data('id'));
		});
		$('[data-id=' + id + ']').find('a.delete').click(function(e){
			e.preventDefault();
			Core.Block.deleteBlock($(this).parents(Core.$.BLOCK_HOLDER).data('id'));
		});

		$.contextMenu({
			selector: '[data-id=' + id + ']',
			callback: function (key, options) {
				switch (key) {
					case 'edit':
						Core.Block.showBlockDialog($(this).parents(Core.$.BLOCK_VIEW_CONTAINER).attr('id'), $(this).data('id'));
						break;
					case 'copy':
						Core.Block.copyBlock($(this).data('id'));
						break;
					case 'delete':
						Core.Block.deleteBlock($(this).data('id'));
						break;
					case 'cut':
						Core.Block.cutBlock($(this).data('id'));
						break;
				}
			},
			items: {
				"edit": { name: Core.getBaseWindow().Core.Translation.ContextMenu.EditBlock, icon: "edit" },
				"copy": { name: Core.getBaseWindow().Core.Translation.ContextMenu.CopyBlock, icon: "copy" },
				"cut": { name: Core.getBaseWindow().Core.Translation.ContextMenu.CutBlock, icon: "cut" },
				"delete": { name: Core.getBaseWindow().Core.Translation.ContextMenu.DeleteBlock, icon: "delete" }
			}
		});

		$('[data-id=' + id + ']').swipe({
			swipeLeft: function (event, direction, distance, duration, fingerCount) {
				if (fingerCount === 1) {
					event.preventDefault();
					event.stopPropagation();
					$(this).contextMenu({
						x: event.changedTouches[0].screenX,
						y: event.changedTouches[0].screenY
					});
					$(document).swipe("destroy");
				}
			}
		});
	}
};