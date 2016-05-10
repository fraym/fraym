/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
$(Core.Block).bind('blockConfigLoaded', function(e, json){
     $('#htmlblock-tabs').tabs();
     CKEDITOR.plugins.registered['save'] =
     {
      init : function( editor )
      {
         var command = editor.addCommand( 'save',
            {
               modes : { wysiwyg:1, source:1 },
               exec : function( editor ) {
                  $('form').submit();
               }
            }
         );
         editor.ui.addButton( 'Save', { command : 'save' });
      }
     }
 });

$(Core.Block).bind('saveBlockConfig', function(e, json){
    if (typeof CKEDITOR != 'undefined') {
        for (instance in CKEDITOR.instances)
            CKEDITOR.instances[instance].updateElement();
    }
});