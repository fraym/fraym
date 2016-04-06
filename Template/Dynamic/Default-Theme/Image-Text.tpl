
<block type="config">
    <template>
        <![CDATA[
        <div class="row">
            <div class="col-xs-12">
                <label>{_('Pre-Headline')}</label>
                <input type="text" class="form-control" name="config[preheadline]" value="{$config.preheadline}" />
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <label>{_('Headline')}</label>
                <input type="text" class="form-control" name="config[headline]" value="{$config.headline}" />
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <label>
                    {_('Text')}
                </label>
                <textarea name="config[rte]" class="form-control" data-rte='{ "toolbar":[{ "name":"document","groups":["mode","document","doctools"],"items":["Source","-"] },{ "name":"clipboard","groups":["clipboard","undo"],"items":["Cut","Copy","Paste","PasteText","PasteFromWord","-","Undo","Redo"] },{ "name":"editing","groups":["find","selection","spellchecker"],"items":["Find","Replace","-","SelectAll","-","Scayt"] },{ "name":"tools","items":["Maximize","ShowBlocks"] },"/",{ "name":"insert","items":["Image","Flash","Table","HorizontalRule","SpecialChar","Iframe"] },{ "name":"paragraph","groups":["list","indent","blocks","align","bidi"],"items":["NumberedList","BulletedList","-","Outdent","Indent","-","Blockquote","CreateDiv","-","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock","-","BidiLtr","BidiRtl"] },{ "name":"links","items":["Link","Unlink","Anchor"] },{ "name":"basicstyles","groups":["basicstyles","cleanup"],"items":["Bold","Italic","Underline","Strike","Subscript","Superscript","-","RemoveFormat"] },{ "name":"styles","items":["Styles","Format","Font","FontSize"] },{ "name":"colors","items":["TextColor","BGColor"] }] }'>{$config.rte}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <label>{_('Image')}</label>
                <input type="text" class="form-control" name="config[image]" value="{$config.image}" data-absolutepath="false" data-filepath="true" data-singlefileselect="1" data-filefilter="*.jpg,*.png" />
            </div>
        </div>
        ]]>
    </template>
</block>

<div id="tf-about">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <block type="image" width="555" method="resize" src="{$config.image}" class="img-responsive"></block>
            </div>
            <div class="col-md-6">
                <div class="about-text">
                    {if $config.headline}
                        <div class="section-title">
                            {if $config.preheadline}<h4>{$config.preheadline}</h4>{/if}
                            <h2>{$config.headline}</h2>
                            <hr>
                            <div class="clearfix"></div>
                        </div>
                    {/if}

                    {{$config.rte}}
                </div>
            </div>
        </div>
    </div>
</div>