{if $inEditMode}
    <div id="{$contentId}-block-container-actionbar" class="block-container-actionbar" data-toggle="tooltip" data-placement="bottom" title="{$description}" style="{$actionBarStyle}">
        <a class="block-to-top" href="#" title="{_('Block to top', 'FRAYM_ADMIN_BLOCK_TO_TOP')}"><i class="fa fa-caret-square-o-up"></i></a>
        <span>{_('Container Id', 'FRAYM_CONTAINER_ID')}: {$contentId}</span>
        <a class="add" href="#" title="{_('Add block', 'FRAYM_ADMIN_CONTEXT_MENU_ADD_BLOCK')}"><i class="fa fa-plus"></i></a>
        <a class="paste" href="#" title="{_('Paste block', 'FRAYM_ADMIN_CONTEXT_MENU_PASTE_BLOCK')}"><i class="fa fa-clipboard"></i></a>
        <a class="pasteref" href="#" title="{_('Paste as referance', 'FRAYM_ADMIN_CONTEXT_MENU_PASTE_REF_BLOCK')}"><i class="fa fa-exchange"></i></a>
    </div>
{/if}
{if ($renderElement && $inEditMode == false) || ($renderElement == false && $inEditMode) || ($renderElement && $inEditMode)}
<{$htmlElement} id="{$contentId}"{if $inEditMode && $editStyle}style="{$editStyle}" {/if}{if $inEditMode || $cssClass} class="{if $inEditMode}edit-view-content {/if}{$cssClass}"{/if}{if $unique && $inEditMode} data-unique="true"{/if}>
{/if}
{{$content}}

{if($renderElement && $inEditMode == false) || ($renderElement == false && $inEditMode == true) || ($renderElement && $inEditMode)}
</{$htmlElement}>
{/if}