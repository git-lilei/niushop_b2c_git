$(function () {
	
	//找回密码弹窗
	$('#msgback').click(function () {
		$('#mask-layer-login').show();
		$('#layui-layer').show();
	});
	
	$('#mask-layer-login').click(function () {
		$('#mask-layer-login').hide();
		$('#layui-layer').hide();
	});
});

var member_sub = false;

function check() {
	var username = $("#username").val();
	var password = $("#password").val();
	var login_captcha = $("#login_captcha").val();
	if (username == '') {
		toast(lang_login.account_cannot_be_empty);
		return false;
	} else if (password == '') {
		toast(lang_login.password_cannot_empty);
		return false;
	}
	if (login_captcha == '') {
		toast(lang_login.verification_code_cannot_be_null);
		return false;
	}
	if (login_captcha != '' && login_captcha != undefined) {
		var vertification_error = false;
		api("System.Login.checkCaptcha", {vertification: login_captcha}, function (res) {
			if (res.data.code < 0) {
				toast(res.data.message);
				vertification_error = true;
				$(".verifyimg").click();
			}
		}, false);
		if (vertification_error) return;
	}
	
	if (member_sub) return;
	member_sub = true;
	api("System.Login.login", {username: username, password: password}, function (res) {
		var data = res.data;
		if (data["code"] >= 0) {
			$.ajax({
				type: 'post',
				url: __URL(APPMAIN + "/login/index"),
				dataType: "JSON",
				data: {token: data.token},
				success: function (code) {
					if (code == 1) {
						if (login_pre_url != "") {
							toast('登录成功', login_pre_url);
						} else {
							toast('登录成功', __URL(APPMAIN + "/member/index"));
						}
					} else {
						toast("登录失败");
						$(".verifyimg").attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
						member_sub = false;
					}
				}
			});
		} else {
			member_sub = false;
			toast(data["message"]);
			$(".verifyimg").attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
		}
	});
}

//发送验证码
function sendOutCode() {
	var mobile = $("#mobile").val();
	var vertification = $("#captcha").val();
	//验证手机号格式是否正确
	if (mobile.search(regex.mobile) == -1) {
		$("#mobile").trigger("focus");
		toast(lang_login.member_enter_correct_phone_format);
		return false;
	}
	if (vertification == '') {
		$("#captcha").trigger("focus");
		toast(lang_login.please_enter_verification_code);
		return false;
	}
	if (vertification != '' && vertification != undefined) {
		var vertification_error = false;
		api("System.Login.checkCaptcha", {vertification: vertification}, function (res) {
			if (res.data.code < 0) {
				toast(res.data.message);
				vertification_error = true;
				$(".verifyimg").click();
			}
		}, false);
		if (vertification_error) return;
	}
	$("#mobile_is_has").val(1);
	//判断输入的验证码是否正确
	api("System.Login.sendRegisterMobileCode", {"mobile": mobile}, function (res) {
		var data = res.data;
		if (data.code == 0) {
			toast(lang_login.send_successfully);
			time();
		} else {
			toast(data.message);
			$(".verifyimg").attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
		}
	});
}

var wait = 120;

function time() {
	if (wait == 0) {
		$("#sendOutCode").removeAttr("disabled").addClass("ns-border-color").removeClass("ns-border-color-gray").val(lang_login.get_dynamic_code);
		wait = 120;
	} else {
		$("#sendOutCode").attr("disabled", 'disabled').add("ns-border-color-gray").removeClass("ns-border-color").val(wait + "s" + lang_login.post_resend);
		wait--;
		setTimeout(function () {
			time();
		}, 1000);
	}
}

var mobile_sub = false;

function check_mobile() {
	var mobile = $("#mobile").val();
	var captcha = $("#captcha").val();
	var sms_captcha = $("#sms_captcha").val();
	var mobile_is_has = $("#mobile_is_has").val();
	if (mobile == '') {
		$("#mobile").trigger("focus");
		toast(lang_login.phone_number_cannot_empty);
		return false;
	} else if (mobile.search(regex.mobile) == -1) {
		$("#mobile").trigger("focus");
		toast(lang_login.member_enter_correct_phone_format);
		return false;
	} else if (mobile_is_has == 0) {
		toast(lang_login.current_phone_number_not_registered_yet);
		return false;
	} else if (sms_captcha == '') {
		toast(lang_login.dynamic_code_cannot_be_empty);
		return false;
	}
	if (captcha == '') {
		toast(lang_login.verification_code_cannot_be_null);
		return false;
	}

	if (mobile_sub) return;
	mobile_sub = true;
	api("System.Login.mobileLogin", {"mobile": mobile, "sms_captcha": sms_captcha}, function (res) {
		var data = res.data;
		if (data["code"] > 0) {
			$.ajax({
				type: 'post',
				url: __URL(APPMAIN + "/login/index"),
				dataType: "JSON",
				data: {token: data.token},
				async : false,
				success: function (code) {
					if (code == 1) {
						if (login_pre_url != "") {
							toast('登录成功', login_pre_url);
						} else {
							toast('登录成功', __URL(APPMAIN + "/member/index"));
						}
					} else {
						mobile_sub = false;
						$(".verifyimg").attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
						toast("登录失败");
					}
				}
			});
		} else if (data["code"] == -10) {
			mobile_sub = false;
			toast(lang_login.dynamic_error_code);
		} else {
			mobile_sub = false;
			toast(data.message);
		}
	});
}

function loginType(obj, type) {
	if (type == 1) {
		$('#account_login').hide();
		$('#mobile_login').show();
		$('#nk_text1').show();
		$('#nk_text2').hide();
		$('#msgback').show();
		$(".js-login-type").text("账户登录");
	} else {
		$('#msgback').hide();
		$('#account_login').show();
		$('#mobile_login').hide();
		$('#nk_text1').hide();
		$('#nk_text2').show();
		$(".js-login-type").text("手机快捷登录");
	}
}