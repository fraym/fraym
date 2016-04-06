<ul class="nav navbar-nav navbar-right" id="mainMenu">
    {if $root.id && $root.getCurrentTranslation().visible && $root.getCurrentTranslation().active}
        <li class=dropdown m_{$root.id}{if $root.id == $activeItem->id} active{/if}">
            <a href="{i('Fraym\Route\Route')->buildFullUrl($root, true)}">{if $root.getCurrentTranslation()}{$root.getCurrentTranslation().title}{/if}</a>
        </li>
    {/if}
    {if count($root.children) > 0}
        {foreach $root.children as $entry}
            {if $entry.getCurrentTranslation().visible && $entry.getCurrentTranslation().active}
            <li class="dropdown m_{$entry.id}{if $entry.id == $activeItem->id || ($entry.children.contains($activeItem) && $root.id != $activeItem->id)} active{/if}">
                <a href="{i('Fraym\Route\Route')->buildFullUrl($entry, true)}">{if $entry.getCurrentTranslation()}{$entry.getCurrentTranslation().title}{/if}</a>
                {if $entry.children.count() > 0}
                    <ul>
                        {foreach $entry.children as $subEntry}
                            {if $subEntry.getCurrentTranslation().visible && $subEntry.getCurrentTranslation().active}
                                <li class="dropdown m_{$subEntry.id}{if $subEntry.id == $activeItem.id} active{/if}">
                                    <a href="{i('Fraym\Route\Route')->buildFullUrl($subEntry, true)}">{if $subEntry.getCurrentTranslation()}{$subEntry.getCurrentTranslation().title}{/if}</a>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                {/if}
            </li>
            {/if}
        {/foreach}
    {/if}
</ul>