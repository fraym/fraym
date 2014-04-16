
{if count((array)$newsItems)}
    {foreach $newsItems as $k => $newsItem}
        <div class="col-lg-4 col-md-4">
              <h3><a href="{$getNewsItemUrl($newsItem)}">{$newsItem.title}</a></h3>
              <p><strong>{formatDate($newsItem.date)}</strong> {{$newsItem.shortDescription}}</p>
        </div>
    {/foreach}
{else}
    <div class="news-no-entries">{_('No entries found.')}</div>
{/if}