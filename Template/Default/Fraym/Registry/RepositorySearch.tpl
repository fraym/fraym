<table class="table table-striped">
    <thead>
    <th>{_('Name')}</th>
    <th>{_('Description')}</th>
    <th>{_('Version')}</th>
    <th>{_('Author')}</th>
    <th class="text-right">{_('Options')}</th>
    </thead>
    <tbody>
    {foreach $extensions as $extension}
        <tr>
            <td>{$extension.name}</td>
            <td>{$extension.description}</td>
            <td>{$extension.version}</td>
            <td>{$extension.author}</td>
            <td class="text-right">
                {if $availableExtensions.containsKey($extension.repositoryKey)}
                    <span class="label label-success">{_('Already exists')}</span>
                {elseif $extension.buyLink}
                    <a class="btn btn-default btn-xs" href="#" title="{_('Buy extension')}" data-buy="{$extension.buyLink}"><i class="fa fa-shopping-cart"></i></a>
                {else}
                    <a class="btn btn-default btn-xs" href="#" title="{_('Download')}" data-download="{$extension.repositoryKey}"><i class="fa fa-download"></i></a>
                {/if}
                {if $extension.documentationLink}<a class="btn btn-default btn-xs" href="{$extension.documentationLink}" target="_blank" title="{_('Show documentation')}"><i class="fa fa-book"></i></a>{/if}
                {if $extension.website}<a class="btn btn-default btn-xs" href="{$extension.website}" target="_blank" title="{_('Show project website')}"><i class="fa fa-globe"></i></a>{/if}
             </td>
        </tr>
    {/foreach}
    </tbody>
</table>