<div class="container site-menu-overview">
    <div class="row">
        <div class="col-lg-12">
            <fieldset>
                <p>
                    {_("Chose a website then right click on a menu item for the context menu.")}
                </p>
                <div class="form-group">
                    <select class="form-control" id="site" onchange="Fraym.Menu.getSiteMenu();">
                        <option value="">{_("Choose")}</option>
                        {foreach $sites as $k => $site}
                            <option value="{$site.id}"{if $k == 0} selected{/if}>{$site.name}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" id="menu-add-item" class="btn btn-default"><i class="fa fa-plus"></i> {_('Add item')}</button>
                    <button type="button" id="menu-del-item" class="btn btn-default"><i class="fa fa-trash-o"></i> {_('Del item')}</button>
                    <button type="button" id="menu-edit-item" class="btn btn-default"><i class="fa fa-pencil"></i> {_('Edit item')}</button>
                </div>
            </fieldset>

            <fieldset>
                <h4>{_("Menu")}</h4>
                <div class="form-group">
                    <div id="menu-item-list">

                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</div>
<script type="text/javascript">
    Fraym.Menu.mode = '{if $mode}{$mode}{/if}';
    Fraym.Translation.Menu = {
      AddItem: '{_('Add item')}',
      DelItem: '{_('Del item')}',
      EditItem: '{_('Edit item')}',
      NoItemSelected: '{_('No menu item selected!')}'
    };
    Fraym.Menu.init();
</script>