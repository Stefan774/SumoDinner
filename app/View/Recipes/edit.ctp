<!-- File: /app/View/Recipes/edit.ctp -->
<?php 
    echo $this->Html->script('jquery'); // Include jQuery library
    echo $this->Html->script('jquery-ui-1.9.2.custom'); // Include jQuery UI-library
    echo $this->Html->script('plupload.full'); // Include plupload
?>


<h1>Edit Recipe</h1>

<style>
#images_editor { list-style-type: none; margin: 0; padding: 0; width: 550px; }
#images_editor li { margin: 3px 3px 3px 3px; padding: 1px; float: left; width: 100px; height: 90px; font-size: 4em; text-align: center; }
 html>body #images_editor li { height: 1.5em; line-height: 1.2em; }
.ui-state-highlight { height: 1.5em; line-height: 1.2em; }
</style>
<script>
$(function() {
/** Handle sortable elements **/
    $( "#images_editor" ).sortable({
        placeholder: "ui-state-highlight"
    });
    $( "#images_editor" ).disableSelection();
    
    $("#images_editor").bind( "sortupdate", function(event, ui) {
        //alert("changed")
        $('ul#images_editor > li').each(function(index) {
                $('#error').show()
                $('#error').append('Index'+index + ' '+ $('img',this).attr('name') + '<br>')	
                var attr_helper = $('img',this).attr('name')
                $('#error').append("Picture " + attr_helper + "Changed To")
                attr_split = attr_helper.split("_")
                attr_helper = (attr_split[0]+'_'+ index)
                $('img',this).attr('name', attr_helper)
                $('#error').show()
                $('#error').append('Index'+index + '=' + attr_helper + '<br>')
        });
    });
    
/** END Handle sortable elements **/
});
</script>
<div id="error" style="display:none"></div>
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

<ul id="images_editor">
    <?php
        foreach ($recipe['Image'] as $img) {
            echo "<li class='ui-state-default'>".$this->Html->image($recipe['Recipe']['contentkey'].'/'.$img['name'],array('alt' => 'CakePHP','pathPrefix' => CONTENT_URL,'width'=>'100px','height'=>'90px','name' => 'pic_'.$img['ordernum']))."</li>";
        }
    ?>
</ul>

