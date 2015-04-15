<div class="edit_bar">
    {if $label != null}
        <span class="label">{$label}: </span>
    {/if}
    {foreach from=$items item=item}
        <a href="{$item.target}" class="item"><img src="<!-- $IMAGE_PATH$ -->{$item.image}" alt="{$item.text}" /></a>
    {/foreach}
</div>