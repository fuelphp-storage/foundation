<!--  Pagination - Twitter Bootstrap 2.x template -->
<?php if ($total): ?>
<ul class="pager">
	<?php if (isset($previous)): ?>
		<?php if ($previous == '#'): ?>
			<li class="disabled<?php if($align) echo ' previous'; ?>"><a href="<?php echo $previous; ?>">&laquo;</a></li>
		<?php else: ?>
			<li<?php if($align) echo ' class="previous"'; ?>><a href="<?php echo $previous; ?>">&laquo;</a></li>
		<?php endif; ?>
	<?php endif; ?>
	<?php if (isset($next)): ?>
		<?php if ($next == '#'): ?>
			<li class="disabled<?php if($align) echo ' next'; ?>"><a href="<?php echo $next; ?>">&raquo;</a></li>
		<?php else: ?>
			<li<?php if($align) echo ' class="next"'; ?>><a href="<?php echo $next; ?>">&raquo;</a></li>
		<?php endif; ?>
	<?php endif; ?>
</ul>
<?php endif; ?>
