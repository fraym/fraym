<!DOCTYPE html>
<html>
<head>
    <title>Fraym installation</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    {css('fraym/bootstrap.min.css')}
    {css('fraym/main-theme.css')}
    {css('install/install.css')}
    {js('fraym/libs/jquery.min.js', 'default', 'jquery')}
    {js('fraym/libs/jquery-ui.min.js')}
    {js('fraym/libs/datetimepicker.js')}
    {js('fraym/libs/jquery.ui.nestedSortable.js')}
    {js('fraym/libs/jquery.json-2.2.min.js')}
    {js('fraym/libs/bootstrap/button.js')}
    {js('fraym/main.js')}
    {js('fraym/core/block.js')}
    {js('fraym/core/menu.js')}
    {js('fraym/core/admin.js')}
    {js('fraym/core/install.js')}
    {js('fraym/selector_config.js')}

    <block type="css" sequence="outputFilter" consolidate="false"></block>
    <block type="js" sequence="outputFilter" consolidate="false"></block>

</head>
<body id="install">
    <div id="wrapper">
        {if $done}
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-success">{_('Installation complete!', 'FRAYM_INSTALL_COMPLETE')}<br/><a href="http://{$post.site.url}">{_('Click here to access your website.', 'FRAYM_INSTALL_CLICK_TO_WEBSITE')}</a></div>
                </div>
            </div>
        {else}
          <form action="" method="post" enctype="multipart/form-data" autocomplete="off">
              <fieldset>
                  <legend>Install</legend>

                  <div class="form-group">
                      <div class="text-center">
                          <img src="/images/fraym/logo-opacity.png" class="img-responsive" />
                      </div>
                      <strong>Fraym version:</strong> {\Fraym\Core::VERSION}<br/>
                      <strong>PHP version:</strong> {phpversion()}<br/>
                      <strong>Mod ReWrite enabled:</strong> {if function_exists('apache_get_modules')}{if in_array('mod_rewrite', apache_get_modules())}Yes{else}No{/if}{else}Can't detect{/if}<br/>
                      <strong>ZipArchive enabled:</strong> {if class_exists('ZipArchive')}Yes{else}'No{/if}
                  </div>

                  {if $error}
                      <div class="form-group">
                          <div class="alert alert-danger">{$error}</div>
                      </div>
                  {/if}

                  <div class="form-group">
                      <label>Database hostname:</label>
                      <input class="form-control"name="database[host]" type="text" placeholder="localhost" value="{if isset($post.database) && $post.database.host}{$post.database.host}{else}localhost{/if}" required>
                  </div>

                  <div class="form-group">
                      <label>Database port:</label>
                      <input class="form-control" name="database[port]" type="number" placeholder="3306" value="{if isset($post.database) && $post.database.port}{$post.database.port}{else}3306{/if}" maxlength="5" min="0" max="65535" required>
                  </div>

                  <div class="form-group">
                  <label>Database name:</label>
                  <input class="form-control" name="database[name]" value="{if isset($post.database) && $post.database.name}{$post.database.name}{else}fraym{/if}" type="text" placeholder="fraym" required>
                  </div>

                  <div class="form-group">
                  <label>Database user:</label>
                  <input class="form-control" name="database[user]" value="{if isset($post.database) && $post.database.user}{$post.database.user}{else}root{/if}" type="text" placeholder="root" required>
                  </div>

                  <div class="form-group">
                  <label>Database password:</label>
                  <input class="form-control" name="database[password]" value="{if isset($post.database) && $post.database.password}{$post.database.password}{else}root{/if}" type="password" placeholder="root">
                  </div>

                  <div class="form-group">
                  <label>Database type:</label>
                  <input class="form-control" name="database[type]" value="{if isset($post.database) && $post.database.type}{$post.database.type}{else}pdo_mysql{/if}" type="text" placeholder="pdo_mysql" >
                  </div>

                  <div class="form-group">
                  <label>Database table prefix:</label>
                  <input class="form-control" name="database[prefix]" value="{if isset($post.database) && $post.database.prefix}{$post.database.prefix}{else}fraym_{/if}" type="text" placeholder="fraym_">
                  </div>

                  <div class="form-group">
                  <label>Website name:</label>
                  <input class="form-control" name="site[name]" type="text" value="{if isset($post.site) && $post.site.name}{$post.site.name}{else}My website{/if}" placeholder="My website" required>
                  </div>

                  <div class="form-group">
                  <label>Hostname or IP address for your website:</label>
                  <input class="form-control" name="site[url]" type="text" value="{if isset($post.site) && $post.site.url}{$post.site.url}{else}{$_SERVER['HTTP_HOST']}{/if}" placeholder="{$_SERVER['HTTP_HOST']}" required>
                  </div>

                  <div class="form-group">
                  <label>E-mail address:</label>
                  <input class="form-control" name="user[email]" type="email" value="{if isset($post.user) && $post.user.email}{$post.user.email}{else}admin@yourwebsite.com{/if}" placeholder="admin@yourhostname.com" required>
                  </div>

                  <div class="form-group">
                  <label>Username:</label>
                  <input class="form-control" name="user[username]" type="text" value="{if isset($post.user) && $post.user.username}{$post.user.username}{else}fraym{/if}" required>
                  </div>

                  <div class="form-group">
                  <label>Password:</label>
                  <input class="form-control" name="user[password]" type="password" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$" required>
                  <span class="help-block">Password must contains uppercase and lowercase letters, a number and a special char like (!$%=..) and min 8 chars.</span>
                  </div>

                  <div class="form-group">
                  <label>Repeat password:</label>
                  <input class="form-control" name="user[password_repeat]" type="password" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$" required>
                  </div>

                  <div class="form-group">
                  <label>Environment:</label>
                  <select name="environment" class="form-control" required>
                    <option value="development"{if isset($post.environment) && $post.environment == "development"} selected{/if}>Development</option>
                    <option value="testing"{if isset($post.environment) && $post.environment == "testing"} selected{/if}>Testing</option>
                    <option value="staging"{if isset($post.environment) && $post.environment == "staging"} selected{/if}>Staging</option>
                    <option value="production"{if isset($post.environment) && $post.environment == "production"} selected{/if}>Production</option>
                  </select>
                  </div>

                  <div class="form-group">
                  <label>Timezone:</label>
                      <select name="timezone" class="form-control" required>
                          {foreach $timezones as $timezone}
                                <option value="{$timezone}"{if isset($post.timezone) && $post.timezone == $timezone} selected{/if}>{$timezone}</option>
                          {/foreach}
                      </select>
                  </div>

                  <div class="form-group">
                  <label>Default language:</label>
                  <select name="locale" class="form-control" required>
                    <option value="english"{if isset($post.locale) && $post.locale == "english"} selected{/if}>English</option>
                    <option value="german"{if isset($post.locale) && $post.locale == "german"} selected{/if}>German (Automatic translation)</option>
                    <option value="french"{if isset($post.locale) && $post.locale == "french"} selected{/if}>French (Automatic translation)</option>
                    <option value="swedish"{if isset($post.locale) && $post.locale == "swedish"} selected{/if}>Swedish (Automatic translation)</option>
                    <option value="spanish"{if isset($post.locale) && $post.locale == "spanish"} selected{/if}>Spanish (Automatic translation)</option>
                  </select>
                  </div>

                  <div class="form-group text-right">
                    <button type="submit" class="btn btn-primary" data-loading-text="Loading..."/>Save</button>
                  </div>
              </fieldset>
          </form>
        {/if}
    </div>
<script>
    if(typeof Intl != 'undefined') {
        $('[name="timezone"]').val(Intl.DateTimeFormat().resolved.timeZone);
    }
</script>
</body>
</html>