<!DOCTYPE HTML>
<HTML>
<head>
<title>Rightmove-PHP-Crawler Examples</title>
</head>
<body>
<?php
$d = dir('.');
while (false !== ($entry = $d->read())) {
  
   if( $entry != 'index.php' && $entry != '.' && $entry != '..') {
	$title = str_replace(array('-', '.php'), ' ', $entry);
        echo "<a href='".$entry."'>".ucwords($title)."</a><br/>\n";
   }
  
}
$d->close()
?>
</body>
</html>
