<?php
require '../templates/header.php';
?>
<link rel="stylesheet" href="css/question.css">
<link rel="stylesheet" href="css/loader.css">
<script>
    function startUpload(){
        $(function() {
            $('#dialogDiv').html("<div id=\"upload_process\" class=\"loader triangle\"> <svg viewBox=\"0 0 86 80\"><polygon points=\"43 8 79 72 7 72\"></polygon></svg></div>");
        });
    }

    function stopUpload(){
        $(function() {
            $('#dialogDiv').html("");
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
                dataType: 'JSON',
                data: formData,
                success: function(data) {
                    stopUpload();

                    if (data.hasOwnProperty('error')) {
                        $("#dialogDiv").append("<div><span id=\"upload-error\" style=\"color: #993333; font-size: 20px;\">" + data + "</span></div>")
                    } else {
                        Object.keys(data).forEach(function(k) {
                            var symbol = data[k]["symbol"];
                            if (symbol == "*") {
                                var time = Math.round(data[k]["time"] * 1000);
                                $("#dialogDiv").append("<div class=\"trial-result trial-status-yes\"><div class=\"res-symbol\">*</div><div class=\"trial-num\">" + k + "</div><div class=\"info\"><span class=\"msize\">" + time + "ms</span></div></div>");
                            } else {
                                $("#dialogDiv").append("<div class=\"trial-result trial-status-no\"><div class=\"res-X\">" + symbol + "</div><div class=\"trial-num\">" + k + "</div><div class=\"info\"></div></div>");
                            }
                        });
                    }
                    // $("#dialogDiv").html(data);
                    $("#fileInput").val("");
                },
                error: function(data) {
                    stopUpload();
                    $("#upload-error").html("Error happened");
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
        <div id="dialogDiv" class="outer">
            <div><span id="upload-error" style="color: #993333; font-size: 20px;">You have not submitted anything.</span></div>
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
            <input id="file-upload" name="fileInput" type="file" style="display:none; margin: 20px;">
            <input name="fileSubmit" type="submit" class="section-btn" value="Send File" style="margin: 20px;"/>
        </form>
    </div>
</center>
</section>
<script src="js/question.js"></script>
<?php
require '../templates/footer.php';
?>
