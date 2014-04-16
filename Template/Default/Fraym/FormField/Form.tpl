{foreach $formFields as $propertyName => $field}
    {@$templateVars = array('propertyName' => $propertyName, 'field' => $field, 'errors' => $errors, 'entity' => $entity, 'data' => $data, 'locales' => $locales)}
        <div class="form-group">
            {if $field.type == 'text'}
                {include('Fraym/FormField/Text.tpl', $templateVars)}
            {elseif $field.type == 'password'}
                {include('Fraym/FormField/Password.tpl', $templateVars)}
            {elseif $field.type == 'textarea'}
                {include('Fraym/FormField/Textarea.tpl', $templateVars)}
            {elseif $field.type == 'rte'}
                {include('Fraym/FormField/Rte.tpl', $templateVars)}
            {elseif $field.type == 'select'}
                {include('Fraym/FormField/Select.tpl', $templateVars)}
            {elseif $field.type == 'radio'}
                {include('Fraym/FormField/Radio.tpl', $templateVars)}
            {elseif $field.type == 'checkbox'}
                {include('Fraym/FormField/Checkbox.tpl', $templateVars)}
            {elseif $field.type == 'multiselect'}
                {include('Fraym/FormField/MultiSelect.tpl', $templateVars)}
            {elseif $field.type == 'date'}
                {include('Fraym/FormField/Date.tpl', $templateVars)}
            {elseif $field.type == 'datetime'}
                {include('Fraym/FormField/DateTime.tpl', $templateVars)}
            {elseif $field.type == 'filepath'}
                {include('Fraym/FormField/FilePath.tpl', $templateVars)}
            {elseif $field.type == 'description'}
                {include('Fraym/FormField/Description.tpl', $templateVars)}
            {/if}
        </div>
{/foreach}