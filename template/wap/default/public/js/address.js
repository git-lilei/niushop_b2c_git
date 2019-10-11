function saveAddress() {
	if (!Check_Consignee()) {
		return false;
	}
	var addressinfo = $("#AddressInfo").val();
	var province = $("#seleAreaNext").val();
	var city = $("#seleAreaThird").val();
	var district = $("#seleAreaFouth").val();
	var name = $("#Name").val();
	var mobile = $("#Moblie").val();
	var address_id = $("#adressid").val();
	var parmas = {}, ajax_url = '';
	var phone = $("#phone").val();
	var goods_id = $("#hidden_goods_id").val();
	var bargain_id = $("#hidden_bargain_id").val();
	
	if (!address_id) {
		parmas = {
			"consigner": name,
			"mobile": mobile,
			"province": province,
			"city": city,
			"district": district,
			"address": addressinfo,
			"phone": phone
		};
		ajax_url = "addAddress";
	} else {
		parmas = {
			"id": address_id,
			"consigner": name,
			"mobile": mobile,
			"province": province,
			"city": city,
			"district": district,
			"address": addressinfo,
			"phone": phone
		};
		ajax_url = "updateAddress";
	}
	var flag = $("#hidden_flag").val();
	var ref_url = $("#ref_url").val();
	
	api("System.Member." + ajax_url, parmas, function (data) {
		var txt = data['data'];
		if (txt > 0) {
			if (flag == 1) {
				location.href = __URL(APPMAIN + "/member/address?flag=1");
			} else if (flag == 2) {
				location.href = __URL(APPMAIN + "/member/receivePrize");
			} else if (flag == 9) {
				location.href = __URL(APPMAIN + "/goods/detail?goods_id=" + goods_id + "&bargain_id=" + bargain_id);
			} else {
				if (ref_url != '') {
					location.href = __URL(APPMAIN + "/order/payment");
				} else {
					location.href = __URL(APPMAIN + "/member/address");
				}
			}
		} else {
			toast(txt);
		}
	})
}

function Check_Consignee() {
	if ($("#Name").val() == "") {
		toast("姓名不能为空");
		$("#Name").focus();
		return false;
	}
	if ($("#Moblie").val() == "") {
		toast("手机号码不能为空");
		$("#Moblie").focus();
		return false;
	}
	if ($("#Moblie").val().search(regex.mobile) == -1) {
		toast("请输入正确的手机号码");
		$("#Moblie").focus();
		return false;
	}
	
	var phone = $("#phone").val();
	if (phone.length > 0) {
		var pattern = /(^[0-9]{3,4}\-[0-9]{3,8}$)|(^[0-9]{3,8}$)|(^\([0-9]{3,4}\)[0-9]{3,8}$)|(^0{0,1}13[0-9]{9}$)/;
		if (!pattern.test(phone)) {
			toast("请输入正确的固定电话");
			$("#phone").focus();
			return false;
		}
	}
	
	if ($("#seleAreaFouth").val() < 0 || $("#seleAreaFouth").val() == "") {
		if ($("#seleAreaNext").val() == "" || $("#seleAreaNext").val() < 0) {
			toast("请选择省份");
			$("#seleAreaNext").focus();
			return false;
		}
		if ($("#seleAreaThird").val() == "" || $("#seleAreaThird").val() < 0) {
			toast("请选择市");
			$("#seleAreaThird").focus();
			return false;
		}
		if ($("#seleAreaFouth option").length > 1) {
			if ($("#seleAreaFouth").val() == "" || $("#seleAreaFouth").val() < 0) {
				toast("请选择区/县");
				$("#seleAreaFouth").focus();
				return false;
			}
		}
	}
	
	if ($("#AddressInfo").val() == "") {
		toast("详细地址不能为空");
		$("#AddressInfo").focus();
		return false;
	}
	
	return true;
}

// 选择省份弹出市区
function GetProvince() {
	var id = $("#seleAreaNext").find("option:selected").val();
	var selCity = $("#seleAreaThird")[0];
	for (var i = selCity.length - 1; i >= 0; i--) {
		selCity.options[i] = null;
	}
	
	var opt = new Option("请选择市", "-1");
	selCity.options.add(opt);
	
	var selArea = $("#seleAreaFouth")[0];
	for (var i = selArea.length - 1; i >= 0; i--) {
		selArea.options[i] = null;
	}
	var opt = new Option("请选择区/县", "-1");
	selArea.options.add(opt);
	api('System.Address.city', {"province_id": id}, function (res) {
		var data = res.data;
		if (data != null && data.length > 0) {
			for (var i = 0; i < data.length; i++) {
				var opt = new Option(data[i].city_name, data[i].city_id);
				selCity.options.add(opt);
			}
			if (typeof($("#cityid").val()) != 'undefined') {
				$("#seleAreaThird").val($("#cityid").val());
				getSelCity();
				$("#cityid").val('-1');
			}
		}
	})
}

// 选择市区弹出区域
function getSelCity() {
	var id = $("#seleAreaThird").find("option:selected").val();
	var selArea = $("#seleAreaFouth")[0];
	for (var i = selArea.length - 1; i >= 0; i--) {
		selArea.options[i] = null;
	}
	var opt = new Option("请选择区/县", "-1");
	selArea.options.add(opt);
	api('System.Address.district', {"city_id": id}, function (res) {
		var data = res.data;
		if (data != null && data.length > 0) {
			for (var i = 0; i < data.length; i++) {
				var opt = new Option(data[i].district_name, data[i].district_id);
				selArea.options.add(opt);
			}
			if (typeof($("#districtid").val()) != 'undefined') {
				$("#seleAreaFouth").val($("#districtid").val());
				$("#districtid").val('-1');
			}
		}
	});
}

$(function () {
	var selCity = $("#seleAreaNext")[0];
	for (var i = selCity.length - 1; i >= 0; i--) {
		selCity.options[i] = null;
	}
	var opt = new Option("请选择省", "-1");
	selCity.options.add(opt);
	// 添加省
	api('System.Address.province', {}, function (res) {
		var data = res.data;
		if (data != null && data.length > 0) {
			for (var i = 0; i < data.length; i++) {
				var opt = new Option(data[i].province_name,
					data[i].province_id);
				selCity.options.add(opt);
			}
			if (typeof($("#provinceid").val()) != 'undefined') {
				$("#seleAreaNext").val($("#provinceid").val());
				GetProvince();
				$("#provinceid").val('-1');
			}
		}
	})
});