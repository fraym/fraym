{if isset($newsItem)}
<article class="post post-single" id="news-{$newsItem->id}">
    <h2>{{$newsItem->title}}</h2>

    {if $newsItem.image}
        <block type="image" src="{$newsItem.image}" autosize="1"></block>
    {/if}
    <div class="entry">
        {{$newsItem->description}}
    </div>

    <block type="content">
        <view id="news-content-{$newsItem->id}">
        </view>
    </block>
</article>
{/if}