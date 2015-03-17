<p>
    Use form on bottom to add link label and URL. Reorder by dragging and dropping.
    Edit label or URL by left-clicking on text.
</p>
<div id="linkListContainer">
    <?php if(!empty($links)): ?>
        <ul class="linkList" id="relatedLinks">
            <?php foreach($links as $indx => $link): ?>
            <li data-id="<?php echo $link->id; ?>" class="ui-state-default clearfix rlItem">
                <span class="ui-icon ui-icon-arrowthick-2-n-s goleft"></span>
                <div class="goleft">
                    <div class="flLabel" data-id="<?php echo $link->id; ?>"><?php echo stripslashes($link->label); ?></div>
                    <div class="flLink" data-id="<?php echo $link->id; ?>"><?php echo $link->url; ?></div>
                </div>
                <div class="goright">
                    <a href="#" data-id="<?php echo $link->id; ?>" class="removeFeatureLink">Remove</a>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
    <ul class="linkList" id="relatedLinks">
        <li style="padding:10px 0 10px 0; font-weight: bold;">No links</li>
    </ul>
    <?php endif; ?>
</div>
<div>
    <input type="hidden" name="featureLinkId" id="featureLinkId" value="" />
    <div>
        <label style="width:75px;">Link text:</label>
        <br />
        <input style="width: 98%;" type="text" name="featureLinkLabel" id="featureLinkLabel" value="" />
    </div>
    <div>
        <label style="width:75px;">URL:</label>
        <br />
        <input style="width: 98%;" type="text" name="featureLinkUrl" id="featureLinkUrl" value="" />
    </div>
</div>
<p style="clear:both;">
    <button type="button" class="button" id="addLinkButton">Add Link</button>
</p>
