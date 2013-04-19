<!-- File: /app/View/Recipes/add.ctp -->
<?php
    echo $this->Html->script('plupload.full'); // Include plupload
    echo $this->Html->script('advanced'); // Include wysihtml5 parser rules
    echo $this->Html->script('wysihtml5-0.3.0.min'); // Include wysihtml5 library
    //pr($result);
    //pr($categories);
    //pr($remoteImages);
?>

<script type="text/javascript">
$(function() {
 
 //Uploader plupload +++++++++++++++++++++++
        var queuedImages = 0;
        var addedImages = 0;
        
	var uploader = new plupload.Uploader({
		runtimes : 'html5,flash,silverlight,html4',
		browse_button : 'pickfiles',
		container : 'container',
		max_file_size : '10mb',
                chunk_size : '1mb',
	        unique_names : true,
                
		url : '<?php echo $this->Html->Url(array("controller"=>"recipes","action"=>"addImages"),true); ?>',
                
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"}
		],
                
                flash_swf_url : '<?php echo $this->webroot.'plupload/js/plupload.flash.swf' ?>',
                silverlight_xap_url : '<?php echo $this->webroot.'plupload/js/plupload.silverlight.xap' ?>'
	});

	uploader.bind('Init', function(up, params) {
		$('#filelist').html("<div>Bilder ausw&auml;hlen und dann Bilder hochladen nicht vergessen !!</div>");
	});

	$('#uploadfiles').click(function(e) {
		uploader.start();
		e.preventDefault();
	});

	uploader.init();

	uploader.bind('FilesAdded', function(up, files) {            
            $.each(files, function(i, file) {
                    ++queuedImages;
                    $('#filelist').append(                            
                            '<div id="' + file.id + '">' +
                            file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                    '</div>');
            });

            up.refresh(); // Reposition Flash/Silverlight
	});
        
	uploader.bind('UploadProgress', function(up, file) {
            $('#' + file.id + " b").html(file.percent + "%");
	});

	uploader.bind('Error', function(up, err) {
            $('#filelist').append("<div>Error: " + err.code +
                    ", Message: " + err.message +
                    (err.file ? ", File: " + err.file.name : "") +
                    "</div>"
            );
            up.refresh(); // Reposition Flash/Silverlight
	});

	uploader.bind('FileUploaded', function(up, file,response) {
            $('#' + file.id + " b").html("100%");
            var obj = jQuery.parseJSON(response["response"]);
            var tmpdir = "<?php  echo $this->Html->webroot('uploads/tmp'); ?>";
            
            if (addedImages <= queuedImages) {
                $('#RecipeAddForm').append('<input name="data[Image]['+addedImages+'][name]" type="hidden" value="'+obj["result"]["fileName"]+'"/>');
                $('#RecipeAddForm').append('<input name="data[Image]['+addedImages+'][ordernum]" type="hidden" value="'+addedImages+'"/>');                
                $('#images_editor').append('<li class="ui-state-default"><img src="'+tmpdir+'/100x75_'+obj["result"]["fileName"]+'" alt="" width="100px" height="90px" name="pic_'+addedImages+'" /></li>');
                ++addedImages;
            }
            //Uncomment for debugging
            //console.log("AddedImages images total: " + addedImages + " Queued Images total: " + queuedImages + "\n");
            if (addedImages == queuedImages) {
                $('#filelist').empty();
                //Uncomment for debugging
                //console.log($('img[name="pic_0"]').attr('src').replace(tmpdir+'/',''));
                var src = $('img[name="pic_0"]').attr('src').split("_")[1];
                
                $('#recipe_main_pic').html('<img src="'+tmpdir+"/500x300_"+src+'" alt="Title Picture" width="500px" height="300px" >');
                $('#RecipePicture').attr('value',src);
                $( "#images_editor" ).sortable( "refresh" );
            }
	});
//END uploader plupload +++++++++++++++++++++++

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
     
/** Handle some input elements **/
    
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
    
    $("select#RecipeSeverity_edit").change(function(){
        var formElementId = this.id.split("_")[0];
        $('#'+formElementId).attr('value',$(this).attr('value'));
    });
/** END Handle some input elements **/

/** Autocomplete function for categories **/
    var availableTags = [
        <?php if (isset($categories)) {foreach($categories as $category){echo "'$category',";}} ?>
   ];
   function split( val ) {
       return val.split( /;\s*/ );
   }
   function extractLast( term ) {
       return split( term ).pop();
   }
   $( "#CategoryName_edit" )
   // don't navigate away from the field on tab when selecting an item
   .bind( "keydown", function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB &&
        $( this ).data( "ui-autocomplete" ).menu.active ) {
        event.preventDefault();
   }
   })
   .autocomplete({
        minLength: 0,
        source: function( request, response ) {
        // delegate back to autocomplete, but extract the last term
        response( $.ui.autocomplete.filter(
        availableTags, extractLast( request.term ) ) );
        },
        focus: function() {
        // prevent value inserted on focus
        return false;
        },
        select: function( event, ui ) {
        var terms = split( this.value );
        // remove the current input
        terms.pop();
        // add the selected item
        terms.push( ui.item.value );
        // add placeholder to get the comma-and-space at the end
        terms.push( "" );
        this.value = terms.join( "; " );
        return false;
        }
   });
/** END autocomplete function for categories **/
});

</script>

<?php
echo $this->Form->create('Recipe');
echo $this->Form->input('title',array('class'=>'recipeTitle_Input','label'=>false,'value'=>isset($result)?$result['result'][0]['rezept_name']." ".$result['result'][0]['rezept_name2']:'Name deines Rezepts...'));
?>
<div id="recipe_part1" class="row">
    <div id="recipe_main_pic" class="span8"><?php echo isset($remoteImages[0])?$this->Html->image('tmp/500x300_'.$remoteImages[0],array('height'=>'300px','pathPrefix' => CONTENT_URL)):"Dein Titelbild"; ?></div>
        <div class="additional_widget span2"><p>Schwierigkeitsgrad</p>
            <select id="RecipeSeverity_edit">
                <?php foreach (Configure::read('severity_level') as $key=>$severity_level){echo "<option value='$key'>$severity_level</option>";}?>
            </select>
        </div>
        <div class="additional_widget span3">
            <p>Kategorie(n) <small>z.B. Hauptspeise</small></p>
            <input type="text" id="CategoryName_edit" value="<?php if (isset($result)) {foreach ($result['result'][0]['rezept_tags'] as $category) {echo $category."; ";};} ?>">
        </div>
</div>

<div id="picUpload">
    <h3>Bilder hinzuf&uuml;gen</h3>
    <div id="success"></div>
    <div id="error"></div>
    <div id="container">
            <div id="filelist">No runtime found.</div>
            <ul id="images_editor">
                <?php
                    if (isset($remoteImages)) {
                        $index = 0;
                        foreach ($remoteImages as $remoteImage) {
                            echo "<li class='ui-state-default, img-polaroid'>".$this->Html->image('tmp/100x75_'.$remoteImage,array('alt' => "",'width'=>'100px','height'=>'90px','name' => 'pic_'.$index,'pathPrefix' => CONTENT_URL))."</li>";
                            $index++;
                        }
                    }
                ?>
            </ul>
            <div class="clear"></div>
            <br />
            <a id="pickfiles" href="#" class="btn"><i class="icon-picture"></i>&nbsp; Bilder ausw&auml;hlen</a>
            <a id="uploadfiles" href="#" class="btn"><i class="icon-upload"></i>&nbsp; Bilder hochladen</a>
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
$valueIngredients = "<ul><li></li></ul>";
if (isset($result)){
    $valueIngredients = "<ul>";
    foreach ($result['result'][0]['rezept_zutaten'] as $ingredient) {
        $valueIngredients .= "<li>".$ingredient['menge']."&nbsp;".$ingredient['einheit']."&nbsp;".$ingredient['name']."</li>";
    };
    $valueIngredients .= "</ul>";
}
echo $this->Form->input("ingredients", array("rows" => "7","label"=>"","value"=>"$valueIngredients"));
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
echo $this->Form->input('description', array('rows' => '10','label'=>'','value'=>isset($result)?$result['result'][0]['rezept_zubereitung']:""));
?>
</div>
<?php
echo $this->Form->input('picture', array('label'=>'', 'type'=>'hidden', 'value' => isset($remoteImages[0])?"$remoteImages[0]":"" ));
echo $this->Form->input('maincategory', array('label'=>'','type'=>'hidden'));
echo $this->Form->input('severity', array('label'=>'','value'=>'0','type'=>'hidden'));
echo $this->Form->input('Category.name', array('label'=>'','type'=>'hidden'));
?>
<?php
echo $this->Form->submit('Save Recipe', array('class' => 'btn btn-success'));
$index = 0;
if (isset($remoteImages)) {
    foreach ($remoteImages as $img) {
        echo $this->Form->input('Image.'.$index.'.name', array('type' => 'hidden' , 'value' => $img));
        echo $this->Form->input('Image.'.$index.'.ordernum', array('type' => 'hidden', 'value' => $index));
        $index++;
    }
}
echo $this->Form->end();
?>  
<?php
echo $this->Js->writeBuffer(); // Write cached scripts
?>