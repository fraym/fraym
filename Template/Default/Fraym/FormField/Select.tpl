<label for="{$propertyName}_{$entity.id}">{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>

<div class="input-group">
    <select class="form-control" name="{$propertyName}" id="{$propertyName}_{$entity.id}" {if $errors && $errors->$propertyName}class="error"{/if}>
            <option value=""></option>
        {foreach $field.options as $opt}
            <option value="{$opt.id}"{if $entity && $entity->$propertyName->id == $opt.id} selected="selected"{/if}>{$opt}</option>
        {/foreach}
    </select>

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
