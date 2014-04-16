<label for="{$propertyName}_{$entity.id}">{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>

<input type="password" name="{$propertyName}" id="{$propertyName}_{$entity.id}" value="" {if $errors && $errors->$propertyName}class="error form-control"{else}class="form-control"{/if}/>
{if $errors && $errors->$propertyName}
    {foreach $errors->$propertyName as $error}
        <div class="error">{$error.message}</div>
    {/foreach}
{/if}