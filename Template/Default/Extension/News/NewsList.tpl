{if isset($newsItems) && count((array)$newsItems)}
    {foreach $newsItems as $k => $newsItem}
        <div class="row">

            <div class="col-md-6">
              <block type="content">
                <view id="{$newsItem.id}"></view>
              </block>
                <a href="{$getNewsItemUrl($newsItem)}">
                    {if $newsItem.image}
                        <block type="image" src="{$newsItem.image}" autosize="1"></block>
                    {else}
                        <block type="image" phbgcolor="F0F0F0" phtext="{_('No image')}" phheight="100" phwidth="300"></block>
                    {/if}
                </a>
                <p>{_('Posted on <span class="time">:date</span> by :author', 'EXT_NEWS_THEME_DEFAULT_POSTED_ON', null, array(':date' => $newsItem.date.format('Y-m-d'), ':author' => $newsItem.author))}</p>
            </div>
            <div class="col-md-6">
              <h3><a href="{$getNewsItemUrl($newsItem)}">{$newsItem.title}</a></h3>
              {{$newsItem.shortDescription}}
              <a class="btn btn-primary" href="{$getNewsItemUrl($newsItem)}">{_('Read more', 'FRAYM_DEFAULT_THEME_READ_MORE')} <i class="fa fa-angle-right"></i></a>
            </div>

          </div>

          <hr>
    {/foreach}

    <div class="row">
      <ul class="pager">
      {if isset($prevPage)}
        <li class="previous"><a href="{$prevPage}">&larr; {_('Previous page')}</a></li>
      {/if}
      {if isset($nextPage)}
        <li class="next"><a href="{$nextPage}">{_('Next page')} &rarr;</a></li>
      {/if}
      </ul>
    </div>
{elseif isset($newsItems)}
    <div class="row">{_('No entries found.')}</div>
{/if}