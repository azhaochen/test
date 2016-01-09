<?php
	include "HTTPCODE.php";

	$url = 'http://tieba.baidu.com';
	$cookie='SINAGLOBAL=1148521739631.7915.1442221848724; _s_tentry=tool.lanrentuku.com; YF-Ugrow-G0=ad83bc19c1269e709f753b172bddb094; SUS=SID-2305390305-1451357854-GZ-0oetl-d7e1c4a0db13c52effee547766abe078; SUE=es%3D790782302af28f8df63e37e0d61791db%26ev%3Dv1%26es2%3D8d36154014c7df3cd5a716b35303aa7a%26rs0%3Dbh%252BkuvBQlze0vEjEgYPooK2afh0E9Dujn8PCJX2RgT0TnHExxMWtVl3gA1oa61x2r7DKStQEuB3bmkDnconppph3REUpTZnKN4uv4gNNZbna%252BNfPEZt54Ccf3jQOpILjdAhjwKhW7F4agcJ%252FkhhBp%252BBAsV7kGsJmPcWKbef49Ks%253D%26rv%3D0; SUP=cv%3D1%26bt%3D1451357854%26et%3D1451444254%26d%3Dc909%26i%3De078%26us%3D1%26vf%3D0%26vt%3D0%26ac%3D0%26st%3D0%26uid%3D2305390305%26name%3Dchen.zh01%2540mail.scut.edu.cn%26nick%3D%25E5%258D%2581%25E6%2597%25A5%25E5%258D%2581%25E6%259C%2588%26fmp%3D%26lcp%3D; SUB=_2A257hYbODeRxGeRN61cS-S7PyzmIHXVY8v8GrDV8PUJbvNBeLXLHkW8ZJO1yg3IGL0aOwvp3zlX8k-4Elg..; SUBP=0033WrSXqPxfM725Ws9jqgMF55529P9D9WWoY4glFPWOxXcvWe.YCZnC5JpX5K-t; SUHB=0y0oLKMs415o3N; ALF=1482889912; SSOLoginState=1451357854; wvr=6; YF-V5-G0=f7add382196ce7818cd5832b5a20aaf5; Apache=3509581864345.819.1451439231756; ULV=1451439231768:9:5:1:3509581864345.819.1451439231756:1451013832169; UOR=codeigniter.org.cn,widget.weibo.com,www.baidu.com; YF-Page-G0=bf52586d49155798180a63302f873b5e';
	//$url = 'http://user.qzone.qq.com/761212804';
	//$cookie='hasShowWeiyun761212804=1; pgv_flv=10.0; hasShowWeiyun761212804=1; cuid=2473639170; __Q_w_s__QZN_TodoMsgCnt=1; ts_uid=9186974465; __Q_w_s_hat_seed=1; sd_userid=87531450333819360; sd_cookie_crttime=1450333819360; rankv=2015121709; RK=gUdP/uMYGK; pgv_pvi=6011429888; pgv_si=s4047378432; QZ_FE_WEBP_SUPPORT=1; cpu_performance_v8=1; pgv_info=ssid=s5566468510; pgv_pvid=5574890549; o_cookie=761212804; pt_clientip=a2803b257d3a8833; pt_serverip=3b6a0af17263b2a7; p_uin=o0761212804; p_skey=xcILCP-8mAWyUlT9ZFKiJrnMBKN5XovLPCVC1i97T0w_; pt4_token=IEOsbCcZy1ED7*DMHPnWspA-6UgF-r*9XQaQNvFH6Xc_; pt2gguin=o0761212804; uin=o0761212804; skey=@Rlt8htMJ0; ptisp=ctc; qzone_check=761212804_1451441652; ptcz=b947ca2f538f7e4edfae0139557cdf1490aa9952f544cc38ee9e762205e64f60; Loading=Yes; qzspeedup=sdch; qz_screen=1920x1080';
	
	
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	 //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
	curl_setopt($ch,CURLOPT_PROXY,"web-proxy.oa.com:8080"); //代理
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	
    $html = curl_exec($ch);
	$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	$request  = curl_getinfo($ch,CURLINFO_HEADER_OUT);
	$contentype  = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
    curl_close($ch);
	
	
	echo $httpCode.": $http_code[$httpCode]\n";
	echo "Request: ".$request."\n";
	echo "Content-type: ".$contentype."\n";

	if(preg_match_all('@<img.*\s*src=\s*(\'|")?(http(?:(?!\1).)+\.(?:jpg|gif|bmp|bnp|png))\s*\1?.*>@iU',$html,$matches)){
		//var_dump($matches);
		file_put_contents(__DIR__.DIRECTORY_SEPARATOR."result.txt",var_export($matches,true));
	}
	
	//保存文件
	//$filepath=__DIR__.DIRECTORY_SEPARATOR."r.html";
	//file_put_contents($filepath,$html);

?>
