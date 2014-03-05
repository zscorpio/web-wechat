# web-wechat(微信网页版接口)

------

首先上demo:http://labs.zscorpio.com/weixin

config.php			==>一些基础变量定义

getSidUin.php		==>获取sid和uin判断是否登录成功

HttpRequest.php		==>一个http请求类

index.php			==>网页访问路口

jquery-1.11.0.min.js==>jquery

logined.php			==>登录之后的页面,唯一一个操作就是发送消息给Wesley-zhou,你们可以换成自己名字,也可以加Wesley-zhou微信好友

weixin.php			==>网页版微信接口类主要文件

因为使用了redis,需要你们自己配置,或者仅参考weixin.php类,自己重新修改.

有问题请联系zsw.scorpio#gmail.com.