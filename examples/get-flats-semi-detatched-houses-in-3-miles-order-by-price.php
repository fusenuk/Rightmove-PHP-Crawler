<?php
require('../crawler.class.php');

$rightmove = new rightMoveCrawl('REGION^279', 100);

$rightmove->modifySearch('propertyTypes', array('flat', 'detached'));

$rightmove->modifySearch('radius', '1');

$rightmove->modifySearch('sortType', 'old');

$rightmove->crawl();

$results = $rightmove->getProperties();

echo '<pre>'.print_r($results, true).'</pre>';

?>