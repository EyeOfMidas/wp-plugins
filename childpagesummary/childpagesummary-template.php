<?php //var_dump($children); ?>

<?php foreach($children as $childPage) : ?>
<div>
	<h2><a href="<?php echo $childPage['permalink']?>"><?php echo $childPage['title']?></a></h2>
	<p><?php echo $childPage['summary']?> <a href="<?php echo $childPage['permalink']?>">Read More...</a></p>
	
</div>
<?php endforeach; ?>
