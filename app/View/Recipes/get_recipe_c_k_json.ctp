<?php
//pr($result);
?>
<!-- File: /app/View/Recipes/view.ctp -->
<?php //pr($result); ?>
<?php 
    echo $this->Html->script('jquery.fancybox'); // Include fancybox
    echo $this->Html->css('jquery.fancybox');
?>
<script>
$(function() {    
    var rating =  parseInt(<?php echo $recipe['Recipe']['rating']; ?>) * 40;
    $('ul.star-rating > li > a').hover(function(){$("#currentR").hide();}, function(){$("#currentR").show();})
    $('ul.star-rating > li > a').click(function() {
        rating = parseInt($(this).html()) * 40;
        $.get("<?php echo $this->Html->Url(array("controller"=>"recipes","action"=>"rateRecipe"),false); ?>/<?php echo $recipe['Recipe']['id']; ?>/"+$(this).html(), function(data) {
            $('.result').html(data);
            $(".current-rating").attr('style',"width:"+rating+"px");
        });
    });
    
    $(".current-rating").attr('style',"width:"+rating+"px");  
    
// Start fancybox image viewer
    $(".fancybox").fancybox();
// Disable sorting feature for imageEditor
    $( "#images_editor" ).sortable("disable");
    $( "#accordion" ).accordion();
});
</script>
<?php echo $this->Html->Link('Save-Recipe', array('controller'=>'recipes', 'action'=>'add', true, $result['result'][0]['rezept_show_id'])) ?>
<div id="result"></div>
<div data-spy="affix" data-offset-top="10"></div>
<div class="editable recipeTitle"><?php echo $result['result'][0]['rezept_name']."&nbsp;".$result['result'][0]['rezept_name2']; ?></div>
<div id="recipe_part1" class="row">
    <div id="recipe_main_pic" class="span8"><?php echo isset($result['result'][0]['rezept_bilder'][0]['bigfix']['file'])?$this->Html->image($result['result'][0]['rezept_bilder'][0]['big']['file'],array('height'=>'300px')):"Dein Titelbild"; ?></div>
    <div id="recipe_ratings" class="span4">
        <div id="recipe_text_ratings">
            <b>Schwierigkeitsgrad</b><br>
            <?php echo isset($result['result'][0]['rezept_schwierigkeit'])?$result['result'][0]['rezept_schwierigkeit']:""; ?><br><br>
            <b>Kategorie(n)</b><br>
            <?php foreach ($result['result'][0]['rezept_tags'] as $category) {echo $category."; ";}; ?>
        </div>
        <div id="recipe_star_ratings">  
            <ul class="star-rating">
                 <li id="currentR" class="current-rating" style="width:0px;"></li>
                <li><a title="Rate this 1 Sumo out of 5" class="star-hover">1</a></li>
                <li><a title="Rate this 2 Sumos out of 5" class="two-stars">2</a></li>
                <li><a title="Rate this 3 Sumos out of 5" class="three-stars">3</a></li>
                <li><a title="Rate this 4 Sumos out of 5" class="four-stars">4</a></li>
                <li><a title="Rate this 5 Sumos out of 5" class="five-stars">5</a></li>
            </ul>
        </div>
    </div>
</div>
<div id="recipe_part2">
    <h3 class="clearBottomBorder">Zutaten:</h3>
    <div id="ingredients">
        <ul>
        <?php foreach ($result['result'][0]['rezept_zutaten'] as $ingredient) : ?>
            <li><?php echo $ingredient['menge']."&nbsp;".$ingredient['einheit']."&nbsp;".$ingredient['name']; ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
    <h3>Zubereitung:</h3>
    <div><?php echo $result['result'][0]['rezept_zubereitung']; ?></div>
    <h3>Sumo ART:</h3>
    <ul id="images_editor">
        <?php
            foreach ($result['result'][0]['rezept_bilder'] as $img) {
                echo "<li class='ui-state-default, img-polaroid'><a class='fancybox' rel='group' href='".$img['224x148-fix']['file']."'>".$this->Html->image($img['bigfix']['file'],array('width'=>'100px','height'=>'90px'))."</a></li>";
            }
        ?>
    </ul>
    <div style="clear: both">&nbsp;</div>
    <p><?php # echo h($post['Post']['body']); ?></p>
</div>