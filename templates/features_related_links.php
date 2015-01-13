<?php if (!empty($links)): ?>
    <?php foreach ($links as $indx => $link): ?>
        <li class="ui-state-default clearfix rlItem">
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
<?php endif; ?>