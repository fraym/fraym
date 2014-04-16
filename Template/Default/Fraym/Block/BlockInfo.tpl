<div {if $type == 'content'} class="block-container"{else} class="block-holder" data-id="{$id}"{if $block && $block->byRef} data-byRef="{$block->byRef->id}"{/if}{/if}>

    {if $type != 'content'}
        <div class="block-info">{if $moudleName}{$moudleName} :{else}Static{/if} {$renderTime}</div>
    {/if}

    <div class="block-container-content">
    {{$content}}
    </div>
</div>


{if $type != 'content'}
    <script type="text/javascript">
        $(function(){ Core.Block.addBlockContextMenu('{$id}'); });
    </script>
{/if}