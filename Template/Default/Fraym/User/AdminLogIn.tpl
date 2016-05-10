<div id="wrap">
    <div class="row">
        <div id="content" class="col-md-12 full-page login">
            <div class="welcome">
                <a href="/"><img alt="Fraym" src="/images/fraym/logo-opacity.png" class="logo img-responsive"></a>

                <form action="" id="form-signin" class="form-signin" method="post">

                    {if count((array)$errorFields)}
                        <div class="alert alert-danger">{_('Wrong E-Mail or password', 'FRAYM_LOGIN_ERROR')}</div>
                    {/if}
                    <section>
                        <div class="input-group">
                            <input type="text" class="form-control" name="login_name"
                                   placeholder="{_('E-Mail', 'FRAYM_EMAIL')}">

                            <div class="input-group-addon"><i class="fa fa-user"></i></div>
                        </div>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password"
                                   placeholder="{_('Password', 'FRAYM_PASSWORD')}">

                            <div class="input-group-addon"><i class="fa fa-key"></i></div>
                        </div>
                    </section>
                    <section class="controls">
                        <div class="checkbox check-transparent">
                            <input type="checkbox" value="1" id="stay_signed_in" name="stay_signed_in">
                            <label for="stay_signed_in">{_('Stay signed in', 'FRAYM_STAY_SIGNED_IN')}</label>
                        </div>
                    </section>
                    <section class="new-acc">
                        <button type="submit" class="btn btn-logingray">{_('Login', 'FRAYM_LOGIN')}</button>
                    </section>
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