<block type="config">
    <template>
        <![CDATA[

        <div class="form-group">
            <label>{_('Headline')}</label>
            <input type="text" class="form-control" name="config[headline]" value="{$config.headline}" />
        </div>
        <div class="form-group">
            <label>{_('Subheadline')}</label>
            <input type="text" class="form-control" name="config[subheadline]" value="{$config.subheadline}" />
        </div>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="config[showSubheadline]" value="1"{if $config.showSubheadline} checked{/if}/>
                    {_('Show subheadline')}
                </label>
            </div>
        </div>
        <div class="form-group">
            <div class="radio">
                <label>
                    <input type="radio" name="config[showTextImage]" value="0"{if $config.showTextImage == '0'} checked{/if}/>
                    {_('Show text')}
                </label>
            </div>
        </div>
        <div class="form-group">
            <div class="radio">
                <label>
                    <input type="radio" name="config[showTextImage]" value="1"{if $config.showTextImage == '1'} checked{/if}/>
                    {_('Show image')}
                </label>
            </div>
        </div>

        <div class="form-group">
            <label>
                {_('Color')}
            </label>
            <select class="form-control" name="config[headlineColor]">
                <option value=""{if $config.headlineColor == ''} selected{/if}></option>
                <option value="red"{if $config.headlineColor == 'red'} selected{/if}>{_('Red')}</option>
                <option value="blue"{if $config.headlineColor == 'blue'} selected{/if}>{_('Blue')}</option>
                <option value="green"{if $config.headlineColor == 'green'} selected{/if}>{_('Green')}</option>
                <option value="black"{if $config.headlineColor == 'black'} selected{/if}>{_('Black')}</option>
            </select>
        </div>
        <div class="form-group">
            <label>
                {_('Text')}
            </label>
            <textarea name="config[text]" class="form-control">{$config.text}</textarea>
        </div>
        <div class="form-group">
            <label>
                {_('Rich-text editor')}
            </label>
            <textarea name="config[rte]" class="form-control" id="rte" data-rte="{ toolbar: [{ name: 'document', groups: ['mode', 'document', 'doctools'], items: ['Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates'] }, { name: 'clipboard', groups: ['clipboard', 'undo'], items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] }, { name: 'editing', groups: ['find', 'selection', 'spellchecker'], items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'] }, { name: 'tools', items: ['Maximize', 'ShowBlocks']}, '/', { name: 'insert', items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'SpecialChar', 'Iframe'] }, { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'], items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl'] }, { name: 'links', items: ['Link', 'Unlink', 'Anchor'] }, { name: 'basicstyles', groups: ['basicstyles', 'cleanup'], items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] }, { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] }, { name: 'colors', items: ['TextColor', 'BGColor'] }] }">{$config.rte}</textarea>
        </div>

        <div class="form-group">
            <label>{_('Date')}</label>
            <input id="dynamicTemplateDate" type="text" class="form-control" name="config[date]" value="{$config.date}"/>
        </div>
        <div class="form-group">
            <label>{_('Date and time')}</label>
            <input id="dynamicTemplateDateTime" type="text" class="form-control" name="config[datetime]" value="{$config.datetime}" />
        </div>
        <div class="form-group">
            <label>{_('Image')}</label>
            <input type="text" class="form-control" name="config[image]" value="{$config.image}" data-absolutepath="false" data-filepath="true" data-singlefileselect="1" data-filefilter="*.jpg,*.png" />
        </div>

        <script type="text/javascript">
            $('#dynamicTemplateDate').datepicker({ dateFormat:'yy-mm-dd' });
            $('#dynamicTemplateDateTime').datetimepicker({ dateFormat:'yy-mm-dd' });
        </script>

        ]]>
    </template>
</block>
<div>
    <div class="media">
        <div class="media-left">
            {if $config.showTextImage == '0'}
                <p>{_('No image')}</p>
            {else}
                <a href="#">
                    <block type="image" src="{$config.image}" autosize="1"></block>
                </a>
            {/if}
        </div>
        <div class="media-body">
                <h4 class="media-heading"{if $config.headlineColor} style="color:{$config.headlineColor};"{/if}>{$config.headline}</h4>
                {if $config.showSubheadline}<h5>{$config.subheadline}</h5>{/if}
                <p>{$config.date}</p>
                <p>
                    {$config.text}
                </p>
                {{$config.rte}}
            <p>{_('Updated')}: {date('d.m.Y H:i:s', strtotime($config.datetime))}</p>
        </div>
    </div>
</div>

