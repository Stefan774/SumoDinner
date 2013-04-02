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

