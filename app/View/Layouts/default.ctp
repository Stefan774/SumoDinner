<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

$cakeDescription = __d('SumoDinner', 'SumoDinner: Dine like a Sumo');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $cakeDescription ?>:
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');

		//echo $this->Html->css('cake.generic');
                //echo $this->Html->css('wysihtml5'); 
                echo $this->Html->css('bootstrap');
                echo $this->Html->css('jquery-ui-1.9.2.custom');  
                echo $this->Html->css('customSumo');              
                               
                echo $this->Html->script('jquery'); // Include jQuery library
                echo $this->Html->script('jquery-ui-1.9.2.custom.min'); // Include jQuery UI-library    
                echo $this->Html->script('bootstrap'); // Include Bootstrap js for affix Navi
                echo $this->Html->script('sumo'); // Include sumo specific .js

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
    <div class="navbar">
        <div class="navbar-inner">
            <?php echo $this->Html->link('SumoDinner',array('controller'=>'Recipes','action'=>'index'),array('class'=>'brand')); ?>
            <ul class="nav">
                <li class="active"><a href="#">Home</a></li>
                <li><a href="#">Link</a></li>
                <li><a href="#">Link</a></li>
            </ul>
            <form class="navbar-search pull-right">
                <input type="text" class="search-query" placeholder="Search">
            </form>
        </div>
    </div>
    <div  class="container">
            <div id="content">

                    <?php echo $this->Session->flash(); ?>

                    <?php echo $this->fetch('content'); ?>
            </div>
    </div>
    <?php echo $this->element('sql_dump'); ?>
</body>
</html>
