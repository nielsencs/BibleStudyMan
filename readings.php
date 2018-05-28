<?php
    require_once 'header.php';
    require_once '../sqlCon.php';
    require_once 'dbFunctions.php';
?>
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
                          $tOutput .= '<div class="media">';
                          $tOutput .= $row["mediaName"];
                          $tOutput .= '<br />';
                          $tOutput .= ' <a href="https://soundcloud.com/user-442938965/';
                          $tOutput .= $row["audioURL"];
                          $tOutput .= '">Play on SoundCloud.com';
                          $tOutput .= '</a> ';
                          $tOutput .= '<br />';

                          // $tOutput .= '<iframe width="100%" height="300" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/
                          // $tOutput .= $row["audioTrack"];
                          // $tOutput .= '&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true&visual=true"></iframe>';
                          $tOutput .= '<iframe width="100%" height="166" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/';
                          $tOutput .= $row["audioTrack"];
                          $tOutput .= '&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true"></iframe>';
                          $tOutput .= '<br />';

                          $tOutput .= '<a href="https://youtu.be/';
                          $tOutput .= $row["videoURL"];
                          $tOutput .= '">Play on YouTube.com';
                          $tOutput .= '</a>';
                          $tOutput .= '<br />';


                          $tOutput .= '<iframe src="https://www.youtube.com/embed/';
                          $tOutput .= $row["videoURL"];
                          $tOutput .= '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
                          $tOutput .= '</div>';
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
