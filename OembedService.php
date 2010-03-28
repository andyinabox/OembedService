<?php

/**
 * Create an interface to load Oembed embed data
 * 
 * @see [url for docs]
 * @todo Finish a working version
 */
class OembedService extends RestfulService {
	
	
	/**
	 * True if format request is formatted as an extension
	 * example: http://www.vimeo.com/api/oembed.{format}
	 * otherwise, defaults to querystring format request
	 *
	 * @var bool
	 */
	private $format_as_extension;
	
	/**
	 * OembedObject with oembed data
	 *
	 * @var OembedObject
	 */	
	private $oembed_object;
	
	function __construct($oembed_endpoint_url, $format_as_extension=false) {
		$this->baseURL = $oembed_endpoint_url;		
		$this->format_as_extension = $format_as_extension;
	}
	
	
	// format data for use with RestfulService
	function request($url = '', $format='xml', $params=null, $method = "GET", $data = null, $headers = null) {
		$query_string_params = array();
				
		// add additional parameters if necessary
		if (!is_null($params) && is_array($params)) {
			$query_string_params = $params;
		}
		

		// set proper format request
		if ($this->format_as_extension) { $this->baseURL .= '.' . $format; }
		else { $query_string_params['format'] = $format; }

		// add the url as parameter, oembed-style
		$query_string_params['url'] = $url;		
		
		$this->setQueryString($query_string_params);		

		return parent::request('', $method, $data, $headers);
	}
			
	function requestOembedObject($url, $format='xml', $params=null) {
		
		//have to make a custom XML object
		try {
			$response = $this->request($url, $format, $params);
			// Debug::show($response);
			
			$this->oembed_object = new OembedObject($response);
			return true;
		} catch (Exception $e) {
			user_error("Error occurred in processing oembed response");
			return false;
		}
	}
	
	function getEmbedCode() {
		return $this->oembed_object->getHtml();
	}
	
}

class OembedObject extends Object {
	
	// attributes. these are all based on the oembed spec
	// <http://www.oembed.com/>
	private $original_xml_data;
	private $type;
	private $version;
	private $title;
	private $author_name;
	private $author_url;
	private $provider_name;
	private $provider_url;
	private $cache_age;
	private $thumbnail_url;
	private $thumbnail_width;
	private $thumbnail_height;
	
	// additionl arguments, optional depending on type
	private $url;
	private $width;
	private $height;
	private $html;
	
	
	function __construct($xml) {
		$this->original_xml_data = $xml;
		$this->parseOembedXml();
	}
	
	private function parseOembedXml() {
		try {
						
			$sxml = new SimpleXMLElement($this->original_xml_data->getBody());			
			
			
			
			$this->type = (string)$sxml->type;	
			$this->version = (string)$sxml->version;
			$this->title = (string)$sxml->title;
			$this->author_name = (string)$sxml->author_name;
			$this->author_url = (string)$sxml->author_url;
			$this->provider_name = (string)$sxml->provider_name;
			$this->provider_url = (string)$sxml->provider_url;
			$this->cache_age = (string)$sxml->cache_age;
			$this->thumbnail_url = (string)$sxml->thumbnail_url;
			$this->thumbnail_width = (string)$sxml->thumbnail_width;
			$this->thumbnail_url = (string)$sxml->thumbnail_url;
			
			switch($this->type) {
				case 'photo':
					$this->url = (string)$sxml->url;
					$this->width = (string)$sxml->width;
					$this->height = (string)$sxml->height;
					break;
				case 'video':
					$this->html = (string)$sxml->html;
					$this->width = (string)$sxml->width;
					$this->height = (string)$sxml->height;
					break;
				case 'rich':
					$this->url = (string)$sxml->html;
					$this->width = (string)$sxml->width;
					$this->height = (string)$sxml->height;
					break;
				default:
					break;
			}
			
			return true;
		} catch (Exception $e) {
			user_error("Error occurred in processing Oembed xml");
			return false;
		}
	}
	
	// getters
	function getOriginalXmlData() { return $this->original_xml_data; }
	function getType() { return $this->type; }
	function getVersion() { return $this->version; }
	function getTitle() { return $this->title; }
	function getAuthorName() { return $this->author_name; }
	function getAuthorUrl() { return $this->author_url; }
	function getProviderName() { return $this->provider_name; }
	function getProviderUrl() { return $this->provider_url; }
	function getCacheAge() { return $this->cache_age; }
	function getThumbnailUrl() { return $this->thumbnail_url; }
	function getThumbnailWidth() { return $this->thumbnail_width; }
	function getThumbnailHeight() { return $this->thumbnail_height; }
	
	// optional getters
	function getUrl() { if(isset($this->url)) { return $this->url; }}
	function getWidth() { if(isset($this->width)) { return $this->width; }}
	function getHeight() { if(isset($this->height)) { return $this->height; }}
	function getHtml() { if(isset($this->html)) { return $this->html; }}

}


?>