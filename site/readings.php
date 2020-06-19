<?php
    require_once 'header.php';
    require_once 'dbFunctions.php';
?>
        <div class="main readings">
            <h1>The Dramatic Bible Readings</h1>
            <div class="subMain sectGeneral">
                <!-- a href="https://youtu.be/cuxOBLFUABE">Psalm 22</a -->
                <!-- <iframe src="https://www.youtube.com/embed/cuxOBLFUABE" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe> -->
                <!-- <iframe src="https://www.youtube.com/embed/LyPfQ-in8-g" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe> -->
                <div class="infoBox">
                  <p>This is still very much a work in progress (if you want to help
                    out then please check out the &lsquo;<a href="support.php">Support
                      this ministry</a>&rsquo; page).</p><!-- I do plan to develop the code to:</p>
                  <ul>
                    <li>Enable a checklist so you can &lsquo;mark as read&rsquo;</li>
                    <li>provide alternative arrangements of the readings for variety</li>
                    <li>Any other requests?</li>
                  </ul -->
                </div>
<?php
  echo videoList('R');
?>
            </div>
        </div>
<?php
  require_once 'footer.php';
?>
