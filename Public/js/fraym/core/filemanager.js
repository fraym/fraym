/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
if (typeof Modernizr != 'undefined' && Modernizr.touch) {
	jQuery.event.special.dblclick = {
		setup: function (data, namespaces) {
			var elem = this,
				$elem = jQuery(elem);
			$elem.bind('touchend.dblclick', jQuery.event.special.dblclick.handler);
		},

		teardown: function () {
			var elem = this,
				$elem = jQuery(elem);
			$elem.unbind('touchend.dblclick');
		},

		handler: function (event) {
			var elem = event.target,
				$elem = jQuery(elem),
				lastTouch = $elem.data('lastTouch') || 0,
				now = new Date().getTime();

			var delta = now - lastTouch;
			if (delta > 20 && delta < 500) {
				$elem.data('lastTouch', 0);
				$elem.trigger('dblclick');
			} else
				$elem.data('lastTouch', now);
		}
	};
}

var FileManager = {
	selectors: {
		fileView: '#fileView',
		selectedItems: "#fileView .file-item.selected:not(.new-file,.new-folder)",
		selectedAllItems: "#fileView .file-item.selected",
		fileItem: '#fileView .file-item',
		tree: '#tree',
		selection: '#selection'
	},
	mouseX: 0,
	mouseY: 0,
	previewIconFileExtensions: ['jpg', 'jpeg', 'gif', 'png'],
	fileFilter: '*',
	currentFile: '',
	singleFileSelect: false,
	rteSelectOptionCallback: false,
	fileViewerSrc: '',
	fileManagerSrc: '',
	dynatreeJson: {},
	dynatreeConfig: {
		fx: { height: "toggle", duration: 200 },
		autoFocus: false,
		clickFolderMode: 1,
		keyPathSeparator: "",
		debugLevel: 0,
		persist: true,
		onPostInit: function(isReloading, isError) {
			this.reactivate();
		},
		onActivate: function (node) {
			var parentNode = FileManager.getRootFromNode(node);
			var path = node.data.path;
			$('body').mask();

			$.ajax({
				url: window.location.href,
				dataType: 'json',
				data: { path: path, storage: parentNode.data.storage, cmd: 'getFiles', fileFilter: FileManager.fileFilter },
				type: 'post',
				success: function (jsonObj, textStatus, jqXHR) {
					if (jsonObj) {
						FileManager.buildDetailView(jsonObj, parentNode.data.storage);
					}
					$('body').unmask();
				}
			});
		}
	},

	initFilePathInput: function () {
		$.each($('[data-filepath]:not(.fraym-file-select)'), function(){
			var $this = $(this).clone(true);
			$this.addClass('fraym-file-select');

			var $selectFileBtn = $('<i class="fa fa-hdd-o"></i>');
			var $wrapper = $('<div class="fraym-file-input-wrapper"></div>');
			$wrapper.append($this);
			$wrapper.append($selectFileBtn);
			$(this).replaceWith($wrapper);

			$selectFileBtn.click(function(e){
				e.preventDefault();
				FileManager.open($this.data('filefilter'), $this.data('singlefileselect'), $this.val(), function (filemanager) {
					if($this.prop("tagName") === 'SELECT') {
						$this.html('');
						$.each(filemanager.File.getSelectedItems(), function(){
							if($this.data('absolutepath')) {
								$this.append($('<option></option>').val(this.path).html(this.path));
							} else {
								$this.append($('<option></option>').val(this.relativePath).html(this.relativePath));
							}
						});
					} else {
						var file = filemanager.File.getSelectedItems()[0];
						if($this.data('absolutepath')) {
							var newVal = file.path;
						} else {
							var newVal = file.relativePath;
						}
						if(newVal !== $this.val()) {
							$this.val(newVal);
							$this.trigger('change');
						}
					}
				});
			});
		});
	},

	open: function (fileFilter, singleFileSelect, currentFile, callback) {
		var fileFilter = typeof fileFilter == 'undefined' ? '' : fileFilter;
		var singleFileSelect = typeof singleFileSelect == 'undefined' ? '' : singleFileSelect;
		var callback = typeof callback == 'undefined' ? function () {
		} : callback;

        var extensions = [];
        $.each(fileFilter.split(','), function(){
            if(this.length && typeof this.match(/\*.(.*)/i)[1] != 'undefined') {
                extensions.push(this.match(/\*.(.*)/i)[1]);
            }
        });

		var $dialog = Fraym.getBaseWindow().Fraym.Block.showDialog({
			title: Fraym.Translation.FileManager.DialogTitleSelect
		}, FileManager.fileManagerSrc + '&fileFilter=' + fileFilter + '&singleFileSelect=' + singleFileSelect + '&currentFile=' + currentFile);

		$dialog.find('iframe').load(function () {
			var $iframeDOM = $(this).get(0).contentWindow;
			$iframeDOM.setInterval(function () {
                var regex = '\\.(' + extensions.join('|') + ')$';
                var r = new RegExp(regex);

				// TODO: Test all files not only the first
                if ($iframeDOM.FileManager.File.getSelectedItems().length) {
                    if(extensions.length === 0 || r.test($iframeDOM.FileManager.File.getSelectedItems()[0].name)) {
                        callback($iframeDOM.FileManager);
                    }
                }
			}, 100);
		});
	},

	File: {
		itemsCopiedStorage: null,
		itemsCopied: [],
		itemsCut: false,

		getFileInfo: function ($file) {
			var info = $.parseJSON($file.attr('data-info'));
			return info;
		},

		download: function () {
            if ($(FileManager.selectors.selectedItems).length === 0){
                return;
            }
			if (!$(FileManager.selectors.selectedItems).hasClass('folder')) {
				var file = FileManager.File.getFileInfo($(FileManager.selectors.selectedItems));
				var data = { storage: FileManager.getRootFromActiveNode().data.storage, path: file.path, cmd: 'download' }
				window.open(window.location.origin + window.location.pathname + '?' + Fraym.encodeQueryData(data), 'Download');
			}
		},

		open: function () {
			if ($(FileManager.selectors.selectedItems).hasClass('folder')) {
				$(FileManager.selectors.selectedItems).dblclick();
			} else {
				var file = FileManager.File.getFileInfo($(FileManager.selectors.selectedItems));
				var data = { storage: FileManager.getRootFromActiveNode().data.storage, path: file.path }
				Fraym.getBaseWindow().Fraym.Block.showDialog({ title: 'File: ' + $(FileManager.selectors.selectedItems).find(".file-name").html() }, FileManager.fileViewerSrc + '&' + Fraym.encodeQueryData(data));
			}
		},

		copyPath: function () {
			if (!$(FileManager.selectors.selectedItems).hasClass('folder')) {
				var file = FileManager.File.getFileInfo($(FileManager.selectors.selectedItems));

				var target = document.createElement("textarea");
				target.style.position = "absolute";
				target.style.left = "-9999px";
				target.style.top = "0";
				target.id = '_hiddenCopyText_';
				document.body.appendChild(target);

				target.textContent = file.publicPath;
				var currentFocus = document.activeElement;
				target.focus();
				target.setSelectionRange(0, target.value.length);

				var succeed;

				try {
					succeed = document.execCommand("copy");
				} catch(e) {
					window.prompt("Copy to clipboard: Ctrl+C, Enter", file.publicPath);
				}

				// restore original focus
				if (currentFocus && typeof currentFocus.focus === "function") {
					currentFocus.focus();
				}

				$(target).remove();
			}
		},

		copy: function () {
			FileManager.File.itemsCut = false;
			FileManager.File.itemsCopied = FileManager.File.getSelectedItems();
			FileManager.File.itemsCopiedStorage = FileManager.getRootFromActiveNode();
		},

		cut: function () {
			FileManager.File.copy();
			FileManager.File.itemsCut = true;

			$.each($(FileManager.selectors.selectedItems), function () {
				$(this).css({ opacity: 0.6 });
			});
		},

		paste: function () {
			var items = FileManager.File.itemsCopied;
			var cutMode = FileManager.File.itemsCut;
			var storageFrom = FileManager.File.itemsCopiedStorage.data.storage;
			var storageTo = FileManager.getRootFromActiveNode().data.storage;
			var path = $(FileManager.selectors.tree).dynatree("getActiveNode").data.key;

			$.ajax({
				url: window.location.href,
				dataType: 'json',
				data: { items: $.toJSON(items), storage: storageFrom, cutMode: cutMode, storageTo: storageTo, copyTo: path, cmd: 'pasteFile' },
				type: 'post',
				success: function (json) {
					FileManager.refreshTree();
				}
			});
		},

		getSelectedItems: function () {
			var $selectedItems = $(FileManager.selectors.selectedItems);
			var items = [];
			$.each($selectedItems, function () {
				var info = FileManager.File.getFileInfo($(this));
				items.push(info);
			});
			return items;
		},

		delete: function () {
			if (!confirm(Fraym.Translation.FileManager.DeleteConfirm)) {
				return false;
			}
			var items = FileManager.File.getSelectedItems();
			var parentNode = FileManager.getRootFromActiveNode();

			$.ajax({
				url: window.location.href,
				dataType: 'json',
				data: { items: $.toJSON(items), storage: parentNode.data.storage, cmd: 'deleteFile' },
				type: 'post',
				success: function (json) {
					if (json.error == false) {
						FileManager.refreshTree();
					}
				}
			});
		},

		newFile: function (isFolder) {
			$(FileManager.selectors.selectedItems).removeClass('selected');
			var $item = $('<div class="file-item selected"><div class="file-name"></div></div>');
			if (isFolder === true) {
				$item.addClass('new-folder');
				$item.addClass('folder');
			} else {
				$item.addClass('new-file');
			}
			$item.appendTo($("#fileView"));
			FileManager.File.rename(true);
		},

		rename: function (newFile) {
            if(typeof newFile != 'undefined') {
                var $item = $(FileManager.selectors.selectedAllItems);
            } else {
                var $item = $(FileManager.selectors.selectedItems);
            }
			var filename = $item.find('.file-name').html();
			var currentPath = $(FileManager.selectors.tree).dynatree("getActiveNode").data.path;
			var $input = $('<input id="newFileName" value="' + filename + '"/>');
			$input.css({
				'width': 'auto',
				'font-size': '10px',
				'border': '1px dotted',
				'padding': '0px',
				'text-align': 'center'
			});
			$item.find('.file-name').html($input);
			$input.focus();
			$input.keydown(function (e) {
				var keyCode = (e.which ? e.which : e.keyCode);
				var $input = $(this);
				if (keyCode == 13) {
					var parentNode = FileManager.getRootFromActiveNode();
					var newVal = $input.val();
					var $parentFileName = $input.parent();
					$parentFileName.html(newVal);
					$.ajax({
						url: window.location.href,
						dataType: 'json',
						data: {
							item: $item.attr('data-info'),
							path: currentPath,
							storage: parentNode.data.storage,
							newName: newVal,
							cmd: 'renameFile',
							newFolder: $item.hasClass('new-folder'),
							newFile: $item.hasClass('new-file')
						},
						type: 'post',
						success: function (json) {
							if (json.error == false) {
								FileManager.refreshTree();
							} else if (json.error == true && ($item.hasClass('new-folder') || $item.hasClass('new-file'))) {
								$item.remove();
							} else if (json.error == true) {
								$parentFileName.html(filename);
							}
						}
					});
				} else if (keyCode == 27) {
					if (FileManager.singleFileSelect) {
						$(FileManager.selectors.fileItem).removeClass('selected');
					}
					if (($item.hasClass('new-folder') || $item.hasClass('new-file'))) {
						$item.remove();
					} else {
						$item.find('.file-name').html(filename);
					}
				}
			});
		}
	},

	init: function () {
		FileManager.Uploader.init();

		FileManager.fileViewerSrc = FileManager.fileViewerSrc;
		if (!$(FileManager.selectors.fileView).length) {
			return;
		}

		$('.filemanager-file-download').click(FileManager.File.download);
		$('.filemanager-refresh').click(FileManager.refreshTree);

		if (FileManager.rteSelectOptionCallback != false) {
			$(window).unload(function () {
				var file = FileManager.File.getFileInfo($(FileManager.selectors.selectedItems));
				window.opener.CKEDITOR.tools.callFunction(FileManager.rteSelectOptionCallback, file.publicPath);
			});
		}

		var $doneSelectionFunc = function (e) {
			e.preventDefault();
			FileManager.mouseX = 0;
			FileManager.mouseY = 0;
			$(FileManager.selectors.selection).hide();
		};

		var shortcuts = function(e){
			e.stopPropagation();
			var keyCode = (e.which ? e.which : e.keyCode);
			if(keyCode == '65' && (e.metaKey || e.ctrlKey)) {
				$(FileManager.selectors.fileItem).addClass('selected');
				e.preventDefault();
			} else if(keyCode == '67' && (e.metaKey || e.ctrlKey)) {
				FileManager.File.copy();
			} else if(keyCode == '86' && (e.metaKey || e.ctrlKey)) {
				FileManager.File.paste();
			} else if(keyCode == '88' && (e.metaKey || e.ctrlKey)) {
				FileManager.File.cut();
			}
		};

		$(window).keydown(shortcuts);
		$(window).focus();

		var selecting = false;
		$(FileManager.selectors.fileView).mousedown(function (e) {
			if (e.which === 1) {
				e.preventDefault();
				FileManager.mouseX = (e.pageX ? e.pageX : e.clientX);
				FileManager.mouseY = (e.pageY ? e.pageY : e.clientY);
			}
			selecting = false;
		}).mousemove(function (e) {
				e.preventDefault();
				if (FileManager.mouseX && FileManager.mouseY) {
					var x2 = (e.pageX ? e.pageX : e.clientX);
					var y2 = (e.pageY ? e.pageY : e.clientY);
					var TOP = ((FileManager.mouseY < y2) ? FileManager.mouseY : y2) - $('#fileView').offset().top;
					var LEFT = ((FileManager.mouseX < x2) ? FileManager.mouseX : x2) - $('#fileView').offset().left;
					var WIDTH = (FileManager.mouseX < x2) ? x2 - FileManager.mouseX : FileManager.mouseX - x2;
					var HEIGHT = (FileManager.mouseY < y2) ? y2 - FileManager.mouseY : FileManager.mouseY - y2;
					$(FileManager.selectors.selection).css({
						position: 'absolute',
						zIndex: 5000,
						left: LEFT,
						top: TOP,
						width: WIDTH,
						height: HEIGHT
					});
					$(FileManager.selectors.selection).show();

					var newMouseX = (e.pageX ? e.pageX : e.clientX);
					var newMouseY = (e.pageY ? e.pageY : e.clientY);
					var elements = FileManager.rectangleSelect(FileManager.selectors.fileItem, FileManager.mouseX, FileManager.mouseY, newMouseX, newMouseY);
					$(FileManager.selectors.fileItem).removeClass('selected');
					selecting = true;
					$.each(elements, function () {
						this.addClass('selected');
						if (FileManager.singleFileSelect) {
							return false;
						}
					});
				}
			}).mouseup($doneSelectionFunc).bind('contextmenu',function (e) {
				e.preventDefault();
				selecting = false;
			}).click(function (e) {
				e.preventDefault();
				if ($(e.target).attr('id') == 'fileView' && selecting === false) {
					$(FileManager.selectors.fileItem).removeClass('selected');
					var e = jQuery.Event("keydown");
					e.which = 27;
					$(FileManager.selectors.fileItem).find("input").trigger(e);
				}
			}).swipe({
				click: function (e, target) {
					if ($(target).hasClass('file-item')) {
						if (FileManager.singleFileSelect) {
							$(FileManager.selectors.fileItem).removeClass('selected');
						}
						$(target).toggleClass('selected');
					}
				},
				swipe: function (event, direction, distance, duration, fingerCount) {
					if (fingerCount === 1) {
						$(this).contextMenu({
							x: event.changedTouches[0].screenX,
							y: event.changedTouches[0].screenY
						});
					}
				}
			});

		$(FileManager.selectors.tree).dynatree(
			$.extend({ children: FileManager.dynatreeJson }, FileManager.dynatreeConfig)
		);

		if(FileManager.currentFile != '') {
			if(FileManager.currentFile.substr(0, 1) == '/') {
				var path = FileManager.currentFile.substr(0, FileManager.currentFile.lastIndexOf('/'));
			} else {
				var path = FileManager.currentFile.substr(0, FileManager.currentFile.lastIndexOf('/'));
				var storageName = FileManager.currentFile.substr(0, FileManager.currentFile.indexOf('/'));

				$.each(FileManager.dynatreeJson, function(){
					if(this.title === storageName) {
						path = this.path.substr(0, this.path.lastIndexOf('/')) + '/' + path;
					}
				});
			}
			$(FileManager.selectors.tree).dynatree("getTree").activateKey(path);
		}

		var isDisabled = function () {
			return FileManager.File.getSelectedItems().length ? false : true;
		};
		var isDisabledPaste = function () {
			return FileManager.File.itemsCopied.length ? false : true;
		};
		var isDisabledRenameOpen = function () {
			return FileManager.File.getSelectedItems().length == 1 ? false : true;
		};
		var isDisabledNewFile = function () {
			return $(FileManager.selectors.tree).dynatree("getActiveNode") == null;
		};

		var isDisabledCopyPath = function () {
			if(FileManager.File.getSelectedItems().length === 0 || FileManager.File.getSelectedItems().length > 1) {
				return true;
			}

			if(FileManager.File.getSelectedItems()[0].isDir) {
				return true;
			}
			return false;
		};

		var items = {
			"open": { name: "Open", icon: "open", disabled: isDisabledRenameOpen },
			"sep1": "---------",
			"rename": { name: "Rename", icon: "edit", disabled: isDisabledRenameOpen },
			"cut": { name: "Cut", icon: "cut", disabled: isDisabled },
			"copy": { name: "Copy", icon: "copy", disabled: isDisabled },
			"paste": { name: "Paste", icon: "paste", disabled: isDisabledPaste },
			"delete": { name: "Delete", icon: "delete", disabled: isDisabled },
			"sep2": "---------",
			"newFolder": { name: "New folder", icon: "folder", disabled: isDisabledNewFile },
			"newFile": { name: "New file", icon: "doc", disabled: isDisabledNewFile},
			"sep3": "---------",
			"copyPath": { name: "Copy public path", icon: "copy", disabled: isDisabledCopyPath}
		};

		$.contextMenu({
			selector: FileManager.selectors.fileView,
			callback: function (key, options) {
				switch (key) {
					case "rename":
						FileManager.File.rename();
						break;
					case "cut":
						FileManager.File.cut();
						break;
					case "copy":
						FileManager.File.copy();
						break;
					case "paste":
						FileManager.File.paste();
						break;
					case "delete":
						FileManager.File.delete();
						break;
					case "newFolder":
						FileManager.File.newFile(true);
						break;
					case "newFile":
						FileManager.File.newFile(false);
						break;
					case "open":
						FileManager.File.open();
						break;
					case "copyPath":
						FileManager.File.copyPath();
						break;
				}
			},
			items: items
		});

	},

	refreshTree: function () {
		$('body').mask();
		var activeKey = $(FileManager.selectors.tree).dynatree("getActiveNode") ? $(FileManager.selectors.tree).dynatree("getActiveNode").data.key : false;

		$.ajax({
			url: window.location.href,
			dataType: 'json',
			data: { cmd: 'updateTree' },
			type: 'post',
			success: function (json) {
				$(FileManager.selectors.tree).dynatree("destroy");
				$(FileManager.selectors.tree).dynatree(
					$.extend({ children: json }, FileManager.dynatreeConfig)
				);
				if (activeKey) {
					FileManager.activateKey(activeKey);
				}
				$('body').unmask();
			}
		});
	},

	activateKey: function (activeKey) {
		$(FileManager.selectors.tree).dynatree("getTree").activateKey(activeKey).toggleExpand();
	},

	getRootFromActiveNode: function () {
		var node = $(FileManager.selectors.tree).dynatree("getActiveNode");

		if (node) {
			return FileManager.getRootFromNode(node);
		} else {
			return false;
		}
	},

	getRootFromNode: function (parentNode) {
		if (parentNode.parent.data.key != '_1') {
			do {
				parentNode = parentNode.getParent();
			} while (parentNode.data.isRoot == false)
		}
		return parentNode;
	},

	rectangleSelect: function (selector, x1, y1, x2, y2) {
		var elements = [];
		$(selector).each(function () {
			var p = $(this).offset();
			var xmiddle = p.left + $(this).width() / 2;
			var ymiddle = p.top + $(this).height() / 2;
			if (FileManager.matchPos(xmiddle, ymiddle, x1, y1, x2, y2)) {
				elements.push($(this));
			}
		});
		return elements;
	},

	matchPos: function (xmiddle, ymiddle, x1, y1, x2, y2) {
		if (x1 > x2) {
			var myX1 = x2;
			var myX2 = x1;
		} else {
			var myX1 = x1;
			var myX2 = x2;
		}
		if (y1 > y2) {
			var myY1 = y2;
			var myY2 = y1;
		} else {
			var myY1 = y1;
			var myY2 = y2;
		}
		// Matching
		if ((xmiddle > myX1) && (xmiddle < myX2)) {
			if ((ymiddle > myY1) && (ymiddle < myY2)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	},

	buildDetailView: function (jsonObj, storage) {
		$(FileManager.selectors.fileItem).remove();
		$.each(jsonObj, function () {
			var obj = this;
			var $file = $('<div class="file-item"></div>');
			var $fileName = $('<div class="file-name"></div>');
			$file.append($fileName);

			if (obj.isDir) {
				$file.addClass('folder');
			}
			if (Fraym.inArray(obj.extension, FileManager.previewIconFileExtensions)) {
				$file.css('background-image', 'url(' + window.location.pathname + '?cmd=getPreviewIcon&path=' + Fraym.urlEncode(obj.path) + '&storage=' + Fraym.urlEncode(storage) + ')');
			}

			$fileName.html(obj.name);
			$file.attr('data-info', $.toJSON(obj));
			$file.attr('data-name', obj.name);
			$('#fileView').append($file);
			$file.bind('contextmenu',function (e) {
				e.preventDefault();
			}).mousedown(function (e) {
					e.preventDefault();
					e.stopPropagation();
					var keyCode = (e.which ? e.which : e.keyCode);
					if (keyCode != 3 || !$(this).hasClass('selected')) {
						if (FileManager.singleFileSelect) {
							$(FileManager.selectors.fileItem).removeClass('selected');
						}
						$(this).toggleClass('selected');
					}

					return false;
				}).dblclick(function () {
					if (obj.isDir) {
						FileManager.activateKey(obj.path + obj.directorySeparator + obj.name);
					} else {
						$(FileManager.selectors.selectedItems).removeClass('selected');
						$(this).addClass('selected');
						FileManager.File.open();
					}
				});
		});
	},

	Uploader: {
		files: [],
		fileCount: 0,
		progress: 0,
		progressPercent: 0,
		resumable: null,

		init: function () {
			FileManager.Uploader.resumable = new Resumable({
				chunkSize: 2 * 1024 * 1024,
				simultaneousUploads: 4,
				target: window.location.pathname,
				query: { cmd: 'upload', path: ''},
				prioritizeFirstAndLastChunk: true
			});

			FileManager.Uploader.resumable.assignBrowse($('.resumable-browse'));
			FileManager.Uploader.resumable.assignDrop($('.resumable-drop'));
			FileManager.Uploader.resumable.on('fileAdded', function (file) {
				var path = $(FileManager.selectors.tree).dynatree("getActiveNode").data.key;
				FileManager.Uploader.resumable.opts.query.path = path;
				FileManager.Uploader.addFile(file);
				FileManager.Uploader.resumable.upload();
			});

			FileManager.Uploader.resumable.on('fileProgress', function(file){
				FileManager.Uploader.setFileProgress(file.uniqueIdentifier, file.progress());
				FileManager.Uploader.setProgress(FileManager.Uploader.resumable.progress());
            });

			FileManager.Uploader.resumable.on('fileError', function(file, message){
				FileManager.Uploader.setFileUploadStatus(file.uniqueIdentifier, 'error', message);
				FileManager.Uploader.setFileProgress(file.uniqueIdentifier, -1);
		   });

			FileManager.Uploader.resumable.on('fileSuccess', function(file, message){
				FileManager.Uploader.setFileUploadStatus(file.uniqueIdentifier, 'completed', '');
				FileManager.Uploader.setFileProgress(file.uniqueIdentifier, 1);
		   });
		},

		setFileUploadStatus: function(identifier, uploadStatus, errorMessage){
			if(!FileManager.Uploader.files[identifier]) return;
			FileManager.Uploader.files[identifier].uploadStatus = uploadStatus;
			FileManager.Uploader.files[identifier].errorMessage = errorMessage;
		},


		setFileProgress: function(identifier, progress) {
			if(!FileManager.Uploader.files[identifier]) return;
			var f = FileManager.Uploader.files[identifier];
			f.progress = progress;
			f.progressPercent = Math.floor(Math.round(progress*100.0));
			$('[data-identifier="' + identifier + '"]').html(f.fileName + ' ('+f.fileSizeFmt+') (' + f.progressPercent + '%)');
		},

		setProgress: function(progress) {
			if(progress>=1) {
                FileManager.refreshTree();
                FileManager.Uploader.resumable.cancel();
            }
			FileManager.Uploader.progress = progress;
			FileManager.Uploader.progressPercent = Math.floor(Math.floor(progress*100.0));
		},

		addFile: function (resumableFile) {
			// (uploadStatus=[uploading|completed|error])
			var identifier = resumableFile.uniqueIdentifier;
			var file = {
				identifier: identifier,
				resumableFile: resumableFile,
				fileName: resumableFile.fileName,
				uploadStatus: 'uploading',
				errorMessage: '',
				progress: 0,
				progressPercent: '0 %',
				fileSize: resumableFile.size,
				fileSizeFmt: Math.round((resumableFile.size / 1024.0 / 1024.0) * 10.0) / 10.0 + ' MB'
			};

			FileManager.Uploader.files[identifier] = file;
			FileManager.Uploader.fileCount++;

            var $item = $('<div class="file-item selected"><div class="file-name" data-identifier="' + identifier + '">' + file.fileName + ' ('+file.fileSizeFmt+')' + '</div></div>');
            $item.addClass('new-file');
            $item.appendTo($("#fileView"));

			return file;
		}

	}
};

$(function () {
	FileManager.initFilePathInput();
});