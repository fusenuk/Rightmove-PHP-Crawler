<?php
require('../crawler.class.php');

$rightmove = new rightMoveCrawl('REGION^279', 100);

$rightmove->crawl();

$results = $rightmove->getFilteredPropertyIDs();

echo '<pre>'.print_r($results, true).'</pre>';

?>