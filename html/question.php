<?php
require("../templates/header.php");
?>
<script>
    function startUpload(){
        $(function() {
            $('#upload_process').css("display", "inline");
            $('#dialogDiv').html('');
        });
    }

    function stopUpload(){
        $(function() {
            $('#upload_process').css("display", "none");
        });
    }

    $(function() {
        $("form#fileSubmission").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            startUpload();

            $.ajax({
                url: "ajax/upload.php",
                type: 'POST',
                data: formData,
                success: function(data) {
                    $("#dialogDiv").html(data);
                    stopUpload();
                    $("#fileInput").val("");
                },
                error: function(data) {
                    $("#dialogDiv").html("Error happened");
                    stopUpload();
                    $("#fileInput").val("");
                },
                contentType: false,
                processData: false
            });
        });

        $.ajax({
            url: "questions/<?php echo $_GET['questionName']; ?>/prompt.txt",
            success: function(data) {
                $("#prompt").html(data);
            }
        });
    });
</script>
<section data-stellar-background-ratio="0.5" class = "questionlist">
    <div><h1 class="problemtitle" style="margin-bottom: 3px;">Problem: Gymnastics <h3 class="problemtitle" style="margin-top: 0px">Points/Case: 20</h3></h1></div>

    <center>
        <div class="outer">
            <div><span style="color: #993333; font-size: 20px;">Red Text</span></div>
            <div class="loader triangle">
                <svg viewBox="0 0 86 80">
                    <polygon points="43 8 79 72 7 72"></polygon>
                </svg>
            </div>
            <div class="trial-result trial-status-yes"><div class="res-symbol">*</div><div class="trial-num">1</div><div class="info"><span class="msize">152ms</span></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">X</div><div class="trial-num">2</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">E</div><div class="trial-num">3</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">!</div><div class="trial-num">4</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">T</div><div class="trial-num">5</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">M</div><div class="trial-num">6</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">!</div><div class="trial-num">7</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">T</div><div class="trial-num">8</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">M</div><div class="trial-num">9</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">!</div><div class="trial-num">10</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">T</div><div class="trial-num">11</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">M</div><div class="trial-num">12</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">!</div><div class="trial-num">13</div><div class="info"></div></div>
            <div class="trial-result trial-status-no"><div class="res-X">T</div><div class="trial-num">14</div><div class="info"></div></div>
            <div class="trial-result trial-status-yes"><div class="res-symbol">*</div><div class="trial-num">15</div><div class="info"><span>1345ms</span></div></div>
            <div class="trial-result trial-status-yes"><div class="res-symbol">*</div><div class="trial-num">16</div><div class="info"><span>1345ms</span></div></div>
            <div class="trial-result trial-status-yes"><div class="res-symbol">*</div><div class="trial-num">17</div><div class="info"><span>1345ms</span></div></div>
        </div>
    </center>
<div class="container">
    <pre id="prompt"></pre>
</div>
<center>
    <div>
        <form id="fileSubmission" method="post" enctype="multipart/form-data">
            <input type="hidden" name="MAX_FILE_SIZE" value="30000"/>
            <input type="hidden" name="questionName" value="<?php echo $_GET['questionName'] ?>" />
            <label for="file-upload" class="section-btn">
                <i class="fa fa-cloud-upload"></i> Upload File
            </label>
            <input id="file-upload" name='fileInput' type="file" style="display:none; margin: 20px;">
            <input name="fileSubmit" type="submit" class="section-btn" value="Send File" style="margin: 20px;"/>
        </form>
    </div>
</center>
</section>
<!--<p id="upload_process" style="display: none">Loading <img src="images/loader.gif" /></p>-->
<!--<p id="result"></p>-->
<!--<div id="dialogDiv"></div>-->
<!--<form id="fileSubmission" method="post" enctype="multipart/form-data">-->
<!--    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />-->
<!--    <input type="hidden" name="questionName" value="--><?php //echo $_GET['questionName'] ?><!--" />-->
<!--    Send this file: <input id="fileInput" name="fileInput" type="file" />-->
<!--    <input name="fileSubmit" type="submit" value="Send File" />-->
<!--</form>-->
<?php
require("../templates/footer.php");
?>
