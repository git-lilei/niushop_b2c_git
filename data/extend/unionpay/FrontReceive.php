<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/sdk/acp_service.php';
/**
 * 交易说明：	前台类交易成功才会发送后台通知。后台类交易（有后台通知的接口）交易结束之后成功失败都会发通知。
 *              为保证安全，涉及资金类的交易，收到通知后请再发起查询接口确认交易成功。不涉及资金的交易可以以通知接口respCode=00判断成功。
 *              未收到通知时，查询接口调用时间点请参照此FAQ：https://open.unionpay.com/ajweb/help/faq/list?id=77&level=0&from=0
 */

$logger = com\unionpay\acp\sdk\LogUtil::getLogger();
$logger->LogInfo("receive front notify: " . com\unionpay\acp\sdk\createLinkString ( $_POST, false, true ));

?>
<!DOCTYPE unspecified PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>银联在线交易测试-结果</title>
<style type="text/css">
body table tr td {
	font-size: 14px;
	word-wrap: break-word;
	word-break: break-all;
	empty-cells: show;
}
</style>
</head>
<body>
	<table width="800px" border="1" align="center">
		<tr>
			<th colspan="2" align="center">银联在线交易测试-交易结果</th>
		</tr>
			<?php
			foreach ( $_POST as $key => $val ) {
				?>
			<tr>
			<td width='30%'><?php echo isset($mpi_arr[$key]) ?$mpi_arr[$key] : $key ;?></td>
			<td><?php echo $val ;?></td>
		</tr>
			<?php }?>
			<tr>
			<td width='30%'>验证签名</td>
			<td><?php			
			if (isset ( $_POST ['signature'] )) {
				
				echo com\unionpay\acp\sdk\AcpService::validate ( $_POST ) ? '验签成功' : '验签失败';
				$orderId = $_POST ['orderId']; //其他字段也可用类似方式获取
				$respCode = $_POST ['respCode'];
                //判断respCode=00、A6后，对涉及资金类的交易，请再发起查询接口查询，确定交易成功后更新数据库。

			} else {
				echo '签名为空';
			}
			?></td>
		</tr>
	</table>
	<?php 
		//如果卡号我们业务配了会返回且配了需要加密的话，请按此方法解密
// 		if(array_key_exists ("accNo", $_POST)){
// 			$accNo = com\unionpay\acp\sdk\AcpService::decryptData($_POST["accNo"]);
// 			echo  "accNo=" . $accNo . "<br>\n";
// 		}
	?>
</body>
</html>