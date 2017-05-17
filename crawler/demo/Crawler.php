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

	//返回结果
	private $_result;

	//重试次数
	private $_retryTimes = 0;

	//初始级别
	private $_initLevel;

	//初始parentId
	private $_initParentId;

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
	public function __construct($baseUrl, $initLevel = '_getCity', $initParentId = 0)
	{
		$this->_baseUrl = $baseUrl;
		$this->_initLevel   = $initLevel;
		$this->_initParentId = $initParentId;
	}

	//获取市信息
	protected function _getCity($url, $parentId)
	{
	    $reg = array(
	        "id"   => ["td:eq(0) a", "text"], 
	        "url"  => ["td:eq(0) a", "href"], 
	        "name" => ["td:eq(1) a", "text"]
	    );
	    $rang = ".citytr";
	    $html = iconv('gb2312','UTF-8', $this->_getResult($url, __METHOD__, $parentId));
	    return QueryList::Query($html, $reg, $rang, 'get', 'UTF-8')->jsonArr;
	}

	//获取区信息
	protected function _getCountry($url, $parentId) 
	{
	    $reg = array(
	        "id"   => ["td:eq(0)", "html", 'a'], 
	        "url"  => ["td:eq(0) a", "href"], 
	        "name" => ["td:eq(1)", "html", 'a']
	    );
	    $rang = ".countytr";
	    $html = iconv('gb2312','UTF-8', $this->_getResult($url, __METHOD__, $parentId));
	    return QueryList::Query($html, $reg, $rang, 'get', 'UTF-8')->jsonArr;
	}

	//获取城镇信息
	protected function _getTown($url, $parentId)
	{
	    $reg = array(
	        "id"   => ["td:eq(0) a", "text"], 
	        "url"  => ["td:eq(0) a", "href"], 
	        "name" => ["td:eq(1) a", "text"]
	    );
	    $rang = ".towntr";
	    $html = iconv('gb2312','UTF-8', $this->_getResult($url, __METHOD__, $parentId));
	    return QueryList::Query($html, $reg, $rang, 'get', 'UTF-8')->jsonArr;
	}

	//获取村信息
	protected function _getVillage($url, $parentId)
	{
	    $reg = array(
	        "id"   => ["td:eq(0)", "text"], 
	        "name" => ["td:eq(2)", "text"]
	    );
	    $rang = ".villagetr";
	    $html = iconv('gb2312','UTF-8', $this->_getResult($url, __METHOD__, $parentId));
	    return QueryList::Query($html, $reg, $rang, 'get', 'UTF-8')->jsonArr;
	}

	/**
	 * 获取远程的数据，有重试机制（重试三次）
	 *
	 * @return mixed
	 * @author Sphenginx
	 **/
	private function _getResult($url, $currentMethod, $parentId)
	{
		$result = file_get_contents($url);
		if (!$result) {
			if ($this->_retryTimes >= 2) {
				$this->_retryTimes = 0;
				$this->_recordFailResult($url, $currentMethod, $parentId);
				return '';
			}
			$this->_retryTimes++;
			return $this->_getResult($url, $currentMethod, $parentId);
		} else {
			return $result;
		}
	}

	/**
	 * 记录抓取失败时候的url、method、parentId
	 *
	 * @return void
	 * @author Sphenginx
	 **/
	private function _recordFailResult($url, $currentMethod, $parentId)
	{
		$this->_result['failed'][] = ['url' => $url, 'method'=> $currentMethod, 'parentId' => $parentId];
	}

	//格式化下一级的url
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
		$initResults = $this->$currentLevel($this->_baseUrl, $this->_initParentId);
		$this->_getNextLevel($this->_initLevel, $initResults, $this->_initParentId, $this->_baseUrl);
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
			$this->_result['success'][] = ['id' => $data['id'], 'keyid' => $parentId, 'name' => $data['name']];
			$nextLevel = $this->_next_level[$currentLevel];
			if ($nextLevel) {
				if ($data['url']) {
					$nextLevelUrl = $this->_formatCityUrl($parentUrl, $data['url']);
					$nextDatas = $this->$nextLevel($nextLevelUrl, $data['id']);
					$this->_getNextLevel($nextLevel, $nextDatas, $data['id'], $nextLevelUrl);
				}
			}
		}
	}

} // END class 
