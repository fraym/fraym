<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="icon" type="image/ico" href="/images/fraym/favicon.ico"/>
    {css('fraym/bootstrap.min.css')}

    {css('fraym/jquery-ui.css')}
    {css('fraym/main.css')}
    {css('fraym/main-theme.css')}
    {css('fraym/chosen.css')}
    {css('fraym/jquery.contextMenu.css')}
    {css('fraym/font-awesome.min.css')}
    {css('fraym/dynatree-skin-vista/ui.dynatree.css')}
    {css('fraym/codemirror/codemirror.css')}

    {js('fraym/libs/jquery.min.js')}
    {js('fraym/libs/jquery.contextmenu.js')}
    {js('fraym/libs/jquery.noty.packaged.min.js')}
    {js('fraym/libs/jquery.slimscroll.min.js')}
    {js('fraym/libs/jquery.touchSwipe.min.js')}
    {js('fraym/libs/jquery-ui.min.js')}
    {js('fraym/libs/bootstrap.min.js')}

    {js('fraym/libs/codemirror/codemirror.js')}

    {js('fraym/libs/codemirror/addon/edit/closebrackets.js')}
    {js('fraym/libs/codemirror/addon/search/searchcursor.js')}
    {js('fraym/libs/codemirror/addon/search/search.js')}
    {js('fraym/libs/codemirror/addon/fold/xml-fold.js')}
    {js('fraym/libs/codemirror/addon/edit/matchtags.js')}
    {js('fraym/libs/codemirror/addon/edit/closetag.js')}
    {js('fraym/libs/codemirror/addon/selection/active-line.js')}

    {js('fraym/libs/codemirror/mode/css/css.js')}
    {js('fraym/libs/codemirror/mode/xml/xml.js')}
    {js('fraym/libs/codemirror/mode/php/php.js')}
    {js('fraym/libs/codemirror/mode/sass/sass.js')}
    {js('fraym/libs/codemirror/mode/javascript/javascript.js')}
    {js('fraym/libs/codemirror/mode/htmlmixed/htmlmixed.js')}

    {js('fraym/libs/ckeditor/ckeditor.js')}
    {js('fraym/libs/ckeditor/config.js')}
    {js('fraym/libs/ckeditor/adapters/jquery.js')}

    {js('fraym/libs/jquery.ui.touch-punch.min.js')}
    {js('fraym/libs/datetimepicker.js')}
    {js('fraym/libs/jquery.ui.nestedSortable.js')}
    {js('fraym/libs/jquery.json-2.2.min.js')}
    {js('fraym/libs/chosen.jquery.min.js')}
    {js('fraym/libs/jquery.coloranimations.js')}
    {js('fraym/libs/formsubmit.js')}
    {js('fraym/libs/spin.min.js')}
    {js('fraym/libs/jquery.loadmask.spin.js')}
    {js('fraym/libs/jquery.cookie.js')}
    {js('fraym/main.js')}
    {js('fraym/core/block.js')}
    {js('fraym/core/notification.js')}
    {js('fraym/core/menu.js')}
    {js('fraym/core/admin.js')}
    {js('fraym/selector_config.js')}
    {js('fraym/libs/modernizr.min.js')}
    {js('fraym/libs/dynatree/jquery.dynatree.js')}
    {js('fraym/core/filemanager.js')}
    {js('fraym/core/dynamictemplate.js')}
    {js('fraym/core/changesetmanager.js')}

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <block type="css" sequence="outputFilter" consolidate="false"></block>

    <block type="css" sequence="outputFilter" consolidate="false" group="iframe-extension"></block>


    <block type="js" sequence="outputFilter" consolidate="false"></block>
    <block type="js" sequence="outputFilter" consolidate="false" group="iframe-extension"></block>

    <script type="text/javascript">
        var menu_id =   '{i('Fraym\Route\Route')->getCurrentMenuItem()->id}';
        var base_path = '{i('Fraym\Route\Route')->getSiteBaseURI()}';
        var menu_path = '{i('Fraym\Route\Route')->getMenuPath()}';
        var ajax_handler_uri = base_path + menu_path + '?function=ajax';

        Fraym.Translation = {
            Menu: {
                DialogTitle: '{_('Select a menu entry', 'FRAYM_SELECT_MENU_DIALOG_TITLE')}'
            },
            FileManager: {
                DialogTitle: '{_('File Manager', 'EXT_FILE_MANAGER')}',
                DialogTitleSelect: '{_('File Manager - Select File / Folder', 'EXT_FILE_MANAGER_SELECT')}',
                DeleteConfirm: '{_('Do you want to delete the file really?', 'FRAYM_FILEMANAGER_DELETE_CONFIRM')}'
            }
        };

        var filebrowserBrowseUrl = '{i('Fraym\Route\Route')->getVirtualRoute('fileManager')->route}?locale={i('Fraym\Registry\Config')->get('ADMIN_LOCALE_ID')->value}&rte=1';
        var filebrowserImageBrowseUrl = '{i('Fraym\Route\Route')->getVirtualRoute('fileManager')->route}?locale={i('Fraym\Registry\Config')->get('ADMIN_LOCALE_ID')->value}&rte=1&singleFileSelect=1&fileFilter=*.jpg,*.svg,*.jpeg,*.png,*.gif';
        var filebrowserWindowWidth = 1000;
        var filebrowserWindowHeight = 600;
        var pageListJson = {i('Fraym\SiteManager\SiteManager')->getRteMenuItemArray()};

        FileManager.fileViewerSrc = '//{i('Fraym\Route\Route')->getSiteBaseURI(false)}{i('Fraym\Route\Route')->getVirtualRoute('fileViewer')->route}?locale={i('Fraym\Registry\Config')->get('ADMIN_LOCALE_ID')->value}';
        FileManager.fileManagerSrc = '//{i('Fraym\Route\Route')->getSiteBaseURI(false)}{i('Fraym\Route\Route')->getVirtualRoute('fileManager')->route}?locale={i('Fraym\Registry\Config')->get('ADMIN_LOCALE_ID')->value}';
        Fraym.Menu.selectionSrc = '//{i('Fraym\Route\Route')->getSiteBaseURI(false)}{i('Fraym\Route\Route')->getVirtualRoute('menuSelection')->route}?locale={i('Fraym\Registry\Config')->get('ADMIN_LOCALE_ID')->value}';
        Fraym.locales = {json_encode($locales)};
    </script>

</head>
<body id="fraym-iframe"{if $options.cssClass} class="{$options.cssClass} clearfix"{/if}>
    {{$content}}
</body>
</html>