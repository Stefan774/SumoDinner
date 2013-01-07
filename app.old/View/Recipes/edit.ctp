<!-- File: /app/View/Recipes/edit.ctp -->

<h1>Edit Recipe</h1>
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