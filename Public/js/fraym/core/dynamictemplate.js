/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
$(Core.Block).bind('blockConfigLoaded', function (e, json) {
    $('#dynamicTemplate').change(function () {
        if($(this).val() != '') {
            $.ajax({
                url: '/fraym/load-dynamic-template-config',
                dataType: 'html',
                data: {template: $(this).val(), blockId: Core.getBaseWindow().Core.Block.dialogBlockId},
                type: 'post',
                async: false,
                success: function (data, textStatus, jqXHR) {
                    $('#dynamicTemplateConfig').html(data).parents('.panel:first').show();
                    Core.Block.initElements();
                }
            });
        } else {
            $('#dynamicTemplateConfig').html('').parents('.panel:first').hide();
        }
    });

    $('#dynamicTemplate').change();
});