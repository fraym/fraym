{if count((array)$newsItem->tags)}
<div class="news-tags">
    <h4>{_('Tags')}</h4>
    <ul class="list-checkmark">
    {foreach $newsItem->tags as $tag}
        <li><a href="{$getNewsListTagUrl($tag)}">{$tag}</a></li>
    {/foreach}
    </ul>
</div>
{/if}