<!-- File: /app/View/Recipes/view.ctp -->
<?php //pr($recipe); ?>
<div class="editable recipeTitle"><?php echo h($recipe['Recipe']['title']); ?></div>
<div id="recipe_part1" class="row">
    <div id="recipe_main_pic" class="span8"><?php echo $this->Html->image($recipe['Recipe']['contentkey'].'/'.$recipe['Image'][0]['name'], array('pathPrefix' => CONTENT_URL,'alt' => $recipe['Image'][0]['titel'])); ?></div>
    <div id="recipe_ratings" class="span4">
        <div id="recipe_text_ratings">
            <b>Schwierigkeitsgrad</b><br>
            <?php $severity_level = Configure::read('severity_level'); echo $severity_level[$recipe['Recipe']['severity']]; ?><br><br>
            <b>Kategorie(n)</b><br>
            <?php foreach ($recipe['Category'] as $category) {echo $category['name']." ";}; ?>
        </div>
    </div>
</div>
<h3 class="clearBottomBorder">Zutaten:</h3>
<div id="ingredients"><?php echo h($recipe['Recipe']['ingredients']); ?></div>
<h3>Zubereitung:</h3>
<div><?php echo h($recipe['Recipe']['description']); ?></div>
<h3>Sumo ART:</h3>
<ul id="images_editor">
    <?php
        foreach ($recipe['Image'] as $img) {
            echo "<li class='ui-state-default, img-polaroid'>".$this->Html->image($recipe['Recipe']['contentkey'].'/'.$img['name'],array('alt' => $img['titel'],'pathPrefix' => CONTENT_URL,'width'=>'100px','height'=>'90px','name' => 'pic_'.$img['ordernum']))."</li>";
        }
    ?>
</ul>
<div style="clear: both">&nbsp;</div>
<p><small>Created: <?php echo $recipe['Recipe']['created']; ?></small></p>
<p><?php # echo h($post['Post']['body']); ?></p>