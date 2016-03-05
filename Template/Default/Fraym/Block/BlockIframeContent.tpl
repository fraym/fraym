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
            <div>
                <table class="table table-striped">
                    <tr>
                        <td class="title-col">{_('Display on', 'FRAYM_LANGUAGE_SELECTION')}</td>
                        <td>
                            <select class="form-control" name="menuTranslation" id="menuTranslation">
                                <option value="">{_('All languages', 'FRAYM_BLOCK_DISPLAY_ALL')}</option>
                                <option value="current">{_('Current language', 'FRAYM_BLOCK_DISPLAY_CURRENT')}</option>
                            </select>
                            <div class="form-group">
                                <div class="checkbox">
                                    <label for="all-pages">
                                        <input type="checkbox" name="menu" id="all-pages" value="1"/> {_('Show on all pages', 'FRAYM_SHOW_ON_ALL_PAGES')}
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <table class="table">

                                <tr>
                                    <td class="title-col">{_('Block Id', 'FRAYM_BLOCKID')}</td>
                                    <td><span id="currentBlockId">{_('No Id', 'FRAYM_NOID')}</span></td>
                                    <td class="title-col">{_('Selected content', 'FRAYM_SELECT_CONTENT')}</td>
                                    <td><span id="selected-content-id"></span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width="20%" class="title-col">{_('Extension', 'FRAYM_EXTENSION')}</td>
                        <td colspan="4">
                            <select class="form-control" id="extension" name="id">
                                <option value="">- {_('Please select', 'FRAYM_PLEASE_SELECT')} -</option>
                                <optgroup label="{$extension.category.name}">
                                {foreach $extensions as $extension}
                                    <option value="{$extension.id}">{_($extension.name, 'BLOCK_EXT-' . $extension.name)}</option>
                                {/foreach}
                                </optgroup>
                            </select>

                        </td>
                    </tr>
                    <tr>
                        <td class="title-col">{_('Template', 'FRAYM_TEMPLATE')}</td>
                        <td colspan="4">
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
                        </td>
                    </tr>
                    <tr class="template-file-select">
                        <td class="title-col">{_('Template file', 'FRAYM_TEMPLATE_FILE')}:</td>
                        <td colspan="4">
                            <input class="form-control" data-absolutepath="false" data-filepath="true" data-singlefileselect="1" data-filefilter="*.tpl,*.html,*.htm" type="text" name="templateFile"/>
                        </td>
                    </tr>
                    <tr class="hide">
                        <td class="title-col">
                            {_('Template content', 'FRAYM_TEMPLATE_CONTENT')}:
                        </td>
                        <td colspan="4">
                            <textarea rows="15" name="templateContent" id="templateContent"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="title-col">{_('View permission', 'FRAYM_VIEW_PERMISSION')}</td>
                        <td colspan="4">
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
                        </td>
                    </tr>
                    <tr>
                        <td class="title-col">{_('Active', 'FRAYM_ACTIVE')}</td>
                        <td colspan="4">
                            <select class="form-control" name="active">
                                <option value="1">{_('Yes', 'FRAYM_YES')}</option>
                                <option value="0">{_('No', 'FRAYM_NO')}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="title-col">{_('Excluding devices', 'FRAYM_EXCLUDE_DEVICE')}</td>
                        <td colspan="4">
                            <select class="form-control excludedDevices" data-placeholder=" " name="excludedDevices[]" multiple="multiple">
                                <option value="desktop">{_('Desktop', 'FRAYM_EXCLUDE_DEVICE_DESKTOP')}</option>
                                <option value="tablet">{_('Tablet', 'FRAYM_EXCLUDE_DEVICE_TABLET')}</option>
                                <option value="mobile">{_('Mobile', 'FRAYM_EXCLUDE_DEVICE_MOBILE')}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="title-col">{_('Active date', 'FRAYM_ACTIVE_DATE')}</td>
                        <td colspan="2">
                            <input class="form-control datetime" type="text" name="startDate" placeholder="{_('Start date', 'FRAYM_START_DATE')}" />
                        </td>
                        <td colspan="2">
                            <input class="form-control datetime" type="text" name="endDate" placeholder="{_('End date', 'FRAYM_END_DATE')}" />
                        </td>
                    </tr>
                    <tr>
                        <td class="title-col">{_('Caching', 'FRAYM_CACHING')}</td>
                        <td colspan="4">
                            <select class="form-control" name="cache">
                                <option value="1">{_('Yes', 'FRAYM_YES')}</option>
                                <option value="0">{_('No', 'FRAYM_NO')}</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
</form>
