<div id="dynamicTemplateConfigForm">
    <block type="js" group="dynamic-template" consolidate="false"></block>
    {js('fraym/core/dynamictemplate.js', 'dynamic-template')}

    <div class="row">
        <div class="col-xs-12">
            <div class="form-group">
                <label for="dynamicTemplate">{_('Dynamic template')}</label>

                <select class="form-control" id="dynamicTemplate" name="dynamicTemplate">
                    <option value="">-{_('Please select')}-</option>
                    {foreach $selectOptions as $group => $opt}
                        {if is_string($group)}
                            <optgroup label="{$group}">
                                {foreach $opt as $file}
                                    <option value="{$group}/{$file}"{if $blockConfig.dynamicTemplate == $group.'/'.$file} selected="selected"{/if}>{$file}</option>
                                {/foreach}
                            </optgroup>
                        {else}
                            <option value="{$group}/{$file}"{if $blockConfig.dynamicTemplate == $file} selected="selected"{/if}>{$file}</option>
                        {/if}
                    {/foreach}
                </select>


            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">{_('Template configuration')}</div>
                <div class="panel-body">
                    <div id="dynamicTemplateConfig" class="col-xs-12">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>