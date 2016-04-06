
<block type="config">
    <template>
        <![CDATA[
        <div class="row">
            <div class="col-xs-12">
                <label>{_('Headline')}</label>
                <input type="text" class="form-control" name="config[headline]" value="{$config.headline}" />
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <label>
                    {_('Small text')}
                </label>
                <textarea name="config[smallText]" class="form-control">{$config.smallText}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <label>
                    {_('Icon 1 Text')}
                </label>
                <textarea name="config[icon1]" class="form-control" data-rte='{ "toolbar":[{ "name":"document","groups":["mode","document","doctools"],"items":["Source","-"] },{ "name":"clipboard","groups":["clipboard","undo"],"items":["Cut","Copy","Paste","PasteText","PasteFromWord","-","Undo","Redo"] },{ "name":"editing","groups":["find","selection","spellchecker"],"items":["Find","Replace","-","SelectAll","-","Scayt"] },{ "name":"tools","items":["Maximize","ShowBlocks"] },"/",{ "name":"insert","items":["Image","Flash","Table","HorizontalRule","SpecialChar","Iframe"] },{ "name":"paragraph","groups":["list","indent","blocks","align","bidi"],"items":["NumberedList","BulletedList","-","Outdent","Indent","-","Blockquote","CreateDiv","-","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock","-","BidiLtr","BidiRtl"] },{ "name":"links","items":["Link","Unlink","Anchor"] },{ "name":"basicstyles","groups":["basicstyles","cleanup"],"items":["Bold","Italic","Underline","Strike","Subscript","Superscript","-","RemoveFormat"] },{ "name":"styles","items":["Styles","Format","Font","FontSize"] },{ "name":"colors","items":["TextColor","BGColor"] }] }'>{$config.icon1}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <label>
                    {_('Icon 2 Text')}
                </label>
                <textarea name="config[icon2]" class="form-control" data-rte='{ "toolbar":[{ "name":"document","groups":["mode","document","doctools"],"items":["Source","-"] },{ "name":"clipboard","groups":["clipboard","undo"],"items":["Cut","Copy","Paste","PasteText","PasteFromWord","-","Undo","Redo"] },{ "name":"editing","groups":["find","selection","spellchecker"],"items":["Find","Replace","-","SelectAll","-","Scayt"] },{ "name":"tools","items":["Maximize","ShowBlocks"] },"/",{ "name":"insert","items":["Image","Flash","Table","HorizontalRule","SpecialChar","Iframe"] },{ "name":"paragraph","groups":["list","indent","blocks","align","bidi"],"items":["NumberedList","BulletedList","-","Outdent","Indent","-","Blockquote","CreateDiv","-","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock","-","BidiLtr","BidiRtl"] },{ "name":"links","items":["Link","Unlink","Anchor"] },{ "name":"basicstyles","groups":["basicstyles","cleanup"],"items":["Bold","Italic","Underline","Strike","Subscript","Superscript","-","RemoveFormat"] },{ "name":"styles","items":["Styles","Format","Font","FontSize"] },{ "name":"colors","items":["TextColor","BGColor"] }] }'>{$config.icon2}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <label>
                    {_('Icon 3 Text')}
                </label>
                <textarea name="config[icon3]" class="form-control" data-rte='{ "toolbar":[{ "name":"document","groups":["mode","document","doctools"],"items":["Source","-"] },{ "name":"clipboard","groups":["clipboard","undo"],"items":["Cut","Copy","Paste","PasteText","PasteFromWord","-","Undo","Redo"] },{ "name":"editing","groups":["find","selection","spellchecker"],"items":["Find","Replace","-","SelectAll","-","Scayt"] },{ "name":"tools","items":["Maximize","ShowBlocks"] },"/",{ "name":"insert","items":["Image","Flash","Table","HorizontalRule","SpecialChar","Iframe"] },{ "name":"paragraph","groups":["list","indent","blocks","align","bidi"],"items":["NumberedList","BulletedList","-","Outdent","Indent","-","Blockquote","CreateDiv","-","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock","-","BidiLtr","BidiRtl"] },{ "name":"links","items":["Link","Unlink","Anchor"] },{ "name":"basicstyles","groups":["basicstyles","cleanup"],"items":["Bold","Italic","Underline","Strike","Subscript","Superscript","-","RemoveFormat"] },{ "name":"styles","items":["Styles","Format","Font","FontSize"] },{ "name":"colors","items":["TextColor","BGColor"] }] }'>{$config.icon3}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <label>
                    {_('Icon 4 Text')}
                </label>
                <textarea name="config[icon4]" class="form-control" data-rte='{ "toolbar":[{ "name":"document","groups":["mode","document","doctools"],"items":["Source","-"] },{ "name":"clipboard","groups":["clipboard","undo"],"items":["Cut","Copy","Paste","PasteText","PasteFromWord","-","Undo","Redo"] },{ "name":"editing","groups":["find","selection","spellchecker"],"items":["Find","Replace","-","SelectAll","-","Scayt"] },{ "name":"tools","items":["Maximize","ShowBlocks"] },"/",{ "name":"insert","items":["Image","Flash","Table","HorizontalRule","SpecialChar","Iframe"] },{ "name":"paragraph","groups":["list","indent","blocks","align","bidi"],"items":["NumberedList","BulletedList","-","Outdent","Indent","-","Blockquote","CreateDiv","-","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock","-","BidiLtr","BidiRtl"] },{ "name":"links","items":["Link","Unlink","Anchor"] },{ "name":"basicstyles","groups":["basicstyles","cleanup"],"items":["Bold","Italic","Underline","Strike","Subscript","Superscript","-","RemoveFormat"] },{ "name":"styles","items":["Styles","Format","Font","FontSize"] },{ "name":"colors","items":["TextColor","BGColor"] }] }'>{$config.icon4}</textarea>
            </div>
        </div>
        ]]>
    </template>
</block>

<div id="tf-services" class="text-center">
    <div class="container">
        <div class="section-title center">
            <h2>{$config.headline}</h2>
            <div class="line">
                <hr>
            </div>
            <div class="clearfix"></div>
            <small><em>{$config.smallText}</em></small>
        </div>
        <div class="space"></div>
        <div class="row">
            <div class="col-md-3 col-sm-6 service">
                <i class="fa fa-desktop"></i>
                {{$config.icon1}}
            </div>

            <div class="col-md-3 col-sm-6 service">
                <i class="fa fa-mobile"></i>
                {{$config.icon2}}
            </div>

            <div class="col-md-3 col-sm-6 service">
                <i class="fa fa-camera"></i>
                {{$config.icon3}}
            </div>

            <div class="col-md-3 col-sm-6 service">
                <i class="fa fa-bullhorn"></i>
                {{$config.icon4}}
            </div>
        </div>
    </div>
</div>