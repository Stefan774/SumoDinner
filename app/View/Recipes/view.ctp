<!-- File: /app/View/Recipes/view.ctp -->
<?php //pr($recipe); ?>
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

<div id="result"></div>
<div data-spy="affix" data-offset-top="10"></div>
<div class="editable recipeTitle"><?php echo h($recipe['Recipe']['title']); ?></div>
<div id="recipe_part1" class="row">
    <div id="recipe_main_pic" class="span8"><?php echo isset($recipe['Image'][0]['name'])?$this->Html->image($recipe['Recipe']['contentkey'].'/'.$recipe['Image'][0]['name'], array('pathPrefix' => CONTENT_URL,'alt' => $recipe['Image'][0]['titel'])):"Dein Titelbild"; ?></div>
    <div id="recipe_ratings" class="span4">
        <div id="recipe_text_ratings">
            <b>Schwierigkeitsgrad</b><br>
            <?php $severity_level = Configure::read('severity_level'); echo $severity_level[$recipe['Recipe']['severity']]; ?><br><br>
            <b>Kategorie(n)</b><br>
            <?php foreach ($recipe['Category'] as $category) {echo $category['name']." ";}; ?>
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
    <div id="ingredients"><?php echo $recipe['Recipe']['ingredients']; ?></div>
    <h3>Zubereitung:</h3>
    <div><?php echo $recipe['Recipe']['description']; ?></div>
    <h3>Sumo ART:</h3>
    <ul id="images_editor">
        <?php
            foreach ($recipe['Image'] as $img) {
                echo "<li class='ui-state-default, img-polaroid'><a class='fancybox' rel='group' href='/SumoDinner/uploads/".$recipe['Recipe']['contentkey'].'/'.$img['name']."'>".$this->Html->image($recipe['Recipe']['contentkey'].'/100x75/'.$img['name'],array('alt' => $img['titel'],'pathPrefix' => CONTENT_URL,'width'=>'100px','height'=>'90px','name' => 'pic_'.$img['ordernum']))."</a></li>";
            }
        ?>
    </ul>
    <div style="clear: both">&nbsp;</div>
    <p><small>Created: <?php echo $recipe['Recipe']['created']; ?></small></p>
    <p><?php # echo h($post['Post']['body']); ?></p>
</div>