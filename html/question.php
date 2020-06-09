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
    });
</script>

<pre>
    Back to the Barn
    ================

    As every cow does occasionally, Bessie has lost herself in the
    woods! She desperately needs to get back to the barn but has no
    idea where to go.

    The woods can be thought of as an R x C grid (1 <= R <= 5; 1 <= C
    <= 5). Bessie is located in the lower left corner at row 1, column
    1; the barn is located in the upper right corner at in row R, column
    C.  Each square in the grid is either empty (denoted by '.') or
    blocked by a tree (denoted by 'T'). Bessie's square and the barn
    will always be empty.

    From a given square, Bessie may move to any adjacent square (one
    that shares a long edge with Bessie's current square) as long as
    it is empty. As Bessie is quite a smart cow, she never visits a
    square twice on the way back to the barn.

    Determine the number of different ways Bessie can take that lead
    her from her initial position back to the barn while visiting at
    most K different squares (1 <= K <= R * C).

    By way of example, consider this forest:

            ....
            .T..
            ....

    Bessie has a number of ways to get to the upper right corner, here
    are all seven of them and their path lengths (the number of squares
    visited):

             cdef  ...f  ..ef  ..gh  cdeh  cdej  ...f
             bT..  .T.e  .Td.  .Tfe  bTfg  bTfi  .Tde
             a...  abcd  abc.  abcd  a...  a.gh  abc.

    Length:    6     6     6     8     8    10    6

    PROBLEM NAME: backbarn

    INPUT FORMAT:

    * Line 1: Three space-separated integers: R, C, and K

    * Lines 2..R+1: Line i+1 contains the C characters representing row
            R+1-i of the forest (with no spaces)

    SAMPLE INPUT:

    3 4 6
    ....
    .T..
    ....

    INPUT DETAILS:

    The forest is a 3 x 4 grid. A single tree sits almost in the middle,
    as shown. No more than six steps can be taken.

    OUTPUT FORMAT:

    * Line 1: A single integer that is the number of different paths,
            visiting at most K squares, that Bessie can take to get back
            to the barn. Note that this number will always fit into a
            signed 32-bit integer.

    SAMPLE OUTPUT:

    4

    OUTPUT DETAILS:

    Of the several ways to complete the traversal, only four of them touch
    no more than six squares along the route.
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
