## 百步梯外联部“商家信息管理系统”前后台对接文档 v1.2

### A. 商家信息部分
- 获取所有商家信息
```json
GET ./assets/API/businesses.php

RESPONSE:
//On Success:
{
	"code": 0,                                       //操作成功
	"businesses": [                                  //所有商家信息
		{
			"business_id": 3                         //商家 ID
			"name": "xxxxxx"                         //商家名称
			"industry": "xxxxxx"                     //行业
			"contact": "xxxxxx"                      //联系方式
			"address": "xxxxxx"                      //商家地址
			"willingness": "xxxxxx"                  //合作意愿
			"sponsorship_content": "xxxxxx"          //赞助内容
			"charge_history": "xxxxxx"               //原负责人、合作时间
			"business_evaluation": "xxxxxx"          //商家评价
			"remarks": "xxxxxx"                      //备注
			"is_contacted": 0                        //是否已被联系 取值为 1 或 0
			"contact_history": "xxxxxx"              //联系历史
			"import_time": "xxxxxx"                  //导入时间
			"update_time": "xxxxxx"                  //更新时间
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
operation: search    //搜索操作
keyword: xxxxxx      //关键词

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
	"code": 0, //操作成功
	"errMsg": "success"
}

//On Failure:
{
	"code": 3, //操作失败
	"errMsg": "写入数据库时发生错误，请联系管理员"
}
```

- 修改一条商家信息
***
1. 所有账号均可将“已被联系”修改为 1，或直接增加“联系历史”项
2. “联系历史”项的增加只需将增加的内容传给后台即可，建议在修改界面注明“此项仅需填写增加内容”
3. 仅部长账号可将“已被联系”修改至 0，后台在确认账号权限后会自动将“联系历史”项清空
***
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

RESPONSE:
//On Success:
{
	"code": 0, //操作成功
	"errMsg": "success"
}

//On Failure:
{
	"code": 4, //操作失败
	"errMsg": "更新数据库时发生错误，请联系管理员"
}
```

### B. 账号管理部分
- 新建账号
***
1. 超级管理员**能且只能**新建部长账号
2. 部长**能且只能**新建干事账号
3. 密码允许设置为空（如果前端也允许的话）
***
```json
POST(form-data) ./assets/API/accounts.php
operation: create             //新建账号操作
name: xxxxxx                  //新用户姓名
username: xxxxxx              //新用户用户名
password: xxxxxx              //新用户密码

RESPONSE:
//On Success:
{
	"code": 0, //操作成功
	"errMsg": "success"
}

//On Failure:
{
	"code": 1, //无新建账号权限（当前登录的是干事账号）
	"errMsg": "权限验证出错！"
}

{
	"code": 2, //用户名已存在
	"errMsg": "该用户名已存在，请更换"
}

{
	"code": 3, //操作失败
	"errMsg": "新建账号失败，请联系管理员"
}
```

- 修改账号
***
1. 超级管理员**能且只能**修改部长账号
2. 部长**能且只能**修改干事账号
3. 若需要修改密码请将 `set_new_password` 项设置为 1，默认请传输 0
***
```json
POST(form-data) ./assets/API/accounts.php
operation: modify             //修改账号操作
username: xxxxxx              //待修改账号用户名
new_name: xxxxxx              //修改后姓名
set_new_password: 1           //是否修改密码
new_password: xxxxxx          //新密码，不需修改密码时可不传输给后台

RESPONSE:
//On Success:
{
	"code": 0, //操作成功
	"errMsg": "success"
}

//On Failure:
{
	"code": 4, //无修改账号权限（当前登录的是干事账号）
	"errMsg": "权限验证出错！"
}

{
	"code": 5, //操作失败
	"errMsg": "修改账号失败，请联系管理员"
}
```

- 登录系统
```json
POST(form-data) ./assets/API/accounts.php
operation: login              //登录操作
username: xxxxxx              //用户名
password: xxxxxx              //密码

RESPONSE:
//On Success:
{
	"code": 0,                //操作成功
	"name": "xxx",            //姓名
	"register_time": "xxx",   //注册时间
	"update_time": "xxx",     //更新时间
	"is_minister": 1          //是否为部长账号
}

//On Failure:
{
	"code": 6, //用户名或密码错误
	"errMsg": "用户名或密码错误"
}
```

- 退出系统
```json
GET ./assets/API/accounts.php

RESPONSE:
//On Success:
{
	"code": 0, //退出系统成功
	"errMsg": "success"
}
```

##### 通配错误信息
```json
Extra Failure Response:
{
	"code": 233, //未登录系统
	"errMsg": "请登录系统！"
}

{
	"code": 2333, //配置错误
	"errMsg": "数据库连接出错，请联系管理员"
}
```
