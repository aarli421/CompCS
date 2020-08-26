<?php
require '../templates/helper.php';
require '../vendor/autoload.php';
require '../templates/header.php';
?>
<div class="background">
    <section data-stellar-background-ratio="0.5">
        <div class="container">
            <div class="form">
                <div class="form-panel one">
<?php
if (hasValue($_GET['hash']) && hasValue($_GET['email'])) {
    $sth = $db->prepare("SELECT `user_id` FROM `users` WHERE `email`=? AND `hash`=? AND `change_password`=1 AND `active`=1");
    $sth->execute([$_GET['email'], $_GET['hash']]);
    $user = $sth->fetchAll();

    if (empty($user)) {
        redirect("forgot");
    }
?>
    <script>
        $('#newPassword, #newCPassword').on('keyup', function () {
            if ($("#newPassword").val() == $("#newCPassword").val()) {
                $("#newCPassword")[0].setCustomValidity('');
            } else {
                $("#newCPassword")[0].setCustomValidity('The passwords do not match.');
            }
        });
    </script>

    <div class="form-header">
        <h1>Change Password</h1>
    </div>
    <div class="form-content">
        <form id="change" action="forgot" method="post">
            <div class="form-group"><label for="newPassword">New Password</label><input type="password" id="newPassword" name="newPassword" required="required" pattern="<?php echo $passwordReg; ?>" /></div>
            <div class="form-group"><label for="newCPassword">Confirm Password</label><input type="password" id="newCPassword" name="newCPassword" required="required" pattern="<?php echo $passwordReg; ?>" /></div>
            <input type="hidden" name="userID" value="<?php echo $user[0]['user_id']; ?>">
            <input type="hidden" name="hash" value="<?php echo $_GET['hash']; ?>">
            <div class="form-group"><button form="change" type="submit">Change Password</button></div>
        </form>
    </div>
    <h6 id="changeSuccess" class="text-success"></h6>
    <h6 id="changeError" class="text-danger"></h6>
<?php
} else {
?>
    <div class="form-header">
        <h1>Forgot Password</h1>
    </div>
    <div class="form-content">
        <form id="forgot" action="forgot" method="post">
            <div class="form-group"><label for="forgotEmail">Email</label><input type="email" id="forgotEmail" name="forgotEmail" required="required" /></div>
            <div class="form-group"><button form="forgot" type="submit">Request Change</button></div>
        </form>
    </div>
    <h6 id="forgotSuccess" class="text-success"></h6>
    <h6 id="forgotError" class="text-danger"></h6>
<?php
}
?>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
if (hasValue($_POST['forgotEmail'])) {
    $sth = $db->prepare("SELECT `change_password` FROM `users` WHERE `email`=? AND `active`=1");
    $sth->execute([$_POST['forgotEmail']]);
    $user = $sth->fetchAll();

    if (empty($user)) {
        redirect("forgot");
        exit();
    }

    if ($user[0]['change_password'] == 1) {
        ?>
        <script>
            $("#forgotSuccess").html("");
            $("#forgotError").html("You have already request to change your password. If this is a mistake please contact us at compcscodes@gmail.com.");
        </script>
        <?php
    } else {

        $hash = md5(rand(0, 10000));

        $host = $_SERVER["HTTP_HOST"];
        $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
        $forgotLink = 'https://' . $host . '/forgot?email=' . $_POST['forgotEmail'] . '&hash=' . $hash;

        $handle = fopen('../private/keys.csv', 'r');
        $data = fgetcsv($handle, 5, ',');

        $content = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional //EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
                    
                    <html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:v=\"urn:schemas-microsoft-com:vml\">
                    <head>
                    
                    <meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\"/>
                    <meta content=\"width=device-width\" name=\"viewport\"/>
                    
                    <meta content=\"IE=edge\" http-equiv=\"X-UA-Compatible\"/>
                    
                    <title></title>
                    
                    <link href=\"https://fonts.googleapis.com/css?family=Montserrat\" rel=\"stylesheet\" type=\"text/css\"/>
                    
                    <style type=\"text/css\">
                            body {
                                margin: 0;
                                padding: 0;
                            }
                    
                            table,
                            td,
                            tr {
                                vertical-align: top;
                                border-collapse: collapse;
                            }
                    
                            * {
                                line-height: inherit;
                            }
                    
                            a[x-apple-data-detectors=true] {
                                color: inherit !important;
                                text-decoration: none !important;
                            }
                        </style>
                    <style id=\"media-query\" type=\"text/css\">
                            @media (max-width: 520px) {
                    
                                .block-grid,
                                .col {
                                    min-width: 320px !important;
                                    max-width: 100% !important;
                                    display: block !important;
                                }
                    
                                .block-grid {
                                    width: 100% !important;
                                }
                    
                                .col {
                                    width: 100% !important;
                                }
                    
                                .col>div {
                                    margin: 0 auto;
                                }
                    
                                img.fullwidth,
                                img.fullwidthOnMobile {
                                    max-width: 100% !important;
                                }
                    
                                .no-stack .col {
                                    min-width: 0 !important;
                                    display: table-cell !important;
                                }
                    
                                .no-stack.two-up .col {
                                    width: 50% !important;
                                }
                    
                                .no-stack .col.num4 {
                                    width: 33% !important;
                                }
                    
                                .no-stack .col.num8 {
                                    width: 66% !important;
                                }
                    
                                .no-stack .col.num4 {
                                    width: 33% !important;
                                }
                    
                                .no-stack .col.num3 {
                                    width: 25% !important;
                                }
                    
                                .no-stack .col.num6 {
                                    width: 50% !important;
                                }
                    
                                .no-stack .col.num9 {
                                    width: 75% !important;
                                }
                    
                                .video-block {
                                    max-width: none !important;
                                }
                    
                                .mobile_hide {
                                    min-height: 0px;
                                    max-height: 0px;
                                    max-width: 0px;
                                    display: none;
                                    overflow: hidden;
                                    font-size: 0px;
                                }
                    
                                .desktop_hide {
                                    display: block !important;
                                    max-height: none !important;
                                }
                            }
                        </style>
                    <style id=\"icon-media-query\" type=\"text/css\">
                            @media (max-width: 520px) {
                                .icons-inner {
                                    text-align: center;
                                }
                    
                                .icons-inner td {
                                    margin: 0 auto;
                                }
                            }
                        </style>
                    </head>
                    <body class=\"clean-body\" style=\"margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #FFFFFF;\">
                    
                    
                    <div style=\"background-color:transparent;\">
                    <div class=\"block-grid\" style=\"Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;\">
                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:transparent;\">
                    
                    
                    <div class=\"col num12\" style=\"min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;\">
                    <div style=\"width:100% !important;\">
                    
                    <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                    
                    
                    <div style=\"color:#555555;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;\">
                    <div style=\"line-height: 1.2; font-size: 12px; color: #555555; font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; mso-line-height-alt: 14px;\">
                    <p style=\"text-align: left; line-height: 1.2; word-break: break-word; font-size: 26px; mso-line-height-alt: 31px; margin: 0;\"><span style=\"font-size: 26px;\"><strong>Hi,</strong></span></p>
                    </div>
                    </div>
                    
                    
                    </div>
                    
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    <div style=\"background-color:transparent;\">
                    <div class=\"block-grid\" style=\"Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;\">
                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:transparent;\">
                    
                    
                    <div class=\"col num12\" style=\"min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;\">
                    <div style=\"width:100% !important;\">
                    
                    <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                    
                    
                    <div style=\"color:#555555;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;\">
                    <div style=\"line-height: 1.2; font-size: 12px; color: #555555; font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; mso-line-height-alt: 14px;\">
                    <p style=\"text-align: left; line-height: 1.2; word-break: break-word; font-size: 16px; mso-line-height-alt: 19px; margin: 0;\"><span style=\"font-size: 16px;\">To reset your CCC password, click the button.</span></p>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    <div style=\"background-color:transparent;\">
                    <div class=\"block-grid\" style=\"Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;\">
                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:transparent;\">
                    
                    
                    <div class=\"col num12\" style=\"min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;\">
                    <div style=\"width:100% !important;\">
                    <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                    <div align=\"center\" class=\"button-container\" style=\"padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;\">
                    <a href=\"$forgotLink\" style=\"-webkit-text-size-adjust: none; text-decoration: none; display: inline-block; color: #ffffff; background-color: #ce3232; border-radius: 4px; -webkit-border-radius: 4px; -moz-border-radius: 4px; width: auto; width: auto; border-top: 1px solid #ce3232; border-right: 1px solid #ce3232; border-bottom: 1px solid #ce3232; border-left: 1px solid #ce3232; padding-top: 5px; padding-bottom: 5px; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; text-align: center; mso-border-alt: none; word-break: keep-all;\" target=\"_blank\"><span style=\"padding-left:30px;padding-right:35px;font-size:12px;display:inline-block;\"><span style=\"line-height: 24px; word-break: break-word;\">Reset</span></span></a>
                    
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    <div style=\"background-color:transparent;\">
                    <div class=\"block-grid\" style=\"Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;\">
                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:transparent;\">
                    <div class=\"col num12\" style=\"min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;\">
                    <div style=\"width:100% !important;\">
                    <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                    <div style=\"color:#555555;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;\">
                    <div style=\"line-height: 1.2; font-size: 12px; font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; color: #555555; mso-line-height-alt: 14px;\">
                    <p style=\"font-size: 16px; line-height: 1.2; font-family: Montserrat, 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; word-break: break-word; mso-line-height-alt: 19px; margin: 0;\"><span style=\"font-size: 16px;\">If you have previously changed a password, only the button in this email will work.</span></p>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    <div style=\"background-color:transparent;\">
                    <div class=\"block-grid\" style=\"Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;\">
                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:transparent;\">
                    <div class=\"col num12\" style=\"min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;\">
                    <div style=\"width:100% !important;\">
                    <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                    
                    <div style=\"color:#555555;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;\">
                    <div style=\"line-height: 1.2; font-size: 12px; font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; color: #555555; mso-line-height-alt: 14px;\">
                    <p style=\"font-size: 14px; line-height: 1.2; font-family: Montserrat, 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; word-break: break-word; mso-line-height-alt: 17px; margin: 0;\"><strong><span style=\"font-size: 16px;\">If this wasn't you:</span></strong></p>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    <div style=\"background-color:transparent;\">
                    <div class=\"block-grid\" style=\"Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;\">
                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:transparent;\">
                    <div class=\"col num12\" style=\"min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;\">
                    <div style=\"width:100% !important;\">
                    <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                    <div style=\"color:#555555;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;\">
                    <div style=\"line-height: 1.2; font-size: 12px; color: #555555; font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; mso-line-height-alt: 14px;\">
                    <p style=\"line-height: 1.2; word-break: break-word; font-size: 16px; mso-line-height-alt: 19px; margin: 0;\"><span style=\"font-size: 16px;\">Your CCC account may have been compromised and you should reset your password now.</span></p>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    <div style=\"background-color:transparent;\">
                    <div class=\"block-grid\" style=\"Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;\">
                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:transparent;\">
                    <div class=\"col num12\" style=\"min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;\">
                    <div style=\"width:100% !important;\">
                    <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                    <div style=\"color:#555555;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;\">
                    <div style=\"line-height: 1.2; font-size: 12px; color: #555555; font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; mso-line-height-alt: 14px;\">
                    <p style=\"line-height: 1.2; word-break: break-word; font-size: 16px; mso-line-height-alt: 19px; margin: 0;\"><span style=\"font-size: 16px;\">Happy Programming,</span></p>
                    <p style=\"line-height: 1.2; word-break: break-word; font-size: 16px; mso-line-height-alt: 19px; margin: 0;\"><span style=\"font-size: 16px;\">CCC Team</span></p>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </td>
                    </tr>
                    </tbody>
                    </table>
                    </body>
                    </html>";

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("noreply@compcs.codes", "CompCS");
        $email->setSubject("Password Change CompCS Account");
        $email->addTo($_POST['forgotEmail'], "CompCS Codes User");
        $email->addContent(
            "text/html", $content
        );
        $sendgrid = new \SendGrid($data[1]);
        try {
            $response = $sendgrid->send($email);

            $sth = $db->prepare("UPDATE `users` SET `hash`=?, `change_password`=1 WHERE `email`=? AND `active`=1");
            $sth->execute([$hash, $_POST['forgotEmail']]);
            ?>
            <script>
                $("#forgotSuccess").html("Successfully sent email. Please check your email for further inquiry.");
                $("#forgotError").html("");
            </script>
            <?php
        } catch (Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
            ?>
            <script>
                $("#forgotSuccess").html("");
                $("#forgotError").html("Server error!");
            </script>
            <?php
        }
    }
}

if (hasValue($_POST['newPassword']) && hasValue($_POST['newCPassword']) && hasValue($_POST['userID']) && hasValue($_POST['hash'])) {
    $sth = $db->prepare("SELECT `user_id` FROM `users` WHERE `user_id`=? AND `hash`=? AND `change_password`=1 AND `active`=1");
    $sth->execute([$_POST['userID'], $_POST['hash']]);
    $passArr = $sth->fetchAll();

    if (empty($passArr)) {
        redirect("forgot");
        exit();
    }

    $passwordRegPHP = "/" . $passwordReg . "/iD";

    if (preg_match($passwordRegPHP, $_POST['newPassword']) === 1) {
        $password = hash('sha256', $_POST['newPassword']);

        $sth = $db->prepare("UPDATE `users` SET `password`=?, `change_password`=0 WHERE `user_id`=? AND `active`=1");
        $sth->execute([$password, $_POST['userID']]);
        redirect("login");
        exit();
    }
}

require '../templates/footer.php';
