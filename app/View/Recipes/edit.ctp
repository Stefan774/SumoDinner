<!-- File: /app/View/Recipes/edit.ctp -->
<?php
    echo $this->Html->script('plupload.full'); // Include plupload
    echo $this->Html->script('advanced'); // Include wysihtml5 parser rules
    echo $this->Html->script('wysihtml5-0.3.0.min'); // Include wysihtml5 library
    echo $this->Html->script('jEditable');
?>
<script>
$(function() {
/** Handle some input elements **/
    $("#CategoryName_edit").attr('value','<?php echo $categories; ?>');
    $("#CategoryName_edit").keyup(function() {
        var formElementId = this.id.split("_")[0];
        $('#'+formElementId).attr('value',$(this).attr('value'));
    });
    
    $("select#RecipeSeverity_edit")[0].selectedIndex = <?php echo ($recipe['Recipe']['severity']); ?>;
    $("select#RecipeSeverity_edit").change(function(){
        var formElementId = this.id.split("_")[0];
        $('#'+formElementId).attr('value',$(this).attr('value'));
    });
/** END Handle some input elements **/

/** Handle editable elements **/ 
    $('.editable').editable(function(value, settings) {
            var formElementId = this.id.split("_")[0];
            $('#'+formElementId).attr('value',value);
            return value;
        }, 
        { 
            type        : 'textarea',
            submit      : 'OK',
            event       : 'dblclick',
            cssclass    : 'jeditTextarea',
            width       : 'none',
            onblur      : 'ignore'
    });
    
    /* Find and trigger "edit" event on correct Jeditable instance. */
    $(".edit_trigger").bind("click", function() {
        triggerElement = this.id.split('_')[0]+'_edit';
        $('#'+triggerElement).trigger('dblclick');
    });
/** END Handle editable elements **/

 /** Handle wysihtml5 editor for ingredients and description **/
    
    var editor = new wysihtml5.Editor("RecipeIngredients", { // id of textarea element
        name: "wysihtml5desc",
        toolbar:      "wysihtml5-toolbar-Description", // id of toolbar element
        parserRules:  wysihtml5ParserRules, // defined in parser rules set 
        stylesheets: ['<?php echo $this->webroot.'css/wysihtml5.css' ?>']
    });
    
    editor.setValue("<ul><li><br></li></ul>");
    $("#wysihtml5-toolbar-Description").hide();
    
     /** END handle wysihtml5 editor  **/
});
</script>

<h2 class="salter">Rezept nachsalzen</h2>
<div><div class="editable recipeTitle" id="RecipeTitle_edit"><?php echo $recipe['Recipe']['title'] ?></div><a href="#" class="edit_trigger" id="RecipeTitle_trigger">Edit me!!</a></div>
<br>
<h3>Zutaten:</h3>
<div id="ingredients"><div class="editable" id="RecipeIngredients_edit"><?php echo $recipe['Recipe']['ingredients'] ?></div></div>
<br>
<div id="additionalInfo">
    <div class="additional_widget"><p>Schwierigkeitsgrad</p>
        <select id="RecipeSeverity_edit">
            <?php foreach (Configure::read('severity_level') as $key=>$severity_level){echo "<option value='$key'>$severity_level</option>";}?>
        </select>
    </div>
    <div class="additional_widget">
        <p>Kategorie(n)</p>
        <input type="text" id="CategoryName_edit">
    </div>
</div>
<div class="wys-container">
    <h3>Zubereitung:</h3>
    <div id="wysihtml5-toolbar-Description" style="display: none" class="btn-toolbar">
        <div class="btn-group">
            <a data-wysihtml5-command="bold" class="btn"><i class="icon-bold">&nbsp;</i></a>
            <a data-wysihtml5-command="italic" class="btn"><i class="icon-italic">&nbsp;</i></a> 
          <!-- Some wysihtml5 commands require extra parameters -->
            <a data-wysihtml5-command="insertUnorderedList" class="btn"><i class="icon-list">&nbsp;</i></a>
            <a data-wysihtml5-command="insertOrderedList" class="btn"><i class="icon-th-list">&nbsp;</i></a>
            <a data-wysihtml5-action="change_view" class="btn"><i class="icon-eye-open">&nbsp;</i></a>
          <!-- Some wysihtml5 commands like 'createLink' require extra paramaters specified by the user (eg. href) -->
        </div>
    </div>
    <div><div class="editable" id="RecipeDescription_edit"><?php echo $recipe['Recipe']['description'] ?></div></div>
</div>
<?php
    echo $this->Form->create('Recipe', array('action' => 'edit'));
    echo $this->Form->input('title', array('type' => 'text','type' => 'hidden'));
    echo $this->Form->input('description', array('type' => 'hidden'));
    echo $this->Form->input('ingredients', array('type' => 'hidden'));
    echo $this->Form->input('severity', array('type' => 'hidden'));
    echo $this->Form->input('Category.name', array('value' => $categories, 'label'=>'Categories','type' => 'hidden'));
    echo $this->Form->input('id', array('type' => 'hidden'));
    echo $this->Form->input('contentkey', array('type' => 'hidden'));
    foreach ($recipe['Image'] as $img) {
        echo $this->Form->input('Image.'.$img['ordernum'].'.id', array('type' => 'hidden'));
        echo $this->Form->input('Image.'.$img['ordernum'].'.name', array('type' => 'hidden'));
        echo $this->Form->input('Image.'.$img['ordernum'].'.ordernum', array('type' => 'hidden'));
    }
    echo $this->Form->submit('Save Recipe', array('class' => 'btn btn-success'));
    echo $this->Form->end();
?>
<br>
<h3>Bilder:</h3>
<ul id="images_editor">
    <?php
        foreach ($recipe['Image'] as $img) {
            echo "<li class='ui-state-default, img-polaroid'>".$this->Html->image($recipe['Recipe']['contentkey'].'/100x75/'.$img['name'],array('alt' => $img['titel'],'pathPrefix' => CONTENT_URL,'width'=>'100px','height'=>'90px','name' => 'pic_'.$img['ordernum']))."<button class='btn_delete' id='".$img['id']."'>Löschen</button></li>";
        }
    ?>
</ul>