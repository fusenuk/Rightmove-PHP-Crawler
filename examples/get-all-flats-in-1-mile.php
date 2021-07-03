<?php
require('../crawler.class.php');

$rightmove = new rightMoveCrawl('REGION^279', 100);

$rightmove->modifySearch('propertyTypes', array('flats'));

$rightmove->modifySearch('radius', '1');

$rightmove->crawl();

$results = $rightmove->getProperties();

echo '<pre>'.print_r($results, true).'</pre>';

?>