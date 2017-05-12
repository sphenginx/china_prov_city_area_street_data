<?php
require 'Crawler.php';
header('Content-type:text/html;charset=UTF-8');
set_time_limit(0);
//使用curl抓取源码并以uft8编码格式输出
$fushun = [];
//采集中国统计局抚顺市的区、街道等列表
$url = "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2015/21/2104.html";
$crawler = new Crawler($url, '_getCountry');
$fushun = $crawler->run();


echo "<pre>";print_r($fushun);echo "</pre>";exit;

//组建SQL，并写入文件
// $sql_ln = 'INSERT INTO `t31_city_class_fushun`(`id`, `keyid`, `name`) VALUES '."\n";
// $values = [];
// foreach ($fushun as $key => $fs) {
//     $values[] = "(".$fs['id'].", ".$fs['keyid'].", '".$fs['name']."')";
// }
// $sql_ln .= implode(",\n", $values).";\n";
// $file = 'city/fushun.sql';
// $handle = fopen($file, 'w+');
// fwrite($handle, $sql_ln);
// fclose($handle);
