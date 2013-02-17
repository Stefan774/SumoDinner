<h1>Recipes</h1>
<?php pr($recipes); ?>
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
        <td><?php echo $recipe['Recipe']['description']; ?></td>
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