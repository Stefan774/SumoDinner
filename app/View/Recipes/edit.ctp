<!-- File: /app/View/Recipes/edit.ctp -->
<?php 
    echo $this->Html->script('jquery'); // Include jQuery library
    echo $this->Html->script('jquery-ui-1.9.2.custom'); // Include jQuery UI-library
    echo $this->Html->script('plupload.full'); // Include plupload
?>


<h1>Edit Recipe</h1>

<style>
#sortable { list-style-type: none; margin: 0; padding: 0; width: 550px; }
#sortable li { margin: 3px 3px 3px 3px; padding: 1px; float: left; width: 100px; height: 90px; font-size: 4em; text-align: center; }
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
?>

<ul id="sortable">
    <?php 
        foreach ($recipe['Image'] as $img) {
            echo '<li class="ui-state-default">'.$this->Html->image($recipe['Recipe']['contentkey'].'/'.$img['name'],array('alt' => 'CakePHP','pathPrefix' => CONTENT_URL,'width'=>'100px','height'=>'90px')).'</li>';
        }
    ?>
</ul>

