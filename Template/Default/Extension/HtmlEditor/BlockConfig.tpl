
<block type="js" group="extension-htmleditor" consolidate="false"></block>
{js('fraym/extension/htmleditor/html.js', 'extension-htmleditor')}

<div id="htmlblock-tabs">
       <ul>
           {foreach $locales as $k => $locale}
            <li><a href="#htmlblock-tabs-{$k}">{$locale.name}</a></li>
           {/foreach}
       </ul>
        {foreach $locales as $k => $locale}
           <div id="htmlblock-tabs-{$k}">
               <div class="block-html-config">
                   <label for="html-block">{_('Enter your HTML:')}</label>
                   {@$localeId = $locale.id}
                   <textarea class="ckeditor" id="html-block-{$locale.id}" name="html[{$locale.id}]" rows="15">{$blockConfig->$localeId}</textarea>
               </div>
           </div>
        {/foreach}
</div>

<script type="text/javascript">
    var CKEDITOR_BASEPATH = '/js/fraym/libs/ckeditor/';
    var page_list_json = {{$menuItems}};
    $(Core.Block).bind('blockConfigLoaded', function(e, json){
        if(CKEDITOR) {
            {foreach $locales as $locale}
                CKEDITOR.replace( 'html-block-{$locale.id}',
                 {
                    filebrowserBrowseUrl : '{i('Fraym\Route\Route')->getVirtualRoute('fileManager')->route}?locale={i('Fraym\Registry\Config')->get('ADMIN_LOCALE_ID')->value}&rte=1',
                    filebrowserImageBrowseUrl : '{i('Fraym\Route\Route')->getVirtualRoute('fileManager')->route}?locale={i('Fraym\Registry\Config')->get('ADMIN_LOCALE_ID')->value}&rte=1&singleFileSelect=1&fileFilter=*.jpg,*.svg,*.jpeg,*.png,*.gif',
                    filebrowserWindowWidth  : 1000,
                    filebrowserWindowHeight : 600
                });
            {/foreach}
        }
    });
</script>
