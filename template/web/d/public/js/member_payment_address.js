// 待支付订单 地址相关
var Address = {},
	is_sub_address = false;

// 添加地址
Address.addAddress = function (event) {
	$('#addressModal .modal-title').text('新增收货地址');
	$('#addressModal').modal();
};

// 修改地址
Address.editAddress = function (event) {
	$('#addressModal .modal-title').text('编辑收货地址');
	var dataJson = $(event).parents('li').find('[data-json]').attr('data-json'),
		data = JSON.parse(dataJson);
	
	getProvince('#addressModal [name="province"]', data.province);
	getCity('#addressModal [name="city"]', data.province, data.city);
	getDistrict('#addressModal [name="district"]', data.city, data.district);
	$('#addressModal [name="consigner"]').val(data.consigner);
	$('#addressModal [name="address"]').val(data.address);
	$('#addressModal [name="mobile"]').val(data.mobile);
	$('#addressModal [name="phone"]').val(data.phone);
	$('#addressModal [name="id"]').val(data.id);
	
	$('#addressModal').modal();
};

// 设为默认地址
Address.setDefaultAddr = function (id) {
	api("System.Member.modifyAddressDefault", {"id": id}, function (res) {
		if (res.data > 0) {
			location.reload();
		} else {
			show(res.message);
		}
	});
};

// 删除地址
Address.deleteAddr = function (id) {
	api("System.Member.addressDelete", {"id": id}, function (res) {
		if (res.data > 0) {
			show('地址删除成功！');
			location.reload();
		} else {
			show(res.message);
		}
	});
};

// 保存地址
Address.saveAddress = function () {
	if (this.verify()) {
		if (is_sub_address) return;
		is_sub_address = true;
		var field = {};
		$('#addressModal [name]').each(function (index, el) {
			var key = $(el).attr('name');
			field[key] = $(el).val();
		});
		var url = field.id == 0 ? 'System.Member.addAddress' : 'System.Member.updateAddress';
		api(url, field, function (res) {
			if (res.data > 0) {
				location.reload();
			} else {
				show(res.message);
				is_sub_address = false;
			}
		});
	}
};

Address.verify = function () {
	if ($('#addressModal [name="consigner"]').val().search(/[\S]+/)) {
		$('#addressModal [name="consigner"]').focus();
		show('请输入收货人姓名');
		return false;
	}
	if ($('#addressModal [name="province"]').val() == 0) {
		$('#addressModal [name="province"]').focus();
		show('请选择省');
		return false;
	}
	if ($('#addressModal [name="city"]').val() == 0) {
		$('#addressModal [name="city"]').focus();
		show('请选择市');
		return false;
	}
	if ($('#addressModal [name="district"]').val() == 0 && $('#addressModal [name="district"] option').length > 1) {
		$('#addressModal [name="district"]').focus();
		show('请选择区/县');
		return false;
	}
	if ($('#addressModal [name="address"]').val().search(/[\S]+/)) {
		$('#addressModal [name="address"]').focus();
		show('请输入详细地址');
		return false;
	}
	if ($('#addressModal [name="mobile"]').val().search(regex.mobile)) {
		$('#addressModal [name="mobile"]').focus();
		show('请输入正确的手机号码');
		return false;
	}
	return true;
};

$(function () {
	// 获取省列表
	getProvince('#addressModal [name="province"]');
	// 获取市列表
	$('#addressModal [name="province"]').change(function () {
		getCity('#addressModal [name="city"]', $(this).val());
		$('#addressModal [name="district"]').html('<option value="0">请选择区/县</option>');
	});
	// 获取区/县列表 
	$('#addressModal [name="city"]').change(function () {
		getDistrict('#addressModal [name="district"]', $(this).val());
	});
	// 地址弹框隐藏
	$('#addressModal').on('hidden.zui.modal', function (e) {
		$('#addressModal [name]').val('');
		$('#addressModal [name="province"] option:eq(0),#addressModal [name="city"] option:eq(0),#addressModal [name="district"] option:eq(0)').prop('selected', true);
		$('#addressModal [name="city"] option:gt(0),#addressModal [name="district"] option:gt(0)').remove();
	})
});