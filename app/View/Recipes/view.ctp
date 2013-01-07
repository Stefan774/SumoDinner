<!-- File: /app/View/Recipes/view.ctp -->
<?php pr($recipe); ?>
<h1><?php echo h($recipe['Recipe']['title']); ?></h1>
<label>Description</label>
<div><?php echo h($recipe['Recipe']['description']); ?></div>
<label>Ingredients</label>
<div><?php echo h($recipe['Recipe']['ingredients']); ?></div>
<label>Severity</label>
<div><?php echo h($recipe['Recipe']['severity']); ?></div>
<p><small>Created: <?php echo $recipe['Recipe']['created']; ?></small></p>
<p><?php # echo h($post['Post']['body']); ?></p>