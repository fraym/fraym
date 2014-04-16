<div class="container">
    <div class="row">
         <div class="col-md-offset-4 col-md-4">
            {if count((array)$errorFields)}<div class="alert alert-danger">{_('Wrong E-Mail or password', 'FRAYM_LOGIN_ERROR')}</div>{/if}
            <form class="form-horizontal" role="form" action="" method="post" autocomplete="off">
              <div class="form-group">
                <label for="inputEmail">{_('E-Mail', 'FRAYM_EMAIL')}</label>
                <input type="text" id="inputEmail" placeholder="{_('E-Mail', 'FRAYM_EMAIL')}" name="login_name" value="{$loginName}" class="form-control">
              </div>
              <div class="form-group">
                <label for="inputPassword1">{_('Password', 'FRAYM_PASSWORD')}</label>
                <input type="password" id="inputPassword" name="password" placeholder="{_('Password', 'FRAYM_PASSWORD')}" class="form-control">
              </div>
              <div class="form-group">
                  <div class="checkbox">
                    <label>
                        <input type="checkbox" name="stay_signed_in" value="1"> {_('Stay signed in', 'FRAYM_STAY_SIGNED_IN')}
                    </label>
                  </div>
              </div>
              <div class="form-group">
                  <button type="submit" id="submit" class="btn btn-default button-loading" data-loading-text="{_('Loading', 'FRAYM_LOADING')}...">{_('Sign in', 'FRAYM_SINGIN')}</button>
              </div>
            </form>
        </div>
    </div>
</div>