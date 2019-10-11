$(function () {
	$('[name="province"]').change(function () {
		getCity('[name="city"]', $(this).val());
		$('[name="district"]').html('<option value="0">请选择区/县</option>');
	});
	
	$('[name="city"]').change(function () {
		getDistrict('[name="district"]', $(this).val());
	});
});

$('#myPager').pager({
	linkCreator: function (page, pager) {
		return __URL(SHOPMAIN + "/member/address?page=" + page);
	}
});

// 设为默认
function setDefault(id) {
	api("System.Member.modifyAddressDefault", {"id": id}, function (res) {
		if (res.data > 0) {
			location.reload();
		} else {
			show(res.message);
		}
	});
}

// 删除地址
function delAddress(id) {
	api("System.Member.addressDelete" +
		"", {"id": id}, function (res) {
		if (res.data > 0) {
			show('地址删除成功！');
			location.reload();
		} else {
			show(res.message);
		}
	});
}

// 添加或修改地址
var is_sub = false;

function save() {
	if (verify()) {
		if (is_sub) return;
		is_sub = true;
		var field = {};
		$('.address-form [name]').each(function (index, el) {
			var key = $(el).attr('name');
			field[key] = $(el).val();
		});
		var url = field.id == undefined ? 'System.Member.addAddress' : 'System.Member.updateAddress';
		api(url, field, function (res) {
			if (res.data > 0) {
				window.location.href = __URL(SHOPMAIN + "/member/address");
			} else {
				show(res.message);
				is_sub = false;
			}
		});
	}
}

// 验证
function verify() {
	if ($('[name="consigner"]').val().search(/[\S]+/)) {
		$('[name="consigner"]').focus();
		show('请输入收货人姓名');
		return false;
	}
	if ($('[name="province"]').val() == 0) {
		$('[name="province"]').focus();
		show('请选择省');
		return false;
	}
	if ($('[name="city"]').val() == 0) {
		$('[name="city"]').focus();
		show('请选择市');
		return false;
	}
	if ($('[name="district"]').val() == 0 && $('[name="district"] option').length > 1) {
		$('[name="district"]').focus();
		show('请选择区/县');
		return false;
	}
	if ($('[name="address"]').val().search(/[\S]+/)) {
		$('[name="address"]').focus();
		show('请输入详细地址');
		return false;
	}
	if ($('[name="mobile"]').val().search(regex.mobile)) {
		$('[name="mobile"]').focus();
		show('请输入正确的手机号码');
		return false;
	}
	
	return true;
}