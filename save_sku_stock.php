<?php
/**
 * Created by Juns <46231996@qq.com>.
 * User: jun
 * Date: 2019-08-30 18:37
 * Copyright: @比邻信息科技有限公司
 * Description:
 */


$hdrs = array(
    'http' =>array(
        'method'=>"POST",
        'header' =>
           "Accept: */*\r\n" .
           "Accept-Encoding: gzip, deflate\r\n" .
           "Accept-Language: zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3\r\n" .
           "Accept-Encoding: gzip, deflate\r\n" .
           "Connection: keep-alive\r\n" .
           "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n".
           "DNT: 1\r\n".
           "Referer: http://s.shopmid.online/ad19blmp/goods/goodslist.html\r\n".
           "Cookie: PHPSESSID=fa5e98485dac0270f8cc40db2db90421; module_list=think%3A%5B%5D; admininstance_id=0; adminuser_name=bladm888; appuid=506; appis_system=0; appis_member=1; appinstance_id=0; appuser_name=u156568298881; appinstance_name=%E5%85%A8%E9%A4%A8%E7%B2%BE%E9%81%B8%E5%85%A8%E7%90%83%E5%A5%BD%E7%89%A9; niu_access_token=MDAwMDAwMDAwMJjcemKSuIGetZ6Iq4CdqJrJnoncl7qOq5t6mpGai2HRfM2fqX-1iWa0npqtgYiYZcyCcKE; niu_member_detail=think%3A%7B%22user_info%22%3A%7B%22uid%22%3A%22506%22%2C%22user_name%22%3A%22u156568298881%22%2C%22user_status%22%3A%221%22%2C%22user_headimg%22%3A%22%22%2C%22user_tel%22%3A%22%22%2C%22user_qq%22%3A%22%22%2C%22qq_openid%22%3A%22%22%2C%22qq_info%22%3A%22%22%2C%22user_email%22%3A%22%22%2C%22wx_openid%22%3A%22%22%2C%22wx_is_sub%22%3A0%2C%22wx_info%22%3A%22%22%2C%22real_name%22%3A%22%22%2C%22sex%22%3A0%2C%22location%22%3A%22%22%2C%22nick_name%22%3A%22u156568298881%22%2C%22wx_unionid%22%3A%22%22%2C%22birthday%22%3A0%2C%22user_status_name%22%3A%22%25E6%25AD%25A3%25E5%25B8%25B8%22%7D%2C%22member_level%22%3A%222%22%2C%22member_label%22%3A%22%22%7D; _ga=GA1.2.976827868.1565682990; adminuid=3; adminis_system=1; adminis_member=1; admininstance_name=%E5%85%A8%E9%A4%A8%E7%B2%BE%E9%81%B8%E5%85%A8%E7%90%83%E5%A5%BD%E7%89%A9; adminis_admin=1; admingroup_id=3; _gid=GA1.2.1804288880.1567152408; goodshistory=449%2C433%2C513%2C772%2C773%2C773%2C625%2C438%2C875%2C874%2C873%2C1116%2C921%2C925%2C977%2C1028%2C1079%2C1129%2C1179%2C1130%2C1129%2C161%2C141%2C142%2C139%2C142%2C168%2C171%2C172%2C173%2C174%2C277%2C255%2C235%2C255%2C341%2C327%2C395%2C346%2C445%2C445%2C451%2C450%2C449%2C448%2C447%2C443%2C468; page_cookie=%7B%22page_index%22%3A%2217%22%2C%22show_number%22%3A%2250%22%2C%22url%22%3A%22http%3A%2F%2Fs.shopmid.online%2Fad19blmp%2Fgoods%2Fgoodslist.html%22%7D\r\n" .
           "Host: s.shopmid.online\r\n" .
           "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.42 Safari/537.36\r\n" .
           "X-Requested-With: XMLHttpRequest",
        'timeout'=>2,
        'content' => 'goods_id=514'
    ),
);

$goods = [521];
foreach ($goods as $g){
    $hdrs['http']['content'] = 'goods_id='.$g;
    $context = stream_context_create($hdrs);
    
    $html = file_get_contents('http://s.shopmid.online/ad19blmp/goods/getGoodsSkuList.html', false, $context);
    
    $json = json_decode($html, true);
    if (!$json) {
        echo $g.':error<br>';
        continue;
    }
    $d = [];
    //{"sku_id":"1734","price":"699.00","market_price":"1588.00","cost_price":"0.00","stock":"575"}
    foreach ($json as $j){
        $d[] = [
            'sku_id' => $j['sku_id'],
            'price' => $j['price'],
            'market_price' => $j['market_price'],
            'cost_price' => $j['cost_price'],
            'stock' => $j['stock'],
        ];
    }
    $s = [
        'sku_data' => json_encode([
            [],
            $d
        ]),
        'goods_id' => $g
    ];
    
    $hdrs = array(
        'http' =>array(
            'method'=>"POST",
            'header' =>
                "Accept: */*\r\n" .
                "Accept-Encoding: gzip, deflate\r\n" .
                "Accept-Language: zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3\r\n" .
                "Accept-Encoding: gzip, deflate\r\n" .
                "Connection: keep-alive\r\n" .
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n".
                "DNT: 1\r\n".
                "Referer: http://s.shopmid.online/ad19blmp/goods/goodslist.html\r\n".
                "Cookie: PHPSESSID=fa5e98485dac0270f8cc40db2db90421; module_list=think%3A%5B%5D; admininstance_id=0; adminuser_name=bladm888; appuid=506; appis_system=0; appis_member=1; appinstance_id=0; appuser_name=u156568298881; appinstance_name=%E5%85%A8%E9%A4%A8%E7%B2%BE%E9%81%B8%E5%85%A8%E7%90%83%E5%A5%BD%E7%89%A9; niu_access_token=MDAwMDAwMDAwMJjcemKSuIGetZ6Iq4CdqJrJnoncl7qOq5t6mpGai2HRfM2fqX-1iWa0npqtgYiYZcyCcKE; niu_member_detail=think%3A%7B%22user_info%22%3A%7B%22uid%22%3A%22506%22%2C%22user_name%22%3A%22u156568298881%22%2C%22user_status%22%3A%221%22%2C%22user_headimg%22%3A%22%22%2C%22user_tel%22%3A%22%22%2C%22user_qq%22%3A%22%22%2C%22qq_openid%22%3A%22%22%2C%22qq_info%22%3A%22%22%2C%22user_email%22%3A%22%22%2C%22wx_openid%22%3A%22%22%2C%22wx_is_sub%22%3A0%2C%22wx_info%22%3A%22%22%2C%22real_name%22%3A%22%22%2C%22sex%22%3A0%2C%22location%22%3A%22%22%2C%22nick_name%22%3A%22u156568298881%22%2C%22wx_unionid%22%3A%22%22%2C%22birthday%22%3A0%2C%22user_status_name%22%3A%22%25E6%25AD%25A3%25E5%25B8%25B8%22%7D%2C%22member_level%22%3A%222%22%2C%22member_label%22%3A%22%22%7D; _ga=GA1.2.976827868.1565682990; adminuid=3; adminis_system=1; adminis_member=1; admininstance_name=%E5%85%A8%E9%A4%A8%E7%B2%BE%E9%81%B8%E5%85%A8%E7%90%83%E5%A5%BD%E7%89%A9; adminis_admin=1; admingroup_id=3; _gid=GA1.2.1804288880.1567152408; goodshistory=449%2C433%2C513%2C772%2C773%2C773%2C625%2C438%2C875%2C874%2C873%2C1116%2C921%2C925%2C977%2C1028%2C1079%2C1129%2C1179%2C1130%2C1129%2C161%2C141%2C142%2C139%2C142%2C168%2C171%2C172%2C173%2C174%2C277%2C255%2C235%2C255%2C341%2C327%2C395%2C346%2C445%2C445%2C451%2C450%2C449%2C448%2C447%2C443%2C468; page_cookie=%7B%22page_index%22%3A%2217%22%2C%22show_number%22%3A%2250%22%2C%22url%22%3A%22http%3A%2F%2Fs.shopmid.online%2Fad19blmp%2Fgoods%2Fgoodslist.html%22%7D\r\n" .
                "Host: s.shopmid.online\r\n" .
                "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.42 Safari/537.36\r\n" .
                "X-Requested-With: XMLHttpRequest",
            'timeout'=>2,
            'content' => http_build_query ($s)
        ),
    );
    
    $context2 = stream_context_create($hdrs);
    
    $html2 = file_get_contents('http://s.shopmid.online/ad19blmp/goods/editGoodsSku.html', false, $context2);
    $j2 = json_decode($html2, true);
    echo $g.':'.$j2['message'].'<br/>';
    
}



