<!-- File: /app/View/Recipes/add.ctp -->
<?php 
echo $this->Html->script('jquery'); // Include jQuery library
echo $this->Html->script('jquery-ui-1.8.16.custom.min'); // Include jQuery library 
echo $this->Html->script('plupload.full'); // Include plupload
?>
<h1>Add Recipe</h1>
<script type="text/javascript">
var uploadCounter = 0;
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
/**
* Configure pluploader
*/
$(function() {
    /*Step 3 Upload Pictures and sort them */
    $( "#sortable" ).sortable({
            revert: true
    });
    $( "ul, li" ).disableSelection();
    $("#sortable").bind( "sortupdate", function(event, ui) {
            //alert("changed")
            $('ul#sortable > li').each(function(index) {
                    // $('#error').show()
                    // $('#error').append('Index'+index + ' '+ $('img',this).attr('name') + '<br>')	
                    var new_index = "";
                    var img = $('img',this).attr('id');
                    var img_index = img.split("_");
                    var old_index = $('img',this).attr('name');
                    // $('#error').append("Picture " + attr_helper + "Changed To")
                    
                    old_split = old_index.split("_");
                    new_index = (old_split[0]+'_'+ index);
                    //alert(attr_helper);
                    $('img',this).attr('name', new_index);
                    $('#Image' + img_index[1] + 'orderNum').val(index);
                    
                    //$('#error').show()
                   //$('#error').append('old_index = ' + old_index + ' :: new_index = ' + new_index + ' :: img :: ' + img + '<br>')
            });
    });
    /*End 3 Step */
   
   //$( "#dialog" ).dialog();

	var uploader = new plupload.Uploader({
		runtimes : 'html5,silverlight,flash,html4',
		browse_button : 'pickfiles',
		container : 'container',
		max_file_size : '10mb',
		url : '<?php echo $this->Html->Url(array("controller"=>"recipes","action"=>"addImages"),true); ?>',
                
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"}
		],
                
		//resize : {width : 320, height : 240, quality : 90},
                
                flash_swf_url : '<?php echo $this->webroot.'plupload/js/plupload.flash.swf' ?>',
                silverlight_xap_url : '<?php echo $this->webroot.'plupload/js/plupload.silverlight.xap' ?>',
                multipart_params : {"uploaddir": ""}
	});

	uploader.bind('Init', function(up, params) {
		$('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
	});

	$('#uploadfiles').click(function(e) {
		uploader.start();
		e.preventDefault();
	});

	uploader.init();

	uploader.bind('FilesAdded', function(up, files) {
		$.each(files, function(i, file) {
			$('#filelist').append(
				'<div id="' + file.id + '">' +
				file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
			'</div>');
		});

		up.refresh(); // Reposition Flash/Silverlight
	});
        uploader.bind('FileUploaded', function(up, file, response) {
            //alert(response);           
            $('#success').html(dump(response));
            var obj = jQuery.parseJSON(response.response);
            up.settings.multipart_params.uploaddir = obj.uploaddir;
            //$('#success').html(obj.uploaddir + 'UploadCounter = ' + uploadCounter + 'FileName');            
            $('#RecipeAddForm').append('<input type="hidden" name="data[Image][' + uploadCounter + '][name]" value="' + obj.filename + '" id="Image' + uploadCounter + 'Name"/>');
            $('#RecipeAddForm').append('<input type="hidden" name="data[Image][' + uploadCounter + '][orderNum]" value="' + uploadCounter + '" id="Image' + uploadCounter + 'orderNum"/>');
            var tempImg = obj.uploaddir + '/resized_' + obj.filename;
            //tempImg = tempImg.replace("\\","/");
            //alert(tempImg);
            $('#sortable').append('<li class="ui-state-default"><img src="/NCooking/' + tempImg + '" name="pic_' + uploadCounter + '" id="pic_' + uploadCounter + '" /></li>');            
            uploadCounter ++;
            $('#RecipeContentURL').val(obj.uploaddir);
            //'<input type="hidden" name="data[Image][0][name]" value="Test.img" id="Image0Name"/>'
           // $('#error').html(dump(file));
            //$('#uploaddir').val(obj.uploaddir);
            //alert(obj);
            //alert(obj.result);
            //alert(json.jsonrpc); 
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

	uploader.bind('FileUploaded', function(up, file) {
		$('#' + file.id + " b").html("100%");
	});
});

</script>
<h3>Custom example</h3>
<div id="success"></div>
<div id="error"></div>
<div id="container">
	<div id="filelist">No runtime found.</div>
	<br />
	<a id="pickfiles" href="#">[Select files]</a>
	<a id="uploadfiles" href="#">[Upload files]</a>
</div>
<div id="img_placeholder">
    <ul id="sortable">
    </ul>
    <div class="ui-helper-clearfix">&nbsp;</div>
</div>
<?php
echo $this->Form->create('Recipe');
echo $this->Form->input('title');
echo $this->Form->input('description', array('rows' => '3'));
echo $this->Form->input('ingredients', array('rows' => '2'));
echo $this->Form->input('severity');
echo $this->Form->hidden('contentURL');
echo $this->Form->input('Category.name', array('label'=>'Categories'));
#echo $this->Form->hidden('Image.0.name', array('value'=>'Test.img'));
#echo $this->Form->hidden('uploaddir');
#echo $this->Form->innput('file', array('type' => 'file'));
#echo $this->Form->input('categories');
echo $this->Form->end('Save Recipe');
?>
<?php
echo $this->Js->writeBuffer(); // Write cached scripts
?>