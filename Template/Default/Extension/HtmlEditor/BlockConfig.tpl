
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
                   <textarea data-rte class="ckeditor" name="html[{$locale.id}]" rows="15">{$blockConfig->$localeId}</textarea>
               </div>
           </div>
        {/foreach}
</div>