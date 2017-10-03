## 百步梯外联部“商家信息管理系统”前后台对接文档 v0.1

### A. 商家信息部分
- 获取所有商家信息
```json
GET ./assets/API/businesses.php

RESPONSE:
//On Success:
{
	"code": 0,
	"businesses": [ //所有商家信息
		{
			"business_id": 3 //商家ID
			"name": "xxxxxx" //商家名称
			"industry": "xxxxxx" //行业
			"contact": "xxxxxx" //联系方式
			"address": "xxxxxx" //商家地址
			"willingness": "xxxxxx" //合作意愿
			"sponsorship_content": "xxxxxx" //赞助内容
			"charge_history": "xxxxxx" //原负责人、合作时间等历史
			"business_evaluation": "xxxxxx" //商家评价
			"remarks": "xxxxxx" //备注
			"is_contacted": 0 //是否已被联系 取值为1或0
			"contact_history": "xxxxxx" //联系历史
			"import_time": "xxxxxx" //导入时间
			"update_time": "xxxxxx" //更新时间
		},
		...
	]
}

//On Failure:
{
	"code": 1, //数据库为空
	"errMsg": "数据库中暂无商家信息"
}
```

- 搜索商家信息
```json
POST(form-data) ./assets/API/businesses.php
operation: search //搜索操作
keyword: xxxxxx //关键词

RESPONSE:
//On Success:
返回所有匹配到的商家信息，格式同上

//On Failure:
{
	"code": 2, //未找到符合条件的商家信息
	"errMsg": "未找到符合条件的商家信息，换个关键词试试吧~"
}
```

- 增加一条商家信息
```json
POST(form-data) ./assets/API/businesses.php
operation: add //增加操作
name: xxxxxx
industry: xxxxxx
contact: xxxxxx
address: xxxxxx
willingness: xxxxxx
sponsorship_content: xxxxxx
charge_history: xxxxxx
business_evaluation: xxxxxx
remarks: xxxxxx
is_contacted: 0
contact_history: xxxxxx

RESPONSE:
//On Success:
{
	"code": 0,
	"errMsg": "Success"
}

//On Failure:
{
	"code": 3, //操作失败
	"errMsg": "写入数据库时发生错误，请联系管理员"
}
```

- 修改一条商家信息
```json
POST(form-data) ./assets/API/businesses.php
operation: update, //修改操作
business_id: 3
name: xxxxxx,
industry: xxxxxx,
contact: xxxxxx,
address: xxxxxx,
willingness: xxxxxx,
sponsorship_content: xxxxxx,
charge_history: xxxxxx,
business_evaluation: xxxxxx,
remarks: xxxxxx,
is_contacted: 0,
contact_history: xxxxxx
// !!! 注意：
// 1. 所有账号均可将“已被联系”修改为1，或直接增加“联系历史”项（此情况下只需将增加的内容传给后台即可）
// 2. 仅部长账号可将“已被联系”修改至0，后台在确认账号权限后会自动将“联系历史”项清空

RESPONSE:
//On Success:
{
	"code": 0,
	"errMsg": "Success"
}

//On Failure:
{
	"code": 4, //操作失败
	"errMsg": "更新数据库时发生错误，请联系管理员"
}
```

##### 通配错误信息
```json
Extra Failure Response:
{
	"code": 233, //未登录系统
	"errMsg": "请先登录系统！"
}

{
	"code": 2333, //配置错误
	"errMsg": "数据库连接出错，请联系管理员"
}
```
