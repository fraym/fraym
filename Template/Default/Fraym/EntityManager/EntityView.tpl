<div class="container entitymanager">
    <div class="row">
        <div class="col-md-8">
            <div class="col-md-8">
                <form id="entityForm" class="form-horizontal clearfix" action="" method="post" autocomplete="off" role="form">
                    <div class="form-group">
                        <label for="id">{_('Type', 'FRAYM_TYPE')}</label>

                        <select name="model" id="model" style="width:100%;">
                            <option value="">- {_('Please choose a type', 'FRAYM_CHOOSE_A_TYPE')} -</option>
                            {foreach $groupedModels as $group}
                                <optgroup label="{_($group.name, 'MANAGED_ENTITY_GROUP-' . $group.name)}">
                                    {foreach $group.entites as $currentModel}
                                        <option value="{$currentModel.id}" {if $model && $model.id == $currentModel.id}selected{/if}>{_($currentModel, 'MANAGED_ENTITY-' . $currentModel)}</option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                        </select>

                    </div>

                    {if $model}
                        <div class="form-group">
                            <label for="id">{_('Entry', 'FRAYM_ENTRY')}</label>

                            <select name="id" id="id" style="width:100%;">
                                <option value="">{_('[New]', 'FRAYM_[NEW]')}</option>
                                {foreach $entities as $item}
                                    <option value="{$item.id}" {if $currentEntity && $currentEntity.id == $item.id}selected{/if}>{$item} (ID: {$item.id})</option>
                                {/foreach}
                            </select>

                        </div>

                        {if ($data && $data.id) || ($currentEntity && $currentEntity.id)}
                            <input type="hidden" name="cmd" value="update"/>
                            {else}
                            <input type="hidden" name="cmd" value="new"/>
                        {/if}

                    {/if}
                    {if $saveError}
                        <div class="alert alert-danger" role="alert">{_('Duplicate entry! Please check the fields.', 'FRAYM_DUPLICATE_ENTRY')}</div>
                    {/if}
                    {include('Fraym/FormField/Form.tpl', array('formFields' => $formFields, 'errors' => $errors, 'entity' => $currentEntity, 'data' => $data, 'locales' => $locales))}
                    <div class="head-buttons">
                        <div class="pull-right">
                            {if ($data && $data.id) || ($currentEntity && $currentEntity.id)}
                                <button type="button" onclick="$('[name=cmd]').val('remove');this.form.submit();" class="btn btn-danger">{_('Delete', 'FRAYM_DELETE')}</button>
                            {/if}
                            {if $model}
                                <button type="submit" class="btn">{_('Save', 'FRAYM_SAVE')}</button>
                            {/if}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        $('select#id, select#model').change(function(){
            $('input[name=cmd]').val('');
            this.form.submit();
        });
        $('.create-new').click(function(e){
            e.preventDefault();
            Fraym.getBaseWindow().Fraym.Block.showDialog({ close: function(){
                $('#entityForm').submit();
                $(this).remove(); }, title: '{_('Entity Manager', 'EXT_ENTITYMANAGER')}' }, window.location.href + '&model=' + $(this).data('model') );
        });

        $('#entityForm select:not([multiple])').attr('data-placeholder', '{_('- Please select -', 'FRAYM_PLEASE_SELECT_PLACEHOLDER')}').chosen({ width:'100%', allow_single_deselect:true, search_contains: true });

        {if $model.id && $errors !== false && $errors !== true && count((array)$errors)}
            Fraym.Notification.show('error', '{_('The entry was not saved - Please check the marked fields.', 'FRAYM_CHECK_MARKED_FIELDS')}');
        {/if}
    });
</script>