<table class="table table-striped">
    <thead>
    <th>{_('Package Name')}</th>
    <th>{_('Description')}</th>
    <th>{_('Version')}</th>
    <th>{_('Author')}</th>
    <th width="15%" class="text-right">{_('Options')}</th>
    </thead>
    <tbody>
    {foreach $extensions as $extension}
        <tr>
            <td>{$extension.getName()}</td>
            <td>{$extension.getDescription()}</td>
            <td>{$extension.getVersion()}</td>
            <td>{$extension.author}</td>
            <td class="text-right">
                {if $availableExtensions.containsKey($extension.repositoryKey)}
                    <span class="label label-success">{_('Already exists')}</span>
                {else}
                    <a class="btn btn-default btn-xs" href="#" title="{_('Download')}" data-download="{$extension.getName()}"><i class="fa fa-download"></i></a>
                {/if}
                {if $extension.getHomepage()}<a class="btn btn-default btn-xs" href="{$extension.getHomepage()}" target="_blank" title="{_('Show project website')}"><i class="fa fa-globe"></i></a>{/if}
             </td>
        </tr>
    {/foreach}
    </tbody>
</table>