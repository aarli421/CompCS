<?php
require '../templates/helper.php';

$sth = $db->prepare("SELECT `question_id`, `prompt`, `testcase_value`, `unlock_value`, `testcases`, `contest_id` FROM questions WHERE `name`=?");
$sth->execute([$_GET['questionName']]);
$passArr = $sth->fetchAll();

$sth = $db->prepare("SELECT `points` FROM users WHERE `user_id`=?");
$sth->execute([$user_id]);
$points = $sth->fetchAll();

if (!isset($_SESSION['user'])) {
    redirect("login");
    exit();
}

if (empty($passArr)) {
    redirect("home");
    exit();
}

if ($points[0]['points'] < $passArr[0]['unlock_value']) {
    redirect("home");
    exit();
}

if (hasValue($_SESSION['contest'])) {
    if ($passArr[0]['contest_id'] != 0 && $passArr[0]['contest_id'] != $_SESSION['contest']) {
        redirect("home");
        exit();
    }
} else {
    if ($passArr[0]['contest_id'] != 0) {
        redirect("home");
        exit();
    }
}

require '../templates/header.php';

$sth = $db->prepare("SELECT `output_json` FROM `grades` WHERE `user_id`=? AND `question_id`=? ORDER BY `grade_id` DESC LIMIT 1;");
$sth->execute([$user_id, $passArr[0]['question_id']]);
$output = $sth->fetchAll();
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
    
    function parseJSON(output) {
        // console.log(output);

        if (output.hasOwnProperty('error')) {
            $("#prompt-center").css("text-align", "left");
            // console.log(output);
            $("#dialogDiv").append("<div><span id=\"upload-error\" style=\"color: #993333; font-size: 14px;\">" + output["error"] + "</span></div>")
        } else {
            Object.keys(output).forEach(function(k) {
                if (k == "correct_cases") return;

                // console.log(k);

                var symbol = output[k]["symbol"];
                if (symbol == "*") {
                    var time = Math.round(output[k]["time"] * 1000);
                    $("#dialogDiv").append("<div class=\"trial-result trial-status-yes\"><div class=\"res-symbol\">*</div><div class=\"trial-num\">" + k + "</div><div class=\"info\"><span class=\"msize\">" + time + "ms</span></div></div>");
                    // console.log(time);
                } else {
                    $("#dialogDiv").append("<div class=\"trial-result trial-status-no\"><div class=\"res-X\">" + symbol + "</div><div class=\"trial-num\">" + k + "</div><div class=\"info\"></div></div>");
                }
            });
        }
        // $("#dialogDiv").html(output);
        $("#file-upload").val("");
        $("#file-upload").prev('label').text("");
        $("#file-label").append("<i class=\"fa fa-cloud-upload\"></i> Upload File");
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
                    parseJSON(data);
                },
                error: function(data) {
                    stopUpload();
                    $("#upload-error").html("Server error");
                    $("#file-upload").val("");
                    $("#file-upload").prev('label').text("");
                    $("#file-label").append("<i class=\"fa fa-cloud-upload\"></i> Upload File");
                },
                contentType: false,
                processData: false
            });
        });
    });
</script>
<section data-stellar-background-ratio="0.5" class="questionlist" style="padding-bottom: 0px;">
    <div class="container">
        <h1 class="problemtitle" style="margin-bottom: 3px;">Problem: <?php echo $_GET['questionName']; ?></h1>

        <?php
        if (hasValue($_SESSION['contest'])) {
            $sth = $db->prepare("SELECT `start`, `end` FROM tries WHERE `user_id`=? AND `contest_id`=?");
            $sth->execute([$user_id, $_SESSION['contest']]);
            $try = $sth->fetchAll();
            ?>
            <h3 id="countdown" class="problemtitle" style="margin-top: 0px;">Updating...</h3>
            <script>
                var countDownDate = new Date(<?php echo strtotime($try[0]['end']); ?> * 1000).getTime();
            </script>
            <script src="js/countdown.js"></script>
            <?php
        }
        ?>

        <h3 class="problemtitle" style="margin-top: 0px">Testcases: <?php echo $passArr[0]['testcases']; ?></h3>
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
    <p id="prompt"><?php echo $passArr[0]['prompt']; ?></p>
</div>
<center>
    <div class="submission">
        <form id="fileSubmission" method="post" enctype="multipart/form-data">
            <input type="hidden" name="MAX_FILE_SIZE" value="30000"/>
            <input type="hidden" name="questionName" value="<?php echo $_GET['questionName']; ?>" />
            <label id="file-label" for="file-upload" class="section-btn">
                <i class="fa fa-cloud-upload"></i> Upload File
            </label>
            <input id="file-upload" name="fileInput" type="file" style="display:none; margin: 20px;" required>
            <input name="fileSubmit" type="submit" class="section-btn" value="Send File" style="margin: 20px;"/>
        </form>
    </div>
</center>
</section>
<section class="footnote">
    <div class="container">
        <div class="row">
            <div>
                <p>Note: Submission times can vary, meaning your submission could be on time for one try but be out of time on another try. Moreover,
                    submission answers may also differ while running your program multiple times if you access out of bounds memory slots, use uninitialized
                    variables etc. If any issues regarding uncertainty in results occur, be sure to look into those issues. Also, it is good practice
                    to make sure your program works every time, not just because of some serendipitous occasion. Lastly, our grading server is not top
                    quality due to limited funds, so timing might be different from official websites such as USACO, but it should still be close enough.</p>
                <p>Submissions are governed as follows:</p>
                <ul>
                    <li><p>C++ | g++ 7.5.0 | 30 second compilation time | 2 second submission time</p></li>
                    <li><p>Java | OpenJDK 8 | 30 second compilation time | 4 second submission time</p></li>
                    <li><p>Python | 3.6.9 | 4 second submission time</p></li>
                </ul>
            </div>
        </div>
    </div>
</section>
<script>
    function open() {
        var dots = document.getElementById("dots");
        var moreText = document.getElementById("more");
        var btnText = document.getElementById("myBtn");

        console.log("works");

        if (dots.style.display === "none") {
            dots.style.display = "inline";
            btnText.innerHTML = "Read more";
            moreText.style.display = "none";
        } else {
            dots.style.display = "none";
            btnText.innerHTML = "Read less";
            moreText.style.display = "inline";
        }

        return false;
    }
</script>
<script>
    <?php
    if (!empty($output)) {
    ?>
    $(function () {
        $(window).load(function() {
            stopUpload();
            parseJSON(JSON.parse('<?php echo $output[0]['output_json']; ?>'));
        });
    });
    <?php
    }
    ?>
</script>
<script src="js/question.js"></script>
<?php
require '../templates/footer.php';
?>
