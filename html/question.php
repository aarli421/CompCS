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
                },
                error: function(data) {
                    $("#dialogDiv").html("Error happened");
                    stopUpload();
                },
                cache: false,
                contentType: false,
                processData: false
            });
        });

        $.ajax({
            url: "questions/" + <?php echo "\"" . $_GET['questionName'] . "\"" ?> + "/prompt.txt",
            success: function(data) {
                $("#prompt").html(data);
            }
        });
    });
</script>

<pre id="prompt">

</pre>
<br>
<p id="upload_process" style="display: none">Loading <img src="media/loader.gif" /></p>
<p id="result"></p>
<div id="dialogDiv"></div>
<form id="fileSubmission" method="post" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
    <input type="hidden" name="questionName" value="<?php echo $_GET['questionName'] ?>" />
    Send this file: <input name="fileInput" type="file" />
    <input name="fileSubmit" type="submit" value="Send File" />
</form>
<?php
require("../templates/footer.php");
?>
