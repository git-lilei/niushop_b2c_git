$(document).ready(function () {
	
	//输入账户名
	$(".js-input-account button").click(function () {
		var info = $("#account").val();
		var captcha = $("#captcha").val();
		var type = "";
		if (info.length == 0) {
			$("#account").trigger("focus").addClass("ns-border-color");
			$("#tip_account").text(lang_find.user_name_cannot_be_empty);
			return false;
		} else {
			$("#account").removeClass("ns-border-color");
			$("#tip_account").text("");
		}
		
		if (info.search(regex.email) == -1 && info.search(regex.mobile) == -1) {
			$("#account").trigger("focus").addClass("ns-border-color");
			$("#tip_account").text(lang_find.phone_or_email_false);
			return false;
		} else {
			$("#account").removeClass("ns-border-color");
			$("#tip_account").text("");
		}
		
		if (info.search(regex.email) != -1) {
			type = "email";
			$("#verific_mode").text(lang_find.mailbox_validation);
			$("#verified").text(lang_find.verified_mailbox);
			$("#code").text(lang_find.member_mailbox_authentication_code);
			$("#get_code").text(lang_find.member_gets_mailbox_validation_codet);
		} else if (info.search(regex.mobile) != -1) {
			type = "mobile";
			$("#verific_mode").text(lang_find.mobile_verification);
			$("#verified").text(lang_find.verified_mobile);
			$("#code").text(lang_find.member_mobile_verification_code);
			$("#get_code").text(lang_find.member_get_mobile_verification_code);
		}
		
		if ($("#login_verify_code").val() == 1) {
			if (captcha.length == 0) {
				$("#captcha").addClass("ns-border-color");
				$("#tip_captcha").text(lang_find.verification_code_must_not_be_null);
				return false;
			} else {
				$("#captcha").removeClass("ns-border-color");
				$("#tip_captcha").text("");
			}
			var check_captcha = true;//检测验证码是否正确，
			api("System.Login.checkCaptcha", {"vertification": captcha}, function (res) {
				var data = res.data;
				if (data['code'] == 1) {
					check_captcha = false;
				} else {
					$("#captcha").addClass("ns-border-color");
					$(".img-captcha").attr("src", __URL(SHOPMAIN + '/captcha?tag=1&send=' + Math.random()));
					$("#tip_captcha").text(data['message']);
				}
			}, false);
			
			if (check_captcha) return false;
		}
		
		var myinfo = info.substr(3, 4);
		var deinfo = info.replace(myinfo, "****");
		$("#verified_info").text(deinfo);
		api("System.Login.findPassword", {type: type, info: info}, function (res) {
			var data = res.data;
			if (data) {
				$(".js-input-account").hide();
				$(".js-verify").show();
				$("#current2").find("span").addClass("ns-border-color").removeClass("ns-border-color-gray");
				$("#current2").find("p:eq(0)").addClass("ns-bg-color").removeClass("ns-bg-color-gray-shade-20");
				$("#current2").find("p:eq(1)").addClass("ns-text-color").removeClass("ns-text-color-gray");
				var nick_name = data.nick_name;
				var myname = nick_name.substr(3, 4);
				nick_name = nick_name.replace(myname, "****");
				$("#nickname_info").text(nick_name);
			} else {
				$("#account").trigger("focus").addClass("ns-border-color");
				if (type == "email") {
					$("#tip_account").text(lang_find.mailbox_is_not_registered);
				} else {
					$("#tip_account").text(lang_find.phone_number_not_registered);
				}
			}
		});
	});
	
	//验证身份
	$("#get_code").click(function () {
		var info = $("#account").val();
		if (info.search(regex.email) == -1 && info.search(regex.mobile) != -1) {
			var type = "sms";
		} else if (info.search(regex.email) != -1 && info.search(regex.mobile) == -1) {
			var type = "email";
		}
		//发送手机邮箱验证码
		api("System.Login.sendFindPasswordCode", {"type": type, "send_param": info}, function (res) {
			var data = res.data;
			if (data['code'] < 0) {
				show(data.message);
				$(".img-captcha").attr("src", __URL(SHOPMAIN + '/captcha?tag=1&send=' + Math.random()));
				return false;
			} else if (data['code'] == 0) {
				time();
			}
		}, false);
		
	});
	
	//倒计时
	var wait = 120;
	
	function time() {
		if (wait == 0) {
			$("#get_code").text(lang_find.get_validation_code);
			wait = 120;
		} else {
			$("#get_code").attr("disabled", true).text(wait + lang_find.post_resend);
			wait--;
			setTimeout(function () {
				time()
			}, 1000);
		}
	}
	
	//验证身份点击下一步
	$(".js-verify button").click(function () {
		var sms_code = $("#sms_code").val();
		var verific = $("#verific_mode").val();
		if (sms_code.length == 0) {
			$("#sms_code").trigger("focus").addClass("ns-border-color");
			$("#tip_sms_code").text(verific + lang_find.code_cannot_empty);
			return false;
		} else {
			api("System.Login.checkFindPasswordCode", {"send_param": sms_code}, function (res) {
				var data = res.data;
				if (data['code'] != 0) {
					$("#sms_code").trigger("focus").addClass("ns-border-color");
					$("#tip_sms_code").text(data['message']);
				} else {
					$(".js-verify").hide();
					$(".js-reset").show();
					$("#current3").find("span").addClass("ns-border-color").removeClass("ns-border-color-gray");
					$("#current3").find("p:eq(0)").addClass("ns-bg-color").removeClass("ns-bg-color-gray-shade-20");
					$("#current3").find("p:eq(1)").addClass("ns-text-color").removeClass("ns-text-color-gray");
				}
			})
		}
	});
	
	//重置密码点击下一步
	$(".js-reset button").click(function () {
		var new_pass = $("#newpass").val();
		var confirm_pass = $("#confirmpass").val();
		var info = $("#account").val();
		var sms_code = $("#sms_code").val();
		if (new_pass.length < 6) {
			$("#newpass").trigger("focus").addClass("ns-border-color");
			$("#newpassinfo").text(lang_find.password_cannot_low_liu_char);
			return false;
		}
		if (confirm_pass != new_pass) {
			$("#confirmpass").trigger("focus").addClass("ns-border-color");
			$("#confirmpassinfo").text(lang_find.member_password_inconsistent);
			return false;
		}
		if (info.search(regex.email) == -1 && info.search(regex.mobile) != -1) {
			var type = "mobile";
		} else if (info.search(regex.email) != -1 && info.search(regex.mobile) == -1) {
			var type = "email";
		}
		api("System.Login.passwordReset", {
			"account": info,
			"password": new_pass,
			"type": type,
			"code": sms_code
		}, function (res) {
			var data = res.data;
			if (data['code'] == 1) {
				$(".js-reset").hide();
				$(".js-finish").show();
				$("#current4").find("span").addClass("ns-border-color").removeClass("ns-border-color-gray");
				$("#current4").find("p:eq(0)").addClass("ns-bg-color").removeClass("ns-bg-color-gray-shade-20");
				$("#current4").find("p:eq(1)").addClass("ns-text-color").removeClass("ns-text-color-gray");
			} else {
				show(data['message']);
				setTimeout(function () {
					location.reload()
				}, 2000);
			}
		})
	})
});