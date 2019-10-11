$(function () {
	
	$('.nk-cell').click(function () {
		if (!$(this).is('.active')) {
			$('.nk-cell').removeClass('active');
			$(this).addClass('active');
			typeSelect();
		}
	});
	
	typeSelect();
	
	function typeSelect() {
		var type = $('#hidden_type').val();
		var oDiv1 = $('#nk_text1');
		var oDiv2 = $('#nk_text2');
		if (type == 2) {
			oDiv1.hide();
			oDiv2.show();
			$('#hidden_type').val(1);
		} else if (type == 1) {
			oDiv2.hide();
			oDiv1.show();
			$('#hidden_type').val(2);
		}
	}
	
	$('.nk-cell span').click(function () {
		$('.nk-cell span').removeClass('ns-text-color ns-border-color');
		$(this).addClass('ns-text-color ns-border-color');
	});
	var wait = 120;
	
	function time() {
		if (wait == 0) {
			$("#sendOutCode").removeAttr("disabled").val(lang_register.get_dynamic_code);
			wait = 120;
		} else {
			$("#sendOutCode").attr("disabled", 'disabled').val(wait + "s");
			wait--;
			setTimeout(function () {
				time();
			}, 1000);
		}
	}
	
	$("#sendOutCode").click(function () {
		var mobile = $("#mobile").val();
		var vertification = $("#captcha").val();
		//验证手机号格式是否正确
		if (mobile.search(regex.mobile) == -1) {
			$("#mobile").trigger("focus");
			toast(lang_register.mobile_phone_number_is_wrong);
			return false;
		}
		//验证手机号是否已经注册
		api("System.Member.checkMobile", {mobile: mobile}, function (res) {
			var data = res.data;
			if (data) {
				toast(lang_register.mobile_phone_is_registered);
			} else {
				//判断输入的验证码是否正确
				api("System.Login.sendRegisterMobileCode", {"mobile": mobile}, function (res) {
					var data = res.data;
					if (data['code'] == 0) {
						time();
					} else {
						toast(data["message"]);
						$(".verifyimg").attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
					}
				});
			}
		});
		
	});
	
});

var is_member_sub = false;

function register_member() {
	var username = $("#username").val();
	var password = $("#password").val();
	var cfpassword = $("#cfpassword").val();
	var reg = /^.{6,}$/;
	var register_captcha = $("#register_captcha").val();
	var protocol = $("#register_protocol").is(':checked') == false ? 0 : 1;
	
	if (username == '') {
		toast(lang_register.account_cannot_be_empty);
		return false;
	}
	//账号验证
	var is_username_true = verifyUsername(username);
	if (is_username_true > 0) {
		return false;
	}
	if (password == '') {
		toast(lang_register.password_cannot_empty);
		return false;
	}
	
	//密码验证
	var is_password_true = verifyPassword(password);
	if (is_password_true > 0) {
		return false;
	}
	if (cfpassword == '') {
		toast(lang_register.confirm_password_can_not_be_empty);
		return false;
	}
	if (password != cfpassword) {
		toast(lang_register.two_password_input_is_inconsistent);
		$("#cfpassword").focus();
		return false;
	}
	if (register_captcha == '') {
		toast(lang_register.verification_code_cannot_be_null);
		return false;
	}
	if (register_captcha != undefined && register_captcha != '') {
		var vertification_error = false;
		api("System.Login.checkCaptcha", {vertification: register_captcha}, function (res) {
			if (res.data.code < 0) {
				toast(res.data.message);
				vertification_error = true;
			}
		}, false);
		if (vertification_error) return false;
	}
	if (protocol == 0) {
		toast(lang_register.agreement_registration_agreement);
		return false;
	}
	
	if (is_member_sub) return;
	is_member_sub = true;
	api("System.Login.usernameRegister", {"username": username, "password": password}, function (res) {
		var data = res.data;
		if (data.code > 0) {
			$.ajax({
				type: 'post',
				url: __URL(APPMAIN + "/login/index"),
				dataType: "JSON",
				async: false,
				data: {token: data.token},
				success: function (code) {
					if (code == 1) {
						location.href = __URL(APPMAIN + "/member/index");
					}
				}
			});
		} else {
			is_member_sub = false;
			$(".verifyimg").attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
			toast(data.message);
		}
	});
}

function check_mobile_is_has() {
	var mobile = $("#mobile").val();
	api("System.Member.checkMobile", {mobile: mobile}, function (res) {
		var data = res.data;
		if (data) {
			$("#mobile_is_has").val(0);
		} else {
			$("#mobile_is_has").val(1);
		}
	});
}

var is_mobile_sub = false;

function register_mobile() {
	var mobile = $("#mobile").val();
	var vertification = $("#captcha").val();
	var password_mobile = $("#password_mobile").val();
	var cfpassword_mobile = $("#cfpassword_mobile").val();
	var verify_code = $("#verify_code").val();
	var reg = /^.{6,}$/;
	var mobile_is_has = $("#mobile_is_has").val();
	var protocol = $("#protocol").is(':checked') == false ? 0 : 1;
	if (mobile == '') {
		toast(lang_register.phone_number_cannot_empty);
		return false;
	} else if (!(regex.mobile.test(mobile))) {
		toast(lang_register.mobile_phone_number_is_wrong);
		return false;
		
	} else if (mobile_is_has == 0) {
		toast(lang_register.mobile_phone_is_registered);
		return false;
	}
	if (vertification == '') {
		toast(lang_register.verification_code_cannot_be_null);
		return false;
	}
	if (vertification != undefined && vertification != '') {
		var vertification_error = false;
		api("System.Login.checkCaptcha", {vertification: vertification}, function (res) {
			if (res.data.code < 0) {
				toast(res.data.message);
				vertification_error = true;
			}
		}, false);
		if (vertification_error) return false;
	}
	var is_password_true = verifyPassword(password_mobile);
	if (is_password_true > 0) {
		return false;
	}
	if (password_mobile != cfpassword_mobile) {
		toast(lang_register.two_password_input_is_inconsistent);
		$("#cfpassword_mobile").focus();
		return false;
	}
	if (verify_code == '') {
		toast(lang_register.mobile_phone_dynamic_password_can_not_be_empty);
		return false;
	}
	if (protocol == 0) {
		toast(lang_register.agreement_registration_agreement);
		return false;
	}
	
	if (is_mobile_sub) return;
	is_mobile_sub = true;
	
	api("System.Login.mobileRegister", {
		"mobile": mobile,
		"password": password_mobile,
		'mobile_code': verify_code
	}, function (res) {
		var data = res.data;
		if (data.code > 0) {
			$.ajax({
				type: 'post',
				url: __URL(APPMAIN + "/login/index"),
				dataType: "JSON",
				async: false,
				data: {token: data.token},
				success: function (code) {
					if (code == 1) {
						location.href = __URL(APPMAIN + "/member/index");
					}
				}
			});
		} else {
			is_member_sub = false;
			$(".verifyimg").attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
			toast(data.message);
		}
	});
	
}

//验证用户名
function verifyUsername(username) {
	var is_true = 0;
	if (/.*[\u4e00-\u9fa5]+.*$/.test(username)) {
		is_true = 1;
		toast(lang_register.user_name_cannot_contain_chinese_characters);
		return is_true;
	}
	if (regex.email.test(username)) {
		is_true = 1;
		toast(lang_register.user_name_canno_be_mailbox);
		return is_true;
	}
	if (regex.mobile.test(username)) {
		is_true = 1;
		toast(lang_register.user_name_canno_be_phone);
		return is_true;
	}
	
	var usernme_verify_array = new Array();
	if ($.trim(username_verify) != "" && username_verify != undefined) {
		usernme_verify_array = username_verify.split(",");
	}
	usernme_verify_array.push(",");
	$.each(usernme_verify_array, function (k, v) {
		if ($.trim(v) != "") {
			if (username.indexOf(v) >= 0) {
				is_true = 1;
				toast(lang_register.username_cannot_includ + v + lang_register.such_characters);
				return false;
			}
		}
	});
	return is_true;
}

//验证密码
function verifyPassword(password) {
	var is_true = 0;
	if ($.trim(min_length_str) != "") {
		var min_length = parseInt(min_length_str);
	} else {
		var min_length = 5;
	}
	if ($.trim(password) == "") {
		is_true = 1;
		toast(lang_register.password_cannot_empty);
		return is_true;
	}
	if (min_length > 0) {
		if (password.length < min_length) {
			is_true = 1;
			toast(lang_register.minimum_password_length + min_length);
			return is_true;
		}
	}
	if (/.*[\u4e00-\u9fa5]+.*$/.test(password)) {
		is_true = 1;
		toast(lang_register.password_cannot_includ_chinese_characters);
		return is_true;
	}
	if ($.trim(regex_str) != "" && regex_str != undefined) {
		//验证是否包含数字
		if (regex_str.indexOf("number") >= 0) {
			var number_test = /[0-9]/;
			if (!number_test.test(password)) {
				is_true = 1;
				toast(lang_register.password_must_contain_numbers);
				return is_true;
			}
		}
		//验证是否包含小写字母
		if (regex_str.indexOf("letter") >= 0) {
			var letter_test = /[a-z]/;
			if (!letter_test.test(password)) {
				is_true = 1;
				toast(lang_register.password_must_have_lowercase_letters);
				return is_true;
			}
		}
		//验证是否包含大写字母
		if (regex_str.indexOf("upper_case") >= 0) {
			var upper_case_test = /[A-Z]/;
			if (!upper_case_test.test(password)) {
				is_true = 1;
				toast(lang_register.password_must_have_uppercase_letters);
				return is_true;
			}
		}
		//验证是否包含特殊字符
		if (regex_str.indexOf("symbol") >= 0) {
			var symbol_test = /[^A-Za-z0-9]/;
			if (!symbol_test.test(password)) {
				is_true = 1;
				toast(lang_register.password_must_contain_symbols);
				return is_true;
			}
		}
	}
	return is_true;
}

$('.protocol_model').on('click', function () {
	$('.protocol-loading').addClass('fadein');
});

$('.protocol-loading .close').on('click', function () {
	$('.protocol-loading').removeClass('fadein');
});