<?php
  require_once 'header.php';
  require_once 'dbFunctions.php';
  require_once 'planFunctions.php';
  require_once 'search.php';

  $tBaseQuery = basicPassageQuery()
?>
  <div class="main plan">
    <h1>The Bible Reading Plan</h1>
    <div class="subMain sectGeneral">
      <p>Ooh isn&apos;t it pretty? Well I like it anyway! More seriously the
        colours can help you (just a little bit) if you have coloured ribbons as
        dividers in your Bible to quickly get to the relevant section.</p>
      <br />
<?php
echo planTable($tSortOrder);
?>
    </div>
  </div>
<?php
  require_once 'footer.php';
?>
