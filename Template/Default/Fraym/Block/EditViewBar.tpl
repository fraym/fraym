{if ($renderElement && $inEditMode == false) || ($renderElement == false && $inEditMode) || ($renderElement && $inEditMode)}
<{$htmlElement} id="{$contentId}"{if $inEditMode && $editStyle}style="{$editStyle}" {/if}{if $inEditMode || $cssClass} class="{if $inEditMode}edit-view-content {/if}{$cssClass}"{/if}{if $unique && $inEditMode} data-unique="true"{/if}>
{/if}

{{$content}}

{if($renderElement && $inEditMode == false) || ($renderElement == false && $inEditMode == true) || ($renderElement && $inEditMode)}
</{$htmlElement}>
{/if}
{if $inEditMode}
<script type="text/javascript">
    $(function(){ Core.Block.addViewContextMenu('{$contentId}'); });
</script>
{/if}