

<li class="languages drop"><a href="#"><span class="icon-globe icon-white"></span> Sprache / Language</a>
    <div class="pPanel">
        <ul class="inner">
            {foreach $languageMenu as $language}
                <li{if $language.active} class="active"{/if}><a href="{$language.url}">{$language.name} <span class="icon-ok"></span></a></li>
            {/foreach}
        </ul>
    </div>
</li>
