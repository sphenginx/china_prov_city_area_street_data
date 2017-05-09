<?php
require '../QueryList.class.php';
set_time_limit(0);

//shandong表的sql
/*
CREATE TABLE `shandong` (
  `id` bigint(50) NOT NULL AUTO_INCREMENT,
  `keyid` bigint(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` smallint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
*/

//组建SQL，并写入文件
$sql_ln = 'INSERT INTO `shandong`(`id`, `keyid`, `name`, `level`) VALUES '."\n";

//定义sql变量
$sql_arr = '';

//计数器
$count = 0;

//采集山东省下的市、区、街道、居委会列表
$url = "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2015/37.html";
$citys = getCity($url);
//循环读取下级数据
foreach ($citys as $key => $city) {
    $values = [];
    $values[] = "(".$city['id'].", 370000000000, '".$city['name']."', 1)";
    // $provinces[] = ['id' => $city['id'], 'keyid' => 0, 'name' => $city['name']];
    $countryUrl = formatCityUrl($url, $city['url']);
    $countrys = getCountry($countryUrl);
    foreach ($countrys as $key => $country) {
        $values[] = "(".$country['id'].", ".$city['id'].", '".$country['name']."', 2)";
        // $provinces[] = ['id' => $country['id'], 'keyid' => $city['id'], 'name' => $country['name']];
        if ($country['url']) {
            $townUrl = formatCityUrl($countryUrl, $country['url']);
            $towns = getTown($townUrl);
            foreach ($towns as $key => $town) {
                $values[] = "(".$town['id'].", ".$country['id'].", '".$town['name']."', 3)";
                // $provinces[] = ['id' => $town['id'], 'keyid' => $country['id'], 'name' => $town['name']];
                $villageUrl = formatCityUrl($townUrl, $town['url']);
                $villages = getVillage($villageUrl);
                foreach ($villages as $key => $village) {
                    $values[] = "(".$village['id'].", ".$town['id'].", '".$village['name']."', 4)";
                    // $provinces[] = ['id' => $village['id'], 'keyid' => $town['id'], 'name' => $village['name']];
                    $count++;
                }
            }
        }
    }
    $sql_arr .= $sql_ln . implode(",\n", $values) . ";\r\n";
}

//把获取到的数据写入文件
$file = 'city/shandong.sql';
$handle = fopen($file, 'w+');
fwrite($handle, $sql_arr);
fclose($handle);
echo '执行完毕，共获取到 '.$count.' 条数据';

//获取市信息
function getCity($url)
{
    $reg = array(
        "id"   => ["td:eq(0) a", "text"], 
        "url"  => ["td:eq(0) a", "href"], 
        "name" => ["td:eq(1) a", "text"]
    );
    $rang = ".citytr";
    $html = iconv('gb2312','UTF-8', file_get_contents($url));
    $citys = QueryList::Query($html, $reg, $rang, 'get', 'UTF-8')->jsonArr;
    return $citys;
}

//获取区信息
function getCountry($url) 
{
    $reg = array(
        "id"   => ["td:eq(0)", "html", 'a'], 
        "url"  => ["td:eq(0) a", "href"], 
        "name" => ["td:eq(1)", "html", 'a']
    );
    $rang = ".countytr";
    $html = iconv('gb2312','UTF-8', file_get_contents($url));
    $countrys = QueryList::Query($html, $reg, $rang, 'get', 'UTF-8')->jsonArr;
    return $countrys;
}

//获取城镇信息
function getTown($url)
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
function getVillage($url)
{
    $reg = array(
        "id"   => ["td:eq(0)", "text"], 
        "name" => ["td:eq(2)", "text"]
    );
    $rang = ".villagetr";
    $next_html = iconv('gb2312','UTF-8', file_get_contents($url));
    return QueryList::Query($next_html, $reg, $rang, 'get', 'UTF-8')->jsonArr;
}

function formatCityUrl($base, $url)
{
    $urlArr = explode('/', $base);
    unset($urlArr[count($urlArr) - 1 ]);
    $after = implode('/', $urlArr) .'/'. $url;
    return $after;
}