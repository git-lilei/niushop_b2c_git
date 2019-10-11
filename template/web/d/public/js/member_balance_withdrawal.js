$(function(){
	// 添加提现账号
	$('.add-account').click(function(event) {
		$('#myModal .modal-title').text('添加账户');
		$('#myModal').modal();
	});

	// 切换账号类型
	$('#myModal [name="account_type"]').change(function(event) {
		switch(parseInt($(this).val())){
			case 1:
				//银行卡
				$("#myModal [data-flag='branch_bank_name']").show();
				$("#myModal [data-flag='account_number']").show();
				break;
			case 2:
				//微信
				$("#myModal [data-flag='branch_bank_name']").hide();
				$("#myModal [data-flag='account_number']").hide();
				break;
			case 3:
				//支付宝
				$("#myModal [data-flag='branch_bank_name']").hide();
				$("#myModal [data-flag='account_number']").show();
				break;
		}
	});

	// 弹出框隐藏
	$('#myModal').on('hidden.zui.modal', function (e) {
  		$('#myModal [name]').val('');
  		$("#myModal [data-flag='branch_bank_name']").show();
		$("#myModal [data-flag='account_number']").show();
  		$('#myModal [name="account_type"] option:eq(0)').prop('selected', true);
	});

	$("#myModal .save").click(function() {
		var field = {};
		$('#myModal [name]').each(function(index, el) {
			var key = $(el).attr('name');
			field[key] = $(el).val();
		});
		field.account_type_name = $('#myModal [name="account_type"] option:selected').text();

		if(vertify(field)){
			var url = field.id == 0 ? 'System.Member.addAccount' : 'System.Member.updateAccount';

			api(url, field, function(res){
				if(res.data>0){
					window.location.reload();
				}
			}, false)
		}
	});

	// 选择提现账号
	$('.account-list li:not(.add-account)').click(function(){
		$(this).attr('class', 'ns-border-color').siblings('li:not(.add-account)').attr('class', 'ns-border-color-gray');
	})
});

// 删除账户
function delAccount(id){
	api("System.Member.deleteAccount",{ "id": id },function (res) {
		if(res.data > 0){
			window.location.reload();
		}
	});
}

// 编辑账户
function editAccount(id){
	api("System.Member.accountDetail",{ "id": id },function (res) {
		var data = res.data;
		$("#myModal [name='id']").val(data.id);
		$("#myModal [name='realname']").val(data.realname);
		$("#myModal [name='mobile']").val(data.mobile);
		$("#myModal [name='account_type'] option[value='"+ data.account_type +"']").prop('selected', true);
		$("#myModal [name='branch_bank_name']").val(data.branch_bank_name);
		$("#myModal [name='account_number']").val(data.account_number);
		if(data.account_type == 2){
			$("#myModal [data-flag='branch_bank_name']").show();
			$("#myModal [data-flag='account_number']").hide();
		}
		if(data.account_type == 3){
			$("#myModal [data-flag='account_number']").show();
			$("#myModal [data-flag='branch_bank_name']").hide();
		}
	});
	$('#myModal .modal-title').text('编辑账户');
	$('#myModal').modal();
}

// 表单验证
function vertify(data){
	if(data.realname.search(/[\S]+/)){
		show('请输入真实姓名');
		$("#myModal [name='realname']").focus();
		return false;
	}
	if(data.mobile.search(regex.mobile) == -1){
		show('请输入正确的手机号');
		$("#myModal [name='mobile']").focus();
		return false;
	}
	if(data.account_type == 1){
		if(data.branch_bank_name.search(/[\S]+/)){
			show('请输入支行名称');
			$("#myModal [name='branch_bank_name']").focus();
			return false;
		}
	}
	if(data.account_type == 1 || data.account_type == 3){
		if(data.account_number.search(/[\S]+/)){
			show('请输入提现账号');
			$("#myModal [name='account_number']").focus();
			return false;
		}
	}
	return true;
}

// 提现
var is_flag = false;
function withdraw(){
	var bank_id = $('.account-list li.ns-border-color').attr('data-id'), // 提现账号id
		cash = parseFloat(parseFloat($('[name="cash"]').val()).toFixed(2));// 提现金额
	if (bank_id == undefined) { show('请先选择提现账号'); return;} 

	if (isNaN(cash) || cash == '' || cash == 0) { show('请输入正确的提现金额');return;}
	if (cash > withdraw_data.balance) { show('提现金额不能超出可提现金额'); return;}
	if (cash < withdraw_data.min_withdrawal) { show( '提现金额不能低于' + withdraw_data.min_withdrawal); return;}
	if (withdraw_data.multiple != 0 && cash % parseInt(withdraw_data.multiple) != 0) { show( '提现金额必须是' + withdraw_data.multiple + '的整数倍'); return;}
	
	if(is_flag) return false;
	is_flag = true;
	api("System.Member.addWithdrawApply", {"bank_account_id": bank_id, "cash": cash },function (res) {
		if(res.data>0){
			show("提交成功，等待商家审核...");
			window.location.href = __URL(SHOPMAIN + "/member/withdrawal");
		}else{
			is_flag = false;
			show(res['message']);
		}
	});
}