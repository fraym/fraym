<div id="wrap">
    <div class="row">
        <div id="content" class="col-md-12 full-page admin-area">
            <div class="welcome">
                <a href="/"><img alt="Fraym" src="/images/fraym/logo-opacity.png" class="logo img-responsive"></a>

                <form class="form-horizontal" name="logout" action="" method="post">
                    <input type="hidden" value="1" name="logout">
                    <button type="submit" id="submit" class="btn btn-default button-loading" data-loading-text="{_('Loading', 'FRAYM_LOADING')}...">{_('Logout', 'FRAYM_LOGOUT')}</button>
                </form>

                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">{_('Administrator options', 'FRAYM_ADMIN_OPTIONS')}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="btn-group" role="group">
                            <button id="clearcache" type="button" class="btn btn-danger" data-placement="top" data-toggle="tooltip" title="{_('Clears cached pages, data and APC/APCu/OPcache.', 'FRAYM_CLEAR_CACHE_INFO')}">{_('Clear cache', 'FRAYM_CLEAR_CACHE')}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
       $('.welcome').addClass('animated bounceIn');
     })
</script>