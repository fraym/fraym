<label for="{$propertyName}_{$entity.id}">{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>
<input class="form-control" type="date" name="{$propertyName}" id="{$propertyName}_{$entity.id}" value="{if $entity->$propertyName}{$entity->$propertyName->format('Y-m-d')}{/if}" {if $errors && $errors->$propertyName}class="error"{/if} />
{if !$field.readOnly || $entity.id === null}
    <script type="text/javascript">
        $('#{$propertyName}_{$entity.id}').datepicker({ dateFormat:'yy-mm-dd' });
    </script>
{/if}