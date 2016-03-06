/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
$(Core.Block).bind('blockConfigLoaded', function (e, json) {

    function initElements() {
        $.each($('[data-rte]'), function () {
            var id = $(this).attr('id');
            CKEDITOR.replace(id, $(this).attr('data-rte'));
            CKEDITOR.instances[id].on('change', function() { CKEDITOR.instances[id].updateElement(); });
        });

        FileManager.initFilePathInput();
    }

    $('#dynamicTemplate').change(function () {
        $.ajax({
            url: '/load-dynamic-template-config',
            dataType: 'html',
            data: {template: $(this).val(), blockId: Core.getBaseWindow().Core.Block.dialogBlockId},
            type: 'post',
            async: false,
            success: function (data, textStatus, jqXHR) {
                $('#dynamicTemplateConfig').html(data);
                initElements();
            }
        });
    });

    $('#dynamicTemplate').change();
});