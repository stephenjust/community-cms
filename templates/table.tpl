<table id="{$id}" class="{$class}">
    <thead>
        <tr>
        {foreach from=$cols item=col}
        <th>{$col}</th>
        {/foreach}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
            <tr>
                {foreach from=$row item=cell}
                    <td>{$cell}</td>
                {/foreach}
            </tr>
        {foreachelse}
            <tr>
                <td colspan="{count($cols)}">{$no_data_message}</td>
            </tr>
        {/foreach}
    </tbody>
</table>
