<?php
Class Category extends AppModel {
    public $name = 'Category';
    public $hasAndBelongsToMany = array(
        'Recipe' =>
            array(
                'className'              => 'Recipe',
                'joinTable'              => 'categories_recipes',
                'foreignKey'             => 'category_id',
                'associationForeignKey'  => 'recipe_id',
                'unique'                 => false
            )
    );
    public $validate = array(
        'name' => array(
            'rule' => 'notEmpty',
            'message' => 'Please provide a category'
        )
    );
}
?>
