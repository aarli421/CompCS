<?php
require '../templates/header.php';

?>
<img id="background_image" src="media/compcs.svg" style="width: 200px; height: 200px; padding: 100px 100px;"/>
<!--    <canvas class="background"></canvas>-->
<script>
    $(function() {
       // Particles.init({
       //     selector: '.background',
       //     color: '#9eff76',
       //     maxParticles: 400,
       //     connectParticles: true,
       //     responsive: [{
       //             breakpoint: 768,
       //             options: {
       //                 maxParticles: 200
       //             }
       //         }, {
       //             breakpoint: 375,
       //             options: {
       //                 maxParticles: 50
       //             }
       //         }
       //     ]
       // });

        anime({
            targets: '#background_image',
            rotate: '2turn',
            duration: 3000,
            loop: true
        });
    });
</script>
<?php
require("../templates/footer.php");
?>
