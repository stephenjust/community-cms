<table class="admintable">
    <tr>
        <th>Date</th>
        <th>Action</th>
        <th>User</th>
        <th>IP</th>
    </tr>
    {foreach from=$log_entries item=entry}
        <tr>
            <td>{$entry.date}</td>
            <td>{$entry.action}</td>
            <td>{$entry.user_name}</td>
            <td>{$entry.ip_addr}</td>
        </tr>
    {foreachelse}
        <tr>
            <td colspan="4">No entries to display.</td>
        </tr>
    {/foreach}
</table>
