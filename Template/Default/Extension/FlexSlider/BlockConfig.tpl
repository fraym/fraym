
<h3>{_('FlexSlider Configuration', 'FRAYM_EXT_IMAGE_HEADLINE')}</h3>
<div class="form-group">
    <label for="numberOfSlides" class="control-label">{_('Number of slides')}</label>
    <input data-toggle="tooltip" data-placement="bottom" title="{_('Enter the number of slider you want to display.')}" type="number" class="form-control" id="numberOfSlides" value="1" name="sliderConfig[numberOfSlides]" min="1" placeholder="1">
</div>

<div class='row'>
    <div class='col-sm-3'>
        <div class="checkbox-inline">
            <label data-toggle="tooltip" data-placement="bottom" title="{_('Reverse the animation direction.')}" >
              <input type="checkbox" value="true" name="sliderConfig[reverse]"/> {_('Reverse')}
            </label>
        </div>
    </div>
    <div class='col-sm-3'>
        <div class="checkbox-inline">
            <label data-toggle="tooltip" data-placement="bottom" title="{_('Gives the slider a seamless infinite loop.')}" >
              <input type="checkbox" value="true" name="sliderConfig[animationLoop]" checked/> {_('Animation loop')}
            </label>
        </div>
    </div>
    <div class='col-sm-3'>
        <div class="checkbox-inline">
            <label data-toggle="tooltip" data-placement="bottom" title="{_('Animate the height of the slider smoothly for slides of varying height.')}">
              <input type="checkbox" value="true" name="sliderConfig[smoothHeight]" /> {_('Smooth height')}
            </label>
        </div>
    </div>
    <div class='col-sm-3'>
        <div class="checkbox-inline">
            <label data-toggle="tooltip" data-placement="bottom" title="{_('Setup a slideshow for the slider to animate automatically.')}" >
              <input type="checkbox" value="true" name="sliderConfig[slideshow]" checked/> {_('Slideshow')}
            </label>
        </div>
    </div>
</div>
<div class='row'>
    <div class='col-sm-3'>
        <div class="checkbox-inline">
            <label data-toggle="tooltip" data-placement="bottom" title="{_('Randomize slide order, on load.')}" >
              <input type="checkbox" value="true" name="sliderConfig[randomize]" /> {_('Randomize')}
            </label>
        </div>
    </div>
    <div class='col-sm-3'>
        <div class="checkbox-inline">
            <label data-toggle="tooltip" data-placement="bottom" title="{_('Pause the slideshow when interacting with control elements.')}" >
              <input type="checkbox" value="true" name="sliderConfig[pauseOnAction]" /> {_('Pause on action')}
            </label>
        </div>
    </div>
    <div class='col-sm-3'>
        <div class="checkbox-inline">
            <label data-toggle="tooltip" data-placement="bottom" title="{_('Pause the slideshow when hovering over slider, then resume when no longer hovering.')}" >
              <input type="checkbox" value="true" name="sliderConfig[pauseOnHover]" /> {_('Pause on hover')}
            </label>
        </div>
    </div>
    <div class='col-sm-3'>
        <div class="checkbox-inline">
            <label data-toggle="tooltip" data-placement="bottom" title="{_('Slider will use CSS3 transitions, if available.')}">
              <input type="checkbox" value="true" name="sliderConfig[useCSS]" checked/> {_('useCSS')}
            </label>
        </div>
    </div>
</div>



<div class='row'>
    <div class='col-sm-3'>
        <div class="checkbox-inline">
            <label data-toggle="tooltip" data-placement="bottom" title="{_('Create navigation for paging control of each slide.')}" >
              <input type="checkbox" value="true" name="sliderConfig[controlNav]" checked/> {_('Control nav.')}
            </label>
        </div>
    </div>
    <div class='col-sm-3'>
        <div class="checkbox-inline">
            <label data-toggle="tooltip" data-placement="bottom" title="{_('Create previous/next arrow navigation.')}" >
              <input type="checkbox" value="true" name="sliderConfig[directionNav]" checked/> {_('Direction nav.')}
            </label>
        </div>
    </div>
    <div class='col-sm-3'>
        <div class="checkbox-inline">
            <label data-toggle="tooltip" data-placement="bottom" title="{_('Create pause/play element to control slider slideshow.')}" >
              <input type="checkbox" value="true" name="sliderConfig[pausePlay]" /> {_('Pause play')}
            </label>
        </div>
    </div>
</div>


<div class="form-group">
  <label for="animation">{_('Animation')}</label>
  <select data-toggle="tooltip" data-placement="bottom" title="{_('Controls the animation type.')}" id="animation" name="sliderConfig[animation]" class="form-control">
    <option value="fade">{_('Fade')}</option>
    <option value="slide">{_('Slide')}</option>
  </select>
</div>
<div class="form-group">
  <label for="direction">{_('Direction')}</label>
  <select data-toggle="tooltip" data-placement="bottom" title="{_('Controls the animation direction.')}" id="direction" name="sliderConfig[direction]" class="form-control">
    <option value="horizontal">{_('Horizontal')}</option>
    <option value="vertical">{_('Vertical')}</option>
  </select>
</div>
<div class="form-group">
    <label for="startAt" class="control-label">{_('Start at')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('The starting slide for the slider, in array notation.')}" type="number" class="form-control" id="startAt" name="sliderConfig[startAt]" placeholder="0" value="0">
</div>
<div class="form-group">
    <label for="slideshowSpeed" class="control-label">{_('Slideshow speed')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('Set the speed of the slideshow cycling, in milliseconds.')}" type="text" class="form-control" id="slideshowSpeed" name="sliderConfig[slideshowSpeed]" placeholder="7000" value="7000">
</div>
<div class="form-group">
    <label for="animationSpeed" class="control-label">{_('Animation speed')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('Set the speed of animations, in milliseconds.')}" type="text" class="form-control" id="animationSpeed" name="sliderConfig[animationSpeed]" placeholder="600" value="600">
</div>
<div class="form-group">
    <label for="initDelay" class="control-label">{_('Init delay')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('Set an initialization delay, in milliseconds.')}" type="text" class="form-control" id="initDelay" name="sliderConfig[initDelay]" placeholder="0" value="0">
</div>
<div class="form-group">
    <label for="prevText" class="control-label">{_('Prev text')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('Set the text for the previous item.')}" type="text" class="form-control" id="prevText" name="sliderConfig[prevText]" placeholder="{_('previous')}" value="">
</div>
<div class="form-group">
    <label for="nextText" class="control-label">{_('Next text')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('Set the text for the next item.')}" type="text" class="form-control" id="nextText" name="sliderConfig[nextText]" placeholder="{_('next')}" value="">
</div>
<div class="form-group">
    <label for="playText" class="control-label">{_('Prev text')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('Set the text for the play item')}" type="text" class="form-control" id="playText" name="sliderConfig[playText]" placeholder="{_('play')}" value="">
</div>

<div class="form-group">
    <label for="minItems" class="control-label">{_('Min items')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('Minimum number of carousel items that should be visible.')}" type="number" class="form-control" id="minItems" name="sliderConfig[minItems]" placeholder="0" value="0">
</div>
<div class="form-group">
    <label for="maxItems" class="control-label">{_('Max items')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('Maximum number of carousel items that should be visible.')}" type="number" class="form-control" id="maxItems" name="sliderConfig[maxItems]" placeholder="0" value="0">
</div>
<div class="form-group">
    <label for="move" class="control-label">{_('Move')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('Number of carousel items that should move on animation.')}" type="number" class="form-control" id="move" name="sliderConfig[move]" placeholder="0" value="0">
</div>

<div class="form-group">
    <label for="itemWidth" class="control-label">{_('Item width')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('Box-model width of individual carousel items, including horizontal borders and padding.')}" type="text" class="form-control" id="itemWidth" name="sliderConfig[itemWidth]" placeholder="0" value="0">
</div>

<div class="form-group">
    <label for="itemMargin" class="control-label">{_('Item margin')}</label>
        <input data-toggle="tooltip" data-placement="bottom" title="{_('Margin between carousel items.')}" type="text" class="form-control" id="itemMargin" name="sliderConfig[itemMargin]" placeholder="0" value="0">
</div>

<script>
    $(Core.Block).bind('blockConfigLoaded', function(e, json){
        if(typeof json.xml != 'undefined') {
            $.each(json.xml.sliderConfig, function(k, v){
                var $item = $('[name="sliderConfig[' + k + ']"]');
                if($item.attr('type') == 'checkbox') {
                    if(v == 'true') {
                        $item.attr('checked', 'checked');
                    } else {
                        $item.removeAttr('checked');
                    }
                } else {
                    $item.val(v);
                }
            });
        }
     });

    $(Core.Block).bind('blockConfigSaved', function(e, json){
        Core.getBaseWindow().Core.reloadPage();
    });
</script>