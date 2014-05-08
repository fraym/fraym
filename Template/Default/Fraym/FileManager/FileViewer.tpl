{css('fraym/jquery.Jcrop.min.css')}
{js('fraym/libs/jquery.Jcrop.min.js', 'file-viewer')}

<block type="js" sequence="outputFilter" consolidate="false" group="file-viewer"></block>

<div class="file-viewer">
    {if $inlineImage}
        <div class="file-viewer-controls">
            <div class="btn-toolbar" role="toolbar">
              <div class="btn-group">
                 <button title="{_('Save cropped image')}" id="save-jcrop" type="button" class="btn btn-success"><i class="fa fa-check"></i></button>
                 <button title="{_('Cancel image cropping')}" id="cancel-jcrop" type="button" class="btn btn-danger"><i class="fa fa-times"></i></button>
              </div>
              <div class="btn-group">
                  <button title="{_('Crop image')}" id="activate-jcrop" type="button" class="btn btn-default"><i class="fa fa-crop"></i></button>
              </div>
            </div>
        </div>
        <form id="edit-form" action="" method="post">
            <input type="hidden" name="cmd" value="crop" />
            <img id="image-view" src="{$inlineImage}">
        </form>
        <script>
            var jcrop_api;
            var cords = { x: 0, y: 0, w: 0, h: 0 };

            var activateJcrop = function() {
                $('#activate-jcrop').hide();
                $('#save-jcrop, #cancel-jcrop').show();

                jcrop_api = $.Jcrop('#image-view', {
            		onSelect: function (c)
                    {
                        cords = c;
                    }
            	});
            };

            var cancelCrop = function() {
                jcrop_api.destroy();
                $('#activate-jcrop').show();
                $('#save-jcrop, #cancel-jcrop').hide();
            };

            var saveCrop = function() {
                $('#edit-form').find('.crop-opt').remove();
                $.each(cords, function(k, v){
                    $('#edit-form').append($('<input class="crop-opt" type="hidden"/>').attr({ 'name': 'cropOpt[' + k + ']', 'value': v }));
                });
                $('#edit-form').submit();
            };

            jQuery(function(){
                $('#activate-jcrop').click(activateJcrop);
                $('#cancel-jcrop').click(cancelCrop);
                $('#save-jcrop').click(saveCrop);
            });
        </script>
    {else}
        <form action="" method="post">
            <input type="hidden" name="storage" value="{$storage}" />
            <input type="hidden" name="file" value="{$file}" />
            <input type="hidden" name="path" value="{$path}" />
            <textarea id="fileViewer" name="fileContent">{$content}</textarea>
    
    
            <div class="buttons">
                <button type="submit" class="btn">{_('Save', 'FRAYM_SAVE')}</button>
            </div>
        </form>

        <script type="text/javascript">
            var $fileViewer = $("#fileViewer");
            var fileViewerCodeMirror = CodeMirror.fromTextArea($fileViewer.get(0), {
                lineNumbers: true,
                lineWrapping: true,
                autoCloseBrackets: true,
                autoCloseTags: true,
                mode: "text/html",
                styleActiveLine: true,
                tabMode: "indent",
                matchTags: { bothTags: true },
                extraKeys: { "Ctrl-J" : "toMatchingTag" }
            });

            fileViewerCodeMirror.on("change", function(cm, change) {
                $fileViewer.val(cm.getValue());
            });

            $(window).resize(function(){
                fileViewerCodeMirror.setSize($('body').width(), $('body').height()-80);
            }).resize();

        </script>
    {/if}
</div>
