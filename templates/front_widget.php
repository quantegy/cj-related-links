<?php if(!empty($links)): ?>
<?php echo $args['before_widget']; ?>
<div class="widget-text wp_widget_plugin_box">
<?php if($title): ?>
<?php echo $args['before_title'] . $title . $args['after_title']; ?>
<?php endif; ?>
    <ul>
        <?php foreach($links as $link): ?>
        <li>
            <div><a href="<?php echo htmlentities($link->url); ?>"><?php echo stripslashes($link->label); ?></a></div>
            <div class="list-url"><?php echo htmlentities($link->url); ?></div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php echo $args['after_widget']; ?>
<?php endif; ?>