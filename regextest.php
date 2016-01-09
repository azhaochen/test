<?php

	$html = "<img src='http://tb1.bdstatic.com/tb/cms/ngmis/adsense/file_1451353562767.jpg' style='width: 100%;height: 100%;' alt='贴吧图片' title='贴吧图片'/><div class='img_txt'>
    <p class='img_title'></p>
</div>
<img src='http://tb1.bdstatic.com/tb/cms/ngmis/adsense/file_1431652528522.jpg' style='width: 100%;height: 100%;' alt='贴吧图片' title='贴吧图片'/><div class='img_txt'>
    <p class='img_title'>女神狂爱 献身推荐 你来不来？</p>
</div><img aaaaewhttp' /> <img src='http://m.tiebaimg.com/timg?wapp&amp;imgtype=0&amp;quality=100&amp;size=b5000_5000&amp;cut_x=0&amp;cut_w=0&amp;cut_y=0&amp;cut_h=0&amp;sec=1369815402&amp;di=09f9e44803114823d5e4e6e0e76a5972&amp;wh_rate=null&amp;src=http%3A%2F%2Ftb1.bdstatic.com%2Ftb%2F%25E6%25B3%25B8%25E5%25B7%259E%25E8%2580%2581%25E7%25AA%2596--%25E5%25AE%2598%25E6%2596%25B9%25E5%2585%25A5%25E9%25A9%25BB.jpg' title='' alt=''><div class='pii_bluev pii_bg'></div>";
	
//if(preg_match_all("@<img.+src=\s*(\'|\")?(http\:.+\.(?:jpg|gif|bmp|bnp|png))\s*\1?.*/>@i",$html,$matches)){
 //   var_dump($matches);
//}

if(preg_match_all('@<img.+src=\s*(\'|")?(http((?:(?!\1).)+)\.(?:jpg|gif|bmp|png))\s*\1?.*>@iU',$html,$matches)){
   var_dump($matches);
}


?>

<?php

$str="abd1aegasg1aega13aergea";
if(preg_match_all('@(\d)(.*)\1@i',$str,$matches)){
	//var_dump($matches);
}

$str='dd';
if(preg_match_all("@\\$@i",$str,$matches)){
	//var_dump($matches);
}

?>
