<form id="add-edit-menu" class="form-horizontal" autocomplete="Off">
    <input type="hidden" value="{$siteId}" name="menu[site]" />
    <input type="hidden" value="{$menuItem.id}" name="menu[id]" />

    {if !$menuItem}
        <input type="hidden" value="{$parentId}" name="menu[parent]" />
        <input type="hidden" value="addMenuItem" name="cmd" />
    {else}
        <input type="hidden" value="editMenuItem" name="cmd" />
    {/if}
    <div id="block-tabs">
        <ul>
            <li><a href="#settings">{_("General Settings")}</a></li>
            {foreach $locales as $key => $locale}
                <li><a href="#tabs-{$key}">{$locale.name} ({$locale.locale})</a></li>
            {/foreach}
        </ul>

        {foreach $locales as $key => $locale}
            {if $menuItem}
                {@$localeMenuItem = $menuItem.getTranslation($locale.id)}
            {/if}

            {if $menuItem && $localeMenuItem.id}
                <input type="hidden" value="{$localeMenuItem.id}" name="menu[translations][{$locale.id}][id]" />
            {/if}
            <input name="menu[translations][{$locale.id}][locale]" type="hidden" value="{$locale.id}" />
            <div id="tabs-{$key}">
                <h4>{_("Settings")} - {$locale.name}</h4>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Title")}*</label>
                    <div class="col-lg-10">
                        <input class="form-control" name="menu[translations][{$locale.id}][title]" type="text" value="{if $menuItem}{$localeMenuItem.title}{/if}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Subtitle")}</label>
                    <div class="col-lg-10">
                        <input class="form-control" name="menu[translations][{$locale.id}][subtitle]" type="text" value="{if $menuItem}{$localeMenuItem.subtitle}{/if}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Page title")}</label>
                    <div class="col-lg-10">
                        <input class="form-control" name="menu[translations][{$locale.id}][pageTitle]" type="text" value="{if $menuItem}{$localeMenuItem.pageTitle}{/if}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Path")}</label>
                    <div class="col-lg-10">
                        <input class="form-control" name="menu[translations][{$locale.id}][url]" type="text" value="{if $menuItem}{$localeMenuItem.url}{/if}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Short description")}</label>
                    <div class="col-lg-10">
                        <input class="form-control" name="menu[translations][{$locale.id}][shortDescription]" type="text" value="{if $menuItem}{$localeMenuItem.shortDescription}{/if}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Long description")}</label>
                    <div class="col-lg-10">
                        <input class="form-control" name="menu[translations][{$locale.id}][longDescription]" type="text" value="{if $menuItem}{$localeMenuItem.longDescription}{/if}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Keywords")}</label>
                    <div class="col-lg-10">
                        <input class="form-control" name="menu[translations][{$locale.id}][keywords]" type="text" value="{if $menuItem}{$localeMenuItem.keywords}{/if}" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Visible")}</label>
                    <div class="col-lg-10">
                        <select class="form-control" name="menu[translations][{$locale.id}][visible]">
                            <option value="1"{if !$menuItem || $localeMenuItem.visible} selected="selected"{/if}>{_("Yes")}</option>
                            <option value="0"{if $menuItem && !$localeMenuItem.visible} selected="selected"{/if}>{_("No")}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Status")}</label>
                    <div class="col-lg-10">
                        <select class="form-control" name="menu[translations][{$locale.id}][active]">
                            <option value="1"{if !$menuItem || $localeMenuItem.active} selected="selected"{/if}>{_("Active")}</option>
                            <option value="0"{if $menuItem && !$localeMenuItem.active} selected="selected"{/if}>{_("Inactive")}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-3">
                        <div class="checkbox">
                            <label>
                                <input name="menu[translations][{$locale.id}][externalUrl]" type="checkbox" value="1"{if $menuItem && $localeMenuItem.externalUrl == true} checked="checked"{/if}/> {_("Is URL external")}?
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
        <div id="settings">
            <fieldset>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Caching")}</label>
                    <div class="col-lg-10">
                        <select class="form-control" name="menu[caching]">
                            <option value="1"{if $menuItem.caching} selected="selected"{/if}>{_("Active")}</option>
                            <option value="0"{if $menuItem.caching === false} selected="selected"{/if}>{_("Inactive")}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Protocol")}</label>
                    <div class="col-lg-10">
                        <select class="form-control" name="menu[https]">
                            <option value="0"{if !$menuItem.https} selected="selected"{/if}>{_("HTTP")}</option>
                            <option value="1"{if $menuItem.https} selected="selected"{/if}>{_("HTTPS")}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Protected page")}</label>
                    <div class="col-lg-10">
                        <select class="form-control" name="menu[checkPermission]">
                            <option value="0"{if !$menuItem.checkPermission} selected="selected"{/if}>{_("No")}</option>
                            <option value="1"{if $menuItem.checkPermission} selected="selected"{/if}>{_("Yes")}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-lg-10">
                        <div class="checkbox">
                            <label>
                                <input name="menu[is404]" type="checkbox" value="1"{if $menuItem && $menuItem.is404} checked="checked"{/if}/> {_("404 Page")}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-2 control-label">{_("Site template")}</label>
                    <div class="col-lg-10">
                        <select class="form-control" name="menu[template]">
                            {foreach $templates as $template}
                                <option value="{$template.id}"{if $menuItem.template.id == $template.id} selected="selected"{/if}>{$template.name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="col-lg-10">
                        <button class="btn btn-primary pull-right" type="submit">{_("Save")}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>

<script type="text/javascript">
    $(Fraym.$.BLOCK_TABS).tabs();
    $('#add-edit-menu').formSubmit({ 'dataType': 'json', 'onSuccess' : function(data){
        Fraym.Notification.show(Fraym.Notification.TYPE_SUCCESS, '{_("Settings saved!")}');

        if(typeof data != 'undefined') {
            $('[name="menu[id]"]').val(data.menuId);
            $('[name="menu[parent]"]').remove();
            $('[name="cmd"]').val('editMenuItem');

            $.each(data.translations, function(k, v) {
                $('#add-edit-menu').append($('<input type="hidden" value="' + v + '" name="menu[translations][' + k + '][id]" />'));
            });
        }
    } });
</script>