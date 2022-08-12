<?php
    require_once 'header.php';
?>
        <div class="main home">
            <h1>Support Me</h1>
            <div class="subMain sectGeneral">
              <div class='intro'>
                <a href="about.php"><img class="mugshot" src="images/CarlSmall.png" alt="mugshot of Carl"/></a>
                <p>Thanks for checking here! This has been and continues to be a
                massive undertaking for me and I could do with all the help I can
                get. Supporting me spiritually by praying that I'd stay on track
                would be wonderful. I would welcome volunteers to help with developing
                the site or some of the (often quite tedious!) &lsquo;grunt&rsquo;
                work on The Bible text.</p>
                <p>If you'd be happy to sign up for the monthly newsletter (see 
                the form at the bottom of this page) I'll keep in touch with you once
                a month (no more, I promise!) via email to let you know my progress.</p>
                <p class="signature">Carl Nielsen, June 2020</p>
                <p>Obviously money is useful too! You can make one off or regular
                donations via PayPal:</p>
              </div>
              <div class="center">
<?php
    require_once 'PayPalButton.php';
?>
              </div>
              <p>Or there's <a href="https://www.patreon.com/BibleStudyMan" target="_blank">Patreon</a>
              where you can become a Patron with a regular contribution of a
              dollar (or whatever is the equivalent in your currency) each
              month. You can of course go for higher amounts too! To find out
              more or to sign up check out
              <a href="https://www.patreon.com/BibleStudyMan" target="_blank">Patreon.com/BibleStudyMan</a>
              </p>
              <div class="center">
              <a href="https://www.patreon.com/bePatron?u=10742455"
              data-patreon-widget-type="become-patron-button" target="_blank">Become
              a Patron!</a><script async src="https://c6.patreon.com/becomePatronButton.bundle.js"></script>
              </div>
<?php
    require_once 'costs.php';
?>
              <p>Thanks again for visiting this page and any support you can
                offer will be very gratefully received.</p>
<?php
    require_once 'newsletterForm.php';
?>
            </div>
        </div>
<?php
    require_once 'footer.php';
?>
