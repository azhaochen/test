<?php

class Mycurl
{
	private $_optionArr = array();
	
	private $_useProxy  = false;	//使用代理
	private $_useCookie = false;	//带上cookie
	private $_useAuth   = false;	//需要登录权限？
	private $_usePost	= false;	//Post方式访问？
	private $_useHeader	= false;	//自己设置http头字段
	
	//执行curl_excu之后的结果
	public $webpage	='';		//获取的页面
	public $errno=0;			//curl执行返回
	public $request;			//request串
	public $httpcode;			//http状态码
	public $cookie = array();	//request里的cookie
	
	public function __construct()
	{
		//basic options
		$this->optionArr = array(
			CURLOPT_RETURNTRANSFER	=> true,	//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
			CURLOPT_TIMEOUT			=> 20,		//设置cURL允许执行的最长秒数
			CURLOPT_FOLLOWLOCATION	=> true,	//遇到跳转页面，自动跳转，前提是php配置safemode=off
												//CURLOPT_FOLLOWLOCATION cannot be activated when safe_mode is enabled or an open_basedir is set 
			CURLOPT_MAXREDIRS		=> 3,		//最大跳转次数
			CURLOPT_AUTOREFERER		=> true,	//当根据Location:重定向时，自动设置header中的Referer:信息
			CURLOPT_USERAGENT		=> 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1',
			CURLOPT_REFERER			=> 'http://www.google.com',
			CURLOPT_ENCODING		=> 'gzip,deflate',
			CURLOPT_HEADER			=> true,	//返回http response头，还是加上会用到，而且相对于html而言也比较小
			CURLINFO_HEADER_OUT		=> true,	//用于获取当前请求的request串
		);
	}
	
	public function setPost($postArr)
	{
		if(empty($postArr))
			return;
		$this->_usePost = true;
		$this->optionArr[CURLOPT_POST]=true;
		//$postArr = array('name' => 'Foo', 'file' => '@/home/user/test.png');
		$this->optionArr[CURLOPT_POSTFIELDS]=$postArr;		//注意要urlencode()
	}

	//第一次从浏览器传入的cookie
	public function setLoginCookie($str){		//str中存放的是从浏览器里拷贝的cookie
		$this->_useCookie = true;
		$str = str_replace(PHP_EOL,'',$str);	//PHP_EOL是php自动根据windows或unix选择\r\n还是\n
		$tmp = explode(';',$str);
		foreach($tmp as $key => $value){
			$tmp1 = explode('=',trim($value));
			$this->cookie[$tmp1[0]]=$tmp1[1];
		}
	}
	
	//把cookie存入内存，减少cookie文件IO
	private function _setCookie()
	{
		$cookieStr='';
		foreach($this->cookie as $key => $value){
			$cookieStr.= $key."=$value; ";
		}
		$this->optionArr[CURLOPT_COOKIE] = trim($cookieStr);	
		//1、设定HTTP请求中"Cookie: "部分的内容。多个cookie用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red")
		//2、注意使用这个的时候，不可在 curl_setopt ($ch, CURLOPT_HTTPHEADER , $header ); 的$header里包含Cookie参数，否则会重叠，造成cookie不可预见的情况发生。
		//3、已验证：curl遇到跳转（及http request头包含Location: 字段）时自动跳转时会自动带上这里设置的cookie，所以放心使用。
	}

	//使用cookie文件
	//public function setCookie2($cookieFile='')
	//{
	//	if($cookieFile==''){
	//		$cookieFile = tempnam(__DIR__.DIRECTORY_SEPARATOR.'cookie.txt','cookie');
	//	}
	//	$this->_useCookie = true;
	//	$this->optionArr[CURLOPT_COOKIEJAR]	=$cookieFile;	//把返回的cookie保存到指定的文件中
	//	$this->optionArr[CURLOPT_COOKIEFILE]=$cookieFile;	//curl下一次发请求时从指定的文件中读取cookie
	//}
	
	public function setProxy($host,$port=8080,$name='',$pwd='')
	{
		$this->_useProxy = true;
		$this->optionArr[CURLOPT_PROXY]="$host:$port";
		if($name!=''){
			$this->optionArr[CURLOPT_PROXYUSERPWD]="$name:$pwd";
		}
		//$this->optionArr[CURLOPT_PROXYAUTH] = CURLAUTH_BASIC;	//默认
		//$this->optionArr[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;	//默认
	}
	
	
	public function setAuth($name,$pwd)
	{
		$this->_useAuth = true;
		$this->optionArr[CURLOPT_HTTPAUTH]=CURLAUTH_BASIC;
		$this->optionArr[CURLOPT_USERPWD]="$name:$pwd";
	}
	
	public function setHeader($headerArr)
	{
		$this->_useHeader = true;
		//$headerArr=array('Content-type: text/plain', 'Content-length: 100')
		$this->optionArr[CURLOPT_HTTPHEADER] = $headerArr;	//设置header相应的字段, 并不是整个header字段
	}
	
	public function setOthers($key,$value)
	{
		/*I had problems with the Wikimedia software and sending a POST request where the data was more than 1024 bytes long. 
			I traced this to cURL adding: Expect: 100-continue to the headers.
			Sending a post file upload across a squid proxy, the request was rejected by the proxy. In the error page returned 
			it provided among other possible causes:"Expect:" feature is being asked from a HTTP/one.zero.
			Solution: Add the option <?php curl_setopt($cl,CURLOPT_HTTPHEADER,array("Expect:")); ?>. This will remove the expect http header.
		
			$this->_useHeader = true;
			$this->optionArr[CURLOPT_HTTPHEADER] = array("Expect:");
		*/
		
		$this->optionArr[$key] = $value;
	}
	
	
	
	public function execute($url)
	{
		if($this->_useCookie)
			$this->_setCookie();

		if (substr($url,0,8) == "https://")
		{
			$this->optionArr[CURLOPT_SSL_VERIFYPEER] = false;	//跳过证书检查
			$this->optionArr[CURLOPT_SSL_VERIFYHOST] = false;	//从证书中检查SSL加密算法是否存在
		}
		
		$ch = curl_init($url);
		curl_setopt_array($ch,$this->optionArr);
		$this->webpage	= curl_exec($ch);
		$this->errno	= curl_errno($ch); 							//非0表示curl执行错误
		$this->errmsg	= curl_error($ch) ; 
		$this->httpcode	= curl_getinfo($ch,CURLINFO_HTTP_CODE);		//http请求状态码
		$this->request	= curl_getinfo($ch,CURLINFO_HEADER_OUT);	//http请求串,调试用
		curl_close($ch);
		
		if($this->_useCookie)
			$this->_RenewCookie();	//如果本页面有传入新的cookie, 更新到$this->cookie中
	}

	private function _RenewCookie()
	{
		preg_match_all('/Set-Cookie:(.*);/iU',$this->webpage,$matches); //正则匹配，U的功能待了解？？？
		if(isset($matches[1])&&!empty($matches[1])){
			foreach($matches[1] as $key => $value){
				$tmp=explode('=',trim($value));
				$this->cookie[$tmp[0]]=$tmp[1];
			}
		}
	}
	

	public function getResult($key='')
	{
		$data = array(
				"errno"	 	=>$this->errno,
				"webpage"	=>$this->webpage,
				"httpcode"	=>$this->httpcode,
				"request"	=>$this->request
			);
		
		if(empty($key)){
			return $data;
		}else{
			if(array_key_exists($key,$data))
				return $data[$key];
		}
	}


}




/*----------_curl类使用方法举例----------
	$mycurl = new Mycurl();
	$mycurl->setProxy("web-proxy.oa.com",8080);
	$str='YF-Ugrow-G0=ad83bc19c1269e709f753b172bddb094; SUE=es%3D790782302af28f8df63e37e0d61791db%26ev%3Dv1%26es2%3D8d36154014c7df3cd5a716b35303aa7a%26rs0%3Dbh%252BkuvBQlze0vEjEgYPooK2afh0E9Dujn8PCJX2RgT0TnHExxMWtVl3gA1oa61x2r7DKStQEuB3bmkDnconppph3REUpTZnKN4uv4gNNZbna%252BNfPEZt54Ccf3jQOpILjdAhjwKhW7F4agcJ%252FkhhBp%252BBAsV7kGsJmPcWKbef49Ks%253D%26rv%3D0; SUP=cv%3D1%26bt%3D1451357854%26et%3D1451444254%26d%3Dc909%26i%3De078%26us%3D1%26vf%3D0%26vt%3D0%26ac%3D0%26st%3D0%26uid%3D2305390305%26name%3Dchen.zh01%2540mail.scut.edu.cn%26nick%3D%25E5%258D%2581%25E6%2597%25A5%25E5%258D%2581%25E6%259C%2588%26fmp%3D%26lcp%3D; SUB=_2A257hYbODeRxGeRN61cS-S7PyzmIHXVY8v8GrDV8PUJbvNBeLXLHkW8ZJO1yg3IGL0aOwvp3zlX8k-4Elg..; SUBP=0033WrSXqPxfM725Ws9jqgMF55529P9D9WWoY4glFPWOxXcvWe.YCZnC5JpX5K-t; SUHB=0y0oLKMs415o3N; ALF=1482889912; SSOLoginState=1451357854; wvr=6; YF-V5-G0=f7add382196ce7818cd5832b5a20aaf5; Apache=3509581864345.819.1451439231756; ULV=1451439231768:9:5:1:3509581864345.819.1451439231756:1451013832169; UOR=codeigniter.org.cn,widget.weibo.com,www.baidu.com; YF-Page-G0=bf52586d49155798180a63302f873b5e';
	$mycurl->setLoginCookie($str);

	$urlArr = array(
		"http://www.baidu.com",
		"http://tieba.baidu.com",
	);


	foreach($urlArr as $key => $value){
		$mycurl->execute($value);		//执行下载
		$data = $mycurl->getResult();	//结果
		$filepath=__DIR__.DIRECTORY_SEPARATOR."r1.html";
		file_put_contents($filepath,$data['webpage']);	//保存
	}

*/
?>
