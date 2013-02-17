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

$(function() {
    
/** Handle sortable elements for image editor **/
    //Effects for img remove, run the currently selected effect
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
    function removeDOMObject(object) {
        this.remove();
    }

    //Initialize the jquery sortable event and assign the highlight css style when sort event occours
    $( "#images_editor" ).sortable({
        placeholder: "ui-state-highlight, img-polaroid"
    });
    
    //Disable the selection 
    $( "#images_editor" ).disableSelection();

    //Handle sortupdate events, this happens when a sort event finished
    $("#images_editor").bind( "sortupdate", function(event, ui) {
        //Loop through all images an change sort order number
        $('ul#images_editor > li').each(function(index) {	
                // setup some helpers for the sort event
                var attr_helper = $('img',this).attr('name');
                var img_name_helper = $('img',this).attr('src');
                
                img_name_helper_split = img_name_helper.split("/");
                img_name_helper = img_name_helper_split[(img_name_helper_split.length -1)];
                
                attr_split = attr_helper.split("_");
                attr_helper = (attr_split[0]+'_'+ index);
                
                //Set new sort index number to the image name attr
                $('img',this).attr('name', attr_helper);
                
                var input_img_name = $('input[value|="'+img_name_helper+'"]').attr('name');
                
                input_img_number_array = input_img_name.split("[");
                input_img_number = input_img_number_array[2].substr(0,input_img_number_array[2].length-1);
                
                //Set new sort index number to cakephp input fields
                $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').removeAttr('value');
                $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').attr('value',index);
        });
    });
    
    $(".btn_delete").click(function() {
        
        var imgListObject = $(this).parent();
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
                
            if (imgOrderNum > lastOrderNum) {
                $(this).children('img').attr('name', "pic_"+(imgOrderNum -1));                
                $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').removeAttr('value');
                $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').attr('value',(imgOrderNum -1));
            }
            if (imgOrderNum == -1) {
                $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').removeAttr('value');
                $('input[name|="data[Image]['+input_img_number+'][ordernum]"]').attr('value',-1);
            }
        }); 
        runHideEffect(imgListObject,'highlight');
    });
/** END handle sortable elements for image editor **/
    
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
});