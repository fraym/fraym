<label for="{$propertyName}_{$entity.id}">{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>
{if !$field.readOnly || $entity.id === null}
    <input class="form-control" type="datetime" name="{$propertyName}" id="{$propertyName}_{$entity.id}" value="{if $entity->$propertyName}{$entity->$propertyName->format('Y-m-d H:i:s')}{/if}" {if $errors && $errors->$propertyName}class="error"{/if}/>
{else}
    <input class="form-control" type="datetime" name="{$propertyName}" id="{$propertyName}_{$entity.id}" value="{if $entity->$propertyName}{$entity->$propertyName->format('Y-m-d H:i:s')}{/if}" readonly="readonly"/>
{/if}

{if $errors && $errors->$propertyName}
    {foreach $errors->$propertyName as $error}
        <div class="error">{$error.message}</div>
    {/foreach}
{/if}
{if !$field.readOnly || $entity.id === null}
    <script type="text/javascript">
        $('#{$propertyName}_{$entity.id}').datetimepicker({ dateFormat:'yy-mm-dd' });
    </script>
{/if}