<!-- File: /app/View/Recipes/add.ctp -->
<?php 
echo $this->Html->script('jquery'); // Include jQuery library
echo $this->Html->script('plupload.full'); // Include plupload
?>
<h1>Add Recipe</h1>
<script type="text/javascript">
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
                
		resize : {width : 320, height : 240, quality : 90},
                
                flash_swf_url : '<?php echo $this->webroot.'plupload/js/plupload.flash.swf' ?>',
                silverlight_xap_url : '<?php echo $this->webroot.'plupload/js/plupload.silverlight.xap' ?>',
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
            $('#success').html(obj["result"]["fileName"]);
            $('#RecipeAddForm').append('<input name="data[Image][][name]" type="hidden" value="'+obj["result"]["fileName"]+'"/>')
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
<?php
echo $this->Form->create('Recipe');
echo $this->Form->input('title');
echo $this->Form->input('description', array('rows' => '3'));
echo $this->Form->input('ingredients', array('rows' => '2'));
echo $this->Form->input('severity');
echo $this->Form->input('Category.name', array('label'=>'Categories'));
echo $this->Form->end('Save Recipe');
?>
<?php
echo $this->Js->writeBuffer(); // Write cached scripts
?>