<!-- File: /app/View/Recipes/edit.ctp -->
<?php
    echo $this->Html->script('plupload.full'); // Include plupload
    echo $this->Html->script('advanced'); // Include wysihtml5 parser rules
    echo $this->Html->script('wysihtml5-0.3.0.min'); // Include wysihtml5 library
    echo $this->Html->script('jEditable');
    //pr($recipe);
     //pr($categories_drdown);
?>
<script>
$(function() {
/** Handle some input elements **/
    $("#CategoryName_edit").attr('value','<?php echo $categories; ?>');
    $("#CategoryName_edit").keyup(function() {
        var formElementId = this.id.split("_")[0];
        $('#'+formElementId).attr('value',$(this).attr('value'));
        var mainCategory = $(this).attr('value');
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

//Uploader plupload +++++++++++++++++++++++
        var queuedImages = 0;
        var uploadedImages = 0;
        var addedImages = <?php echo count($recipe['Image']); ?>
        
	var uploader = new plupload.Uploader({
		runtimes : 'html5,flash,silverlight,html4',
		browse_button : 'pickfiles',
		container : 'container',
		max_file_size : '10mb',
                chunk_size : '2mb',
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
            var deletedPictures = $('img[name|="pic_-1"]').length;
            //var visiblePictures = addedImages - deletedPictures;
            
            var obj = jQuery.parseJSON(response["response"]);
            var tmpdir = "<?php  echo $this->Html->webroot('uploads/tmp'); ?>";
            
            if (uploadedImages <= queuedImages) {
                $('#RecipeEditForm').append('<input name="data[Image]['+addedImages+'][name]" type="hidden" value="'+obj["result"]["fileName"]+'"/>');
                $('#RecipeEditForm').append('<input name="data[Image]['+addedImages+'][ordernum]" type="hidden" value="'+(addedImages-deletedPictures)+'"/>');                
                $('#images_editor').append('<li class="ui-state-default, img-polaroid"><img src="'+tmpdir+'/100x75_'+obj["result"]["fileName"]+'" alt="" width="100px" height="90px" name="pic_'+(addedImages-deletedPictures)+'" /><button class="btn_delete badge badge-important">Löschen</button></li>');
                //$('.btn_delete').on('click',hallo());
                ++uploadedImages;
                ++addedImages;
            }
            
            console.log("Uploaded images total: " + uploadedImages + " Queued Images total: " + queuedImages + "\n");
            
            if (uploadedImages == queuedImages) {
                $('#filelist').empty();
                console.log("Title picture = " + $('img[name="pic_0"]').attr('src').replace(tmpdir+'/',''));
                var imgPath = $('img[name="pic_0"]').attr('src');
                var src = imgPath.substring(imgPath.lastIndexOf('/')+1,imgPath.length).split("_")[1];
                console.log("Title picture src = " + src);
                if (imgPath.search(tmpdir) != -1 ) {
                    $('#recipe_main_pic').html('<img src="'+tmpdir+"/500x300_"+src+'" alt="Title Picture" width="500px" height="300px" >');
                } else {
                    var recipeImgPath = imgPath.substring(0,imgPath.lastIndexOf('/'));
                    console.log("Title picture already on Server content path = " + recipeImgPath);
                    $('#recipe_main_pic').html('<img src="'+recipeImgPath+"/500x300_"+src+'" alt="Title Picture" width="500px" height="300px" >');
                }
                $('#RecipePicture').attr('value',src);
                $( "#images_editor" ).sortable( "refresh" );
                uploadedImages = 0;
            }
	});
//END uploader plupload +++++++++++++++++++++++

/** Autocomplete function for categories **/
    var availableTags = [
        <?php if (isset($categories_drdown)) {foreach($categories_drdown as $category){echo "'$category',";}} ?>
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
<?php echo $this->Html->Link('Abbrechen', array('action'=>'view',$recipe['Recipe']['id'])) ?>
<h2>Rezept nachsalzen</h2>
<?php
    echo $this->Form->create('Recipe', array('action' => 'edit'));
    echo $this->Form->input('title',array('class'=>'recipeTitle_Input','label'=>false));
?>
<div id="recipe_part1" class="row">
    <div id="recipe_main_pic" class="span8"><?php echo isset($recipe['Image'][0]['name'])?$this->Html->image($recipe['Recipe']['contentkey'].'/500x300_'.$recipe['Image'][0]['name'], array('pathPrefix' => CONTENT_URL,'alt' => $recipe['Image'][0]['titel'])):"Dein Titelbild"; ?></div>
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
<div id="picUpload">
    <h3>Bilder hinzuf&uuml;gen</h3>
    <div id="success"></div>
    <div id="error"></div>
    <div id="container">
            <div id="filelist">No runtime found.</div>
            <ul id="images_editor">
                <?php
                    $index = 0;
                    foreach ($recipe['Image'] as $img) {
                        echo "<li class='ui-state-default, img-polaroid'>".$this->Html->image($recipe['Recipe']['contentkey'].'/100x75_'.$img['name'],array('alt' => $img['titel'],'pathPrefix' => CONTENT_URL,'width'=>'100px','height'=>'90px','name' => 'pic_'.$index))."<button class='btn_delete badge badge-important' id='".$img['id']."'>Löschen</button></li>";
                        $index++;
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
    echo $this->Form->input('picture', array('label'=>'','type' => 'hidden'));
    echo $this->Form->input('maincategory', array('label'=>'','type' => 'hidden'));
    echo $this->Form->input('severity', array('type' => 'hidden'));
    echo $this->Form->input('Category.name', array('value' => $categories, 'label'=>'Categories','type' => 'hidden'));
    echo $this->Form->input('id', array('type' => 'hidden'));
    echo $this->Form->input('contentkey', array('type' => 'hidden'));
    $index = 0;
    foreach ($recipe['Image'] as $img) {
        echo $this->Form->input('Image.'.$index.'.id', array('type' => 'hidden'));
        echo $this->Form->input('Image.'.$index.'.name', array('type' => 'hidden'));
        echo $this->Form->input('Image.'.$index.'.ordernum', array('type' => 'hidden'));
        $index++;
    }
    echo $this->Form->submit('Save Recipe', array('class' => 'btn btn-success'));
    echo $this->Form->end();
?>
<br>