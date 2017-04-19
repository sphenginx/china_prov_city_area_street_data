# china_prov_city_area_street_data
原作者2014年提交的博文，私以为应该是2014年中国省市区街道数据库，找了好久，为方便大家查询，特分享mysql数据

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

## 其他

谢谢[CSDN myweishanli的分享](http://blog.csdn.net/myweishanli/article/details/38707247)