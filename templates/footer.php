<!-- FOOTER -->
<footer id="footer" data-stellar-background-ratio="0.5">
    <div class="container">
        <div class="row">

            <div class="col-md-3 col-sm-8">
                <div class="footer-info">
                    <div class="section-title">
                        <h2 class="wow fadeInUp" data-wow-delay="0.2s">Policy</h2>
                    </div>
                    <address class="wow fadeInUp" data-wow-delay="0.4s">
                        <p>123 nulla a cursus rhoncus,<br> augue sem viverra 10870<br>id ultricies sapien</p>
                    </address>
                </div>
            </div>

            <div class="col-md-3 col-sm-8">
                <div class="footer-info">
                    <div class="section-title">
                        <h2 class="wow fadeInUp" data-wow-delay="0.2s">Reservation</h2>
                    </div>
                    <address class="wow fadeInUp" data-wow-delay="0.4s">
                        <p>090-080-0650 | 090-070-0430</p>
                        <p><a href="mailto:info@company.com">info@company.com</a></p>
                        <p>LINE: eatery247 </p>
                    </address>
                </div>
            </div>

            <div class="col-md-4 col-sm-8">
                <div class="footer-info footer-open-hour">
                    <div class="section-title">
                        <h2 class="wow fadeInUp" data-wow-delay="0.2s">Open Hours</h2>
                    </div>
                    <div class="wow fadeInUp" data-wow-delay="0.4s">
                        <p>Monday: Closed</p>
                        <div>
                            <strong>Tuesday to Friday</strong>
                            <p>7:00 AM - 9:00 PM</p>
                        </div>
                        <div>
                            <strong>Saturday - Sunday</strong>
                            <p>11:00 AM - 10:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-4">
                <ul class="wow fadeInUp social-icon" data-wow-delay="0.4s">
                    <li><a href="#" class="fa fa-facebook-square" attr="facebook icon"></a></li>
                    <li><a href="#" class="fa fa-twitter"></a></li>
                    <li><a href="#" class="fa fa-instagram"></a></li>
                    <li><a href="#" class="fa fa-google"></a></li>
                </ul>

                <div class="wow fadeInUp copyright-text" data-wow-delay="0.8s">
                    <p><br>Copyright &copy; 2018 <br>CompCS</p>

                    <!--   <br><br>Design: <a rel="nofollow" href="http://templatemo.com" target="_parent">TemplateMo</a></p> -->
                </div>
            </div>

        </div>
    </div>
</footer>


<!-- SCRIPTS -->
<script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.stellar.min.js"></script>
<script src="js/wow.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="js/smoothscroll.js"></script>
<script src="js/custom.js"></script>
<script src="js/login-register.js"></script>
<script>
    $(function () {
        $("form#login").submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: "ajax/login.php",
                type: "POST",
                data: formData,
                success: function(data) {
                    if (data == "Success") {
                        console.log("Successfully logged in!");
                    } else {
                        console.log("Error: " + data);
                    }
                },
                error: function(data) {

                }
            });
        });

        $("form#register").submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: "ajax/register.php",
                type: "POST",
                data: formData,
                success: function(data) {
                    if (data == "Success") {
                        console.log("Successfully registered!");
                    } else {
                        console.log("Error: " + data);
                    }
                },
                error: function(data) {

                }
            });
        });
    });
</script>

</body>
</html>