<label for="{$propertyName}_{$entity.id}">{$field.label}{if call_user_func_array($field->hasValidation, array('notEmpty'))}<span class="required">*</span>{/if}</label>

{if $field.translateable}
    {foreach $locales as $locale}
        {@$localeString = $locale.locale}
        {@$translatedEntity = et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)}
        <div class="{if $field.createNew}input-group{/if}">
            <input type="hidden" name="{$propertyName}[{$locale.locale}][]" value="" />
            <select class="form-control" name="{$propertyName}[{$locale.locale}][]" id="{$propertyName}_{$locale.locale}_{$entity.id}" data-placeholder=" " {if $field.createNewInline != ''}data-fieldname="{$field.createNewInline}" {/if}multiple="multiple" {if $errors && $errors->$propertyName}class="error"{/if}{if $field.readOnly && $entity.id !== null} readonly="readonly"{/if}>
                {foreach $field.options as $opt}
                    {@$entityValue = et($entity, $propertyName, $locale.locale, $data->$propertyName->$localeString)}
                    <option value="{$opt.id}"{if $entityValue && in_array($opt.id, $entityValue)} selected="selected"{/if}>{$opt.value}</option>
                {/foreach}
            </select>
            {if $field.createNew}
                <span class="input-group-btn">
                    <button class="btn btn-default create-new" type="button" title="{_('Add new', 'FRAYM_ADD_NEW')}" data-model="{$field.model}">+</button>
                </span>
            {/if}
        </div>

        {if count((array)$locales) > 1}<div class="add-on">{$locale->name}</div>{/if}

        <script type="text/javascript">
            $("#{$propertyName}_{$locale.locale}_{$entity.id}").chosen({
                no_results_text: "{_('No results matched', 'FRAYM_NO_RESULTS_MATCHED')}" {if $field.createNewInline != ''},

                create_option: function(term){
                    var chosen = this;
                    var fieldname = $(chosen.form_field).data('fieldname');
                    $.ajax({ type: 'post', data: { field: fieldname, value: term, model: '{addslashes($field.model)}' } }).done(function(result) {
                        if(result.id) {
                            chosen.append_option({
                                value: result.id,
                                text: term
                            });
                        }
                    });

                },
                create_option_text: '{_('Add', 'FRAYM_ADD')}' {/if}
            });
        </script>
    {/foreach}
{else}
    <div class="{if $field.createNew}input-group{/if}">
        <select class="form-control" name="{$propertyName}[]" id="{$propertyName}_{$entity.id}" data-placeholder=" " {if $field.createNewInline != ''}data-fieldname="{$field.createNewInline}" {/if}multiple="multiple" {if $errors && $errors->$propertyName}class="error"{/if}{if $field.readOnly && $entity.id !== null} readonly="readonly"{/if}>
            {foreach $field.options as $opt}
                <option value="{$opt.id}"{if $entity->$propertyName && $entity->$propertyName->contains($opt)} selected="selected"{/if}>{$opt}</option>
            {/foreach}
        </select>
        {if $field.createNew}
            <span class="input-group-btn">
            <button class="btn btn-default create-new" type="button" title="{_('Add new', 'FRAYM_ADD_NEW')}" data-model="{$field.model}">+</button>
        </span>
        {/if}
    </div>

    <script type="text/javascript">
        $("#{$propertyName}_{$entity.id}").chosen({
            no_results_text: "{_('No results matched', 'FRAYM_NO_RESULTS_MATCHED')}" {if $field.createNewInline != ''},

            create_option: function(term){
                var chosen = this;
                var fieldname = $(chosen.form_field).data('fieldname');
                $.ajax({ type: 'post', data: { field: fieldname, value: term, model: '{addslashes($field.model)}' } }).done(function(result) {
                    if(result.id) {
                        chosen.append_option({
                            value: result.id,
                            text: term
                        });
                    }
                });

            },
            create_option_text: '{_('Add', 'FRAYM_ADD')}' {/if}
        });
    </script>
{/if}

{if $errors && $errors->$propertyName}
    {foreach $errors->$propertyName as $error}
        <div class="error">{$error.message}</div>
    {/foreach}
{/if}
