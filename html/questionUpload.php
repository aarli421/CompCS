<?php
$name = explode(".",  basename($_FILES['questionInput']['name']));

$uploadFile = 'questions/' . $_FILES['questionInput']['name'];
$targetFolder = 'questions/' . $name[0];

if (isset($_FILES['questionInput'])) {
    if (move_uploaded_file($_FILES['questionInput']['tmp_name'], $uploadFile)) {
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