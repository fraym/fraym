/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
Core.Menu = {
    menu_items: [],
    mode: [],
    selectionSrc: '',

    getSites: function () {
        var group_id = $('#group').val();

        if (parseInt(group_id) > 0) {
            $('#site_block').show();
            $.get(Core.getAjaxRequestUri(), {group_id: group_id, cmd: 'get_sites'},
                function (data) {
                    $('#site_table').html(data);
                }
            );
        }
        else {
            $('#site_block').hide();
        }
    },

    initInputFields: function () {

        var inputEvent = function (e) {
            e.preventDefault();
            var $this = $(this);

            Core.Menu.openSelectMenuDialog(function(node){
                if($this.is('select')) {
                    $this.html($('<option></option>').val(node.data.key).html(node.data.title));
                } else {
                    $this.siblings('input:not([type=hidden])').attr('disabled', 'disabled').val(node.data.title);
                    $this.siblings('input[type=hidden]').removeAttr('disabled').val(node.data.key);
                }
            });
        };

        $.each($('input[data-menuselection]'), function(){
            var $this = $(this).clone(true);
            var $hiddenClone = $(this).clone(true).attr('type', 'hidden').attr('disabled', 'disabled');
            if($this.attr('data-value') != '') {
                $hiddenClone.val($this.val());
                $this.val($this.attr('data-value'));
                $this.attr('disabled', 'disabled');
            }
            $this.addClass('fraym-menu-select');
            var $selectFileBtn = $('<i class="fa fa-sitemap"></i>');
            var $wrapper = $('<div class="fraym-menu-input-wrapper"></div>');
            $wrapper.append($this);
            $wrapper.append($hiddenClone);
            $wrapper.append($selectFileBtn);
            $(this).replaceWith($wrapper);
            $wrapper.click(function(){
                if($this.is(':disabled')) {
                    $this.val('').removeAttr('disabled').focus();
                    $hiddenClone.attr('disabled', 'disabled');
                }
            });
            $selectFileBtn.click(inputEvent);
        });

        $('body').on('mousedown', 'select[data-menuselection]', inputEvent);
    },

    init: function () {
        $('#del-menu-item-popup .no-del').click(function () {
            $('#del-menu-item-popup').data("overlay").close();
        });

        Core.Menu.initInputFields();
        Core.Menu.getSiteMenu();
    },

    openSelectMenuDialog: function(callback) {
        var callback = typeof callback == 'undefined' ? function(){} : callback;

        var $dialog = Core.getBaseWindow().Core.Block.showDialog({
            title: Core.Translation.Menu.DialogTitle
        }, Core.Menu.selectionSrc + '&mode=selection');

        $dialog.find('iframe').load(function(){
            var $iframeDOM = $(this).get(0).contentWindow;
            $iframeDOM.setInterval(function(){
                try {
                    var node = $iframeDOM.$("#menu-item-list").dynatree("getActiveNode");
                    if(typeof node != 'undefined' && node != null) {
                        callback(node);
                    }
                } catch(e) {}
            }, 10);
        });
    },

    disableMenuSort: function () {
        $('.menu-item-list').sortable("disable");
    },

    enableMenuSort: function () {
        $('.menu-item-list').sortable("enable");
    },

    getSiteMenu: function () {
        var site_id = $('#site').val();

        try {
            $("#menu-item-list").dynatree("destroy");
            $("#menu-item-list").html('');
        } catch (e) {}

        Core.Menu.CustomMenu.initCustomMenuTree();

        if ($("#menu-item-list").hasClass('no-self-drop')) {
            dnd = {
                onDragStart: function (node) {
                    return true;
                },
                onDragStop: function (node) {
                }
            };
        }

        $('#menu-add-item').click(function(){
            if($("#menu-item-list").dynatree("getActiveNode")) {
                Core.Menu.addMenuItemToParent($("#menu-item-list").dynatree("getActiveNode").data.key);
            } else {
                Core.Notification.show('error', Core.Translation.Menu.NoItemSelected);
            }
        });

        $('#menu-del-item').click(function(){
            if($("#menu-item-list").dynatree("getActiveNode")) {
                Core.Menu.delMenuItem($("#menu-item-list").dynatree("getActiveNode").data.key);
            } else {
                Core.Notification.show('error', Core.Translation.Menu.NoItemSelected);
            }
        });

        $('#menu-edit-item').click(function(){
            if($("#menu-item-list").dynatree("getActiveNode")) {
                Core.Menu.editMenuItem($("#menu-item-list").dynatree("getActiveNode").data.key);
            } else {
                Core.Notification.show('error', Core.Translation.Menu.NoItemSelected);
            }
        });

        if (parseInt(site_id) > 0) {
            $.ajax({
                url: '/fraym/admin/menu/ajax',
                dataType: 'json',
                data: {site_id: site_id, cmd: 'getSiteMenu'},
                type: 'post',
                async: false,
                success: function (data, textStatus, jqXHR) {
                    var dnd = {
                        preventVoidMoves: true,
                        onDragStart: function (node) {
                            return true;
                        },
                        onDragEnter: function (node, sourceNode) {
                            return false;
                        },
                        onDrop: function (node, sourceNode, hitMode, ui, draggable) {
                            sourceNode.move(node, hitMode);
                            $.ajax({
                                url: window.location.href,
                                dataType: 'html',
                                async: false,
                                data: {cmd: 'changeMenuItemPosition', menu_id: sourceNode.data.key, parent_id: sourceNode.getParent().data.key, position: $(sourceNode.li).index()},
                                type: 'post',
                                success: function (data, textStatus, jqXHR) {
                                    return true;
                                },
                                error: function () {
                                    return false;
                                }
                            });
                        }
                    };

                    $("#menu-item-list").dynatree({
                        dnd: dnd,
                        onCreate: function (node, span) {
                            if($(span).parents('#custom-menu').length === 0) {
                                $(span).parent().contextMenu({
                                    selector: 'span',
                                    callback: function (key, options) {
                                        var node = $.ui.dynatree.getNode(this);
                                        switch (key) {
                                            case 'add':
                                                Core.Menu.addMenuItemToParent(node.data.key);
                                                break;
                                            case 'del':
                                                Core.Menu.delMenuItem(node);
                                                break;
                                            case 'edit':
                                                Core.Menu.editMenuItem(node.data.key);
                                                break;
                                        }
                                    },
                                    items: {
                                        "add": { icon: "add", name: Core.Translation.Menu.AddItem },
                                        "del": { icon: "delete", name: Core.Translation.Menu.DelItem },
                                        "edit": { icon: "edit", name: Core.Translation.Menu.EditItem }
                                    }
                                });
                            }
                        },
                        children: data
                    });
                }
            });

        }
    },

    editMenuItem: function (menuid) {
        parent.window.Core.Block.showDialog({title: 'Menu-Edit'}, window.location.pathname + '?function=edit-menu-item&menu_id=' + menuid);
    },

    addMenuItemToParent: function (menuid) {
        parent.window.Core.Block.showDialog({title: 'Menu-Edit'}, window.location.pathname + '?function=add&parent_id=' + menuid);
    },

    delMenuItem: function (node) {
        var menu_id = node.data.key;

        $.ajax({
            url: window.location.href,
            data: {cmd: 'removeMenuItem', menu_id: menu_id},
            success: function (data) {
                node.remove();
            }
        });
    },

    getRootFromNode: function (parentNode) {
        if (parentNode.parent.data.key != '_1') {
            do {
                parentNode = parentNode.getParent();
            } while (parentNode.data.isRoot == false)
        }
        return parentNode;
    },

    CustomMenu: {
        initCustomMenuTree: function (customMenuUl) {
            if($('#custom-menu-item-list').length === 0) {
                return;
            }
            try {
                $("#custom-menu-item-list").dynatree("destroy");
            } catch(e) {}
            $("#custom-menu-item-list").empty();
            if (typeof customMenuUl != 'undefined') {
                $("#custom-menu-item-list").html($('<ul><li id="_CUSTOM_ROOT_" class="folder">Custom-Menu</li></ul>')).find('li').append(customMenuUl);
            }
            $('#custom-menu-item-list').dynatree({
                onKeydown: function (node, event) {
                    if (node.data.key != '_CUSTOM_ROOT_' && (event.keyCode == 46 || event.keyCode == 8)) {
                        node.remove();
                        event.preventDefault();
                    }
                },
                dnd: {
                    autoExpandMS: 1000,
                    preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
                    onDragStart: function (node) {
                        return true;
                    },
                    onDragEnter: function (node, sourceNode) {
                        var rootNode = Core.Menu.getRootFromNode(node);
                        return node.data.copy == true || (rootNode.data.key == '_CUSTOM_ROOT_' && node.data.key != '_CUSTOM_ROOT_') ? true : "over";
                    },
                    onDragOver: function (node, sourceNode, hitMode) {
                    },
                    onDrop: function (node, sourceNode, hitMode, ui, draggable) {
                        var rootNode = Core.Menu.getRootFromNode(node);
                        var rootNode2 = Core.Menu.getRootFromNode(sourceNode);

                        if (rootNode2.data.key != rootNode.data.key) {

                            var copynode = sourceNode.toDict(true, function (dict) {
                                dict.copy = true;
                            });

                            if (hitMode == "over") {
                                // Append as child node
                                node.addChild(copynode);
                                // expand the drop target
                                node.expand(true);
                            } else if (hitMode == "before") {
                                // Add before this, i.e. as child of current parent
                                node.parent.addChild(copynode, node);
                            } else if (hitMode == "after") {
                                // Add after this, i.e. as child of current parent
                                node.parent.addChild(copynode, node.getNextSibling());
                            }
                        } else {
                            sourceNode.move(node, hitMode);
                        }
                    },
                    onDragLeave: function (node, sourceNode) {

                    }
                }
            });

            if ($('#custom-menu-item-list').length && typeof customMenuUl == 'undefined') {
                var rootNode = $('#custom-menu-item-list').dynatree("getRoot");
                rootNode.addChild({
                    title: "Custom-Menu",
                    isFolder: true,
                    key: '_CUSTOM_ROOT_'
                });
            }
        },
        init: function () {

            $(Core.Block).bind('blockConfigLoaded', function (e, json) {
                if (typeof json.xml != 'undefined' && typeof json.xml.menuItems != 'undefined') {

                    var menuItems = json.config.toString().replace(/menuItems/g, 'ul').replace(/item/g, 'li').replace(/ id/g, ' class="folder" id');
                    var ul = $(menuItems).filter('ul');
                    var siteId = $(ul).attr('site');
                    $('#site option[value="' + siteId + '"]').prop('selected', 'selected');
                    $('#site').change();
                    var tree = $("#menu-item-list").dynatree("getTree");

                    $.each($(ul).find('li'), function () {
                        var menuId = $(this).attr('id');
                        var node = tree.getNodeByKey(menuId);
                        if(node) {
                            $(this).prepend(node.data.title);
                        }
                    });

                    Core.Menu.CustomMenu.initCustomMenuTree(ul);
                } else {
                    Core.Menu.CustomMenu.initCustomMenuTree();
                }
            });

            $(Core.Block).bind('saveBlockConfig', function (e, json) {
                var tree = $('#custom-menu-item-list').dynatree("getTree").toDict();
                if(typeof tree[0].children != 'undefined') {
                    $('input[name=customMenu]').val($.toJSON(tree[0].children));
                }
            });
            Core.Menu.init();
        }
    }
};