# china_prov_city_area_street_data
2013年中国省市区街道数据库，找了好久，为方便大家查询，特分享mysql数据。

anyway，如果觉得数据不够新，可以爬一下 附 2015年的数据！

## 关于crawler

crawler目录 是一个 采用 QueryList开源库 来爬取山东省的市、区、镇、村的demo。 下载到本地，执行shandong.php 即可获取。欢迎交流指正

## 表结构
```
CREATE TABLE `stone_china_pro_city_area_street` (
  `pid` varchar(255) DEFAULT NULL COMMENT '父级id',
  `id` varchar(255) DEFAULT NULL COMMENT '当前id',
  `pcctvlevel` double DEFAULT NULL COMMENT '第几级',
  `pcctvname` varchar(255) DEFAULT NULL COMMENT '省市区名称',
  `classification` varchar(255) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL COMMENT '备注'
) ENGINE=InnoDB DEFAULT CHARSET=utf8
```

## 附

+ [国家统计局统计用区划和城乡划分代码目录](http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/)

+ [国家统计局2013年统计用区划代码和城乡划分代码(截止2013年8月31日))](http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2013/index.html)

+ [国家统计局2015年统计用区划代码和城乡划分代码(截止2015年09月30日)](http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2015/index.html)


## 其他

谢谢[CSDN myweishanli的分享](http://blog.csdn.net/myweishanli/article/details/38707247)
