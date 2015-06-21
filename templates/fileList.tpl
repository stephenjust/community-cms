<table class="admintable">
    <tr>
        <th>File Name</th>
        <th>Label</th>
        {if $show_delete eq true}
        <th colspan="2"></th>
        {else}
        <th></th>
        {/if}
    </tr>
    {foreach from=$files item=entry}
        {$file_info=$entry->getInfo()}
        <tr>
            <td><a href="{$entry->getPath()}">{$entry->getName()|escape}</a></td>
            <td>{$file_info.label|escape}</td>
            <td>
                <a href="{$edit_url|replace:'FILE':$entry->getName()}">
                    <img src="./admin/templates/default/images/edit.png"
                    alt="Edit Attributes" width="16px" height="16px" border="0px" />
                </a>
            </td>
            {if $show_delete eq true}
            <td>
                <a href="{$delete_url|replace:'FILE':$entry->getName()}">
                    <img src="./admin/templates/default/images/delete.png"
                         alt="Delete" width="16px" height="16px" border="0px" />
                </a>
            </td>
            {/if}
        </tr>
    {foreachelse}
        <tr>
            {if $show_delete eq true}
                <td colspan="4">No files to display.</td>
            {else}
                <td colspan="3">No files to display.</td>
            {/if}
        </tr>
    {/foreach}
</table>