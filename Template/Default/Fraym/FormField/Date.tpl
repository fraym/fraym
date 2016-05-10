<label for="{$propertyName}_{$entity.id}">{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>

{if $field.translateable}
    {foreach $locales as $locale}
        {@$localeString = $locale.locale}
        <input class="form-control" type="date" name="{$propertyName}[{$locale.locale}]" id="{$propertyName}_{$locale.locale}_{$entity.id}" value="{if et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)}{et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)}{/if}" {if $errors && $errors->$propertyName}class="error"{/if} />
        {if !$field.readOnly || $entity.id === null}
            <script type="text/javascript">
                $('#{$propertyName}_{$locale.locale}_{$entity.id}').datepicker({ dateFormat:'yy-mm-dd' });
            </script>
        {/if}
    {/foreach}
{else}
    <input class="form-control" type="date" name="{$propertyName}" id="{$propertyName}_{$entity.id}" value="{if $entity->$propertyName}{$entity->$propertyName->format('Y-m-d')}{/if}" {if $errors && $errors->$propertyName}class="error"{/if} />
    {if !$field.readOnly || $entity.id === null}
        <script type="text/javascript">
            $('#{$propertyName}_{$entity.id}').datepicker({ dateFormat:'yy-mm-dd' });
        </script>
    {/if}
{/if}