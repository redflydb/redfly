<?php
require_once(dirname(__FILE__) . "/../config/linker.php");
header("refresh: 2; url=contact.php");
?>
<head>
    <style>
         @font-face {
            font-family: 'Open Sans';
            src: url('../font/OpenSans-Regular.woff2') format('woff2'),
                 url('../font/OpenSans-Regular.woff') format('woff'),
                 url('../font/OpenSans-Regular.ttf') format('truetype');
        }
        .message {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            margin: auto;
            box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            color: white;
            font-size: 40px;
        }
        .success {
            width: 300px;
            height: 50px;
            background-color: #4CAF50;
        }
        .error {
            width: 560px;
            height: 100px;
            background-color: #f44336;
        }
    </style>
</head>
<?php
// If the captcha is validated successfully, the mail is sent with the 
// inputted credentials to redflyteam@gmail.com.
if ( ! isset($_POST["userName"]) ) {
    exit("Username not provided");
}
if ( ! isset($_POST["userEmail"]) ) {
    exit("Email address not provided");
}
if ( ! isset($_POST["g-recaptcha-response"]) ) {
    exit("No response from Google Captcha");
}
// Validate CAPTCHA
if ( isset($_POST["g-recaptcha-response"]) ) {
    // Information for API request
    $secret = $GLOBALS["options"]->recaptcha->private_key;
    $responseKey = $_POST["g-recaptcha-response"];
    $remoteIPAddress = $_SERVER["REMOTE_ADDR"];
    // Verify the CAPTCHA of the user using an API request and retreiving the data
    $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . 
        $secret . "&response=" . $responseKey . "&remoteip=" . $remoteIPAddress;
    // Echo potential errors with API request
    $data = file_get_contents($url);
    if ( $data === false ) {
        $err = error_get_last();
        exit(sprintf(
            "Error validating recaptcha: %s",
            $err["message"]
        ));
    }
    $result = json_decode($data);
    if ( $result === null ) {
        exit(sprintf(
            "Error decoding recaptcha response: %s",
            json_last_error_msg()
        ));
    }
    if ( $result->success ) {
        // Mail client instantiation
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 587;
        $mail->SMTPSecure = "tls";
        $mail->SMTPAuth = true;
        $mail->AuthType = "XOAUTH2";
        $mail->setOAuth(
            new PHPMailer\PHPMailer\OAuth([
                "provider"     => new League\OAuth2\Client\Provider\Google([
                    "clientId"     => $GLOBALS["options"]->email->gmail_client_id,
                    "clientSecret" => $GLOBALS["options"]->email->gmail_client_secret
                ]),
                "clientId"     => $GLOBALS["options"]->email->gmail_client_id,
                "clientSecret" => $GLOBALS["options"]->email->gmail_client_secret,
                "refreshToken" => $GLOBALS["options"]->email->gmail_refresh_token,
                "userName"     => $GLOBALS["options"]->email->gmail_address
            ])
        );
        $mail->CharSet = "utf-8";
        // Mail contents
        $mail->Subject = "REDfly: Question from " . $_POST["userName"];
        $mail->Body = $_POST["emailBody"];
        $mail->setFrom(
            $_POST["userEmail"],
            $_POST["userName"]
        );
        $mail->addReplyTo(
            $_POST["userEmail"],
            $_POST["userName"]
        );
        $mail->addAddress(
            $GLOBALS["options"]->email->gmail_address,
            "REDfly Team"
        );
        // PHPMailer::send() returns true if mail is staged for delivery, false on failure
        if ( $mail->send() ) {
            echo "<div class=\"message success\"><p>Email Sent! &#10003;</p></div>";
        } else {
            echo "<div class=\"message error\"><p>Email failed to send<br>Please try again</p></div>";
        }
    } else {
        echo "<div class=\"message error\"><p>CAPTCHA could not be validated<br>Please try again</p></div>";
    }
}
