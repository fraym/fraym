<label class="col-lg-2 control-label" for="view">{_('View', 'FRAYM_VIEW')}</label>
<div class="col-lg-10">
    <select id="view" name="view">
        <option value="login-logout"{if $blockConfig && $blockConfig.view == 'login-logout'} selected="selected"{/if}>{_('Login / Logout', 'FRAYM_LOGIN_LOGOUT')}</option>
    </select>
</div>
</div>