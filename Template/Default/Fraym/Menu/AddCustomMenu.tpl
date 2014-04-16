<div id="custom-menu">
    <div class="g6">
        <fieldset>
            <label>
                {_("Choose a website menu")}
            </label>
            <section>
                <div>
                    <select name="site" id="site" onchange="Core.Menu.getSiteMenu();">
                        <option value="">{_("Choose")}</option>
                        {foreach $sites as $site}
                            <option value="{$site.id}">{$site.name}</option>
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
        Core.Menu.CustomMenu.init();
    </script>
</div>