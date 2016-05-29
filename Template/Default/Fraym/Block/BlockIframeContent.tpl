<form id="block-add-edit-form" autocomplete="Off" action="">
    <input type="hidden" value="saveBlockConfig" name="cmd"/>
    <input type="hidden" value="" name="currentBlockId"/>
    <input type="hidden" value="" name="menuId"/>
    <input type="hidden" value="" name="menuTranslationId"/>
    <input type="hidden" value="" name="contentId"/>
    <input type="hidden" value="" name="location"/>
    <div id="block-tabs">
        <ul>
            <li><a href="#block-tabs-1">{_('General', 'FRAYM_GENEREL')}</a></li>
        </ul>
        <div class="save-buttons">
            <button type="submit" class="btn btn-default btn-xs overlay-save" disabled="disabled">{_('Save', 'FRAYM_SAVE')}</button>
            <button type="submit" class="btn btn-default btn-xs overlay-save-and-close" disabled="disabled">{_('Save & Close', 'FRAYM_SAVE_CLOSE')}</button>
        </div>
        <div id="block-tabs-1">
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <label class="control-label">{_('Display on', 'FRAYM_LANGUAGE_SELECTION')}</label>
                </div>
                <div class="col-xs-12 col-sm-4">
                    <select class="form-control" name="menuTranslation" id="menuTranslation">
                        <option value="">{_('All languages', 'FRAYM_BLOCK_DISPLAY_ALL')}</option>
                        <option value="current">{_('Current language', 'FRAYM_BLOCK_DISPLAY_CURRENT')}</option>
                    </select>
                    <div class="checkbox">
                        <label for="all-pages">
                            <input type="checkbox" name="menu" id="all-pages" value="1"/> {_('Show on all pages', 'FRAYM_SHOW_ON_ALL_PAGES')}
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-5">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6">
                            {_('Block Id', 'FRAYM_BLOCKID')}
                        </div>
                        <div class="col-xs-6 col-sm-6">
                            <span id="currentBlockId">{_('No Id', 'FRAYM_NOID')}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 col-sm-6">
                            {_('Selected content', 'FRAYM_SELECT_CONTENT')}
                        </div>
                        <div class="col-xs-6 col-sm-6">
                            <span id="selected-content-id"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <label class="control-label">{_('Name', 'FRAYM_BLOCK_NAME')}</label>
                </div>
                <div class="col-xs-12 col-sm-9">
                    <input class="form-control" type="text" name="name"/>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <label class="control-label">{_('Extension', 'FRAYM_EXTENSION')}</label>
                </div>
                <div class="col-xs-12 col-sm-9">
                    <select class="form-control" id="extension" name="id">
                        <option value="">- {_('Please select', 'FRAYM_PLEASE_SELECT')} -</option>
                        <optgroup label="{$extension.category.name}">
                            {foreach $extensions as $extension}
                                <option value="{$extension.id}">{_($extension.name, 'BLOCK_EXT-' . $extension.name)}</option>
                            {/foreach}
                        </optgroup>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <label class="control-label">{_('Template', 'FRAYM_TEMPLATE')}</label>
                </div>
                <div class="col-xs-12 col-sm-9">
                    <select class="form-control" id="template" name="template" disabled="disabled">
                        <option value="" selected="selected">{_('Default Template', 'FRAYM_DEFAULT_TEMPLATE')}</option>
                        <option value="custom">{_('Custom template', 'FRAYM_CUSTOM_TEMPLATE')}</option>
                        {if count((array)$blockTemplates)}
                            <optgroup label="{_('Block templates', 'FRAYM_BLOCK_TEMPLATE_OPTGROUP')}">
                                {foreach $blockTemplates as $blockTemplate}
                                    <option value="{$blockTemplate.id}">{$blockTemplate.name}</option>
                                {/foreach}
                            </optgroup>
                        {/if}
                    </select>
                </div>
            </div>
            <div class="row template-file-select">
                <div class="col-xs-12 col-sm-3">
                    <label class="control-label">{_('Template file', 'FRAYM_TEMPLATE_FILE')}</label>
                </div>
                <div class="col-xs-12 col-sm-9">
                    <input class="form-control" data-absolutepath="false" data-filepath="true" data-singlefileselect="1" data-filefilter="*.tpl,*.html,*.htm" type="text" name="templateFile"/>
                </div>
            </div>
            <div class="row template-content">
                <div class="col-xs-12 col-sm-3">
                    <label class="control-label">{_('Template content', 'FRAYM_TEMPLATE_CONTENT')}</label>
                </div>
                <div class="col-xs-12 col-sm-9">
                    <textarea rows="15" name="templateContent" id="templateContent"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <label class="control-label">{_('View permission', 'FRAYM_VIEW_PERMISSION')}</label>
                </div>
                <div class="col-xs-12 col-sm-9">
                    <select class="form-control permission" name="permissions[]" multiple>
                        {foreach $userGroups as $userGroup}
                            <optgroup label="{_('Group', 'FRAYM_GROUP')}">
                                <option class="group" value="{$userGroup.identifier}">
                                    {_('Group', 'FRAYM_GROUP')}: {$userGroup.name}
                                </option>
                            </optgroup>
                        {/foreach}
                        <optgroup label="{_('User', 'FRAYM_USER')}">
                            {foreach $users as $user}
                                <option class="user" value="{$user.identifier}">
                                    {_('User', 'FRAYM_USER')}: {$user}
                                </option>
                            {/foreach}
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <label class="control-label">{_('Active', 'FRAYM_ACTIVE')}</label>
                </div>
                <div class="col-xs-12 col-sm-9">
                    <select class="form-control" name="active">
                        <option value="1">{_('Yes', 'FRAYM_YES')}</option>
                        <option value="0">{_('No', 'FRAYM_NO')}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <label class="control-label">{_('Excluding devices', 'FRAYM_EXCLUDE_DEVICE')}</label>
                </div>
                <div class="col-xs-12 col-sm-9">
                    <select class="form-control excludedDevices" data-placeholder=" " name="excludedDevices[]" multiple="multiple">
                        <option value="desktop">{_('Desktop', 'FRAYM_EXCLUDE_DEVICE_DESKTOP')}</option>
                        <option value="tablet">{_('Tablet', 'FRAYM_EXCLUDE_DEVICE_TABLET')}</option>
                        <option value="mobile">{_('Mobile', 'FRAYM_EXCLUDE_DEVICE_MOBILE')}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <label class="control-label">{_('Active date', 'FRAYM_ACTIVE_DATE')}</label>
                </div>
                <div class="col-xs-12 col-sm-4">
                    <input class="form-control datetime" type="text" name="startDate" placeholder="{_('Start date', 'FRAYM_START_DATE')}" />
                </div>
                <div class="margin visible-xs-block"></div>
                <div class="col-xs-12 col-sm-5">
                    <input class="form-control datetime" type="text" name="endDate" placeholder="{_('End date', 'FRAYM_END_DATE')}" />
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <label class="control-label">{_('Caching', 'FRAYM_CACHING')}</label>
                </div>
                <div class="col-xs-12 col-sm-9">
                    <select class="form-control" name="cache">
                        <option value="1">{_('Yes', 'FRAYM_YES')}</option>
                        <option value="0">{_('No', 'FRAYM_NO')}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>
