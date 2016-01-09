<?php
//rolling_curl参考：https://github.com/takinbo/rolling-curl

require_once "Mycurl.php"


class MycurlMulti extends Mycurl
{
	
	// 1、初始化任务队列
	$master = curl_multi_init();
	
	do{
		while (($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM)
		{	/* 2、
			curl_multi_exec 此方法，在do-while循环中被多次调用，PHP文档给此方法的解释是“处理在栈中的每一个句柄。
			无论该句柄需要读取或写入数据都可调用此方法。”,也就是说第一次调用此方法是用于发出HTTP请求数据，当栈中的句柄还有数据需要传送时，
			就会返回 CURLM_CALL_MULTI_PERFORM，当返回CURLM_OK只是意味着数据传送完毕或者没有数据 可传送。
			此方法是不阻塞的，也就是说一旦栈中某个或多个curl句柄有数据读取或者写入，就可以调用此方法传送数据，并立即返回栈中句柄的活动状态。
			$running参数，标志整个批处理栈过程有没有结束。。。
			*/
			;
		}
		if ($execrun != CURLM_OK)	//curl_multi_exec 结束时，应该返回CURLM_OK，返回其他值表示出错了。
			break;
		
		
		while ($done = curl_multi_info_read($master)) {
			// 3、curl_multi_info_read 查询批处理句柄里是否有某个传输线程中有消息或信息返回。
			// $done['handle'] 得到这个有返回response的句柄

			// 4、从请求中获取信息、内容、错误
			$info = curl_getinfo($done['handle']);
			$output = curl_multi_getcontent($done['handle']);	//html
			$error = curl_error($done['handle']);

			// 5、回调函数处理
			$callback = $this->callback;
			if (is_callable($callback)) {
				$key = (string) $done['handle'];
				$request = $this->requests[$this->requestMap[$key]];
				unset($this->requestMap[$key]);
				call_user_func($callback, $output, $info, $request);
			}

			
			// 6、一个请求完了，就加一个进来，一直保证多个任务同时进行 (it's important to do this before removing the old one)
			if ($i < sizeof($this->requests) && isset($this->requests[$i]) && $i < count($this->requests)) {
				$ch = curl_init();
				$options = $this->get_options($this->requests[$i]);
				curl_setopt_array($ch, $options);
				curl_multi_add_handle($master, $ch);

				// Add to our request Maps
				$key = (string) $ch;
				$this->requestMap[$key] = $i;
				$i++;
			}

			// 7、把请求已经完成了得 curl handle 删除
			curl_multi_remove_handle($master, $done['handle']);

		}

		// 8、还未完。等待某个连接直到收到response
		if ($running)
			curl_multi_select($master, 10) == -1 ? usleep(1) : 0;	// 返回值貌似容易是-1，这样处理是参考官方文档
																	//curl_multi_select 阻塞的，但是阻塞的时间自己设定，如10s
    }while($running);
    
	curl_multi_close($master);


}




/*----------_curl类使用方法举例----------
	$mycurl = new Mycurl();
	$mycurl->setProxy("web-proxy.oa.com",8080);
	$str='SINAGLOBAL=1148521739631.7915.1442221848724; _s_tentry=tool.lanrentuku.com; YF-Ugrow-G0=ad83bc19c1269e709f753b172bddb094; SUS=SID-2305390305-1451357854-GZ-0oetl-d7e1c4a0db13c52effee547766abe078; SUE=es%3D790782302af28f8df63e37e0d61791db%26ev%3Dv1%26es2%3D8d36154014c7df3cd5a716b35303aa7a%26rs0%3Dbh%252BkuvBQlze0vEjEgYPooK2afh0E9Dujn8PCJX2RgT0TnHExxMWtVl3gA1oa61x2r7DKStQEuB3bmkDnconppph3REUpTZnKN4uv4gNNZbna%252BNfPEZt54Ccf3jQOpILjdAhjwKhW7F4agcJ%252FkhhBp%252BBAsV7kGsJmPcWKbef49Ks%253D%26rv%3D0; SUP=cv%3D1%26bt%3D1451357854%26et%3D1451444254%26d%3Dc909%26i%3De078%26us%3D1%26vf%3D0%26vt%3D0%26ac%3D0%26st%3D0%26uid%3D2305390305%26name%3Dchen.zh01%2540mail.scut.edu.cn%26nick%3D%25E5%258D%2581%25E6%2597%25A5%25E5%258D%2581%25E6%259C%2588%26fmp%3D%26lcp%3D; SUB=_2A257hYbODeRxGeRN61cS-S7PyzmIHXVY8v8GrDV8PUJbvNBeLXLHkW8ZJO1yg3IGL0aOwvp3zlX8k-4Elg..; SUBP=0033WrSXqPxfM725Ws9jqgMF55529P9D9WWoY4glFPWOxXcvWe.YCZnC5JpX5K-t; SUHB=0y0oLKMs415o3N; ALF=1482889912; SSOLoginState=1451357854; wvr=6; YF-V5-G0=f7add382196ce7818cd5832b5a20aaf5; Apache=3509581864345.819.1451439231756; ULV=1451439231768:9:5:1:3509581864345.819.1451439231756:1451013832169; UOR=codeigniter.org.cn,widget.weibo.com,www.baidu.com; YF-Page-G0=bf52586d49155798180a63302f873b5e';
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
