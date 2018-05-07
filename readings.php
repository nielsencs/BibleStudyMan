<?php
    require_once 'header.php';
    require_once '../sqlCon.php';
    require_once 'dbFunctions.php';
?>
        <div class='intro'>
            <p>These are available as mp3s and videos and are intended to bring
            a more dynamic realisation of the scriptures by a full-on
            'snot-and-tears' reading.</p>
        </div>
        <div class="main readings">
            <h1>The Dramatic Bible Readings</h1>
            <div class="subMain sectGeneral">
                <!-- a href="https://youtu.be/cuxOBLFUABE">Psalm 22</a -->
                <!-- <iframe src="https://www.youtube.com/embed/cuxOBLFUABE" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe> -->
                <!-- <iframe src="https://www.youtube.com/embed/LyPfQ-in8-g" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe> -->
                <?php
                echo videoList();

                function videoList()
                {
                    $tOutput = '';
                    $tQuery = '';
                    global $link;
                    $tQuery .= 'SELECT * FROM media;';

                    $result = doQuery($link, $tQuery);

                    if (mysqli_num_rows($result) == 0) {
                        $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '"';
                    } else {
                        while ($row = mysqli_fetch_assoc($result)) {
                          $tOutput .= $row["mediaName"];
                          $tOutput .= ' <a href="';
                          $tOutput .= $row["audioURL"];
                          $tOutput .= '">audio';
                          $tOutput .= '</a>';

                          $tOutput .= '<a href="https://youtu.be/';
                          $tOutput .= $row["videoURL"];
                          $tOutput .= '">video';
                          $tOutput .= '</a>';

                          $tOutput .= '<iframe src="https://www.youtube.com/embed/';
                          $tOutput .= $row["videoURL"];
                          $tOutput .= '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
                        }
                        mysqli_free_result($result);
                        return $tOutput;
                    }
                }
                ?>
            </div>
        </div>
<?php
  mysqli_close($link);
  require_once 'footer.php';
?>
