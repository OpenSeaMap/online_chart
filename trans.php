<?php
    include("classes/Translation.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Translation table</title>
        <style>
        td,th {
            border:1px solid;
        }
        </style>
    </head>
    <body>
        <?= $t->makeTranslationTable()?>
    </body>
</html>
