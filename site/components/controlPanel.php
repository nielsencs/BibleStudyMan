<link rel="stylesheet" href="styles/controlPanel.css">
<script type="text/javascript" src="scripts/controlPanel.js"></script>
<div id="controlPanel">
    <button id="panelToggle" title="Toggle search panel">&lt;</button>
    <form name="searchForm" id="searchForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');?>" method="get" onsubmit="showWait();">
        <?php include_once 'searchForm.php'; ?>
        <?php include_once 'intWords.php'; ?>
    </form>
</div>
