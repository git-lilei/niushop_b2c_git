function chcek_virtual_code(){
	var virtual_code = $("#virtual_code").val();
	if(virtual_code.length == 0){
		toast("请输入虚拟码");
		return false;
	}
	api("System.Order.checkCode",{ virtual_code : virtual_code },function (res) {
		var data = res.data;
		if(!data){
			toast(res.message);
		}else{
			location.href=__URL(APPMAIN+'/verification/goods?vg_id='+data);
		}
	});
}