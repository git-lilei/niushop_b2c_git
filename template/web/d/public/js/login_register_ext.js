$(function () {
	
	$("#username").change(function () {
		if ($(this).val().length >= 3 && $(this).val().length <= 16) {
			var user_name = $(this).val();
			$("#isset_username").attr("value", "1");
			api("System.Member.checkUsername", {"username": user_name}, function (res) {
				var data = res.data;
				if (data) {
					$("#username").focus().addClass("ns-border-color");
					show(lang_register.user_name_already_exists);
					$("#isset_username").attr("value", "2");
				} else {
					$("#isset_username").attr("value", "1");
					$("#username").removeClass("ns-border-color");
				}
			});
		}
	});
	
	$("#pass").change(function () {
		var password = $("#pass").val();
		var password_length = $("#hidden_pwd_len").val();
		if (password.length < password_length) {
			$("#pass").trigger("focus").addClass("ns-border-color");
			show("密码最小长度为" + password_length);
			return false;
		} else {
			$("#pass").removeClass("ns-border-color");
		}
	});
	
	$("#repass").change(function () {
		var password = $("#pass").val();
		var repass = $("#repass").val();
		if (!(repass == password)) {
			$("#repass").trigger("focus").addClass("ns-border-color");
			show("两次密码输入不相同");
			return false;
		} else {
			$("#repass").removeClass("ns-border-color");
		}
	});
	
});

var perfect_flag = false;

function perfectInfo() {
	var username = $("#username").val();
	var password = $("#pass").val();
	var repass = $("#repass").val();
	var verify_code = $("#verify_code").val();
	var isset_username = $("#isset_username").val();
	if (!(username.length >= 3 && username.length <= 16)) {
		$("#username").trigger("focus").addClass("ns-border-color");
		show(lang_register.user_name_length);
		return false;
	} else {
		$("#username").removeClass("ns-border-color");
	}
	
	var is_username_true = verifyUsername(username);
	
	if (is_username_true > 0) {
		return false;
	}
	
	if (isset_username == "2") {
		$("#username").trigger("focus").addClass("ns-border-color");
		show(lang_register.user_name_already_exists);
		return false;
	} else {
		$("#username").removeClass("ns-border-color");
	}
	
	var is_password_true = verifyPassword(password);
	if (is_password_true > 0) {
		return false;
	}
	if (!(repass == password)) {
		$("#repass").trigger("focus").addClass("ns-border-color");
		show(lang_register.two_password_not_same);
		return false;
	} else {
		$("#repass").removeClass("ns-border-color");
	}
	// 验证码
	if ($("#hidden_verify_pc").val() == 1) {
		if (verify_code == "") {
			$("#verify_code").focus().addClass("ns-border-color");
			show("请输入验证码");
			return false;
		} else {
			$("#verify_code").removeClass("ns-border-color");
		}

		var vertification_error = false;
		api("System.Login.checkCaptcha", {vertification: verify_code}, function (res) {
			if (res.data.code < 0) {
				vertification_error = true;
			}
		}, false);
		if (vertification_error){
			$("#verify_code").focus().addClass("ns-border-color");
			$('#tab1 .verifyimg').attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
			show("验证码错误");
			return false;
		}else{
			$("#verify_code").removeClass("ns-border-color");
		}
	}
	if (perfect_flag) return;
	perfect_flag = true;
	
	api("System.Login.perfectInfo", {
		"username": username,
		"password": password
	}, function (res) {
		var data = res.data;
		if (data["code"] > 0) {
			$.ajax({
				type: 'post',
				url: __URL(SHOPMAIN + "/login/index"),
				dataType: "JSON",
				async: false,
				data: {token: data.token},
				success: function (code) {
					if (code == 1) {
						show(data['message']);
						setTimeout(function () {
							location.href = data["url"];
						}, 1000)
					}
				}
			});
		} else {
			perfect_flag = false;
			show(data['message']);
		}
	}, false);
}

function verifyUsername(username) {
	var is_true = 0;
	if (regex.chinese_characters.test(username)) {
		is_true = 1;
		$("#username").trigger("focus").addClass("ns-border-color");
		show("用户名不能包含中文字符");
		return is_true;
	}
	if (regex.email.test(username)) {
		is_true = 1;
		$("#username").trigger("focus").addClass("ns-border-color");
		show(lang_register.user_name_canno_be_mailbox);
		return is_true;
	}
	if (regex.mobile.test(username)) {
		is_true = 1;
		$("#username").trigger("focus").addClass("ns-border-color");
		show(lang_register.user_name_canno_be_phone);
		return is_true;
	}
	var username_verify = $("#hidden_name_keyword").val();
	var usernme_verify_array = new Array();
	if ($.trim(username_verify) != "" && username_verify != undefined) {
		usernme_verify_array = username_verify.split(",");
	}
	usernme_verify_array.push(",");
	usernme_verify_array.push(" ");
	$.each(usernme_verify_array, function (k, v) {
		if (username.indexOf(v) >= 0) {
			is_true = 1;
			$("#username").trigger("focus").addClass("ns-border-color");
			show(lang_register.username_cannot_includ + v + lang_register.such_characters);
			return false;
		}
	});
	return is_true;
}

//验证密码
function verifyPassword(password) {
	var is_true = 0;
	var min_length_str = $("#hidden_pwd_len").val();
	if ($.trim(min_length_str) != "") {
		var min_length = parseInt(min_length_str);
	} else {
		var min_length = 5;
	}
	if ($.trim(password) == "") {
		is_true = 1;
		$("#pass").trigger("focus").addClass("ns-border-color");
		show(lang_register.password_cannot_empty);
		return is_true;
	} else {
		$("#pass").removeClass("ns-border-color");
	}
	if (min_length > 0) {
		if (password.length < min_length) {
			is_true = 1;
			$("#pass").trigger("focus").addClass("ns-border-color");
			show(lang_register.minimum_password_length + min_length);
			return is_true;
		} else {
			$("#pass").removeClass("ns-border-color");
		}
	}
	
	var regex_str = $("#hidden_pwd_complexity").val();
	if ($.trim(regex_str) != "" && regex_str != undefined) {
		//验证是否包含数字
		if (regex_str.indexOf("number") >= 0) {
			var number_test = /[0-9]/;
			if (!number_test.test(password)) {
				is_true = 1;
				$("#pass").trigger("focus").addClass("ns-border-color");
				show(lang_register.password_must_contain_numbers);
				return is_true;
			} else {
				$("#pass").removeClass("ns-border-color");
			}
		}
		//验证是否包含小写字母
		if (regex_str.indexOf("letter") >= 0) {
			var letter_test = /[a-z]/;
			if (!letter_test.test(password)) {
				is_true = 1;
				$("#pass").trigger("focus").addClass("ns-border-color");
				show(lang_register.password_must_have_lowercase_letters);
				return is_true;
			} else {
				$("#pass").removeClass("ns-border-color");
			}
		}
		//验证是否包含大写字母
		if (regex_str.indexOf("upper_case") >= 0) {
			var upper_case_test = /[A-Z]/;
			if (!upper_case_test.test(password)) {
				is_true = 1;
				$("#pass").trigger("focus").addClass("ns-border-color");
				show(lang_register.password_must_have_uppercase_letters);
				return is_true;
			} else {
				$("#pass").removeClass("ns-border-color");
			}
		}
		//验证是否包含特殊字符
		if (regex_str.indexOf("symbol") >= 0) {
			var symbol_test = /[^A-Za-z0-9]/;
			if (!symbol_test.test(password)) {
				is_true = 1;
				$("#pass").trigger("focus").addClass("ns-border-color");
				show(lang_register.password_must_contain_symbols);
				return is_true;
			} else {
				$("#pass").removeClass("ns-border-color");
			}
		}
	}
	return is_true;
}

//绑定信息的js
var flag = false; // 防止重复提交
function loginBind() {
	var binding_username = $("#binding_username").val(),
		binding_pass = $("#binding_pass").val(),
		binding_verify_code = $("#binding_verify_code").val();
	if (vertify(binding_username, binding_pass, binding_verify_code)) {
		if (flag) return;
		flag = true;
		api("System.Login.bindAccount", {
			"username": binding_username,
			"password": binding_pass
		}, function (res) {
			var data = res.data;
			if (data["code"] > 0) {
				$.ajax({
					type: 'post',
					url: __URL(SHOPMAIN + "/login/index"),
					dataType: "JSON",
					async: false,
					data: {token: data.token},
					success: function (code) {
						if (code == 1) {
							show(data['message']);
							setTimeout(function () {
								location.href = __URL(SHOPMAIN);
							}, 1000)
						}
					}
				});
			} else {
				flag = false;
				show(data['message']);
			}
		}, false);
	}
}

function vertify(binding_username, binding_pass, binding_verify_code) {
	if (binding_username.length == 0) {
		$("#binding_username").focus();
		show("请输入用户名");
		return false;
	}
	if (binding_pass.length == 0) {
		$("#binding_pass").focus();
		show("请输入密码");
		return false;
	}
	if ($("#hidden_verify_pc").val() == 1) {
		if (binding_verify_code.length == 0 && binding_verify_code != undefined) {
			$("#binding_verify_code").focus().addClass("ns-border-color");
			show("请输入验证码");
			return false;
		}else{
			$("#binding_verify_code").removeClass("ns-border-color");
		}

		var vertification_error = false;
		api("System.Login.checkCaptcha", {vertification: binding_verify_code}, function (res) {
			if (res.data.code < 0) {
				vertification_error = true;
			}
		}, false);
		if (vertification_error){
			$("#binding_verify_code").focus().addClass("ns-border-color");
			$('#tab2 .verifyimg').attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
			show("验证码错误");
			return false;
		}else{
			$("#binding_verify_code").removeClass("ns-border-color");
		}
	}
	return true;
}