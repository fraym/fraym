<label>{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>
<div {if $errors && $errors->$propertyName}class="error"{/if}>
    {foreach $field.options as $opt}
        <label>
            <input class="form-control" type="radio" name="{$propertyName}"  value="{$opt.id}" {if $entity->id == $opt.id} checked="checked"{/if}/>
        </label>
    {/foreach}
    {if $errors && $errors->$propertyName}
        {foreach $errors->$propertyName as $error}
            <div class="error">{$error.message}</div>
        {/foreach}
    {/if}
</div>