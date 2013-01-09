<!-- File: /app/View/Recipes/edit.ctp -->
<?php 
    echo $this->Html->script('jquery'); // Include jQuery library
    echo $this->Html->script('jquery-ui-1.9.2.custom'); // Include jQuery UI-library
    echo $this->Html->script('plupload.full'); // Include plupload
?>


<h1>Edit Recipe</h1>

<style>
#sortable { list-style-type: none; margin: 0; padding: 0; width: 450px; }
#sortable li { margin: 3px 3px 3px 0; padding: 1px; float: left; width: 100px; height: 90px; font-size: 4em; text-align: center; }
 html>body #sortable li { height: 1.5em; line-height: 1.2em; }
.ui-state-highlight { height: 1.5em; line-height: 1.2em; }
</style>
<script>
$(function() {
$( "#sortable" ).sortable({
    placeholder: "ui-state-highlight"
});
$( "#sortable" ).disableSelection();
});
</script>

<?php
    echo $this->Form->create('Recipe', array('action' => 'edit'));
    echo $this->Form->input('title');
    echo $this->Form->input('description', array('rows' => '3'));
    echo $this->Form->input('ingredients', array('rows' => '2'));
    echo $this->Form->input('severity');
    echo $this->Form->input('Category.name', array('value' => $categories, 'label'=>'Categories'));
    echo $this->Form->input('id', array('type' => 'hidden'));
    echo $this->Form->end('Save Recipe');
    echo $this->Html->image('cake_logo.png', array('alt' => 'CakePHP'));
?>

<ul id="sortable">
<li class="ui-state-default"><img src="<?php echo "/SumoDinner/SumoDinner/uploads/".$recipe['Recipe']['contentkey']."/".$recipe['Image'][0]['name'] ?>" alt="test" /></li>
<li class="ui-state-default">2</li>
<li class="ui-state-default">3</li>
<li class="ui-state-default">4</li>
<li class="ui-state-default">5</li>
<li class="ui-state-default">6</li>
<li class="ui-state-default">7</li>
<li class="ui-state-default">8</li>
<li class="ui-state-default">9</li>
<li class="ui-state-default">10</li>
<li class="ui-state-default">11</li>
<li class="ui-state-default">12</li>
</ul>

