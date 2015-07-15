<form enctype="multipart/form-data" action="{$action}" method="post">
    <!-- Limit file size to 64MB -->
    <input type="hidden" name="MAX_FILE_SIZE" value="67108864" />
    <label for="upload">Please choose a file:</label> <input name="upload" type="file" /><br />
    {if $directory != null}
        <input type="hidden" name="path" value="{$directory}" />
    {/if}
    {if $show_directories}
        {$directory_list_form}
    {/if}
    {foreach from=$extra_fields item=extra_field key=extra_field_name}
        <input type="hidden" name="{$extra_field_name}" value="{$extra_field}" />
    {/foreach}
    <input type="submit" value="Upload" />
</form>
