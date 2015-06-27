<div class="galleria">
    {foreach from=$images item=image}
        <a href="{$image->getUrl()}">
            <img src="{$image->getThumbUrl()}"
                 data-big="{$image->getUrl()}"
                 data-title="{$image->getTitle()}"
                 data-description="{$image->getDescription()}" />
        </a>
    {/foreach}
</div>

<script>
require(["cms"], function() { 
    require(["galleria", "galleria-theme"], function(Galleria) {
        Galleria.run('.galleria');
    });
});
</script>