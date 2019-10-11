$(function () {
	document.onkeypress = function (e) {
		if (e.keyCode == 13) $("#btn_login").click();
	};

	$('.login-form .form-head .tit').click(function(event) {
		var type = $(this).attr('data-type');
		$(this).addClass('ns-text-color').siblings('.tit').removeClass('ns-text-color');
		$(".login-type."+type).removeClass('hide').siblings('.login-type').addClass('hide');
	});

	$("#btn_login").click(function(){
		var type = $('.login-form .tit.ns-text-color').attr('data-type');
		if (type == 'mobile') mobileLogin();
		else if(type == 'account') login();
	})

	$('#sendOutCode').click(function(event) {
		sendOutCode();
	});
});

// 检验账号，密码和验证码是否是正确的
var is_flag = false;

function login() {
	var user_name = $.trim($('#user_name').val());
	var password = $.trim($('#password').val());
	var vertification = $.trim($('#vertification').val());
	if (user_name == null || user_name == "") {
		show(lang_login.enter_your_account_number);
		$("#userName").focus();
		return;
	}
	if (password == null || password == "") {
		show(lang_login.please_input_password);
		$("#password").focus();
		return;
	}
	if (is_flag) return false;
	is_flag = true;
	if ($("#hidden_verify_pc").val() == 1) {
		if (vertification == null || vertification == "") {
			show(lang_login.please_enter_verification_code);
			$("#vertification").focus();
			return;
		}
		var vertification_error = false;
		api("System.Login.checkCaptcha", {vertification: vertification}, function (res) {
			if (res.data.code < 0) {
				show(res.data.message);
				$('.login-type.account .verifyimg').attr("src", __URL(SHOPMAIN + '/captcha?tag=1&send=' + Math.random()));
				vertification_error = true;
				is_flag = false;
			}
		}, false);
		
		if (vertification_error) return;
	}
	
	api("System.Login.login", {
		"username": user_name,
		"password": password
	}, function (res) {
		var data = res.data;
		if (data['code'] < 0) {
			show(data['message']);
			$('.login-type.account .verifyimg').attr("src", __URL(SHOPMAIN + '/captcha?tag=1&send=' + Math.random()));
			is_flag = false;
		} else {
			$.ajax({
				type: 'post',
				url: __URL(SHOPMAIN + "/login/index"),
				dataType: "JSON",
				data: {token: data.token},
				success: function (code) {
					if (code == 1) {
						if (login_pre_url != "") {
							location.href = login_pre_url;
						} else {
							location.href = __URL(SHOPMAIN + "/index/index");
						}
					} else {
						show("登录失败");
						is_flag = false;
					}
				}
			});
		}
	});
}

var mobile_sub = false;
function mobileLogin(){
	var mobile = $('.login-type.mobile [name="mobile"]').val();
	var captcha = $('.login-type.mobile [name="captcha"]').val();
	var dynamic_code = $('.login-type.mobile [name="dynamic_code"]').val();

	if (mobile == '') {
		show(lang_login.phone_number_cannot_empty);
		return false;
	} else if (mobile.search(regex.mobile) == -1) {
		show(lang_login.member_enter_correct_phone_format);
		return false;
	} 
	if (captcha == '') {
		show(lang_login.please_enter_verification_code);
		return false;
	}
	if (dynamic_code == '') {
		show(lang_login.dynamic_code_cannot_be_empty);
		return false;
	}
	
	if(mobile_sub) return;
	mobile_sub = true;
	api("System.Login.mobileLogin", {"mobile": mobile, "sms_captcha": dynamic_code}, function (res) {
		var data = res.data;
		if (data["code"] > 0) {
			$.ajax({
				type: 'post',
				url: __URL(SHOPMAIN + "/login/index"),
				dataType: "JSON",
				data: {token: data.token},
				async : false,
				success: function (code) {
					if (code == 1) {
						if (login_pre_url != "") {
							show('登录成功');
							location.href = login_pre_url;
						} else {
							show('登录成功');
							location.href = __URL(SHOPMAIN + "/index/index");
						}
					} else {
						mobile_sub = false;
						$('.login-type.mobile .verifyimg').attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
						show("登录失败");
					}
				}
			});
		} else if (data["code"] == -10) {
			mobile_sub = false;
			show(lang_login.dynamic_error_code);
		} else {
			mobile_sub = false;
			show(data.message);
		}
	});
}

//发送验证码
function sendOutCode() {
	var mobile = $('.login-type.mobile [name="mobile"]').val();
	var captcha = $('.login-type.mobile [name="captcha"]').val();
	
	//验证手机号格式是否正确
	if (mobile.search(regex.mobile) == -1) {
		show(lang_login.member_enter_correct_phone_format);
		return false;
	}
	if (captcha == '') {
		show(lang_login.please_enter_verification_code);
		return false;
	}
	if (captcha != '' && captcha != undefined) {
		var vertification_error = false;
		api("System.Login.checkCaptcha", {vertification: captcha}, function (res) {
			if (res.data.code < 0) {
				show(res.data.message);
				vertification_error = true;
				$('.login-type.mobile .verifyimg').attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
			}
		}, false);
		if (vertification_error) return;
	}
	// 发送动态码
	api("System.Login.sendRegisterMobileCode", {"mobile": mobile}, function (res) {
		var data = res.data;
		if (data.code == 0) {
			show(lang_login.send_successfully);
			time();
		} else {
			show(data.message);
		}
	});
}

var wait = 120;

function time() {
	if (wait == 0) {
		$("#sendOutCode").removeAttr("disabled").addClass("ns-border-color").removeClass("ns-border-color-gray").text(lang_login.get_dynamic_code);
		wait = 120;
	} else {
		$("#sendOutCode").attr("disabled", 'disabled').add("ns-border-color-gray").removeClass("ns-border-color").text(wait + "s" + lang_login.post_resend);
		wait--;
		setTimeout(function () {
			time();
		}, 1000);
	}
}