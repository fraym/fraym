{if count((array)$categories)}
<div class="news-categories">
    <h4>{_('Categories')}</h4>
    <ul class="list-checkmark">
    {foreach $categories as $cat}
        <li><a href="{$getNewsListCategoryUrl($cat)}">{$cat}</a></li>
    {/foreach}
    </ul>
</div>
{/if}