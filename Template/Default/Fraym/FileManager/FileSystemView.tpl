{js('fraym/libs/resumable.js', 'iframe-extension')}
{js('fraym/libs/resumable-uploader.js', 'iframe-extension')}

<div class="file-toolbar">
    <div class="row-fluid">
        <div class="span6">
            <div class="selection-info">

            </div>
        </div>
        <div class="span6 upload">
            <div class="btn-toolbar" role="toolbar">
              <div class="btn-group">
                 <button title="{_('Refresh current dir', 'FRAYM_REFRESH_DIR')}" type="button" class="btn btn-default filemanager-refresh"><i class="fa fa-refresh"></i></button>
                 <div title="{_('Download file', 'FRAYM_DOWNLOAD_FILE')}" type="button" class="btn btn-default filemanager-file-download"><i class="fa fa-download"></i></div>
                 <div title="{_('Upload file', 'FRAYM_UPLOAD_FILE')}" type="button" class="btn btn-default resumable-browse"><i class="fa fa-upload"></i></div>
              </div>
            </div>
        </div>
    </div>
</div>

<div id="filemanager">
    <div id="tree"></div>
    <div id="fileView" class="resumable-drop" ondragenter="jQuery(this).addClass('resumable-dragover');" ondragend="jQuery(this).removeClass('resumable-dragover');" ondrop="jQuery(this).removeClass('resumable-dragover');">
        <div id="selection"></div>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        FileManager.dynatreeJson = {{$dynatreeJson}};
        FileManager.fileFilter = '{$fileFilter}';
        FileManager.rteSelectOptionCallback = {if $rteCallback !== false}{$rteCallback}{else}false{/if};
        FileManager.singleFileSelect = {if $singleFileSelect}true{else}false{/if};
        FileManager.currentFile = '{$currentFile}';
        FileManager.init();
    });
</script>