<!--
Take the file field "field_embedded_doc"  and render it inside a Google Docs viewer within an IFrame.
-->
<?php
$aaa = $items[0];
$bbb = $aaa['#file'];
$ccc = $bbb->uri;
$link = file_create_url($ccc);
$data = urlencode($link);
?>

<?php  print '<iframe style="border:1px solid black;width:550px;height:400px;" src="http://docs.google.com/gview?embedded=true&url=' . $data . '"></iframe>';?>