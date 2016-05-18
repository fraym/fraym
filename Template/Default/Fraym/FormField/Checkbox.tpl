<label>{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>
<div{if $errors && $errors->$propertyName}class="error"{/if}>

{if $field.translateable}
    {foreach $locales as $locale}
        {@$localeString = $locale.locale}
        {if count((array)$field.options)}
            {foreach $field.options as $opt}
                <label class="checkbox-inline">
                    {@$entityValue = et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)}
                    <input type="hidden" name="{$propertyName}[{$locale.locale}][{$opt.id}]" value="" />
                    <input type="checkbox" name="{$propertyName}[{$locale.locale}][{$opt.id}]" value="{$opt.id}" {if $entityValue && in_array($opt.id, $entityValue)} checked="checked"{/if}/> {$opt.value}
                </label>
            {/foreach}
        {else}
            <div class="checkbox">
                <label class="checkbox-inline">
                    <input type="hidden" name="{$propertyName}[{$locale.locale}]" value="0" />
                    <input type="checkbox" name="{$propertyName}[{$locale.locale}]" value="1" {if et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString) === true} checked="checked"{/if}/>
                </label>
            </div>
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