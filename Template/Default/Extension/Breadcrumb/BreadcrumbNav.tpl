<ul class="breadcrumbs fixclear">
    {foreach $menuItems as $key => $menuItem}
        <li>
            {if isLast($menuItems, $key) === false}
                <a href="{if $menuItem->url}{$menuItem->url}{else}/{/if}">{$menuItem->title}</a>
            {else}
                {$menuItem->title}
            {/if}
        </li>
    {/foreach}
</ul>