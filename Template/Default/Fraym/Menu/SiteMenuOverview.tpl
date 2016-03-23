<div class="container site-menu-overview">
    <div class="row">
        <div class="col-lg-12">
            <fieldset>
                <p>
                    {_("Chose a website then right click on a menu item for the context menu.")}
                </p>
                <div class="form-group">
                    <select class="form-control" id="site" onchange="Core.Menu.getSiteMenu();">
                        <option value="">{_("Choose")}</option>
                        {foreach $sites as $k => $site}
                            <option value="{$site.id}"{if $k == 0} selected{/if}>{$site.name}</option>
                        {/foreach}
                    </select>
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
    Core.Menu.mode = '{if $mode}{$mode}{/if}';
</script>