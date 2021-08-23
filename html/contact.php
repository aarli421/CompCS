<?php
require '../templates/helper.php';
require '../vendor/autoload.php';

if (hasValue($_POST['name']) && hasValue($_POST['email']) && hasValue($_POST['subject']) && hasValue($_POST['message'])){
    $handle = fopen('../private/keys.csv', 'r');
    $data = fgetcsv($handle, 5, ',');

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("support@compcs.org", $_POST['name']);
    $email->setSubject($_POST['subject']);
    $email->addTo("compcscodes@gmail.com", "CompCS Administrators");
    $email->addContent(
        "text/html", "From " . $_POST['email'] . "<br>" . $_POST['message']
    );
    $sendgrid = new \SendGrid($data[1]);
    try {
        $response = $sendgrid->send($email);
        if ($response->statusCode() == 202) {

        }
    } catch(Exception $e) {

    }
    redirect("contact");
}

require '../templates/header.php';
?>
    <!-- CONTACT -->
    <section id="contact" data-stellar-background-ratio="0.5">
        <div class="container">
            <div class="row">

                <div class="col-md-12 col-sm-12">

                    <div class="col-md-12 col-sm-12">
                        <div class="section-title wow fadeInUp" data-wow-delay="0.1s" style="padding-top: 50px;">
                            <h2>Contact Us</h2>
                        </div>
                    </div>

                    <!-- CONTACT FORM -->
                    <form action="contact" method="post" class="wow fadeInUp" id="contact-form" role="form" data-wow-delay="0.8s">

                        <!-- IF MAIL SENT SUCCESSFUL  // connect this with custom JS -->
                        <h6 class="text-success">Your message has been sent successfully.</h6>

                        <!-- IF MAIL NOT SENT -->
                        <h6 class="text-danger">E-mail must be valid and message must be longer than 1 character.</h6>

                        <div class="col-md-6 col-sm-6">
                            <input type="text" class="form-control" id="cf-name" name="name" placeholder="Full name" required>
                        </div>

                        <div class="col-md-6 col-sm-6">
                            <input type="email" class="form-control" id="cf-email" name="email" placeholder="Email address" required>
                        </div>

                        <div class="col-md-12 col-sm-12">
                            <input type="text" class="form-control" id="cf-subject" name="subject" placeholder="Subject" required>

                            <textarea class="form-control" rows="6" id="cf-message" name="message"
                                      placeholder="Tell us about a problem or ask a question" required></textarea>

                            <button type="submit" class="form-control" id="cf-submit" name="submit">Send Message</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>
<?php
require '../templates/footer.php';
