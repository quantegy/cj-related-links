<?php if(!empty($this->links)): ?>
<ul id="relatedLinks">
	<?php foreach($this->links as $link): ?>
	<li class="ui-state-default clearfix rlItem">
		<span class="ui-icon ui-icon-arrowthick-2-n-s goleft"></span>
		<input type="hidden" class="featureLinkId" value="<?php echo $link->link_id; ?>" />
		<input type="hidden" class="flHref" value="<?php echo $link->link_url; ?>" />
		<input type="hidden" class="flLabel" value="<?php echo $link->link_name; ?>" />
		<div class="goleft">
			<div><a href="<?php echo $link->link_url; ?>" title="<?php echo $link->link_name; ?>"><?php echo $link->link_name; ?></a></div>
			<div class="flLink"><?php echo $link->link_url; ?></div>
		</div>
		<div class="goright">
			<a href="#" class="editFeatureLink">Edit</a>
			| <a href="#" class="removeFeatureLink">Remove</a>
		</div>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>