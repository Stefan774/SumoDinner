<?php
Class Recipe extends AppModel {
    public $name = 'Recipe';
    public $hasAndBelongsToMany = array(
        'Category' =>
            array(
                'className'              => 'Category',
                'joinTable'              => 'categories_recipes',
                'foreignKey'             => 'recipe_id',
                'associationForeignKey'  => 'category_id',
                'unique'                 => false
            )
    );
    public $hasMany = array(
        'CategoryRecipe' => array(
            'className'     => 'CategoryRecipe',
            'foreignKey'    => 'recipe_id'
        ),
         'Image' => array(
            'className'     => 'Image',
            'foreignKey'    => 'recipe_id',
            'order'         => 'Image.ordernum',
            'dependent'     => true
        ),
        'Rating' => array(
            'className'     => 'Rating',
            'foreignKey'    => 'recipe_id',
            'dependent'     => true
        )
    );
    public $validate = array(
        'title' => array(
            'rule' => 'notEmpty'
        ),
        'ingredients' => array(
            'rule' => 'notEmpty'
        ),
        'description' => array(
            'rule' => 'notEmpty'
        ),
        'severity' => array(
            'rule-1' => array (
                'rule'    => array('range', -1, 6),
                'message' => 'Please enter a number between 1 and 5',
             ),
            'rule-2' => array (
                'rule'    => 'alphaNumeric',
                'message' => 'Please enter a natural number',
            )
        )
    );
}
?>
