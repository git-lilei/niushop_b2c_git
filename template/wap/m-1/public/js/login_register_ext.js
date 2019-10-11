var is_sub = false;

function perfectInfo() {
	var username = $("#username").val();
	var password = $("#password").val();
	var cfpassword = $("#cfpassword").val();
	var captcha = $("#captcha").val();
	
	if (username == '') {
		toast("請填寫用戶名");
		return false;
	}
	//账号验证
	var is_username_true = verifyUsername(username);
	if (is_username_true > 0) {
		return false;
	}
	if (password == '') {
		toast(lang_regsiter_ext.password_cannot_empty);
		return false;
	}
	//密码验证
	var is_password_true = verifyPassword(password);
	if (is_password_true > 0) {
		return false;
	}
	if (cfpassword == '') {
		toast(lang_regsiter_ext.confirm_password_can_not_be_empty);
		return false;
	}
	if (password != cfpassword) {
		toast(lang_regsiter_ext.two_password_input_is_inconsistent);
		return false;
	}
	if (captcha == '') {
		toast(lang_regsiter_ext.verification_code_cannot_be_null);
		return false;
	}
	if (is_sub) return;
	is_sub = true;
	api("System.Login.perfectInfo", {"username": username, "password": password, "captcha": captcha}, function (res) {
		var data = res.data;
		if (data["code"] > 0) {
			toast(data['message'], data["url"]);
		} else {
			is_sub = false;
			toast(data['message']);
		}
	});
}

//验证用户名
function verifyUsername(username) {
	var is_true = 0;
	if (/.*[\u4e00-\u9fa5]+.*$/.test(username)) {
		is_true = 1;
		toast(lang_regsiter_ext.user_name_cannot_contain_chinese_characters);
		return is_true;
	}
	if (/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/.test(username)) {
		is_true = 1;
		toast(lang_regsiter_ext.user_name_canno_be_mailbox);
		return is_true;
	}
	if (/^1(3|4|5|7|8)\d{9}$/.test(username)) {
		is_true = 1;
		toast(lang_regsiter_ext.user_name_canno_be_phone);
		return is_true;
	}
	var username_verify = "{$reg_config['name_keyword']}";
	var usernme_verify_array = new Array();
	if ($.trim(username_verify) != "" && username_verify != undefined) {
		usernme_verify_array = username_verify.split(",");
	}
	usernme_verify_array.push(",");
	$.each(usernme_verify_array, function (k, v) {
		if ($.trim(v) != "") {
			if (username.indexOf(v) >= 0) {
				is_true = 1;
				toast(lang_regsiter_ext.username_cannot_includ + v + lang_regsiter_ext.such_characters);
				return false;
			}
		}
	});
	return is_true;
}

//验证密码
function verifyPassword(password) {
	var is_true = 0;
	var min_length_str = "{$reg_config['pwd_len']}";
	if ($.trim(min_length_str) != "") {
		var min_length = parseInt(min_length_str);
	} else {
		var min_length = 5;
	}
	if ($.trim(password) == "") {
		is_true = 1;
		toast(lang_regsiter_ext.password_cannot_empty);
		return is_true;
	}
	if (min_length > 0) {
		if (password.length < min_length) {
			is_true = 1;
			toast(lang_regsiter_ext.minimum_password_length + min_length);
			return is_true;
		}
	}
	if (/.*[\u4e00-\u9fa5]+.*$/.test(password)) {
		is_true = 1;
		toast(lang_regsiter_ext.password_cannot_includ_chinese_characters);
		return is_true;
	}
	var regex_str = "{$reg_config['pwd_complexity']}";
	if ($.trim(regex_str) != "" && regex_str != undefined) {
		//验证是否包含数字
		if (regex_str.indexOf("number") >= 0) {
			var number_test = /[0-9]/;
			if (!number_test.test(password)) {
				is_true = 1;
				toast(lang_regsiter_ext.password_must_contain_numbers);
				return is_true;
			}
		}
		//验证是否包含小写字母
		if (regex_str.indexOf("letter") >= 0) {
			var letter_test = /[a-z]/;
			if (!letter_test.test(password)) {
				is_true = 1;
				toast(lang_regsiter_ext.password_must_have_lowercase_letters);
				return is_true;
			}
		}
		//验证是否包含大写字母
		if (regex_str.indexOf("upper_case") >= 0) {
			var upper_case_test = /[A-Z]/;
			if (!upper_case_test.test(password)) {
				is_true = 1;
				toast(lang_regsiter_ext.password_must_have_uppercase_letters);
				return is_true;
			}
		}
		//验证是否包含特殊字符
		if (regex_str.indexOf("symbol") >= 0) {
			var symbol_test = /[^A-Za-z0-9]/;
			if (!symbol_test.test(password)) {
				is_true = 1;
				toast(lang_regsiter_ext.password_must_contain_symbols);
				return is_true;
			}
		}
	}
	return is_true;
}