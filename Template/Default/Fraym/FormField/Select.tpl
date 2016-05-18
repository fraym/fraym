<label for="{$propertyName}_{$entity.id}">{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>

<div class="input-group">

    {if $field.translateable}
        {foreach $locales as $locale}
            {@$localeString = $locale.locale}
            {@$translatedEntity = et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)}
            <select class="form-control" name="{$propertyName}[{$locale.locale}]" id="{$propertyName}_{$locale.locale}_{$entity.id}" {if $errors && $errors->$propertyName}class="error"{/if}>
                <option value="0"></option>
                {foreach $field.options as $opt}
                    <option value="{$opt.id}"{if $translatedEntity === $opt.id} selected="selected"{/if}>{$opt.value}</option>
                {/foreach}
            </select>
            {if count((array)$locales) > 1}<span class="add-on">{$locale->name}{if $locale->default} ({_('default', 'FRAYM_DEFAULT')}){/if}</span>{/if}
        {/foreach}
    {else}
        <select class="form-control" name="{$propertyName}" id="{$propertyName}_{$entity.id}" {if $errors && $errors->$propertyName}class="error"{/if}>
            <option value=""></option>
            {foreach $field.options as $opt}
                <option value="{$opt.id}"{if $entity && $entity->$propertyName->id == $opt.id} selected="selected"{/if}>{$opt}</option>
            {/foreach}
        </select>
    {/if}

    {if $field.createNew}
        <span class="input-group-btn">
            <button class="btn btn-default create-new" type="button" title="{_('Add new', 'FRAYM_ADD_NEW')}" data-model="{$field.model}">+</button>
        </span>
    {/if}
</div>
{if $errors && $errors->$propertyName}
    {foreach $errors->$propertyName as $error}
        <div class="error">{$error.message}</div>
    {/foreach}
{/if}
