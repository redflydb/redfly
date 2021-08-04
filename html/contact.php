<?php
require_once(dirname(__FILE__) . "/../config/linker.php");
$previousData = ( isset($_SESSION["CONTACT_US_DATA"]) ? $_SESSION["CONTACT_US_DATA"] : NULL );
include("header.php");
?>
<script type="text/javascript" src="https://www.google.com/recaptcha/api.js"></script>
<script type="text/javascript" src="/js/form_verification.js"></script>
<div class="heading_c"> Contact Us </div>
<p>
We welcome queries, corrections, suggestions, and submission of new data.<br>
Please use the form below to contact the <?= HTML_REDFLY_LOGO ?> team.
</p>
<p>
To receive announcements about important <?= HTML_REDFLY_LOGO ?> news, follow us on Twitter
<span style="vertical-align:-8px;">
   <a href="https://twitter.com/REDfly_database"
      class="twitter-follow-button"
      data-show-count="false"
      data-size="large">@REDfly_database</a>
    <script>!function(d,s,id){
        var js, fjs = d.getElementsByTagName(s)[0], p=/^http:/.test(d.location) ? 'http':'https';
        if (!d.getElementById(id)) {
            js = d.createElement(s);
            js.id = id;
            js.src = p+'://platform.twitter.com/widgets.js';
            fjs.parentNode.insertBefore(js, fjs);
        }
        } (document, 'script', 'twitter-wjs');
    </script>
</span>
</p>
<!-- Begin Contact Form -->
<form id="email-form"
      class="email"
      action="mailer.php"
      method="POST">
  <p align="center"><strong>Email the <?= HTML_REDFLY_LOGO ?> team</strong></p>
  <br><br>
  <p>Name</p>
  <br>
  <input class="shadow" 
         type="text"
         name="userName" required>
  <br><br>
  <p>Your Email</p>
  <br>
  <input class="shadow"
         type="email"
         name="userEmail" required>
  <br><br>
  <p>Message</p>
  <br>
  <textarea class="shadow"
            form="email-form"
            name="emailBody"
            cols="60"
            rows="8"
            required style="max-width: 720px; max-height: 600px; overflow: scroll"></textarea>
  <br><br>
   <div class="g-recaptcha"
        data-sitekey=<?= $GLOBALS["options"]->recaptcha->public_key ?>
        data-callback="enableBtn"
        data-expired-callback="disableBtn"></div>
    <input class="email-btn shadow disable"
           id="submit-btn"
           type="submit"
           value="Send Email"
           disabled>
</form>
<!-- End Contact Form -->
<?php include("footer.php"); ?>
