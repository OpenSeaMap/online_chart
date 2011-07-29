<?php
if (isset($_POST['content']) && isset($_POST['filename']) && isset($_POST['mimetype'])) {
    header('Content-Type: ' . $_POST['mimetype']);
    header('Content-Disposition: attachment; filename="' . $_POST['filename'] . '"');
    echo stripslashes($_POST['content']);
} else {
    header("HTTP/1.0 400 Bad Request");
}
?>
