<h3>{_('Add image', 'FRAYM_EXT_IMAGE_HEADLINE')}</h3>
<p>{_('Select an image that you want to display on your website. Leave width and height empty if you wish to use image auto sizing.', 'FRAYM_EXT_IMAGE_TEXT')}</p>
<div class="form-group">
  <label>{_('Image filename', 'FRAYM_EXT_IMAGE_FILENAME')}</label>
  <input type="text" class="form-control" name="image[file]" data-filepath="true" data-filefilter="*.png,*.jpg,*.jpeg,*.svg,*.gif" data-singlefileselect="1" value="{$blockConfig->image_file}">
</div>
<div class="form-group">
  <label>{_('Image width', 'FRAYM_EXT_IMAGE_FILENAME_WIDTH')}</label>
  <input type="text" class="form-control" name="image[width]" value="{$blockConfig.image_width}">
</div>
<div class="form-group">
  <label>{_('Image height', 'FRAYM_EXT_IMAGE_FILENAME_HEIGHT')}</label>
  <input type="text" class="form-control" name="image[height]" value="{$blockConfig.image_height}">
</div>
<div class="form-group">
  <label>{_('Image alt attribute', 'FRAYM_EXT_IMAGE_ALT_ATTR')}</label>
  <input type="text" class="form-control" name="image[alt]" value="{$blockConfig.image_alt}">
</div>
<div class="form-group">
  <label>{_('Image CSS class', 'FRAYM_EXT_IMAGE_CSS')}</label>
  <input type="text" class="form-control" name="image[css]" value="{$blockConfig.image_css}">
</div>
<div class="form-group">
  <label>{_('Image link', 'FRAYM_EXT_IMAGE_LINK')}</label>
  <input type="text" class="form-control" name="image[link]" value="{$blockConfig.image_link}" data-value="{$imageLink}" data-menuselection>
</div>

<div class="form-group">
  <label>{_('Image link target', 'FRAYM_EXT_IMAGE_LINK_TARGET')}</label>
  <select class="form-control" name="image[linkTarget]">
      <option value="_self"{if $blockConfig.image_linkTarget == '_self'} selected="selected"{/if}>{_('Same window', 'FRAYM_EXT_IMAGE_HREF_SELF')}</option>
      <option value="_blank"{if $blockConfig.image_linkTarget == '_blank'} selected="selected"{/if}>{_('New window', 'FRAYM_EXT_IMAGE_HREF_NEW')}</option>
  </select>
</div>
<div class="form-group">
    <div class="checkbox-inline">
        <label data-toggle="tooltip" data-placement="right" title="{_('Enable this if you do not want a height and width attribute on the img tag.')}">
            <input type="checkbox" name="image[auto_size]" value="1"{if $blockConfig && $blockConfig.image_auto_size} checked{/if} />
            {_('Image autosize', 'FRAYM_EXT_IMAGE_FILENAME_AUTOSIZE')}
        </label>
    </div>
</div>
<h3>{_('Advanced - Placeholder', 'FRAYM_EXT_IMAGE_PH_HEADLINE')}</h3>
<p>{_('To create a placeholder leave image filename empty.')}</p>
<div class="form-group">
  <label>{_('Placeholder text', 'FRAYM_EXT_IMAGE_PH_TEXT')}</label>
  <input type="text" class="form-control" name="image[phtext]" value="{$blockConfig.image_phtext}">
</div>
<div class="form-group">
  <label>{_('Placeholder width', 'FRAYM_EXT_IMAGE_PH_WIDTH')}</label>
  <input type="text" class="form-control" name="image[phwidth]" value="{$blockConfig.image_phwidth}">
</div>
<div class="form-group">
  <label>{_('Placeholder height', 'FRAYM_EXT_IMAGE_PH_HEIGHT')}</label>
  <input type="text" class="form-control" name="image[phheight]" value="{$blockConfig.image_phheight}">
</div>
<div class="form-group">
  <label>{_('Placeholder text color', 'FRAYM_EXT_IMAGE_PH_COLOR')}</label>
  <input type="text" class="form-control" name="image[phcolor]" value="{$blockConfig.image_phcolor}">
</div>
<div class="form-group">
  <label>{_('Placeholder background color', 'FRAYM_EXT_IMAGE_phbgcolor')}</label>
  <input type="text" class="form-control" name="image[phbgcolor]" value="{$blockConfig.image_phbgcolor}">
</div>
<div class="form-group">
  <label>{_('Placeholder font file', 'FRAYM_EXT_IMAGE_PH_FONT')}</label>
  <input type="text" data-filepath="true" data-filefilter="*.ttf" data-singlefileselect="1" class="form-control" name="image[phfont]" value="{$blockConfig.image_phfont}">
</div>
<div class="form-group">
  <label>{_('Placeholder font size', 'FRAYM_EXT_IMAGE_PH_FONT_SIZE')}</label>
  <input type="text" class="form-control" name="image[phfontsize]" value="{$blockConfig.image_phfontsize}">
</div>