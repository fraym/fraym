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
                    {_('Text')}
                </label>
                <textarea name="config[text]" class="form-control">{$config.text}</textarea>
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

<div id="tf-home" class="text-center"{if $config.image} style="background-image:url('<block type="image" width="1200" srcOnly="true" method="resize" src="{$config.image}"></block>')" {/if}>
    <div class="overlay">
        <div class="content">
            <h1>{$config.headline}</h1>
            <p class="lead">{$config.text}</p>
            <a href="#tf-about" class="fa fa-angle-down page-scroll"></a>
        </div>
    </div>
</div>

