<?php
  require_once 'header.php';
  require_once '../sqlCon.php';
  require_once 'dbFunctions.php';

  $timestamp = time();
  $month = date("n", $timestamp);
  $day = date("j", $timestamp);

  $tBaseQuery = basicPassageQuery()

  /*
  $month = filter_input(INPUT_GET, 'book');
  $day = filter_input(INPUT_GET, 'chapter');
  $tVerse = filter_input(INPUT_GET, 'verse');
  $tSearch = filter_input(INPUT_GET, 'search');

  $bGotRequest = strlen($tBook . $tChapter. $tVerse . $tSearch)>0;
  */
?>
  <div class="main plan">
    <h1>The Bible Reading Plan</h1>
    <div class="subMain sectGeneral">
<?php
echo planTable(true);

function planTable($bAllColumns){
  $tOutput = '';
  $tQuery = '';
  global $link;
  $tQuery .= 'SELECT *, DATE_FORMAT(planDate, "%b %e") as planDateFormatted';
  //$tQuery .= ', DATE_FORMAT(planDate, '<a href="plan.php?month=%c&day=%e">') as search';
  $tQuery .= ', DATE_FORMAT(planDate, "%c") as searchMonth';
  $tQuery .= ', DATE_FORMAT(planDate, "%e") as searchDay';
  $tQuery .= ' FROM plan';
  $tQuery .= ' ORDER BY planDate ASC';

  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) == 0) {
    $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '"';
  } else {
    $tOutput .= '<table>';
    $tOutput .= '<tr>';
    $tOutput .= '<th>Date</th>';
    if($bAllColumns){
      // $tOutput .= '<th colspan="3">Torah</th>';
      // $tOutput .= '<th colspan="3">Nevi&rsquo;im</th>';
      // $tOutput .= '<th colspan="3">K&rsquo;tuvim</th>';
      // $tOutput .= '<th colspan="3">New Covenant</th>';
      $tOutput .= '<th colspan="3">Section 1</th>';
      $tOutput .= '<th colspan="3">Section 2</th>';
      $tOutput .= '<th colspan="3">Section 3</th>';
      $tOutput .= '<th colspan="3">Section 4</th>';
    }
    while($row = mysqli_fetch_assoc($result)) {
      if ($row['planDateFormatted'] != 'Feb 29'){
        if($row['sectionCode'] == '1TOR'){
          $tOutput .= '</tr>';
          $tOutput .= '<tr>';
          $tOutput .= '<td><a href="plan.php?month=' . $row['searchMonth'];
          //$tOutput .= '<td>';
          $tOutput .= '&day=' . $row['searchDay'] . '">';
          $tOutput .= $row['planDateFormatted'] . '</a>';
        }
        $tOutput .= '</td>';
        if($bAllColumns){
          $tOutput .= '<td>';
          $tOutput .= $row["bookCode"];
          $tOutput .= '</td><td>';
          $tOutput .= $row["passageStart"];
          $tOutput .= '</td><td>';
          $tOutput .= $row["passageEnd"];
        }
      }
    }
    $tOutput .= '</tr></table>';
  }
  mysqli_free_result($result);
  return $tOutput;
}
?>
    </p>
    </div>
  </div>
<?php
  mysqli_close($link);
  require_once 'footer.php';
?>
