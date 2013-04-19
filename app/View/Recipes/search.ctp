<?php //pr($loresult); ?>
<?php //pr($result); ?>
<!-- local search results -->
<h3>Lokale Ergebnise</h3>
<?php if (count($loresult) > 0 ) { ?>
    <?php foreach ($loresult as $item) : { $recipe = $item[0]; ?>
    <div class="row search-grid">
        <a href="<?php echo $this->Html->Url(array("controller"=>"recipes","action"=>"view", $recipe['docid']),false); ?>" class="result">
            <div class="span2"><?php echo $this->Html->image($recipe['contentkey'].'/100x75_'.$recipe['picture'],array('pathPrefix' => CONTENT_URL,)); ?></div>
            <div class="span8"><?php echo $recipe['title']; ?></div>
        </a>
    </div>
    <?php } endforeach; ?>
<?php } ?>
<h3>Gefunden auf Chefkoch.de</h3>
<!-- search results from ck -->
<?php if (count($result) > 0 ) { ?>
    <?php foreach ($result['result'] as $item) : if (isset($item['RezeptShowID']) && isset($item['RezeptBild']) && isset($item['RezeptName'])) {?>
    <div class="row search-grid">
        <a href="<?php echo $this->Html->Url(array("controller"=>"recipes","action"=>"getRecipeCKJson", $item['RezeptShowID']),false); ?>" class="result">
            <div class="span2"><img src="<?php echo $item['RezeptBild']; ?>" ></div>
            <div class="span8"><?php echo $item['RezeptName']."&nbsp;".$item['RezeptName2'] ?></div>
        </a>
    </div>
    <?php } endforeach; ?>
<?php } ?>

