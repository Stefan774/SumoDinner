<?php
Class Rating extends AppModel 
{
    var $name = 'Rating';
    
    public $validate = array(
        'rating' => array(         
            'rule'    => array('range', 0, 6),
            'message' => 'Only ratings between 1 and 5 are possible',
         )
    );
}
?>