<div class="navbar navbar-default" role="navigation">
    <div class="navbar-collapse">
        <ul class="nav navbar-nav side-nav collapsed" id="navigation" tabindex="5000" style="overflow: hidden; outline: none;">
            <li class="sidebar-collapse">
                <a href="#" title="{_('Toggle menu', 'FRAYM_TOGGLE_MENU')}">
                    <i class="fa fa-bars">
                        <span class="overlay-label red"></span>
                    </i>
                    {_('Menu')}
                </a>
            </li>
            <li class="user-status status-online" id="user-status">
                <div class="profile-photo">
                    <a href="javascript:void(0);" onclick="Fraym.getBaseWindow().location = '/fraym';">
                        {if i('Fraym\User\User').getUserEntity().profilePicture}
                            <block type="image" alt="{i('Fraym\User\User').getUserEntity().username}" height="65" width="65"
                                   src="{i('Fraym\User\User').getUserEntity().profilePicture}" method="thumbnail"
                                   mode="outbound"></block>
                        {else}
                            <block type="image" alt="{i('Fraym\User\User').getUserEntity().username}" height="65" width="65"
                                   src="Public/images/fraym/profile-dummy.png" method="resize"></block>
                        {/if}
                    </a>
                </div>
                <div class="user">
                    <strong>{i('Fraym\User\User').getUserEntity().username}</strong>
                    {i('Fraym\User\User').getUserEntity().lastLogin = $date}
                    <span class="role">{_('Last login')}:<br />{formatDateTime($date)}</span>
                </div>
            </li>

            {foreach $extensions as $id => $extension}
                <li>
                    <a title="{$extension.name}" href="#"
                       data-url="//{i('Fraym\Route\Route').getSiteBaseURI(false)}{i('Fraym\Route\Route').getVirtualRoute('siteManagerExt_'.$id).route}?locale={i('Fraym\Registry\Config').get('ADMIN_LOCALE_ID').value}">
                        <i class="{$extension.iconCssClass}"><span
                                    class="overlay-label green"></span></i><span class="title">{_($extension.name)}</span>
                    </a>
                </li>
            {/foreach}

            <li class="{if $inEditMode}active{/if}">
                <a title="{if $inEditMode}{_('Disable Edit Mode', 'FRAYM_DISABLE_EDIT_MODE')}{else}{_('Enable Edit Mode', 'FRAYM_ENABLE_EDIT_MODE')}{/if}" href="#" id="block-edit-mode" data-id="block-edit-mode" data-id="menu-editor" data-editmode="{if $inEditMode}1{else}0{/if}">
                    <i class="fa {if $inEditMode}fa-cogs{else}fa-cog{/if}"><span class="overlay-label green"></span></i>
                    <span>{if $inEditMode}{_('Disable Edit Mode', 'FRAYM_DISABLE_EDIT_MODE')}{else}{_('Enable Edit Mode', 'FRAYM_ENABLE_EDIT_MODE')}{/if}</span>
                </a>
            </li>
        </ul>
    </div>
</div>