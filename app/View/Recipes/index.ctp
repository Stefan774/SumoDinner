<?php //pr($recipes);  ?>
<script>
$(function() {
    $( "#accordion" ).accordion();
    $('.carousel').carousel({
        interval: false
    })
    $( "#tabs" ).tabs({
        beforeLoad: function( event, ui ) {
            ui.jqXHR.error(function() {
            ui.panel.html(
            "Leider sind noch keine Gericht in dieser Kategorie vorhanden =( <br>" +
            "<b>Sei ein SUMO und lege das erste Gericht an!!<b>" );
            });
        },
        ajaxOptions: {
            dataFilter: function(result){
                var data = $.parseJSON(result);
                var viewURL = "<?php echo $this->Html->url(array("controller" => "recipes", "action" => "view")); ?>";
                //console.log(dump(data));
                //console.log(data[0]['Recipe']['title']);
                var panelHTML = '<div class="row-fluid">';
                var prvLetter = "";
                var totalRecipes = data.length;
                if (totalRecipes > 0) {
                    var recipesInSpan = Math.round(totalRecipes/3);
                    console.log("totalRecipes = " + totalRecipes + "  span = " + recipesInSpan);
                    //var spanLetterLock = false;
                    $.each(data, function( intIndex, objValue ) {
                        var currentLetter = objValue['Recipe']['title'].slice(0,1);                                            
                        if (intIndex == 0) {
                            panelHTML += '<div class="span4">';
                            panelHTML += '<h1>' + currentLetter +'</h1>';
                        }else if(currentLetter != prvLetter) {
                             if ((intIndex+1) >= recipesInSpan && (intIndex+1) != totalRecipes) {
                                panelHTML += '</div>';
                                panelHTML += '<div class="span4">';
                                recipesInSpan = recipesInSpan + (intIndex+1);
                            }
                            panelHTML += '<h1>' + currentLetter +'</h1>';
                        }
                        panelHTML += '<blockquote><a href="' + viewURL + '/' + objValue['Recipe']['id'] + '">' + objValue['Recipe']['title'] + '</a></blockquote>';
                       if ((intIndex+1) == totalRecipes) {
                            panelHTML += '</div>';
                        }
                        prvLetter = currentLetter;
                    });
                    panelHTML += '</div>';
                    return panelHTML;
                } return ("Leider sind noch keine Gericht in dieser Kategorie vorhanden =( <br>" +
                         "<b>Sei ein SUMO und lege das erste Gericht an!!<b>" );
            }
        },
        load: function( event, ui ) {
            
        }
    });
});
</script>
<?php
if (count($recipes) >= 3) {
    $preview = array_rand($recipes,3);
}else {
    $preview = FALSE;
}
?>
<?php if ($preview) { ?>
<div id="myCarousel" class="carousel slide">
    <div class="carousel-inner">
      <?php $counter = 0; foreach ($preview as $item) : ?>
        <div class="item <?php echo $counter==0?"active":""; ?>">
        <?php echo isset($recipes[$item]['Recipe']['picture'])?$this->Html->image($recipes[$item]['Recipe']['contentkey'].'/'.$recipes[$item]['Recipe']['picture'], array('pathPrefix' => CONTENT_URL,'alt' => $recipes[$item]['Recipe']['title'])):"Dein Titelbild"; ?>
          <div class="carousel-caption"><a href="#">
          <h4><?php echo $recipes[$item]['Recipe']['title']; ?></h4>
          <p><?php echo String::truncate($recipes[$item]['Recipe']['description'],100,array('ellipsis' => '...','exact' => false)); $counter++; ?></p></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <a class="left carousel-control" href="#myCarousel" data-slide="prev">‹</a>
    <a class="right carousel-control" href="#myCarousel" data-slide="next">›</a>
</div>
<?php } ?>
<div id="tabs">
    <ul>
        <li><?php echo $this->Html->link("Hauptspeise", array('controller' => 'recipes', 'action' => 'getRecipesByCategory', "Hauptspeise")); ?></li>
        <li><?php echo $this->Html->link("Vorspeise", array('controller' => 'recipes', 'action' => 'getRecipesByCategory', "Vorspeise")); ?></li>
        <li><?php echo $this->Html->link("Nachtisch", array('controller' => 'recipes', 'action' => 'getRecipesByCategory', "Nachtisch")); ?></li>
    </ul>
</div>
<!--
<table>
    <tr>
        <th>Id</th>
        <th><?php echo $this->Paginator->sort('title', 'Title'); ?></th>
        <th><?php echo $this->Paginator->sort('description', 'Description'); ?></th>
        <th>Severity</th>
        <th>Category</th>
        <th>Created</th>
        <th>&nbsp;</th>
    </tr>
-->
    <!-- Here is where we loop through our $posts array, printing out post info -->
    <?php foreach ($recipes as $recipe): ?>
    <!--
    <tr>
        <td><?php #pr($recipe);
                  echo $recipe['Recipe']['id']; 
             ?>
        </td>
        <td>
            <?php echo $this->Html->link($recipe['Recipe']['title'],
            array('controller' => 'recipes', 'action' => 'view', $recipe['Recipe']['id'])); ?>
        </td>
        <td><?php echo String::truncate($recipe['Recipe']['description'],100,array('ellipsis' => '...','exact' => false)); ?></td>
        <td><?php echo $recipe['Recipe']['severity']; ?></td>
        <td><?php foreach ($recipe['Category'] as $category) {
                    echo $category['name'];
                  }
            ?>
        </td>
        <td><?php echo $recipe['Recipe']['created']; ?></td>
        <td>
            <?php echo $this->Html->Link('Edit Recipe', array('controller'=>'recipes', 'action'=>'edit', $recipe['Recipe']['id'])) ?>
            <?php echo $this->Form->postLink(
                'Delete Recipe',
                array('action' => 'delete', $recipe['Recipe']['id'],$recipe['Recipe']['title'] ),
                array('confirm' => 'Are you sure to delete recipe '.$recipe['Recipe']['title'].' ?' ));
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table> -->
<?php
    echo $this->Paginator->prev(' << ' . __('previous'), array(), null, array('class' => 'prev disabled'));
    echo $this->Paginator->counter();
    echo $this->Paginator->next(__('next').' >>', array(), null, array('class' => 'next disabled'));
 ?>
<br>
<?php echo $this->Html->Link('Add Recipe', array('controller'=>'recipes', 'action'=>'add')) ?>