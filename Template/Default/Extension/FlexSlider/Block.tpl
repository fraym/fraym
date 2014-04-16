{js('fraym/extension/flexslider/jquery.flexslider-min.js')}
{css('fraym/extension/flexslider/flexslider.css')}


<div class="flexslider">
  <ul class="slides">
      {foreach $views as $view}
          <li>
              <block type="content">
                  <view id="{$view}">

                  </view>
              </block>
          </li>
      {/foreach}
  </ul>
</div>
<script>
    $(function(){
        try {
            $('.flexslider').flexslider({
                {foreach $config as $prop => $conf}
                '{$prop}': {if $conf == 'true'}true{else}'{$conf}'{/if}{if isLast($config, $prop) === false},{/if}
                {/foreach}
            });
        } catch(e) {
        }
    });
</script>