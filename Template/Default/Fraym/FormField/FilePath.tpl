<label for="{$propertyName}_{$entity.id}">{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>

{if $field.translateable}
    {foreach $locales as $locale}
        {@$localeString = $locale.locale}
        <div class="input-append">
            <input class="form-control" type="text" data-filepath="true" data-filefilter="{$field.fileFilter}" data-absolutepath="{$field.absolutePath}" data-singlefileselect="{$field.singleFileSelect}" name="{$propertyName}[{$locale.locale}]" id="{$propertyName}_{$entity.id}" value="{et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)}" class="span2{if $errors && $errors->$propertyName} error{/if}"/>
            {if count((array)$locales) > 1}<span class="add-on">{$locale->name}{if $locale->default} ({_('default', 'FRAYM_DEFAULT')}){/if}</span>{/if}
        </div>
    {/foreach}
{else}
    <input class="form-control" type="text" data-filepath="true" data-filefilter="{$field.fileFilter}" data-singlefileselect="{$field.singleFileSelect}" name="{$propertyName}" id="{$propertyName}_{$entity.id}" value="{$entity->$propertyName}" {if $errors && $errors->$propertyName}class="error"{/if}/>
{/if}

{if $errors && $errors->$propertyName}
    {foreach $errors->$propertyName as $error}
        <div class="error">{$error.message}</div>
    {/foreach}
{/if}