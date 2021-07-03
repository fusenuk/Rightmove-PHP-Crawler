<?php

class rightMoveCrawl {

	private $url;
	private $refurl;
	private $fields;
	private $propertyCrawlArray;
	private $properties;
	private $propertyAttributes;
	private $allPropertyAttributes;
	private $verbose;
	private $filters;
	private $filterlists;
	private $curlSleep;
	private $maxCrawl;
	

	function __construct($region, $maxCrawl = 0, $verbose = false) {
		
		if ($verbose != false) $this->verbose = true;
		
		$this->propertyCrawlArray = array();
		$this->properties = array();
		$this->filterlists = array();
		
		$this->filters = array('propertySubType',
		'bedrooms',
		'price>amount',
		'listingUpdate>listingUpdateReason',
		);
		
		$this->propertyAttributes = array('id',
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
		'propertyTypeFullDescription');
		
		$this->allPropertyAttributes = array();
		
		$this->fields = array('locationIdentifier' => $region,
		'index' => 0,
		'numberOfPropertiesPerPage' => '24',
		'radius' => '0.0',
		'sortType' => '6',
		'includeSSTC' => 'true',
		'viewType' => 'LIST',
		'channel' => 'BUY',
		'areaSizeUnit' => 'sqft',
		'currencyCode' => 'GBP',
		'isFetching' => 'false');
		
		$this->url = 'https://www.rightmove.co.uk/api/_search';
		$this->refurl = 'https://www.rightmove.co.uk/property-for-sale/find.html?sortType=6&index=24&propertyTypes=&includeSSTC=true&mustHave=&dontShow=&furnishTypes=&keywords=&locationIdentifier=';
		
		$this->curlSleep = 0.6;
		$this->maxCrawl = 0;
		if (is_numeric($maxCrawl)) $this->maxCrawl = $maxCrawl;
	
	}
	
	private function buildURL() {
	
		$variablesString = $this->url.'?';
		foreach ($this->fields as $variableName => $variableValue) {
			$variablesString .= $variableName.'='.$variableValue.'&';
		}
		$variablesString = rtrim($variablesString, "&");
		return $variablesString;
		
	}
	
	private function doCurl() {
		
		$ch = curl_init();
		$headers = array();
		$headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0";
		$headers[] = "Accept: application/json, text/javascript";
		$headers[] = "Accept-Language: en-GB,en;q=0.5";
		$headers[] = "Referer: ".$this->refurl.$this->fields['locationIdentifier'];
		$headers[] = "Content-Type: application/x-www-form-urlencoded";
		$headers[] = "Connection: keep-alive";
		$headers[] = "Cookie: ";
		$headers[] = "X-Requested-With:	XMLHttpRequest";
		
		curl_setopt($ch, CURLOPT_URL, $this->buildURL());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		
		if (curl_errno($ch)) {
		  return 'Error:' . curl_error($ch);
		}
		curl_close ($ch);
		return json_decode($result, true);
	}
	
	public function crawl() {
		
		$nextPage = true;
		while ($nextPage != false) {
			$response = $this->doCurl();
			if (!is_array($response)) {
				return $response;
			}
			if (!isset($response['pagination']))  {
				return 'Error, invalid response.';
			}
			$this->propertyCrawlArray = array_merge($this->propertyCrawlArray, $response['properties']);
			$nextPage = $this->checkPagination($response['pagination']);
			$this->fields['index'] = $nextPage;
			if ($this->maxCrawl != 0 && $nextPage > $this->maxCrawl) $nextPage = false;
			sleep($this->curlSleep);
		}
		
		$this->allPropertyAttributes = $this->array_keys_multi($response['properties'][0]);
		$this->processProperties();
		
		return true;
	}
	
	
	private function array_keys_multi(array $array, $parent = '')	{

		$keys = array();
		foreach ($array as $key => $value) {
			if ($parent != '') {
				$path = $parent." > ".$key;
			} else {
				$path = $key;
			}
			$keys[] = $path;

			if (is_array($array[$key])) {
				$keys = array_merge($keys, $this->array_keys_multi($array[$key], $path));
			}
		}

		return $keys;
	}
	
	private function checkPagination($pageination) {
	
		if (!isset($pageination['next'])) return false;
		return $pageination['next'];
		
	}

	private function processProperties() {
		foreach ($this->propertyCrawlArray as $property) {
			$rightMoveID = $property['id'];
			foreach ($this->propertyAttributes as $findAttribute) {
				if (isset($property[$findAttribute])) {
					$this->properties[$rightMoveID][$findAttribute] = $property[$findAttribute];
				}
			}
			foreach ($this->filters as $filter) {
				$multi = explode('>', $filter);
				$multiCount = count($multi);
				if ($multiCount < 2) {
					if (!isset($property[$filter])) continue;
					$this->filterlists[$filter][$property[$filter]][] = $rightMoveID;
				} else {
					switch ($multiCount) {
						case 2:
							if (!isset($property[$multi[0]][$multi[1]])) continue;
							$this->filterlists[$filter][$property[$multi[0]][$multi[1]]][] = $rightMoveID;
						break;
						case 3:
							if (!isset($property[$multi[0]][$multi[1]][$multi[2]])) continue;
							$this->filterlists[$filter][$property[$multi[0]][$multi[1]][$multi[2]]][] = $rightMoveID;
						break;
						default:
							if (!isset($property[$multi[0]][$multi[1]][$multi[2]][$multi[3]])) continue;
							$this->filterlists[$filter][$property[$multi[0]][$multi[1]][$multi[2]][$multi[3]]][] = $rightMoveID;
						break;
					}
				}
			}
		}
		return true;
		
	}
	
	public function addPropertyAttribute($attribute = null) {
	
		if ($attribute === null || !is_string($attribute)) return false;
		$this->propertyAttributes[] = $attribute;
		return true;
	
	}
	
	public function clearProperties() {
	
		$this->properties = array();
		return true;
	
	}
	
	public function modifySearch($field, $value) {

		$allowedFields = array('locationIdentifier', 'radius', 'sortType', 'includeSSTC', 'propertyTypes');
		if (!in_array($field, $allowedFields)) return false;
		
		switch ($field) {
		
			case 'locationIdentifier':
				$this->fields['locationIdentifier'] = $value;
			break;
			
			case 'radius':
				$allowedRadius = array('0.0', '0.25', '0.5', '1', '3', '5', '10', '15', '20', '30', '50');
				$newRadius = 0;
				if (in_array($value, $allowedRadius)) {
					$this->fields['radius'] = $value;
					return true;
				}
				return false;
			break;
			
			case 'sortType':
			
				$value = strtolower($value);
				switch ($value) {
					case 'new':
						$this->fields['sortType'] = 6;
					break;
					case 'old':
						$this->fields['sortType'] = 10;
					break;
					case 'high':
						$this->fields['sortType'] = 2;
					break;
					case 'low':
						$this->fields['sortType'] = 1;
					break;
					default:
						$this->fields['sortType'] = 6;
					break;
				}
				return true;
			break;
			
			case 'includeSSTC':
			
				$value = strtolower($value);
				switch ($value) {
					case 'false':
						$this->fields['includeSSTC'] = 'false';
						return true;
					break;
					default:
						$this->fields['includeSSTC'] = 'true';
						return true;
					break;
				}
			break;
			
			case 'propertyTypes':
				
				if (!is_array($value) || count($value) < 1) return false;
		
				$allowedPropTypes = array('detached', 'semi-detached', 'terraced', 'bungalow', 'flat', 'land', 'park-home');
				foreach ($value as $propType) {
					if (!in_array($value, $allowedPropTypes)) unset($allowedPropTypes[$value]);
				}
				$typeList = implode($value, '%2C');
				$this->fields['propertyTypes'] = $typeList;
				return true;
				
			break;
			
		}
		
		return false;
	
	}

	public function getProperties() {
	
		return $this->properties;
		
	}
	
	public function getProperty($id) {
	
		if (isset($this->properties[$id])) return $this->properties[$id];
		return false;
		
	}
	
	public function getFilteredPropertyIDs($type = null) {
	
		if (!in_array($type, $this->filters) || $type == null) return $this->filterlists;
		return $this->filterlists[$type];
		
	}
	
	public function getFilteredProperties($type = null) {
		
		if ($type == null) return $this->filterlists;
		if (!in_array($type, $this->filters)) return $this->filterlists;
		
		$returnArray = array();
		
		$baseArray = $this->getFilteredPropertyIDs($type);
		
		if ($baseArray === false) return false;

		foreach ($baseArray as $filter => $propertyId) {
			if (is_array($propertyId)) {
				foreach ($propertyId as $subPropId) {
					$returnArray[$filter][] = $this->getProperty($subPropId);
				}
			} else {
				$returnArray[$filter][] = $this->getProperty($propertyId);
			}
		}
		return $returnArray;
	}
	
	public function showAllAttributes() {
		return $this->allPropertyAttributes;
	}
	
	private function debug($data) {
		if ($this->verbose != true) return false;
		print_r($data);
	}

}