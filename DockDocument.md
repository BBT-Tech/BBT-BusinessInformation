## 百步梯外联部“商家信息管理系统”前后台对接文档 v2.1

### A. 商家信息部分
0. 获取所有商家信息
```json
GET ./assets/API/businesses.php

RESPONSE:
//On Success:
{
	"code": 0,                                       //操作成功
	"businesses": [                                  //所有商家信息
		{
			"business_id": 3,                        //商家 ID
			"name": "xxxxxx",                        //商家名称
			"industry": "xxxxxx",                    //行业
			"contact": "xxxxxx",                     //联系方式
			"address": "xxxxxx",                     //商家地址
			"willingness": "xxxxxx",                 //合作意愿
			"sponsorship_content": "xxxxxx",         //赞助内容
			"charge_history": "xxxxxx",              //原负责人及合作时间
			"business_evaluation": "xxxxxx",         //商家评价
			"remarks": "xxxxxx",                     //备注
			"is_contacted": 0,                       //是否已被联系 取值为 1 或 0
			"contact_history": "xxxxxx",             //联系历史
			"import_time": "xxxxxx",                 //导入时间
			"update_time": "xxxxxx"                  //更新时间
		},
		...
	]
}

//On Failure:
{
	"code": 1, //数据库为空
	"errMsg": "数据库中暂无任何商家信息"
}
```

1. 根据关键词搜索商家信息
***
- 搜索范围包括：
  - 商家名称
  - 行业
  - 联系方式
  - 商家地址
  - 合作意愿
  - 赞助内容
  - 原负责人及合作时间
  - 商家评价
  - 备注
  - 联系历史
***
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

2. 增加一条商家信息
***
- 允许增加商家信息的账号包括：
  - **部长账号**
  - **干事账号**
***
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
is_contacted: 1
contact_history: xxxxxx

RESPONSE:
//On Success:
{
	"code": 0, //操作成功
	"errMsg": "success"
}

//On Failure:
{
	"code": 3, //无新建商家信息权限（当前登录的是超级管理员账号）
	"errMsg": "权限验证出错！"
}
```

3. 修改一条商家信息
***
- **部长账号**和**干事账号**均可将 `已被联系` 项从 0 修改为 1，或直接增加 `联系历史` 项
- 仅**部长账号**可将 `已被联系` 项从 1 修改为 0，后台在确认账号权限后会自动将 `联系历史` 项清空
- `联系历史` 项的增加只需将增加的内容传给后台即可，建议在修改界面注明“此项仅需填写增加内容”
***
```json
POST(form-data) ./assets/API/businesses.php
operation: update,     //修改操作
business_id: 3         //待修改信息的商家 ID
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
	"code": 4, //无修改商家信息权限（当前登录的是超级管理员账号）
	"errMsg": "权限验证出错！"
}

{
	"code": 5, //未找到与请求商家 ID 所对应的商家数据
	"errMsg": "请求错误，数据库中无对应商家"
}
```

### B. 账号管理部分
0. 获取所有账号信息
```json
GET ./assets/API/accounts.php

RESPONSE:
//On Success:
{
	"code": 0,                                       //操作成功
	"accounts": [                                    //所有账号信息
		{
			"username": "xxxxxx",                    //用户名
			"name": "xxxxxx",                        //姓名
			"is_minister": 1,                        //是否部长账号 取值为 1 或 0
			"register_time": "xxxxxx",               //注册时间
			"update_time": "xxxxxx"                  //更新时间
		},
		...
	]
}

//On Failure:
{
	"code": 1, //数据库为空
	"errMsg": "数据库中暂无任何账号信息"
}
```

1. 新建账号
***
- 超级管理员**能且只能**新建部长账号
- 部长**能且只能**新建干事账号
- 用户名可以使用中文，且一经注册不允许修改
- 密码允许设置为空（如果前端也允许的话）
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
	"code": 2, //无新建账号权限（当前登录的是干事账号）
	"errMsg": "权限验证出错！"
}

{
	"code": 3, //用户名已存在
	"errMsg": "该用户名已存在，请更换"
}
```

2. 修改账号
***
- 超级管理员**能且只能**修改部长账号
- 部长**能且只能**修改干事账号
- 若需要修改密码请将 `set_new_password` 项设置为 1，默认请传输 0
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
	"code": 5, //账号不存在或跨级修改（如超级管理员直接更改干事账号）
	"errMsg": "修改权限内无对应账号！"
}
```

3. 登录系统
```json
POST(form-data) ./assets/API/accounts.php
operation: login              //登录操作
username: xxxxxx              //用户名
password: xxxxxx              //密码

RESPONSE:
//On Success:
{
	"code": 0,                //操作成功
	"usename": "xxx",
	"name": "xxx",
	"is_minister": 1,
	"register_time": "xxx",
	"update_time": "xxx"
}

//On Failure:
{
	"code": 6, //用户名或密码错误
	"errMsg": "用户名或密码错误"
}
```

4. 退出系统
```json
POST(form-data) ./assets/API/accounts.php
operation: logout             //退出系统操作

RESPONSE:
//On Success:
{
	"code": 0, //退出系统成功
	"errMsg": "success"
}
```

### C. 商家信息更新历史记录
***
- 仅**超级管理员账号**和**部长账号**有获取历史记录的权限
- 页面直接返回包含导出数据的 `update_log.csv` 文件，上限为 3000 条
***
```
GET ./assets/API/update_log.php

//On Failure:
{
	"code": 1, //无访问权限（当前登录的是干事账号）
	"errMsg": "权限验证出错！"
}

{
	"code": 2, //暂无数据
	"errMsg": "数据库中暂无更新历史记录" 
}
```

##### 通配错误信息
```json
Extra Failure Response:
{
	"code": 100, //数据库相关操作出错
	"errMsg": "操作失败，请联系管理员"
}

{
	"code": 233, //未登录系统
	"errMsg": "请登录系统！"
}

{
	"code": 2333, //配置错误
	"errMsg": "数据库连接出错，请联系管理员"
}
```
