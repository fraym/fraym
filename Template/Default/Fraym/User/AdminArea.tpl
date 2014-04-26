<div id="wrap">
    <div class="row">
        <div id="content" class="col-md-12 full-page admin-area">
            <div class="welcome">
                <a href="/"><img alt="Fraym" src="/images/fraym/logo-opacity.png" class="img-responsive"></a>

                <form class="form-horizontal" name="logout" action="" method="post">
                    <input type="hidden" value="1" name="logout">
                    <button type="submit" id="submit" class="btn btn-default button-loading" data-loading-text="{_('Loading', 'FRAYM_LOADING')}...">{_('Logout', 'FRAYM_LOGOUT')}</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
       $('.welcome').addClass('animated bounceIn');
     })
</script>