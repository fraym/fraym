{js('fraym/core/registry.js', 'iframe-extension')}
<div class="container">
    <div class="row">
        <div class=" col-lg-12">
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
                            <th class="text-right">{_('Options')}</th>
                            </thead>
                            <tbody>
                            {foreach $unregisteredExtensions as $extension}
                                <tr>
                                    <td>{$extension.name}</td>
                                    <td>{$extension.version}</td>
                                    <td>{$extension.author}</td>
                                    <td class="text-right">
                                        {if $extension.documentationLink}<a class="btn btn-default btn-xs" href="{$extension.documentationLink}" target="_blank" title="{_('Show documentation')}"><i class="fa fa-book"></i></a>{/if}
                                        {if $extension.website}<a class="btn btn-default btn-xs" href="{$extension.website}" target="_blank" title="{_('Show project website')}"><i class="fa fa-globe"></i></a>{/if}
                                        <a class="btn btn-default btn-xs" href="#" title="{_('Install')}" data-install="{$extension.fileHash}"><i class="fa fa-plus"></i></a>
                                        <a class="btn btn-default btn-xs" href="#" title="{_('Remove')}" data-remove="{$extension.fileHash}"><i class="fa fa-trash-o"></i></a>
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
                            <th class="text-right">{_('Options')}</th>
                            </thead>
                            <tbody>
                            {foreach $extensions as $extension}
                                <tr>
                                    <td>{$extension.name}</td>
                                    <td>{$extension.version}</td>
                                    <td>{$extension.author}</td>
                                    <td>
                                        {if $extensionUpdates.containsKey($extension.repositoryKey) === false}
                                            <span class="label label-success">{_('Up to date')}</span>
                                        {else}
                                            <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target=".ext-{$extension.repositoryKey}">{_('Please update')}</button>

                                            <div class="modal fade ext-{$extension.repositoryKey}" tabindex="-1" role="dialog" aria-labelledby="{$extension.repositoryKey}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                            <h4 class="modal-title" id="mySmallModalLabel">{$extension.name} {$extensionUpdates.get($extension.repositoryKey).version}</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>
                                                                {if $extensionUpdates.get($extension.repositoryKey).updateInformation}
                                                                    {$extensionUpdates.get($extension.repositoryKey).updateInformation}
                                                                {else}
                                                                    {_('Update to version')} {$extensionUpdates.get($extension.repositoryKey).version}
                                                                {/if}
                                                            </p>
                                                            <button data-update="{$extension.repositoryKey}">{_('Update now')}</button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        {/if}
                                    </td>
                                    <td class="text-right">
                                        <a class="btn btn-default btn-xs" href="/fraym/registry/download?repositoryKey={$extension.repositoryKey}" target="_blank" title="{_('Download package')}"><i class="fa fa-archive"></i></a>
                                        {if $extension.documentationLink}<a class="btn btn-default btn-xs" href="{$extension.documentationLink}" target="_blank" title="{_('Show documentation')}"><i class="fa fa-book"></i></a>{/if}
                                        {if $extension.website}<a class="btn btn-default btn-xs" href="{$extension.website}" target="_blank" title="{_('Show project website')}"><i class="fa fa-globe"></i></a>{/if}
                                        {if $extension.deletable}<a class="btn btn-default btn-xs" href="#" title="{_('Uninstall')}" data-uninstall="{$extension.id}"><i class="fa fa-trash-o"></i></a>{/if}
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>