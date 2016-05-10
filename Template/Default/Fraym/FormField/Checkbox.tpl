<label>{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>
<div{if $errors && $errors->$propertyName}class="error"{/if}>

{if $field.translateable}
    {foreach $locales as $locale}
        {@$localeString = $locale.locale}
        {if !$field.options.count}
            <div class="checkbox">
                <label>
                    <input type="hidden" name="{$propertyName}[{$locale.locale}]" value="0" />
                    <input type="checkbox" name="{$propertyName}[{$locale.locale}]" value="1" {if et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)} checked="checked"{/if}/> {$opt}
                </label>
            </div>
        {else}
            {foreach $field.options as $opt}
                <div class="checkbox">
                    <label>
                        <input class="form-control" type="checkbox" name="{$propertyName}[{$locale.locale}][]" value="{$opt.id}" {if $entity->id == $opt.id} checked="checked"{/if}/> {$opt}
                    </label>
                </div>
            {/foreach}
        {/if}
        {if count((array)$locales) > 1}<span class="add-on">{$locale->name}{if $locale->default} ({_('default', 'FRAYM_DEFAULT')}){/if}</span>{/if}
    {/foreach}
{else}
    {if !$field.options.count}
        <div class="checkbox">
            <label>
                <input type="hidden" name="{$propertyName}" value="0" />
                <input type="checkbox" name="{$propertyName}" value="1" {if $entity->$propertyName} checked="checked"{/if}/> {$opt}
            </label>
        </div>
    {else}
        {foreach $field.options as $opt}
            <div class="checkbox">
                <label>
                    <input class="form-control" type="checkbox" name="{$propertyName}[]" value="{$opt.id}" {if $entity->id == $opt.id} checked="checked"{/if}/> {$opt}
                </label>
            </div>
        {/foreach}
    {/if}
{/if}

    {if $errors && $errors->$propertyName}
        {foreach $errors->$propertyName as $error}
            <div class="error">{$error.message}</div>
        {/foreach}
    {/if}
</div>