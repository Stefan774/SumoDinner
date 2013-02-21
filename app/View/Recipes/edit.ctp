<!-- File: /app/View/Recipes/edit.ctp -->
<?php
    echo $this->Html->script('plupload.full'); // Include plupload
    echo $this->Html->script('advanced'); // Include wysihtml5 parser rules
    echo $this->Html->script('wysihtml5-0.3.0.min'); // Include wysihtml5 library
    echo $this->Html->script('jEditable');
    //pr($recipe);
?>
<script>
$(function() {
/** Handle some input elements **/
    $("#CategoryName_edit").attr('value','<?php echo $categories; ?>');
    $("#CategoryName_edit").keyup(function() {
        var formElementId = this.id.split("_")[0];
        $('#'+formElementId).attr('value',$(this).attr('value'));
        var mainCategory = $(this).attr('value');
        //console.log(mainCategory.split(";").length);
        if (mainCategory.split(";").length > 1) {
            mainCategory = mainCategory.split(";")[0]; 
        }
        $('#RecipeMaincategory').attr('value',mainCategory);
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
    
    var editor1 = new wysihtml5.Editor("RecipeIngredients", { // id of textarea element
        toolbar:      "wysihtml5-toolbar-Ingredients", // id of toolbar element
        parserRules:  wysihtml5ParserRules, // defined in parser rules set 
        stylesheets: ['<?php echo $this->webroot.'css/wysihtml5.css' ?>']
    });
    
    var editor2 = new wysihtml5.Editor("RecipeDescription", { // id of textarea element
        toolbar:      "wysihtml5-toolbar-Description", // id of toolbar element
        parserRules:  wysihtml5ParserRules, // defined in parser rules set 
        stylesheets: ['<?php echo $this->webroot.'css/wysihtml5.css' ?>']
    });
/** END handle wysihtml5 editor  **/   

});
</script>

<h2>Rezept nachsalzen</h2>
<?php
    echo $this->Form->create('Recipe', array('action' => 'edit'));
    echo $this->Form->input('title',array('class'=>'recipeTitle_Input','label'=>false));
?>
<div id="recipe_part1" class="row">
    <div id="recipe_main_pic" class="span8"><?php echo isset($recipe['Image'][0]['name'])?$this->Html->image($recipe['Recipe']['contentkey'].'/'.$recipe['Image'][0]['name'], array('pathPrefix' => CONTENT_URL,'alt' => $recipe['Image'][0]['titel'])):"Dein Titelbild"; ?></div>
        <div class="additional_widget span2"><p>Schwierigkeitsgrad</p>
            <select id="RecipeSeverity_edit">
                <?php foreach (Configure::read('severity_level') as $key=>$severity_level){echo "<option value='$key'>$severity_level</option>";}?>
            </select>
        </div>
        <div class="additional_widget span3">
            <p>Kategorie(n) <small>z.B. Hauptspeise</small></p>
            <input type="text" id="CategoryName_edit">
        </div>
</div>

<div class="wys-container">
    <h3>Zutaten: </h3>
    <div id="wysihtml5-toolbar-Ingredients" style="display: none;" class="btn-toolbar">
        <div class="btn-group">
            <a data-wysihtml5-command="insertUnorderedList" class="btn"><i class="icon-th-list">&nbsp;</i></a>
        </div>
    </div>
<?php
echo $this->Form->input('ingredients', array('rows' => '7','label'=>''));
?>
</div>
<div class="wys-container">
<h3>Beschreibung der Zubereitung: </h3>
    <div id="wysihtml5-toolbar-Description" style="display: none;" class="btn-toolbar">
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
<?php
echo $this->Form->input('description', array('rows' => '10','label'=>''));
?>
</div>
<?php
    echo $this->Form->input('picture', array('label'=>''));
    echo $this->Form->input('maincategory', array('label'=>''));
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