<block type="config">
    <template>
        <![CDATA[
        <div class="row">
            <div class="col-xs-12">
                <label>Headline</label>
                <input type="text" class="form-control" name="config[headline]" value="{$config.headline}" />
            </div>
        </div>
        <div class="row">
            <div class="col-xs-4">
                <label>Desktop: Items per slide</label>
                <input type="number" class="form-control" name="config[desktopItems]" min="1" value="{$config.desktopItems}" />
            </div>
            <div class="col-xs-4">
                <label>Tablet: Items per slide</label>
                <input type="number" class="form-control" name="config[tabletItems]" min="1" value="{$config.tabletItems}" />
            </div>
            <div class="col-xs-4">
                <label>Mobile: Items per slide</label>
                <input type="number" class="form-control" name="config[mobileItems]" min="1" value="{$config.mobileItems}" />
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <label>Background image</label>
                <input type="text" class="form-control" name="config[backgroundImage]" value="{$config.backgroundImage}" data-absolutepath="false" data-filepath="true" data-singlefileselect="1" data-filefilter="*.jpg,*.png" />
            </div>
        </div>
        {function createSlideItem($k, $item = null)}
            <div class="slide-item row" data-repeat="slide-item">
                <div class="col-xs-12">
                    <div class="pull-right">
                        <i data-repeat-item-remove class="fa fa-times"></i>
                    </div>
                    <h4>Item <span data-repeat-item-pos>{$k}</span></h4>
                    <div class="row">
                        <div class="col-xs-12">
                            <label>
                                Text
                            </label>
                            <textarea name="config[items][{$k}][rte]" class="form-control" data-rte='{ "toolbar":[{ "name":"document","groups":["mode","document","doctools"],"items":["Source","-"] },{ "name":"clipboard","groups":["clipboard","undo"],"items":["Cut","Copy","Paste","PasteText","PasteFromWord","-","Undo","Redo"] },{ "name":"editing","groups":["find","selection","spellchecker"],"items":["Find","Replace","-","SelectAll","-","Scayt"] },{ "name":"tools","items":["Maximize","ShowBlocks"] },"/",{ "name":"insert","items":["Image","Flash","Table","HorizontalRule","SpecialChar","Iframe"] },{ "name":"paragraph","groups":["list","indent","blocks","align","bidi"],"items":["NumberedList","BulletedList","-","Outdent","Indent","-","Blockquote","CreateDiv","-","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock","-","BidiLtr","BidiRtl"] },{ "name":"links","items":["Link","Unlink","Anchor"] },{ "name":"basicstyles","groups":["basicstyles","cleanup"],"items":["Bold","Italic","Underline","Strike","Subscript","Superscript","-","RemoveFormat"] },{ "name":"styles","items":["Styles","Format","Font","FontSize"] },{ "name":"colors","items":["TextColor","BGColor"] }] }'>{$item.rte}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <label>Image</label>
                            <input type="text" class="form-control" name="config[items][{$k}][image]" value="{$item.image}" data-absolutepath="false" data-filepath="true" data-singlefileselect="1" data-filefilter="*.jpg,*.png" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="config[items][{$k}][roundImage]" value="1"{if $item.roundImage} checked{/if}/>
                                    Round image
                                </label>
                            </div>
                        </div>
                    </div>
                    <hr/>
                </div>
            </div>
        {/function}

        {if count((array)$config.items)}
            {foreach $config.items as $k => $item}
                {createSlideItem($k, $item)}
            {/foreach}
        {else}
            {createSlideItem(1, null)}
        {/if}

        <div class="pull-right clearfix">
            <button id="add-slide" class="btn btn-default" data-repeat-add="slide-item">{_('Add slide item')}</button>
        </div>

        ]]>
    </template>
</block>

<div class="text-center slider-wrapper"{if $config.backgroundImage} style="background-image:url('<block type="image" width="1200" srcOnly="true" method="resize" src="{$config.backgroundImage}"></block>')" {/if}>
    <div class="overlay">
        <div class="container">
            <div class="section-title center">
                <h2>{$config.headline}</h2>
                <div class="line">
                    <hr>
                </div>
            </div>
            {@$id = uniqid()}
            <div id="slider-{$id}" class="owl-carousel owl-theme">
                {foreach $config.items as $k => $item}
                    <div class="item">
                        <div class="thumbnail">
                            <block type="image" autosize="1" src="{$item.image}"{if $item.roundImage} class="img-circle team-img"{/if}></block>
                            <div class="caption">
                                {{$item.rte}}
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>
{if $refreshBlock}
<!-- Init the slider js after save -->
<script>
    $("#slider-{$id}").owlCarousel({
        navigation : false,
        autoHeight : true,
        slideSpeed : 300,
        paginationSpeed : 400,
        itemsCustom : [
            [0, {$config.mobileItems}],
            [450, {$config.mobileItems}],
            [600, {$config.mobileItems}],
            [700, {$config.tabletItems}],
            [1000, {$config.tabletItems}],
            [1200, {$config.desktopItems}],
            [1400, {$config.desktopItems}],
            [1600, {$config.desktopItems}]
        ]
    });
</script>
{/if}
<block type="javascript">
    $("#slider-{$id}").owlCarousel({
        navigation : false,
        autoHeight : true,
        slideSpeed : 300,
        paginationSpeed : 400,
        itemsCustom : [
            [0, {$config.mobileItems}],
            [450, {$config.mobileItems}],
            [600, {$config.mobileItems}],
            [700, {$config.tabletItems}],
            [1000, {$config.tabletItems}],
            [1200, {$config.desktopItems}],
            [1400, {$config.desktopItems}],
            [1600, {$config.desktopItems}]
        ]
    });
</block>