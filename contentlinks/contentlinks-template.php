<div class="row">
	<div class="col-md-12">
		<h2>Related Articles</h2>
		<div class="row relevant-links">
		<?php foreach($bookmarks as $bookmark) :?>
			<div class="col-md-3">
				<h2 class="melbourneregular">
					<a href="<?php echo $bookmark->link_url; ?>" target="<?php echo $bookmark->link_target; ?>">
					<img class="img-responsive" src="<?php echo $bookmark->link_image; ?>">
					<?php echo $bookmark->link_name; ?> 
					</a>
				</h2>
				<p><?php echo $bookmark->link_description; ?></p>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>