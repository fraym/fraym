{if isset($newsItem)}
<article class="post post-single" id="news-{$newsItem->id}">
    <h2>{{$newsItem->title}}</h2>

    {if $newsItem.image}
        <block type="image" src="{$newsItem.image}" autosize="1"></block>
    {/if}
    <div class="meta">
        <p>{_('Posted on <span class="time">:date</span> by :author', 'EXT_NEWS_THEME_DEFAULT_POSTED_ON', null, array(':date' => $newsItem.date.format('Y-m-d'), ':author' => $newsItem.author))}.</p>
    </div>
    <div class="entry">
        {{$newsItem->description}}
    </div>

    <block type="content">
        <view id="news-content-{$newsItem->id}">
        </view>
    </block>
</article>
{/if}