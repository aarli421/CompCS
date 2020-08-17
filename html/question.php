<?php
require '../templates/helper.php';
require '../templates/header.php';

$sth = $db->prepare("SELECT `testcase_value` FROM questions WHERE `name`=?");
$sth->execute([$_GET['questionName']]);
$passArr = $sth->fetchAll();

$sth = $db->prepare("SELECT MAX(correct_cases) FROM grades WHERE user_id=? AND question_id=?");
$sth->execute([$user_id, 1]);
$max = $sth->fetchAll();

echo $max[0][0];
print_r($max);
?>
<link rel="stylesheet" href="css/question.css">
<link rel="stylesheet" href="css/loader.css">
<script>
    function startUpload(){
        $(function() {
            $("#prompt-center").css("text-align", "center");
            $('#dialogDiv').html("<div id=\"upload_process\" class=\"loader triangle\"> <svg viewBox=\"0 0 86 80\"><polygon points=\"43 8 79 72 7 72\"></polygon></svg></div>");
        });
    }

    function stopUpload(){
        $(function() {
            $('#dialogDiv').html("");
            $("#prompt-center").css("text-align", "center");
        });
    }

    $(function() {
        $("form#fileSubmission").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            startUpload();

            window.scrollTo({top: 0, behavior: 'smooth'});

            $.ajax({
                url: "ajax/upload.php",
                type: 'POST',
                dataType: 'JSON',
                data: formData,
                success: function(data) {
                    stopUpload();

                    console.log(data);

                    if (data.hasOwnProperty('error')) {
                        $("#prompt-center").css("text-align", "left");
                        // console.log(data);
                        $("#dialogDiv").append("<div><span id=\"upload-error\" style=\"color: #993333; font-size: 14px;\">" + data["error"] + "</span></div>")
                    } else {
                        Object.keys(data).forEach(function(k) {
                            if (k == "correct_cases") return;

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
                    $("#file-upload").val("");
                    $("#file-upload").prev('label').text("");
                    $("#file-label").append("<i class=\"fa fa-cloud-upload\"></i> Upload File");
                },
                error: function(data) {
                    stopUpload();
                    $("#upload-error").html("Error happened");
                    $("#file-upload").val("");
                    $("#file-upload").prev('label').text("");
                    $("#file-label").append("<i class=\"fa fa-cloud-upload\"></i> Upload File");
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
<section data-stellar-background-ratio="0.5" class="questionlist" style="padding-bottom: 0px;">
    <div class="container">
        <h1 class="problemtitle" style="margin-bottom: 3px;">Problem: <?php echo $_GET['questionName']; ?></h1>
        <h3 class="problemtitle" style="margin-top: 0px">Points/Case: <?php echo $passArr[0]['testcase_value']; ?></h3>
    </div>

    <center id="prompt-center">
        <div class="outer-container">
            <div id="dialogDiv" class="container outer">
                <div><span class="upload-error" style="color: #993333; font-size: 14px;">You have not submitted anything.</span></div>
            </div>
        </div>
    </center>
</section>
<div class="container">
    <pre id="prompt"></pre>
</div>
<center>
    <div>
        <form id="fileSubmission" method="post" enctype="multipart/form-data">
            <input type="hidden" name="MAX_FILE_SIZE" value="30000"/>
            <input type="hidden" name="questionName" value="<?php echo $_GET['questionName'] ?>" />
            <label id="file-label" for="file-upload" class="section-btn">
                <i class="fa fa-cloud-upload"></i> Upload File
            </label>
            <input id="file-upload" name="fileInput" type="file" style="display:none; margin: 20px;" required>
            <input name="fileSubmit" type="submit" class="section-btn" value="Send File" style="margin: 20px;"/>
        </form>
    </div>
</center>
</section>
<script src="js/question.js"></script>
<?php
require '../templates/footer.php';
?>
