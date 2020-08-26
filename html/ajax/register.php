<?php
require '../../templates/helper.php';
require '../../vendor/autoload.php';

if (hasValue($_POST['signUpUsername']) && hasValue($_POST['signUpPassword']) && hasValue($_POST['signUpEmail']) && hasValue($_POST['tos'])) {
    $mail = $_POST['signUpEmail'];
    $username = $_POST['signUpUsername'];
    $password = $_POST['signUpPassword'];
    $hashedPw = hash('sha256', $_POST['signUpPassword']);
    $hash = md5(rand(0, 10000));

    // A note on the regex pattern used below (from the PHP source). It looks like there is some copyright on it of Michael Rushton. As stated: "Feel free to use and redistribute this code. But please keep this copyright notice."
    $usernameReg = "/^[A-Za-z0-9]*$/iD";
    $passwordReg = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?]{8,}$/iD";
    $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

    $sth = $db->prepare("START TRANSACTION");
    $sth->execute();

    $sth = $db->prepare("SELECT EXISTS(SELECT * FROM `users` WHERE `username`=? OR `email`=?) LIMIT 1");
    $sth->execute([$username, $mail]);
    $passArr = $sth->fetchAll();

    if ($passArr[0][0] == 0) {
//        echo "Passed ";
        if ((preg_match($pattern, $mail) === 1) && (preg_match($usernameReg, $username) === 1) && (preg_match($passwordReg, $password) === 1)) {
            $msg = `sudo $scriptsDirectory/createUser.sh $username $password`;

            if (!hasValue($msg)) {
                $host = $_SERVER["HTTP_HOST"];
                $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
                $verLink = 'http://' . $host . '/verify?email=' . $mail . '&hash=' . $hash;

                $handle = fopen('../../private/keys.csv', 'r');
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
                            
                            <table bgcolor=\"#FFFFFF\" cellpadding=\"0\" cellspacing=\"0\" class=\"nl-container\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; min-width: 320px; Margin: 0 auto; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #FFFFFF; width: 100%;\" valign=\"top\" width=\"100%\">
                            <tbody>
                            <tr style=\"vertical-align: top;\" valign=\"top\">
                            <td style=\"word-break: break-word; vertical-align: top;\" valign=\"top\">
                            
                            <div style=\"background-color:transparent;\">
                            <div class=\"block-grid\" style=\"Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;\">
                            <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:transparent;\">
                            
                            
                            <div class=\"col num12\" style=\"min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;\">
                            <div style=\"width:100% !important;\">
                            
                            <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                            
                            <table cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;\" valign=\"top\" width=\"100%\">
                            <tr style=\"vertical-align: top;\" valign=\"top\">
                            <td align=\"center\" style=\"word-break: break-word; vertical-align: top; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px; text-align: center;\" valign=\"top\">
                            
                            
                            <table cellpadding=\"0\" cellspacing=\"0\" class=\"icons-inner\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; display: inline-block; margin-right: -4px; padding-left: NaNpx; padding-right: NaNpx;\" valign=\"top\">
                            
                            <tr style=\"vertical-align: top;\" valign=\"top\">
                            <td align=\"center\" style=\"word-break: break-word; vertical-align: top; text-align: center; padding-top: 5px; padding-bottom: 5px; padding-left: 5px; padding-right: 5px;\" valign=\"top\"><a href=\"https://compcs.codes/\"><img align=\"center\" class=\"icon\" height=\"128\" src=\"https://www.compcs.codes/images/ccc-logo.svg\" style=\"border:0;\" width=\"null\"/></a></td>
                            </tr>
                            </table>
                            </td>
                            </tr>
                            </table>
                            
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
                            <p style=\"text-align: left; line-height: 1.2; word-break: break-word; font-size: 26px; mso-line-height-alt: 31px; margin: 0;\"><span style=\"font-size: 26px;\"><strong>You're almost ready to get started!</strong></span></p>
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
                            <p style=\"text-align: left; line-height: 1.2; word-break: break-word; font-size: 16px; mso-line-height-alt: 19px; margin: 0;\"><span style=\"font-size: 16px;\">You have recently created an account on compcs.codes with a username of <strong>$username.</strong></span></p>
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
                            
                            <a href=\"$verLink\" style=\"-webkit-text-size-adjust: none; text-decoration: none; display: inline-block; color: #ffffff; background-color: #ce3232; border-radius: 4px; -webkit-border-radius: 4px; -moz-border-radius: 4px; width: auto; width: auto; border-top: 1px solid #ce3232; border-right: 1px solid #ce3232; border-bottom: 1px solid #ce3232; border-left: 1px solid #ce3232; padding-top: 5px; padding-bottom: 5px; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; text-align: center; mso-border-alt: none; word-break: keep-all;\" target=\"_blank\"><span style=\"padding-left:30px;padding-right:35px;font-size:12px;display:inline-block;\"><span style=\"line-height: 24px; word-break: break-word;\">Verify</span></span></a>
                            
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
                            <p style=\"font-size: 16px; line-height: 1.2; font-family: Montserrat, 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; word-break: break-word; mso-line-height-alt: 19px; margin: 0;\"><span style=\"font-size: 16px;\">Verifying your email ensures that you can access and manage your </span><span style=\"font-size: 16px; background-color: transparent;\">account, and receive critical notifications.</span></p>
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
                            <p style=\"font-size: 16px; line-height: 1.2; font-family: Montserrat, 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; word-break: break-word; mso-line-height-alt: 19px; margin: 0;\"><span style=\"font-size: 16px;\">If you did not create an account, no further action is needed.</span></p>
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
                $email->setSubject("Verify your CompCS Account");
                $email->addTo($mail, "CompCS Codes User");
                $email->addContent(
                    "text/html", $content
                );
                $sendgrid = new \SendGrid($data[1]);
                try {
                    $response = $sendgrid->send($email);
                    if ($response->statusCode() == 202) {
                        $sql = "
                    INSERT INTO `users` (`username`, `password`, `email`, `hash`)
                    SELECT * FROM (SELECT ? AS `username`, ? AS `password`, ? AS `email`, ? AS `hash`) AS temp 
                    WHERE NOT EXISTS (SELECT * FROM `users` WHERE `username`=? OR `email`=?) LIMIT 1;";
                        $sth = $db->prepare($sql);
                        $sth->execute([$username, $hashedPw, $mail, $hash, $username, $mail]);

                        $sth = $db->prepare("COMMIT");
                        $sth->execute();

//                        `mkdir ../users/$username`;

                        echo "Success";
                    } else {
                        echo "Mail was unable to send.";
                    }
                } catch (Exception $e) {
                    echo 'Caught exception: ' . $e->getMessage() . "\n";
                }
            } else {
                echo "The username already exists";
            }
        } else {
            echo "Invalid email.";
        }
    } else {
        echo "The username / email already exists.";
    }
} else {
    echo "Form not filled completely.";
}
?>
