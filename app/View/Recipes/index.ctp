<script>
$(function() {
    $( "#accordion" ).accordion();
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
                console.log(dump(data));
                console.log(data[0]['Recipe']['title']);               
                var panelHTML = "";
                var prvLetter = "";
                $.each(data, function( intIndex, objValue ) {
                    var currentLetter = objValue['Recipe']['title'].slice(0,1);
                    if (intIndex == 0) {
                        panelHTML += '<h1>' + currentLetter +'</h1>';
                    }else if(currentLetter != prvLetter) {
                        panelHTML += '<h1>' + currentLetter +'</h1>';
                    }
                    panelHTML += '<a href="' + viewURL + '/' + objValue['Recipe']['id'] + '">' + objValue['Recipe']['title'] + '</a> <br>';
                    prvLetter = currentLetter;
                });
                return panelHTML;
            }
        },
        load: function( event, ui ) {
            
        }
    });
});
</script>
<h1>Recipes</h1>
<?php //pr($recipes); ?>
<div id="tabs">
    <ul>
        <li><?php echo $this->Html->link("Hauptspeise", array('controller' => 'recipes', 'action' => 'getRecipesByCategory', "Hauptspeise")); ?></li>
        <li><?php echo $this->Html->link("Vorspeise", array('controller' => 'recipes', 'action' => 'getRecipesByCategory', "Vorspeise")); ?></li>
        <li><?php echo $this->Html->link("Nachtisch", array('controller' => 'recipes', 'action' => 'getRecipesByCategory', "Nachtisch")); ?></li>
    </ul>
</div>
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

    <!-- Here is where we loop through our $posts array, printing out post info -->
    <?php foreach ($recipes as $recipe): ?>
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
</table>
<?php
    echo $this->Paginator->prev(' << ' . __('previous'), array(), null, array('class' => 'prev disabled'));
    echo $this->Paginator->counter();
    echo $this->Paginator->next(__('next').' >>', array(), null, array('class' => 'next disabled'));
 ?>
<br>
<?php echo $this->Html->Link('Add Recipe', array('controller'=>'recipes', 'action'=>'add')) ?>