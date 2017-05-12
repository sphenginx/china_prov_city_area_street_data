<?php
include_once('../QueryList.class.php');

/**
 * 爬取城市数据的爬虫: 目前仅支持某省、某市数据 （site: http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2015）
 *
 * @package QueryList
 * @author Sphenginx
 **/
class Crawler
{
	//基础url
	private $_baseUrl;

	//
	private $_result;

	//重试次数
	private $_retryTimes = 0;

	//初始级别
	private $_initLevel;

	//下一级别对应表
	private $_next_level = [
		'_getCity'    => '_getCountry',
		'_getCountry' => '_getTown',
		'_getTown'    => '_getVillage',
		'_getVillage' => '',
	];

	/**
	 * 初始化方法
	 *
	 * @return void
	 * @author Sphenginx
	 **/
	public function __construct($baseUrl, $initLevel = '_getCity')
	{
		$this->_baseUrl = $baseUrl;
		$this->_initLevel   = $initLevel;
	}

	//获取市信息
	protected function _getCity($url)
	{
	    $reg = array(
	        "id"   => ["td:eq(0) a", "text"], 
	        "url"  => ["td:eq(0) a", "href"], 
	        "name" => ["td:eq(1) a", "text"]
	    );
	    $rang = ".citytr";
	    $html = iconv('gb2312','UTF-8', getResult($url));
	    $citys = QueryList::Query($html, $reg, $rang, 'get', 'UTF-8')->jsonArr;
	    return $citys;
	}

	//获取区信息
	protected function _getCountry($url) 
	{
	    $reg = array(
	        "id"   => ["td:eq(0)", "html", 'a'], 
	        "url"  => ["td:eq(0) a", "href"], 
	        "name" => ["td:eq(1)", "html", 'a']
	    );
	    $rang = ".countytr";
	    $html = iconv('gb2312','UTF-8', file_get_contents($url));
	    $arrQu = QueryList::Query($html, $reg, $rang, 'get', 'UTF-8')->jsonArr;
	    return $arrQu;
	}

	//获取城镇信息
	protected function _getTown($url)
	{
	    $reg = array(
	        "id"   => ["td:eq(0) a", "text"], 
	        "url"  => ["td:eq(0) a", "href"], 
	        "name" => ["td:eq(1) a", "text"]
	    );
	    $rang = ".towntr";
	    $next_html = iconv('gb2312','UTF-8', file_get_contents($url));
	    $towns = QueryList::Query($next_html, $reg, $rang, 'get', 'UTF-8')->jsonArr;
	    return $towns;
	}

	//获取村信息
	protected function _getVillage($url)
	{
	    $reg = array(
	        "id"   => ["td:eq(0)", "text"], 
	        "name" => ["td:eq(2)", "text"]
	    );
	    $rang = ".villagetr";
	    $next_html = iconv('gb2312','UTF-8', file_get_contents($url));
	    return QueryList::Query($next_html, $reg, $rang, 'get', 'UTF-8')->jsonArr;
	}

	/**
	 * 获取远程的数据，有重试机制（重试三次）
	 *
	 * @return void
	 * @author Sphenginx
	 **/
	private function _getResult($url)
	{
		$result = file_get_contents($url);
		if (!$result) {
			if ($this->_retryTimes >= 3) {
				return '';
			}
			$this->_retryTimes++;
			return $this->_getResult($url);
		} else {
			return $result;
		}
	}

	protected function _formatCityUrl($base, $url)
	{
		if (strrpos($base, '/') == strlen($base) - 1) {
			$after = $base . $url;
		} else {
			$urlArr = explode('/', $base);
		    unset($urlArr[count($urlArr) - 1 ]);
		    $after = implode('/', $urlArr) .'/'. $url;
		}
	    return $after;
	}

	/**
	 * 跑起来
	 *
	 * @return void
	 * @author Sphenginx
	 **/
	public function run()
	{
		$currentLevel = $this->_initLevel;
		$initResults = $this->$currentLevel($this->_baseUrl);
		$this->_getNextLevel($this->_initLevel, $initResults, 0, $this->_baseUrl);
		return $this->_result;
	}

	/**
	 * 获取下一级别的数据
	 *
	 * @return void
	 * @author Sphenginx
	 **/
	private function _getNextLevel($currentLevel, $currentDatas, $parentId, $parentUrl)
	{
		foreach ($currentDatas as $key => $data) {
			$this->_result[] = ['id' => $data['id'], 'keyid' => $parentId, 'name' => $data['name']];
			$nextLevel = $this->_next_level[$currentLevel];
			if ($nextLevel) {
				if ($data['url']) {
					$nextLevelUrl = $this->_formatCityUrl($parentUrl, $data['url']);
					$nextDatas = $this->$nextLevel($nextLevelUrl);
					$this->_getNextLevel($nextLevel, $nextDatas, $data['id'], $nextLevelUrl);
				}
			}
		}
	}

} // END class 
