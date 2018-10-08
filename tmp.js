var OAuth = require('../oauth.js');
var wxapiobj = undefined;
var req = undefined;
var loginInfo = {openid:''};
var empty = function(x){
	if(typeof x=='undefined' || !x || x=='' || x==0 || x==null || x=='undefined' || x==undefined){
		return 1;
	}else{
		return 0;
	}
}

/*用于修改公众平台设置授权url的验证
	console.log(req.query);
	if(!empty(req.query.nonce) && !empty(req.query.signature)){
	    var r = OAuth.checkSignature(req.query);
	    console.log(r);
	    res.send(r);
	    return;
	}
*/

var getWxApi = function(){
	if(!wxapiobj){
		var wxapiobj = new OAuth('wx689cab74de842b67', '2d6e5db557c32d9bd0eb6696c04d704e');
	}
	return wxapiobj;
}


var precessLogin = function(oriurl){
    /*var urlencode = require('urlencode');*/
    var api = getWxApi();
    var wxauthurl = api.getAuthorizeURL(oriurl,0,'snsapi_base');
    res.redirect(wxauthurl);
}

var getLoginInfo = function(code){
	if(!empty(req.cookies.openid) && !empty(req.cookies.access_token)){
		loginInfo.openid = req.cookies.openid;
		loginInfo.access_token = req.cookies.access_token;
		loginInfo.head = req.cookies.head;
		loginInfo.nick = urldecode(req.cookies.nick);
	}
	if(!empty(code)){
		//查询code对应的access_token及用户信息，并保存在cookie里面
		var api = getWxApi();
		api.getAccessToken(code,function(err,data){
			console.log(data);
			loginInfo = data;
		});
	}
	return loginInfo;
}


module.exports = function (request, res) {
	req = request;
	console.log(req.originalUrl,'...');
	console.log(req.cookies);
	console.log(req.query);

	//1.获取登录信息，未登录则登录
	if(!empty(req.query.code)){
		loginInfo = getLoginInfo(req.query.code);	//weixin登录跳转回来
	}else{
		var isLogin = 0;
		if(!empty(req.cookies.openid) && !empty(req.cookies.access_token)){
			isLogin = 1;
		}
		if(isLogin==0){
			var oriurl = 'http://http://134.175.16.24/weixin/acts/index.js';
			precessLogin(oriurl);
		}else{
			loginInfo = getLoginInfo();				//已登录，从cookie里面取登录信息
		}
	}

	res.json(loginInfo);
};
