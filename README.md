# Rightmove-PHP-Crawler
PHP class to scrape / crawl rightmove.co.uk

Data is always returned in arrays.
## Basic Use
The class only initially needs to be told about the area you want to scrape, e.g. '*London*' is '*REGION^87490'*, 'Bath' is '*REGION^116*', 'Canterbury West Train Station' is '*STATION^1760'*. To find this value make a real search on rightmove.co.uk and look at the URL in the browser address bar.

    new rightMoveCrawl('REGION^116');

You can limit the number of returned properties by setting a maximum crawl number as the second value

    new rightMoveCrawl('REGION^116', 100);

Rightmove returns 24 properties per page request, so setting 100 would mean 5 page requests are made and then the script would stop any further requests.

You then need to call the `crawl()` method which tells is when the class will make the cURL requests to scrape/crawl the content.
## Returned property attributes
The class uses pre-set defaults for what type of scrape it will do and how it will sort the properties and the property attributes it will return.

The property attributes returned by default are - 


    'id',
    'bedrooms',
    'bathrooms',
    'floorplans',
    'summary',
    'displayAddress',
    'displayStatus',
    'location',
    'propertySubType',
    'listingUpdate',
    'price',
    'propertyUrl',
    'firstVisibleDate',
    'listingUpdate',
    'propertyImages',
    'addedOrReduced',
    'propertyTypeFullDescription'
    
You can add extra property IDs you want returned by calling `addPropertyAttribute('attributeName');` before using the `crawl()` method.

You can use the `showAllAttributes()` method to get an array list of all the property attributes Rightmove has available after doing the crawl.
## Modifying the search
To modify the search, you use the method `modifySearch()` before doing calling crawl()

The possible search modifiers you can use are -

    modifySearch('locationIdentifier', '*value*');

*Value* is the location you want to search. You would only set this if want to do multiple calls to the crawl() method but want to alter where you're searching.

    modifySearch('radius', *value*);

*value* can be *'0.0', '0.25', '0.5', '1', '3', '5', '10', '15', '20', '30', '50'* - these are miles from the location you are searching

    modifySearch('sortType', '*value*');

*value* can be *'new', 'old', 'high', 'low'* - this will change the sort order of properties, value first

    modifySearch('includeSSTC', true);

*value* can be *'true', 'false'* - this is to include sold properties or not

## See returned property data

To see the scraped / crawled property results you can use the following methods

`getProperties()` - this returns an array of all properties showing the values of the attributes mentioned above

`getProperty(*value*)` - this will only return the attributes mentioned above for the property with the ID *value*

`getFilteredPropertyIDs()` - this will return an array of just the property IDs separated into arrays of the filter attribute names.

`getFilteredProperties()` - this will return an array of properties showing the values of the attributes mentioned above, separated into arrays of the filter attribute names.

## Filters

By default, along with the general array containing the details of the properties, there are multiple filter attributes used to create additional separated arrays that only contain properties matching certain filters attributes, this allows sorting and filtering to be done at the point of reading the rightmove website instead of needing to then do additional filtering yourself with the main results.

    'propertySubType',
    'bedrooms',
    'price>amount',
    'listingUpdate>listingUpdateReason',
    
Using the `getFilteredProperties()` method you will see 4 arrays of properties where the properties are in multidimensional arrays ordered by the value of the filter attribute. An Example would be `$properties['bedrooms']['4']` would contain all properties that have 4 bedrooms. Or `$properties['propertySubtype']['flat']` would containing all properties that are 'Flats'. A final example would be `$properties['amount']['400000']` containing all properties that have a price set at £400,000.

You can add extra filters to create additional multi-dimentional arrays by editing the $this->filters array in the class.
    
## Speed of crawling

I think you should attempt to not hammer public websites when crawling them, so the class uses the setting `$this->curlSleep = 0.6;` to force a 0.6 second `sleep()` between every cURL request. If you think differently, you can edit that value to speeden up or slow down the scripts response time.
