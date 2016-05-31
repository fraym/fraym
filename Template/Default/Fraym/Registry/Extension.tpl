{js('fraym/core/registry.js', 'iframe-extension')}
<div id="registry-extensions">
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{_('Online package repository')}</h3></div>
        <div class="panel-body">
            <form rel="form" action="" method="post" id="repository-form" autocomplete="off">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="input-group">
                            <input class="form-control" name="extension_term" type="text">
                                        <span class="input-group-btn">
                                            <button class="btn" type="submit">{_('Search')}</button>
                                        </span>
                        </div>
                    </div>
                </div>
            </form>
            <div id="repositoryResult"></div>
        </div>
    </div>

    {if count((array)$unregisteredExtensions)}
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">{_('Not installed packages')}</h3></div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                    <th>{_('Name')}</th>
                    <th>{_('Version')}</th>
                    <th>{_('Author')}</th>
                    <th width="15%" class="text-right">{_('Options')}</th>
                    </thead>
                    <tbody>
                    {foreach $unregisteredExtensions as $extension}
                        <tr>
                            <td>{$extension.name} ({$extension.package})</td>
                            <td>{$extension.version}</td>
                            <td>{$extension.author}</td>
                            <td class="text-right">
                                {if $extension.documentationLink}<a class="btn btn-default btn-xs" href="{$extension.documentationLink}" target="_blank" title="{_('Show documentation')}"><i class="fa fa-book"></i></a>{/if}
                                {if $extension.homepage}<a class="btn btn-default btn-xs" href="{$extension.homepage}" target="_blank" title="{_('Show project website')}"><i class="fa fa-globe"></i></a>{/if}
                                <a class="btn btn-default btn-xs" href="#" title="{_('Install')}" data-install="{$extension.repositoryKey}"><i class="fa fa-plus"></i></a>
                                <a class="btn btn-default btn-xs" href="#" title="{_('Remove')}" data-remove="{$extension.repositoryKey}"><i class="fa fa-trash-o"></i></a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    {/if}

    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{_('Installed packages')}</h3></div>
        <div class="panel-body">
            <table class="table table-striped">
                <thead>
                <th>{_('Name')}</th>
                <th>{_('Version')}</th>
                <th>{_('Author')}</th>
                <th>{_('Update status')}</th>
                <th width="15%" class="text-right">{_('Options')}</th>
                </thead>
                <tbody>
                {foreach $extensions as $extension}
                    <tr>
                        <td>{$extension.name} ({$extension.repositoryKey})</td>
                        <td>{$extension.version}</td>
                        <td>{if $extensionPackages[$extension.repositoryKey]}{$extensionPackages[$extension.repositoryKey].author}{/if}</td>
                        <td>
                            {if isset($extensionUpdates[$extension.repositoryKey]) === false}
                                {if $extensionPackages[$extension.repositoryKey]}
                                    <span class="label label-success">{_('Up to date')}</span>
                                {else}
                                    <span class="label label-warning" title="{_('The package can not be found in the packagist repository.')}">{_('Could not resolve status')}</span>
                                {/if}
                            {else}
                                <button class="btn btn-xs btn-warning" data-update="{$extension.repositoryKey}">{_('Update to :version', 'FRAYM_UPDATE_TO', 'en_US', [':version' => $extensionUpdates[$extension.repositoryKey].getVersion()])}</button>
                            {/if}
                        </td>
                        <td class="text-right">
                            <a class="btn btn-default btn-xs" href="/fraym/registry/download?id={$extension.id}" target="_blank" title="{_('Download package')}"><i class="fa fa-archive"></i></a>
                            {if $extensionPackages[$extension.repositoryKey]}
                                {if $extensionPackages[$extension.repositoryKey].getHomepage()}<a class="btn btn-default btn-xs" href="{$extensionPackages[$extension.repositoryKey].getHomepage()}" target="_blank" title="{_('Show project website')}"><i class="fa fa-globe"></i></a>{/if}
                            {/if}
                            {if $extension.deletable}<a class="btn btn-default btn-xs" href="#" title="{_('Uninstall')}" data-uninstall="{$extension.id}"><i class="fa fa-trash-o"></i></a>{/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>