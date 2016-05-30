<div id="block-change-set-manager">

    <div class="col-xs-12">
        {if count($changeSets)}
            <ul class="nav nav-pills">
                {foreach $sites as $k => $site}
                    <li role="presentation" class="{if $k == 0}active{/if}"><a href="#site-{$site.id}" role="tab" data-toggle="tab">{$site.name}</a></li>
                {/foreach}
                <li class="pull-right">
                    <button type="button" id="undo-all" class="btn btn-warning">{_('Undo all changes')}</button>
                    <button type="button" id="deploy-all" class="btn btn-success">{_('Deploy all changes')}</button>
                </li>
            </ul>
            <div class="tab-content">
                {foreach $sites as $k => $site}
                    <div role="tabpanel" class="tab-pane{if $k == 0} active{/if}" id="site-{$site.id}">
                        {foreach $changeSets[$site.id] as $siteMenuItems}
                            {foreach $siteMenuItems as $key => $translationMenuItem}
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <span class="badge green"><a href="#" data-deploymenu="{$site.id},{$translationMenuItem['menuItem'].id},{$translationMenuItem['menuItemTranslation'].id}" title="{_('Deploy all changes for this menu')}"><i class="fa fa-check"></i></a></span>
                                        <span class="badge orange"><a href="#" data-undomenu="{$site.id},{$translationMenuItem['menuItem'].id},{$translationMenuItem['menuItemTranslation'].id}" title="{_('Undo all changes for this menu')}"><i class="fa fa-repeat"></i></a></span>
                                        <span class="badge">{count($translationMenuItem['blocks'])}</span>
                                        <div>
                                            <a href="{if $translationMenuItem['menuItem'] && $translationMenuItem['menuItem'].translations.first().url}{$translationMenuItem['menuItem'].translations.first().url}{else}/{/if}" target="_blank"><i class="fa fa-file"></i> {if $translationMenuItem['menuItem']}{$translationMenuItem['menuItem'].translations.first().title}{else}{_('All pages')}{/if} ({_('Language')}: {if $key == 0}{_('All')}{else}{$translationMenuItem['menuItemTranslation'].locale.name}{/if})</a>
                                            <div class="tab-content">
                                                <div id="menuItem-{$key}">
                                                    <ul>
                                                        {foreach $translationMenuItem['blocks'] as $blockId => $block}
                                                            <li>
                                                                <span class="badge green"><a href="#" data-deployblock="{$blockId}" title="{_('Deploy changes')}"><i class="fa fa-check"></i></a></span>
                                                                <span class="badge orange"><a href="#" data-undoblock="{$blockId}" title="{_('Undo changes')}"><i class="fa fa-repeat"></i></a></span>
                                                                <a data-toggle="tooltip" data-placement="right" title="{if $block->user}{$block->user->username} - {/if}{formatDateTime($block->created)}" href="#" onclick="Fraym.getBaseWindow().Fraym.Block.showBlockDialog('{$block.contentId}', '{$blockId}');return false;">{if $block.name}{$block.name}{else}{_('Block Id')}: {$blockId} ({$block.extension.name}){/if} ({$block.contentId}) - {if $block.type === 1}{_('Added')}{elseif $block.type === 2}{_('Edited')}{elseif $block.type === 3}{_('Moved')}{elseif $block.type === 0}{_('Deleted')}{/if}</a>
                                                            </li>
                                                        {/foreach}
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            {/foreach}
                        {/foreach}
                    </div>
                {/foreach}
            </div>
        {else}
            <br/>
            <div class="alert alert-info" role="alert">{_('No changes')}</div>
            <p><strong>{_('After the deploy: Please reload the main window to reload all blocks!')}</strong></p>
        {/if}
    </div>
</div>