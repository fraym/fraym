<label for="{$propertyName}_{$entity.id}">{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>
{if $field.translateable}
    {foreach $locales as $locale}
        {@$localeString = $locale.locale}
        {@$translatedEntity = et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)}
        {if !$field.readOnly || $entity.id === null}
            <input class="form-control" type="text" name="{$propertyName}[{$locale.locale}]" id="{$propertyName}_{$locale.locale}_{$entity.id}" value="{if is_object($translatedEntity)}{$translatedEntity->format('Y-m-d H:i:s')}{else}{$translatedEntity}{/if}" {if $errors && $errors->$propertyName}class="error"{/if}/>
        {else}
            <input class="form-control" type="text" name="{$propertyName}[{$locale.locale}]" id="{$propertyName}_{$locale.locale}_{$entity.id}" value="{if is_object($translatedEntity)}{$translatedEntity->format('Y-m-d H:i:s')}{else}{$translatedEntity}{/if}" readonly="readonly"/>
        {/if}

        {if count((array)$locales) > 1}<div class="add-on">{$locale->name}</div>{/if}

        {if !$field.readOnly || $entity.id === null}
            <script type="text/javascript">
                $('#{$propertyName}_{$locale.locale}_{$entity.id}').datetimepicker({ dateFormat:'yy-mm-dd' });
            </script>
        {/if}
    {/foreach}
{else}
    {if !$field.readOnly || $entity.id === null}
        <input class="form-control" type="datetime" name="{$propertyName}" id="{$propertyName}_{$entity.id}" value="{if $entity->$propertyName}{$entity->$propertyName->format('Y-m-d H:i:s')}{/if}" {if $errors && $errors->$propertyName}class="error"{/if}/>
    {else}
        <input class="form-control" type="datetime" name="{$propertyName}" id="{$propertyName}_{$entity.id}" value="{if $entity->$propertyName}{$entity->$propertyName->format('Y-m-d H:i:s')}{/if}" readonly="readonly"/>
    {/if}

    {if !$field.readOnly || $entity.id === null}
        <script type="text/javascript">
            $('#{$propertyName}_{$entity.id}').datetimepicker({ dateFormat:'yy-mm-dd' });
        </script>
    {/if}
{/if}

{if $errors && $errors->$propertyName}
    {foreach $errors->$propertyName as $error}
        <div class="error">{$error.message}</div>
    {/foreach}
{/if}