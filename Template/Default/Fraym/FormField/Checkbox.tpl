<label>{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>
<div{if $errors && $errors->$propertyName}class="error"{/if}>

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
    {if $errors && $errors->$propertyName}
        {foreach $errors->$propertyName as $error}
            <div class="error">{$error.message}</div>
        {/foreach}
    {/if}
</div>