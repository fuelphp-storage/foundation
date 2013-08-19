<!--  Pagination - Twitter Bootstrap 2.x template -->
<?php if ($total): ?>
<div class="pagination<?php if ($align=='center') echo ' pagination-centered'; if ($align=='right') echo ' pagination-right';?>">
	<ul>
		<?php if (isset($first)): ?>
			<li><a href="<?php echo $first; ?>">&laquo;&laquo;</a></li>
		<?php endif; ?>
		<?php if (isset($previous)): ?>
			<?php if ($previous == '#'): ?>
				<li class="disabled"><a href="<?php echo $previous; ?>">&laquo;</a></li>
			<?php else: ?>
				<li><a href="<?php echo $previous; ?>">&laquo;</a></li>
			<?php endif; ?>
		<?php endif; ?>
		<?php foreach($urls as $page => $url): ?>
			<?php if ($page == $active): ?>
				<li class="active"><a href="<?php echo $url; ?>"><?php echo $page; ?></a></li>
			<?php else: ?>
				<li><a href="<?php echo $url; ?>"><?php echo $page; ?></a></li>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if (isset($next)): ?>
			<?php if ($next == '#'): ?>
				<li class="disabled"><a href="<?php echo $next; ?>">&raquo;</a></li>
			<?php else: ?>
				<li><a href="<?php echo $next; ?>">&raquo;</a></li>
			<?php endif; ?>
		<?php endif; ?>
		<?php if (isset($last)): ?>
			<li><a href="<?php echo $last; ?>">&raquo;&raquo;</a></li>
		<?php endif; ?>
	</ul>
</div>
<?php endif; ?>
