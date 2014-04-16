<label for="{$propertyName}_{$entity.id}">{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>

{if $field.translateable}
    {foreach $locales as $locale}
        {@$localeString = $locale.locale}
        <textarea class="form-control" name="{$propertyName}[{$locale.locale}]" id="{$propertyName}_{$locale.id}_{$entity.id}" rows="5" cols="10" {if $errors && $errors->$propertyName}class="error"{/if}>{et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)}</textarea>
        {if count((array)$locales) > 1}<div>{$locale->name}{if $locale->default} ({_('default', 'FRAYM_DEFAULT')}){/if}</div>{/if}
    {/foreach}
{else}
    <textarea class="form-control" name="{$propertyName}" id="{$propertyName}_{$entity.id}" rows="5" cols="10" {if $errors && $errors->$propertyName}class="error"{/if}>{$entity->$propertyName}</textarea>
{/if}

{if $errors && $errors->$propertyName}
    {foreach $errors->$propertyName as $error}
        <div class="error">{$error.message}</div>
    {/foreach}
{/if}
