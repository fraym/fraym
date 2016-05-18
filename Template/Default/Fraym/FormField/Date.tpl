<label for="{$propertyName}_{$entity.id}">{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>

{if $field.translateable}
    {foreach $locales as $locale}
        {@$localeString = $locale.locale}
        {@$translatedEntity = et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)}
        <input class="form-control" type="text" name="{$propertyName}[{$locale.locale}]" id="{$propertyName}_{$locale.locale}_{$entity.id}" value="{if is_object($translatedEntity)}{$translatedEntity->format('Y-m-d')}{else}{$translatedEntity}{/if}" {if $errors && $errors->$propertyName}class="error"{/if} />
        {if count((array)$locales) > 1}<div class="add-on">{$locale->name}</div>{/if}

        {if !$field.readOnly || $entity.id === null}
            <script type="text/javascript">
                $('#{$propertyName}_{$locale.locale}_{$entity.id}').datepicker({ dateFormat:'yy-mm-dd' });
            </script>
        {/if}
    {/foreach}
{else}
    <input class="form-control" type="text" name="{$propertyName}" id="{$propertyName}_{$entity.id}" value="{if $entity->$propertyName}{$entity->$propertyName->format('Y-m-d')}{/if}" {if $errors && $errors->$propertyName}class="error"{/if} />
    {if !$field.readOnly || $entity.id === null}
        <script type="text/javascript">
            $('#{$propertyName}_{$entity.id}').datepicker({ dateFormat:'yy-mm-dd' });
        </script>
    {/if}
{/if}