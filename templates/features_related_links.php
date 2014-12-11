<?php if(!empty($this->links)): ?>
<li>
	<h2 class="bucket-header">Related Links</h2>
	<ul class="linkList">
		<?php $i=0; foreach($this->links as $link): ?>
		<?php $i++; ?>
		<li>
			<div class="clearfix">
				<div class="holder">
					<div class="link">
						<a target="_blank" href="<?php echo $link->link_url; ?>" title="<?php echo $i . " - " . esc_attr($link->link_name); ?>"><?php echo $link->link_name; ?></a>
					</div>
				</div>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
</li>
<?php endif; ?>