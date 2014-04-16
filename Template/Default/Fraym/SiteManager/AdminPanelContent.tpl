<div class="config-items">
    <span class="profile">
        <a href="javascript:void(0);" onclick="Core.getBaseWindow().location = '/fraym';">
            {if i('Fraym\User\User').getUserEntity().profilePicture}
                <block type="image" alt="{i('Fraym\User\User').getUserEntity().username}" height="40" width="40" src="{i('Fraym\User\User').getUserEntity().profilePicture}" method="thumbnail" mode="outbound"></block>
            {else}
                <block type="image" alt="{i('Fraym\User\User').getUserEntity().username}" width="40" src="Public/images/fraym/logo.png" method="resize"></block>
            {/if}
        </a>
        <span>
            <strong>{_('Welcome', 'FRAYM_WELCOME')}</strong>
            {i('Fraym\User\User').getUserEntity().username}
        </span>
    </span>
    <div class="btn-group btn-group-vertical">

        <ul id="adminMenu">
            {foreach $extensions as $id => $extension}
            <li class="glyphicons display active">
                <a href="#" data-url="//{i('Fraym\Route\Route').getSiteBaseURI(false)}{i('Fraym\Route\Route').getVirtualRoute('siteManagerExt_'.$id).route}?locale={i('Fraym\Registry\Config').get('ADMIN_LOCALE_ID').value}">
                    <i class="fa fa-angle-right"></i><span>{$extension}</span>
                </a>
            </li>
            {/foreach}
        </ul>
        <a href="#" class="btn {if $inEditMode}btn-success{else}btn-danger{/if}" id="block-edit-mode" data-id="block-edit-mode" data-id="menu-editor" data-editmode="{if $inEditMode}1{else}0{/if}">
            {if $inEditMode}{_('Disable edit mode', 'FRAYM_DISABLE_EDIT_MODE')}{else}{_('Enable edit mode', 'FRAYM_ENABLE_EDIT_MODE')}{/if}
        </a>
    </div>
</div>