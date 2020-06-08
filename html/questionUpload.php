<?php
$name = explode(".",  basename($_FILES['fileInput']['name']));

$uploadFile = 'questions/' . $_FILES['fileInput']['name'];
$targetFolder = 'questions/' . $name[0];

if (isset($_POST['questionInput'])) {
    if (move_uploaded_file($_FILES['fileInput']['tmp_name'], $uploadFile)) {
        `unzip $uploadFile -d `;
    } else {
        echo 'Unable to move file';
    }
}
?>
<form method="post" action="questionUpload.php" enctype="multipart/form-data">
    Send this file: <input name="questionInput" type="file" />
    <input type="submit" value="Send File" />
</form>