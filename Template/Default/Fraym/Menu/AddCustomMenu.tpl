<div id="custom-menu">
    <div class="g6">
        <fieldset>
            <label>
                {_("Choose a website menu")}
            </label>
            <section>
                <div>
                    <select name="site" class="form-control" id="site" onchange="Fraym.Menu.getSiteMenu();">
                        <option value="">{_("Choose")}</option>
                        {foreach $sites as $k => $site}
                            <option value="{$site.id}"{if $k == 0} selected{/if}>{$site.name}</option>
                        {/foreach}
                    </select>
                </div>
            </section>
        </fieldset>
        <fieldset>
            <label>{_("Menu")}</label>
            <section>
                <div id="menu-item-list" class="no-self-drop">

                </div>
                <div id="custom-menu-item-list">

                </div>
            </section>
        </fieldset>
        <input type="hidden" name="customMenu" value="" />
    </div>
    <script type="text/javascript">
        Fraym.Menu.CustomMenu.init();
    </script>
</div>