<div style="display:none;" id="editLink">
	<input type="hidden" name="editLinkId" id="editLinkId" value="" />
	<div>
		<label for="editLinkLabel">Link text:</label><br />
		<input type="text" name="editLinkLabel" id="editLinkLabel" value="" />
	</div>
	<div>
		<label for="editLinkHref">URL:</label><br />
		<input type="text" name="editLinkHref" id="editLinkHref" value="" />
	</div>
	<div>
		<input type="button" value="Update" id="editLinkBtn" />
		<input type="button" value="Cancel" id="editCancelBtn" />
	</div>
</div>

<div style="overflow:auto;" id="featLinksList">
	<?php echo $this->fetch("ajax/feature_link_list.tpl.php"); ?>
</div>

<div>
	<div>
		<label style="width:75px;">Link text:</label>
		<br />
		<input type="text" name="featureLinkLabel" id="featureLinkLabel" value="" />
	</div>
	<div>
		<label style="width:75px;">URL:</label>
		<br />
		<input type="text" name="featureLinkUrl" id="featureLinkUrl" value="" />
	</div>
</div>
<p style="clear:left;">
    <button type="button" class="button" id="addLinkButton">Add Link</button>
</p>