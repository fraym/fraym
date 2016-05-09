<div class="form-horizontal">
    <div class="form-group">
        <label class="col-sm-2 control-label" for="newsView">{_('View')}</label>
        <div class="col-sm-10">
            <select id="newsView" name="newsView" class="form-control">
                <optgroup label="{_('List')}">
                    <option value="list"{if $blockConfig && $blockConfig.view == 'list'} selected="selected"{/if}>{_('All news')}</option>
                    <option value="list-category"{if $blockConfig && $blockConfig.view == 'list-category'} selected="selected"{/if}>{_('All news categories')}</option>
                    <option value="list-tag"{if $blockConfig && $blockConfig.view == 'list-tag'} selected="selected"{/if}>{_('All news tags')}</option>
                </optgroup>
                <optgroup label="{_('Detail')}">
                    <option value="detail"{if $blockConfig && $blockConfig.view == 'detail'} selected="selected"{/if}>{_('News detail view')}</option>
                    <option value="detail-category"{if $blockConfig && $blockConfig.view == 'detail-category'} selected="selected"{/if}>{_('News item categories')}</option>
                    <option value="detail-tag"{if $blockConfig && $blockConfig.view == 'detail-tag'} selected="selected"{/if}>{_('News item tags')}</option>
                </optgroup>
            </select>
        </div>
      </div>
    <div class="newsOptions">
        <div id="globalConfig" style="display: none;">
            <div class="form-group">
                <label class="col-sm-2 control-label" for="listPage">{_('List page')}</label>
                <div class="col-sm-10">
                    <select name="listPage" id="listPage" class="form-control default" data-menuselection>
                        {if $blockConfig && intval($blockConfig.listPage) > 0}<option value="{$blockConfig.listPage}">{menuItem($blockConfig.listPage)->getCurrentTranslation()->title}</option>{/if}
                    </select>
                </div>
            </div>
        </div>

        <div id="listConfig" style="display: none;">
            <div class="form-group">
                <label class="col-sm-2 control-label" for="newsListSort">{_('Sort by')}</label>
                <div class="col-sm-10">
                  <select id="newsListSort" name="newsListSort" class="form-control">
                      <option value="date_desc"{if $blockConfig && $blockConfig.newsListSort == 'date_desc'} selected="selected"{/if}>{_('Date descending')}</option>
                      <option value="date_asc"{if $blockConfig && $blockConfig.newsListSort == 'date_asc'} selected="selected"{/if}>{_('Date ascending')}</option>
                      <option value="title_asc"{if $blockConfig && $blockConfig.newsListSort == 'title_asc'} selected="selected"{/if}>{_('Title')}</option>
                  </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="newsListSort">{_('Limit')}</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" min="1" name="limit"{if $blockConfig && $blockConfig.limit > 0} value="{$blockConfig.limit}"{/if} />
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="detailPage">{_('Detail page')}</label>
                <div class="col-sm-10">
                    <select name="detailPage" id="detailPage" class="form-control default" data-menuselection>
                        {if $blockConfig && intval($blockConfig.detailPage) > 0}<option value="{$blockConfig.detailPage}">{menuItem($blockConfig.detailPage)->getCurrentTranslation()->title}</option>{/if}
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="newsListItems">{_('News items')} ({_('Leave blank to view all')})</label>
                <div class="col-sm-10">
                    <select data-placeholder=" " id="newsListItems" name="newsListItems[]" class="form-control" multiple="multiple">
                        {foreach $newsListItems as $item}
                            <option value="{$item.id}" {if in_array($item->id, (array)$selectedNewsItems)} selected="selected"{/if}>{$item}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="forceShowOnDetail">{_('Show on detail')}</label>
                <div class="col-sm-10">
                    <input type="checkbox" class="form-control" name="forceShowOnDetail" value="1"{if $blockConfig && $blockConfig.forceShowOnDetail == '1'} checked="checked"{/if}/>
                </div>
            </div>

        </div>
    </div>
</div>


<script type="text/javascript">
    var initNewsView = function() {
        var nview = $('#newsView').val();
        $('.newsOptions').children().hide();
        if(nview == 'list') {
            $('#listConfig').show();
        } else if(nview == 'detail' || nview == 'detail-category' || nview == 'detail-tag' || nview == 'list-category' || nview == 'list-tag') {
            $('#globalConfig').show();
        }
    };


    $('#newsView').change(initNewsView);

    $("#newsListItems").chosen({ width: '100%', no_results_text: "{_('No results matched')}" });
    initNewsView();



</script>
