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
	var shipping_type_id = $('.shipping-type-list li.selected').attr('data-value');
	var shipping_company = 0;var shipping_pick_up = 0;
	if (shipping_type_id == 3) {
		shipping_company = $('.express-company-list li.selected').attr('data-value');
	} else if (shipping_type_id == 2) {
		shipping_pick_up = $('#cs_2 li.selected').attr('data-value');
	} else {
		shipping_pick_up = $('#cs_1 li.selected').attr('data-value');
	}
	if (!address_id) {
		parmas = {
			"consigner": name,
			"mobile": mobile,
			"province": province,
			"city": city,
			"district": district,
			"address": addressinfo,
			"phone": phone,
			"shipping_type":shipping_type_id,
			"shipping_company":shipping_company,
			"shipping_pick_up":shipping_pick_up
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
			"phone": phone,
			"shipping_type":shipping_type_id,
			"shipping_company":shipping_company,
			"shipping_pick_up":shipping_pick_up
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
		toast("請填寫姓名");
		$("#Name").focus();
		return false;
	}
	if ($("#Moblie").val() == "") {
		toast("請填寫行動號碼");
		$("#Moblie").focus();
		return false;
	}
	if ($("#Moblie").val().search(regex.mobile) == -1) {
		toast("請填寫正確的行動號碼");
		$("#Moblie").focus();
		return false;
	}
	
	var phone = $("#phone").val();
	if (phone.length > 0) {
		var pattern = /(^[0-9]{3,4}\-[0-9]{3,8}$)|(^[0-9]{3,8}$)|(^\([0-9]{3,4}\)[0-9]{3,8}$)|(^0{0,1}13[0-9]{9}$)/;
		if (!pattern.test(phone)) {
			toast("請填寫正確的固定號碼");
			$("#phone").focus();
			return false;
		}
	}
	
	if ($("#seleAreaFouth").val() < 0 || $("#seleAreaFouth").val() == "") {
		if ($("#seleAreaNext").val() == "" || $("#seleAreaNext").val() < 0) {
			toast("請選擇地區");
			$("#seleAreaNext").focus();
			return false;
		}
		if ($("#seleAreaThird").val() == "" || $("#seleAreaThird").val() < 0) {
			toast("請選擇市");
			$("#seleAreaThird").focus();
			return false;
		}
		if ($("#seleAreaFouth option").length > 1) {
			if ($("#seleAreaFouth").val() == "" || $("#seleAreaFouth").val() < 0) {
				toast("請選擇區/縣");
				$("#seleAreaFouth").focus();
				return false;
			}
		}
	}

	if (($('#shipping_type').val() == 1 || $('#shipping_type').val() == 2) && $('#pickuphave').val() <= 0) {
		toast("此地區沒有超商支緩，請選擇其他方式。");
		return false;
	}
	
	if ($("#AddressInfo").val() == "") {
		toast("請填寫詳細地址");
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
	
	var opt = new Option("請選擇市", "-1");
	selCity.options.add(opt);
	
	var selArea = $("#seleAreaFouth")[0];
	for (var i = selArea.length - 1; i >= 0; i--) {
		selArea.options[i] = null;
	}
	var opt = new Option("請選擇區/縣", "-1");
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
	var opt = new Option("請選擇區/縣", "-1");
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
	var opt = new Option("請選擇地區", "-1");
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
	});
	$('#seleAreaFouth').change(function(){
		$('.shipping-type-list li.selected').click();
	});

	// 选择配送方式
	$('.delivery-wrap .shipping-type-list li').click(function () {
		$(this).attr('class', 'selected ns-text-color ns-border-color').siblings('li').attr('class', 'ns-border-color-gray');
		$('.delivery-wrap .panel').addClass('hide');
		var type = $(this).attr('data-value');
		$('#shipping_type').val(type);
		$('#AddressInfo').val();
		switch (type) {
			case '1':
				$('.delivery-wrap .pickup-point').removeClass('hide');
				$('#address_detail').hide();
				break;
			case '2':
				$('.delivery-wrap .pickup-point2').removeClass('hide');
				$('#address_detail').hide();
				break;
			case '3':
				$('.delivery-wrap .logistics').removeClass('hide');
				$('#address_detail').show();
				break;
		}
		if (type == 3) return;
		var d = {
			express_type: type,
			province: $("#seleAreaNext").val(),
			city: $("#seleAreaThird").val(),
			district: $("#seleAreaFouth").val()
		};
		if (d.city <= 0) {
			$("#seleAreaThird").focus();return;
		}
		if (d.district <= 0) {
			$("#seleAreaFouth").focus();return;
		}
		api('System.Shop.ExpressType', {'data': JSON.stringify(d)}, function (res) {
			if (res.code == 0) {
				var ul = '', li = '';
				var data = res.data;
				if (data.length > 0) {
					for (var i = 0; i < data.length; i++) {
						if (i == 0) {
							li += '<li class="clearfix selected" data-value="'+data[i].id+'">' +
								'<i class="iconfont iconchecked ns-text-color"></i>';
							$('#AddressInfo').val(data[i].address);
						} else {
							li += '<li class="clearfix" data-value="'+data[i].id+'">' +
								'<i class="iconfont iconcheckbox ns-text-color-gray"></i>';
						}
						li += '<div class="pickup-point-info">' +
							'<h5 class="name">'+data[i].name+'</h5>' +
							'<p class="address ns-text-color-gray" data-addr="'+data[i].address+'">'+data[i].province_name+' '+data[i].city_name+
							' '+data[i].district_name+' '+ data[i].address+'</p>' +
							'</div>' +
							'</li>' +
							'<div class="line ns-bg-color-gray"></div>'
					}
					if (li != '') {
						ul = '<h3 class="v2-title ns-border-color-gray">取貨點</h3>' +
							'<ul class="pickup-point-list" style="margin-bottom: 50px;">';
						ul += li;
					}
					$('#pickuphave').val(1);
				} else {
					if (d.district > 0) {
						ul = '<h3 class="v2-title ns-border-color-gray">此地區沒有超商支緩，請選擇其他取貨方式或聯系我們。</h3>' +
							'<ul class="pickup-point-list" style="margin-bottom: 50px;">';
					}
					$('#pickuphave').val(0);
				}

				if (ul != '') {
					ul += '</ul>';
				}
				$('#cs_'+type).html(ul);
			} else {
				toast(res.message);
			}
		}, false)
	});
	// 物流公司选择 自提点选择 支付方式选择 发票内容选择
	$('.panel').on('click', 'li', function () {
		$(this).addClass('selected').siblings('li').removeClass('selected');
		$(this).parent('ul').find('i.iconfont').attr('class', 'iconfont iconcheckbox ns-text-color-gray');
		$(this).find('i.iconfont').attr('class', 'iconfont iconchecked ns-text-color');

		$('#AddressInfo').val($(this).find('.address').attr('data-addr'));
		// data.shipping_info = {}; // 每次获取前先清空原数据
		// data.shipping_info.shipping_type = $('.shipping-type-list li.selected').attr('data-value');
		// if (data.shipping_info.shipping_type == 1) {
		// 	data.shipping_info.shipping_company_id = $('.express-company-list li.selected').attr('data-value') != undefined ? $('.express-company-list li.selected').attr('data-value') : 0;
		// 	data.shipping_info.shipping_time = $('.shipping-time-list li.selected').attr('data-time');
		// 	data.shipping_info.distribution_time_out = $('.time-out-list span').text();
		// } else if (data.shipping_info.shipping_type == 2) {
		// 	data.shipping_info.pick_up_id = $('.pickup-point-list li.selected').attr('data-value') != undefined ? $('.pickup-point-list li.selected').attr('data-value') : 0;
		// }

	});
});