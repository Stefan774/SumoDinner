<?php /**
 * Category Behavior class file.
 *
 * Model Behavior to support Categories.
 *
 * @filesource
 * @package    app
 * @subpackage    models.behaviors
 */
 
/**
 * Add Category behavior to a model.
 * 
 */
class CategoryBehavior extends ModelBehavior {
    /**
     * Initiate behaviour for the model using specified settings.
     *
     * @param object $model    Model using the behaviour
     * @param array $settings    Settings to override for model.
     *
     * @access public
     */
    function setup(&$model, $settings = array()) {

    
        $default = array( 'table_label' => 'Categories', 'Category_label' => 'Category', 'separator' => ',');
        
        if (!isset($this->settings[$model->name])) {
            $this->settings[$model->name] = $default;
        }
        
    $this->settings[$model->name] = array_merge($this->settings[$model->name], ife(is_array($settings), $settings, array()));

    }
    
    /**
     * Run before a model is saved, used to set up Category for model.
     *
     * @param object $model    Model about to be saved.
     *
     * @access public
     * @since 1.0
     */
    function beforeSave(&$model) {
    // Define the new Category model
    $Category =& new Category;
        if ($model->hasField($this->settings[$model->name]['table_label']) 
        && $Category->hasField($this->settings[$model->name]['Category_label'])) {


        // Parse out all of the 
        $Category_list = $this->_parseCategory($model->data[$model->name][$this->settings[$model->name]['table_label']], $this->settings[$model->name]);
        $Category_info = array(); // New Category array to store Category id and names from db
        foreach($Category_list as $t) {
            if ($res = $Category->find($this->settings[$model->name]['Category_label'] . " LIKE '" . $t . "'")) {
                $Category_info[] = $res['Category']['id'];
            } else {
                $Category->save(array('id'=>'',$this->settings[$model->name]['Category_label']=>$t));
                $Category_info[] = sprintf($Category->getLastInsertID());
            }
            unset($res);
        }

        // This prepares the linking table data...
        $model->data['Category']['Category'] = $Category_info;
        // This formats the Categories field before save...
        $model->data[$model->name][$this->settings[$model->name]['table_label']] = implode(', ', $Category_list);
    }
    return true;
    }


    /**
     * Parse the Category string and return a properly formatted array
     *
     * @param string $string    String.
     * @param array $settings    Settings to use (looks for 'separator' and 'length')
     *
     * @return string    Category for given string.
     *
     * @access private
     */
    function _parseCategory($string, $settings) {
        $string = strtolower($string);
       
        $string = preg_replace('/[^a-z0-9' . $settings['separator'] . ' ]/i', '', $string);
        $string = preg_replace('/' . $settings['separator'] . '[' . $settings['separator'] . ']*/', $settings['separator'], $string);

    $string_array = preg_split('/' . $settings['separator'] . '/', $string);
    $return_array = array();

    foreach($string_array as $t) {
        $t = ucwords(trim($t));
        if (strlen($t)>0) {
            $return_array[] = $t;
        }
    }
    
        return $return_array;
    }
}

?> 
