<!-- File: /app/View/Recipes/edit.ctp -->
<?php
    echo $this->Html->script('plupload.full'); // Include plupload
    echo $this->Html->script('advanced'); // Include wysihtml5 parser rules
    echo $this->Html->script('wysihtml5-0.3.0.min'); // Include wysihtml5 library
    echo $this->Html->script('jEditable'); // Include wysihtml5 library
?>
<script>
$(function() {
    
    /**
 * Function : dump()
 * Arguments: The data - array,hash(associative array),object
 *    The level - OPTIONAL
 * Returns  : The textual representation of the array.
 * This function was inspired by the print_r function of PHP.
 * This will accept some data as the argument and return a
 * text that will be a more readable version of the
 * array/hash/object that is given.
 * Docs: http://www.openjs.com/scripts/others/dump_function_php_print_r.php
 */
function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}
    
/** Handle sortable elements **/

// run the currently selected effect
function runHideEffect(object,effect) {
        
        var selectedEffect = effect;
        // most effect types need no options passed by default
        var options = {};
        // some effects have required parameters
        if ( selectedEffect === "scale" ) {
                options = { percent: 0 };
        } else if ( selectedEffect === "size" ) {
                options = { to: { width: 200, height: 60 } };
        }
        // run the effect
        $( object).hide( selectedEffect, options, 500, removeDOMObject);
};

//Make images sortable with jquery sortable class
$( "#images_editor" ).sortable({
    placeholder: "ui-state-highlight, img-polaroid"
});

//Prefent selection on sortable elements
$( "#images_editor" ).disableSelection();

/** Handle the sortupdate event which occurs when the user changes
*   the image position. This function sets the necassary fileds to
*   update the corresponding app information when the form is saved. 
**/
$("#images_editor").bind( "sortupdate", function(event, ui) {
    $('ul#images_editor > li').each(function(index) {
            //get the image node
            var attr_helper = $('img',this).attr('name');
            var selector_helper = $('img',this).attr('src');
            
            selector_helper_split = selector_helper.split("/");
            selector_helper = selector_helper_split[(selector_helper_split.length -1)];
            
            attr_split = attr_helper.split("_");
            
            //Leave function if image is markt for delete
            if (attr_split[1] == -1) {
                return;
            }
            
            attr_helper = (attr_split[0]+'_'+ index);
            $('img',this).attr('name', attr_helper);

            var input_img_name = $('input[value|="'+selector_helper+'"]').attr('name');

            input_img_number_array = input_img_name.split("[");
            input_img_number = input_img_number_array[2].substr(0,input_img_number_array[2].length-1);

            $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').removeAttr('value');
            $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').attr('value',index);
    });
});
     function removeDOMObject(object) {
        //alert('Hallo');
        //alert(imgListObject);
        this.remove();
    }
        
    $(".btn_delete").click(function() {
        var imgListObject = $(this).parent();
        var imgID = this.id;
        var lastOrderNum = $(imgListObject).children('img').attr('name').split("_")[1];
        
        //Set img name attribute to -1 and mark it for deletion
        $(imgListObject).children('img').attr('name',"pic_-1");
        
        //Leave function if ordernum is negative
        if (lastOrderNum < 0 ) {
            return;
        }
        
        $('ul#images_editor > li').each(function(index) {
            
            var imgOrderNum = $(this).children('img').attr('name').split("_")[1];
            var selector_helper = $(this).children('img').attr('src').split("/")[$(this).children('img').attr('src').split("/").length -1];
            var input_img_name = $('input[value|="'+selector_helper+'"]').attr('name');

            input_img_number_array = input_img_name.split("[");
            input_img_number = input_img_number_array[2].substr(0,input_img_number_array[2].length-1);
            
            //alert(selector_helper);
                
            if (imgOrderNum > lastOrderNum) {
                $(this).children('img').attr('name', "pic_"+(imgOrderNum -1));                
                $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').removeAttr('value');
                $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').attr('value',(imgOrderNum -1));
                
                //alert ("Changed Ordernum "+imgOrderNum+" to new Ordernum "+(imgOrderNum -1))
            }
            if (imgOrderNum == -1) {
                $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').removeAttr('value');
                $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').attr('value',-1);
            }
        });
        
        runHideEffect(imgListObject,'highlight');
        
        //imgListObject.css("width", "200px");
        //runHideEffect(imgListObject,'highlight');
        
        //$(imgListObject+' > img').css("width", "10px");
       
        //alert ($(imgListObject).children('img').attr('name'));
        /** Possible ajax delete
        var imgDel = $.post("<?php echo $this->Html->Url(array("controller"=>"recipes","action"=>"removeImage"),false); ?>.json", { pic_id : imgID});
        imgDel.success(function(data) {
               // alert('Hallo');
                //alert(data.rq);
                //var obj = jQuery.parseJSON(data);
                //alert(data.pic_id);
                //test();
                runHideEffect(imgListObject,'highlight');
                //$("#error").show();
                //$("#error").append(data.pic_id);
                
            });
        imgDel.error(function() {
             $("#error").show();
             $("#error").append("<b>Image could not be deleted</b>");
        }); 
       **/
    });
/** END Handle sortable elements **/
    
/** Handle editable elements ******/ 
    $('.editable').editable(function(value, settings) {
        var formElementId = this.id.split("_")[0];
        $('#'+formElementId).attr('value',value);
        return value;
     }, { 
        type    : 'textarea',
        submit  : 'OK',
        event   : 'dblclick',
        cssclass : 'jeditTextarea',
        width: 'none',
        onblur : 'ignore'
    });
    /* Find and trigger "edit" event on correct Jeditable instance. */
    $(".edit_trigger").bind("click", function() {
        triggerElement = this.id.split('_')[0]+'_edit';
        //alert(triggerElement);
        $('#'+triggerElement).trigger('dblclick');
    });
/** END Handle editable elements **/

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