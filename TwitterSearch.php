<?php
/**
 * Twitter Search Library updated for oauth
 * Based on the class developed by Ryan Faerman
 * and accessible at https://github.com/ryanfaerman/php-twitter-search-api
 * @author Chad Shinsato <shinsatoc@gmail.com>
 * @version 1
 */

class TwitterSearch
{
	private static $_consumer_key = 'xxxxxxxxxxxxxxxxxxxxx';
	private static $_consumer_secret = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

	public static $_bearer_token = null;
	
	public function TwitterSearch()
	{
		if(!isset(TwitterSearch::$_bearer_token))
		{
			$result = $this->RequestToken();
			if($result)
				TwitterSearch::$_bearer_token = $result;
		}	
	}
	
	/**
	 * Requests a bearer token with the given consumer key and secret
	 *
	 **/
	public function RequestToken()
	{
		$headers = array(
			"Content-Type: application/x-www-form-urlencoded;charset=UTF-8",
			"Authorization: Basic " . base64_encode(TwitterSearch::$_consumer_key.':'.TwitterSearch::$_consumer_secret)
		); 
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,  'https://api.twitter.com/oauth2/token');
		curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 45);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
		
		$buffer = curl_exec($ch);
		$results = json_decode($buffer);
		
		curl_close($ch);
		
		if($results->token_type == 'bearer' && $results->access_token != '')
			return $results->access_token;
		
		return false;
	}
	/**
	 * Search for a user
	 * @param string $user
	 **/
	public function setUser($user)
	{
		$this->user = '@'.str_replace('@', '', $user);
	}
	
	/**
	 * Search for a hash tag
	 * @param string $hashtag
	 **/
	public function setHash($hashtag)
	{
		$this->hash = '#'.str_replace('#', '', $hashtag);
	}
	
	/**
	 * Search for a word
	 * @param string $word
	 **/
	public function setContains($word)
	{
		$this->contains .= $word;
	}

	/**
	 * search within a certian location
	 * @param float $lat
	 * @param float $long
	 * @param string $radius - 2mi
	 **/
	public function setGeocode($lat,$long,$radius)
	{
		$this->geocode = "{$lat},{$long},{$radius}";
	}

	/**
	 * search by language
	 * @param string $language
	 **/
	public function setLang($language)
	{
		$this->lang = $language;
	}
	
	/**
	 * specify the result type
	 * @param string $type
	 **/
	public function setResultType($type = 'mixed')
	{
		if(!in_array($type,array('mixed','recent','popular')))
			$type = 'mixed';
			
		$this->result_type = $type;
	}
	
	/**
	 * number of tweets to return
	 * @param int $count - max 100
	 **/
	public function setCount($count = 15)
	{
		$this->count = (int) $count;
	}
	
	/**
	 * only gets tweets before the given date
	 * @param string $until - YYYY-MM-DD
	 **/
	public function setUntil($until)
	{
		$this->until = date('Y-m-d H:i:s',strtotime($until));
	}
	
	/**
	 * only gets tweets with ids greater than the given id
	 * @param int $since_id
	 **/
	public function setSinceId($since_id)
	{
		$this->since_id = (int) $since_id;
	}
	
	/**
	 * only gets tweets with ids less than the given id
	 * @param int $since_id
	 **/
	public function setMaxId($max_id)
	{
		$this->max_id = (int) $max_id;
	}
	
	/**
	 * Include the entities node or not
	 * @param bool $include_entities
	 **/
	public function setIncludeEntities($include_entities)
	{
		$this->include_entities = (bool) $include_entities;
	}
	
	/**
	 * Resets the search params
	 * 
	 **/
	public function ResetParams()
	{
		$this->user = '';
		$this->hash = '';
		$this->contains = '';
		$this->geocode = '';
		$this->lang = '';
		$this->locale = '';
		$this->result_type = '';
		$this->count = '';
		$this->until = '';
		$this->since_id = '';
		$this->max_id = '';
		$this->include_entites = '';
		$this->callback = '';
	}
	/**
	 * Quick Search
	 * Does a quick contains based search
	 **/
	public function QuickSearch($search)
	{
		$this->setContains($search);
		return $this->RunSearch();
	}
	
	/**
	 * Runs a search with the set parameters
	 *
	 **/
	public function RunSearch()
	{
		$to_search = array();
		if($this->user)
			$to_search[] = $this->user;
		if($this->hash)
			$to_search[] = $this->hash;
		if($this->contains)
			$to_search[] = $this->contains;
		
		$search = implode(' ', $to_search);
		
		$result = $this->Search($search, $this->geocode, $this->lang, $this->locale, $this->result_type, $this->count, $this->until, $this->since_id, $this->max_id, $this->include_entites, $this->callback);
		
		return $result;
	}
	
	/**
	 * Runs a search with the given parameters
	 *
	 **/
	public function Search($search, $geocode = '', $lang = '', $locale = '', $result_type = '', $count = '', $until = '', $since_id = '', $max_id = '', $include_entities = '', $callback = '')
	{
		$headers = array(
			"Content-Type: application/x-www-form-urlencoded;charset=UTF-8",
			"Authorization: Bearer " . (TwitterSearch::$_bearer_token)
		); 
		
		$query = '?q='.urlencode($search);
		if($geocode)
			$query .= '&geocode='.urlencode($geocode);
		if($lang)
			$query .= '&lang='.urlencode($lang);
		if($locale)
			$query .= '&locale='.urlencode($locale);
		if($result_type)
			$query .= '&result_type='.urlencode($result_type);
		if($count)
			$query .= '&count='.urlencode($count);
		if($until)
			$query .= '&until='.urlencode($until);
		if($since_id)
			$query .= '&since_id='.urlencode($since_id);
		if($max_id)
			$query .= '&max_id='.urlencode($max_id);
		if($include_entities)
			$query .= '&include_entities='.urlencode($include_entities);
		if($callback)
			$query .= '&callback='.urlencode($callback);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,  'https://api.twitter.com/1.1/search/tweets.json'.$query);
		curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,0);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 45);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$buffer = curl_exec($ch);
		curl_close($ch);
		return json_decode($buffer);
	}
}

?>