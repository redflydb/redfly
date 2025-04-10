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
To receive announcements about important <?= HTML_REDFLY_LOGO ?> news, follow us on Bluesky
<span style="vertical-align:-8px;">
   <a href="https://bsky.app/profile/redfly-database.bsky.social"
      class="bluesky-follow-button"
      target="_blank"
      data-show-count="false"
      data-size="large">@redfly-database.bsky.social</a>
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
