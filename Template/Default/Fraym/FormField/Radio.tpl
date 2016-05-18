<label>{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>
<div {if $errors && $errors->$propertyName}class="error"{/if}>

    {if $field.translateable}
        {foreach $locales as $locale}
            {@$localeString = $locale.locale}
            {foreach $field.options as $opt}
                <label class="radio-inline">
                    {@$translatedEntity = et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)}
                    <input type="radio" name="{$propertyName}[{$locale.locale}]"  value="{$opt.id}" {if $translatedEntity == $opt.id} checked="checked"{/if}/>
                    {$opt.value}
                </label>
            {/foreach}
            {if count((array)$locales) > 1}<span class="add-on">{$locale->name}{if $locale->default} ({_('default', 'FRAYM_DEFAULT')}){/if}</span>{/if}
        {/foreach}
    {else}
        {foreach $field.options as $opt}
            <label>
                <input class="form-control" type="radio" name="{$propertyName}"  value="{$opt.id}" {if $entity->id == $opt.id} checked="checked"{/if}/>
            </label>
        {/foreach}
    {/if}

    {if $errors && $errors->$propertyName}
        {foreach $errors->$propertyName as $error}
            <div class="error">{$error.message}</div>
        {/foreach}
    {/if}
</div>