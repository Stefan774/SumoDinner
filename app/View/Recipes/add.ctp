<!-- File: /app/View/Recipes/add.ctp -->
<?php 
    echo $this->Html->script('jquery'); // Include jQuery library
    echo $this->Html->script('jquery-ui-1.9.2.custom'); // Include jQuery UI-library
    echo $this->Html->script('plupload.full'); // Include plupload
?>
<h1>Add Recipe</h1>

<style>
#images_editor { list-style-type: none; margin: 0; padding: 0; width: 550px; }
#images_editor li { margin: 3px 3px 3px 3px; padding: 1px; float: left; width: 100px; height: 90px; font-size: 4em; text-align: center; }
 html>body #images_editor li { height: 1.5em; line-height: 1.2em; }
.ui-state-highlight { height: 1.5em; line-height: 1.2em; }
</style>

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
            $('#success').html(obj["result"]["fileName"]);
            if (addedImages <= queuedImages) {
                $('#RecipeAddForm').append('<input name="data[Image]['+addedImages+'][name]" type="hidden" value="'+obj["result"]["fileName"]+'"/>');
                $('#RecipeAddForm').append('<input name="data[Image]['+addedImages+'][ordernum]" type="hidden" value="'+addedImages+'"/>');
                tmpdir = "<?php  echo $this->Html->webroot('uploads/tmp'); ?>";
                $('#images_editor').append('<li class="ui-state-default"><img src="'+tmpdir+'/'+obj["result"]["fileName"]+'" alt="" width="100px" height="90px" name="pic_'+addedImages+'" /></li>');
                ++addedImages;
            }
            if (addedImages == queuedImages) {
                $( "#images_editor" ).sortable( "refresh" );
            }
	});
    /** Handle sortable elements **/
    
        $( "#images_editor" ).sortable({
            placeholder: "ui-state-highlight"
        });
        
        $( "#images_editor" ).disableSelection();

        $("#images_editor").bind( "sortupdate", function(event, ui) {
            //alert("changed")
            $('ul#images_editor > li').each(function(index) {
                    $('#error').show()
                    //$('#error').append('Index'+index + ' '+ $('img',this).attr('name') + '<br>')	
                    var attr_helper = $('img',this).attr('name');
                    var debug_helper = $('img',this).attr('src');
                    debug_helper_split = debug_helper.split("/");
                    debug_helper = debug_helper_split[(debug_helper_split.length -1)];
                    //$('#error').append("Picture " + attr_helper + "Changed To")
                    attr_split = attr_helper.split("_");
                    //$('#error').append('Moved Image'+debug_helper +' Number: '+attr_split[1]+" To Position : " + index+'<br>');
                    attr_helper = (attr_split[0]+'_'+ index);
                    $('img',this).attr('name', attr_helper);
                    
                    var input_img_name = $('input[value|="'+debug_helper+'"]').attr('name');
                    
                    input_img_number_array = input_img_name.split("[");
                    //input_img_number_array = input_img_number.split("[");
                    //alert([input_img_number_array[2]]);
                    input_img_number = input_img_number_array[2].substr(0,input_img_number_array[2].length-1);
                    
                    //alert(input_img_number);
                    $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').removeAttr('value');
                    $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').attr('value',index);
                    //$('#error').show()
                    //$('#error').append('Index'+index + '=' + attr_helper + '<br>')
            });
        });

    /** END Handle sortable elements **/
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
<ul id="images_editor">   
</ul>
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