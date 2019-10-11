var title = "";
var value = "";
var type = "";
$(function () {
	$("#list li").click(function () {
		var titleTag = this.children[0].children[0].children[1];
		if (titleTag == undefined) {
			titleTag = this.children[0].children[1].children[1];
		}
		title = titleTag.innerHTML;
		type = $(titleTag).attr("tage");
		value = this.children[0].children[1].children[1];
		if (value == undefined) {
			value = this.children[0].children[2].children[1]
		}
		value = value.innerHTML;
		value = value.replace("&nbsp;", "");
		$("#value").attr("placeholder", "");
		if (title == lang_member_info.account_number) {
			if ($(this).attr("isnew") == "False") {
				return;
			} else {
				$("#value").attr("placeholder", lang_member_info.please_enter_member_name);
				$(".ns-header h1").html(title);
				$("#saveBtn").toggle();
				$("#divInfo").toggle();
				$("#exit").toggle();
				$("#edit").toggle();
				return;
			}
		}
		if (title == lang_member_info.password) {
			$(".ns-header h1").html(lang_member_info.change_password);
			$("#saveBtn,#logoutBtn").toggle();
			$("#divInfo").toggle();
			$("#exit").toggle();
			$("#editpassword").toggle();
		} else if (title == lang_member_info.member_phone) {
			$("#value").attr("placeholder", lang_member_info.please_enter_your_cell_phone_number);
			$(".ns-header h1").html(lang_member_info.bind_mobile_phone_number);
			$("#saveBtn,#logoutBtn").toggle();
			$("#divInfo").toggle();
			$("#exit").toggle();
			$("#edit_mobile").toggle();
		} else if (title == lang_member_info.member_nickname) {
			$("#value").attr("placeholder", lang_member_info.please_enter_your_nickname);
			$(".ns-header h1").html(lang_member_info.modify_nickname);
			$("#saveBtn,#logoutBtn").toggle();
			$("#divInfo").toggle();
			$("#exit").toggle();
			$("#edit_nick_name").toggle();
		} else if (title == lang_member_info.mailbox) {
			$("#edit_email").toggle();
			$(".ns-header h1").html(title);
			$("#saveBtn,#logoutBtn").toggle();
			$("#divInfo").toggle();
			$("#exit").toggle();
		} else {
			$(".ns-header h1").html(title);
			$("#value").val(value);
			$("#saveBtn,#logoutBtn").toggle();
			$("#divInfo").toggle();
			$("#exit").toggle();
			$("#edit").toggle();
			if (title == lang_member_info.third_party_account_binding) {
				$('.button-submit').hide();
			}
		}
	});
});

//点击返回
function backPage() {
	var title = $(".ns-header h1").html();
	if (title == lang_member_info.member_personal_data) {
		location.href = __URL(APPMAIN + "/member/index");
	} else {
		$.ajax({
			url: __URL(APPMAIN + '/member/updateMemberInfo'),
			type: 'POST',
			success: function (res) {
				location.reload();
			}
		})
	}
}

function logout() {
	api('System.Member.logOut', {}, function (res) {
		if (res.data > 0) {
			window.location.href = __URL(APPMAIN + "/login/index");
		} else if (res["message"] != null) {
			toast(res["message"]);
		}
	})
}

$("#logout").click(function () {
	var json = {"logout": "1"};
	window.webkit.messageHandlers.logout.postMessage(json);
});

function save() {
	switch (type) {
		case "password":
			//密码(6-16)位
			var oldpassword = $("#oldpassword").val();
			var newpassword = $("#newpassword").val();
			var newpassword2 = $("#newpassword2").val();
			var reg = /^[\@A-Za-z0-9\!\#\$\%\^\&\*\.\~]{6,16}$/;
			if (oldpassword.length == 0) {
				toast(lang_member_info.please_enter_the_original_password);
				return false;
			}
			if (!reg.test(newpassword)) {
				toast(lang_member_info.please_enter_6_20_new_passwords, "warning");
				return false;
			}
			if (newpassword2 != newpassword) {
				toast(lang_member_info.the_two_password_is_inconsistent);
				return false;
			}
			api('System.Member.modifyPassword', {
				"new_password": newpassword,
				"old_password": oldpassword
			}, function (res) {
				if (res['data'] > 0) {
					backPage();
				} else {
					toast(lang_member_info.original_password_error);
				}
			});
			break;
		
		case "mobilephone":
			var oldMobile = $.trim($("#oldMobile").val());
			var value = $.trim($("#mobile").val());
			var vertification = $("#input_mobile_code").val();
			var code = $("#mobile_code").val();
			var result = '';
			var mobile_not_exits = 0;
			if (value == oldMobile) {
				toast(lang_member_info.consistent_with_the_original_mobile_phone_number_without_modification);
				return false;
			}
			if (value == "") {
				toast(lang_member_info.mobile_phones_must_not_be_empty);
				return false;
			} else {
				
				if (value.search(regex.mobile) == -1) {
					$("#mobile").trigger("focus");
					toast(lang_member_info.phone_is_not_right_format);
					return false;
				}
				if (notice_mobile != 1) {
					api('System.Member.checkMobile', {"mobile": value}, function (data) {
						if (data.data) mobile_not_exits = 1;
					});
				}
				
				if (pc == 1) {
					if (vertification == "") {
						toast(lang_member_info.please_enter_verification_code);
						return false;
					}
					var vertification_is_error = false;
					api('System.Member.checkCaptcha', {"vertification": vertification}, function (data) {
						if (data.data.code < 0) {
							toast(data.data['message']);
							vertification_is_error = true;
						}
					}, false);
					if (vertification_is_error) return;
				}
				//判断手机号是否已被绑定
				if (mobile_not_exits == 1) {
					toast(lang_member_info.the_phone_number_already_exists);
					return false;
				}
				if (notice_mobile == 1) {
					if (code.length == 0) {
						toast(lang_member_info.member_enter_mobile_verification_code);
						return false;
					}
				}
				api('System.Member.modifyMobile', {
					"mobile": value,
					'code': code
				}, function (res) {
					if (res.code >= 0) {
						$("#mobilephone").text(value);
						backPage();
					} else {
						toast(res.message);
					}
				});
			}
			break;
		case "qqno":
			value = $.trim($("#value").val());
			if (value == "") {
				toast(QQ, lang_member_info.can_not_be_empty);
				return false;
			}
			api('System.Member.modifyQQ', {"qqno": value}, function (res) {
				if (res['data']["code"] > 0) {
					backPage();
					$("#qqno").text(value);
				} else {
					toast(res['data']["message"]);
				}
			});
			break;
		case 'email':
			var oldEmail = $("#oldEmail").val();
			var value = $("#email").val();
			var vertification = $("#input_email_code").val();
			var code = $("#email_code").val();
			var result = '';
			var email_not_exits = 0;
			if (value == oldEmail) {
				toast(lang_member_info.consistent_with_the_original_mailbox_no_change_required);
				return false;
			}
			if (value == "") {
				toast(lang_member_info.mailbox_cannot_be_empty);
				return false;
			} else {
				api('System.Member.checkEmail', {"email": value}, function (data) {
					if (data.data) email_not_exits = 1;
				})
			}
			if (pc == 1) {
				if (vertification == "") {
					toast(lang_member_info.please_enter_verification_code);
					return false;
				}
				var vertification_is_error = false;
				api('System.Member.checkCaptcha', {"vertification": vertification}, function (data) {
					if (data.data.code < 0) {
						toast(data.data['message']);
						vertification_is_error = true;
					}
				}, false);
				if (vertification_is_error) return;
			}
			
			if (email_not_exits == 1) {
				toast(lang_member_info.mailbox_already_exists);
				return false;
			}
			if (notice_email == 1) {
				if (code.length == 0) {
					toast(lang_member_info.member_enter_mailbox_verification_code);
					return false;
				} else {
					api('System.Member.checkCaptcha', {"vertification": code}, function (res) {
						var data = res.data;
						if (data['code'] < 0) {
							toast(data['message']);
							result = true;
						}
						return result;
					});
					if (result) {
						return false;
					}
				}
			}
			api('System.Member.modifyemail', {"email": value, "code": code}, function (res) {
				if (res.code >= 0) {
					backPage();
					$("#email_no").text(value);
				} else {
					toast(res.message);
				}
			});
			break;
		case  "nickname":
			var nickname = $("#nickname").text();
			value = $.trim($("#input_nick_name").val());
			if (nickname == value) {
				toast(lang_member_info.consistent_with_the_original_nickname_without_modification);
				return false;
			}
			if (value == "") {
				toast(lang_member_info.member_nicknames_cannot_empty);
				return false;
			}
			api('System.Member.modifynickname', {"nickname": value}, function (res) {
				if (res.data > 0) {
					$("#email_no").text(value);
					backPage();
				} else {
					toast(res.data.message);
				}
			});
			break;
	}
}

//发送邮箱验证码
$("#send_email").click(function () {
	var email = $("#email").val();
	var email_code = $("#input_email_code").val();
	//验证邮箱格式是否正确
	if (email.search(regex.email) == -1) {
		$("#email").trigger("focus");
		toast(lang_member_info.mailbox_format_is_incorrect);
		return false;
	}
	//验证邮箱是否已经注册
	api('System.Member.checkEmail', {"email": email}, function (data) {
		if (data.data) {
			toast(lang_member_info.mailbox_already_exists);
			return false;
		} else {
			api('System.Member.sendBindCode', {
				"email": email,
				"type": "email"
			}, function (res) {
				var data = res.data;
				if (data.code == 0) {
					time();
				} else {
					toast(data["message"]);
					$(".verifyimg").attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
					return false;
				}
			})
			
		}
	})
});

//发送短信验证码
$("#send_mobile").click(function () {
	var mobile = $("#mobile").val();
	var mobile_code = $("#input_mobile_code").val();
	//验证手机格式是否正确
	if (mobile.search(regex.mobile) == -1) {
		$("#mobile").trigger("focus");
		toast(lang_member_info.phone_is_not_right_format);
		return false;
	}
	//验证手机号是否已经注册
	api('System.Member.checkMobile', {"mobile": mobile}, function (data) {
		if (data.data) {
			toast(lang_member_info.the_phone_number_already_exists);
			return false;
		} else {
			api('System.Member.sendBindCode', {
				"mobile": mobile,
				"type": "mobile"
			}, function (res) {
				var data = res.data;
				if (data.code == 0) {
					time();
				} else {
					toast(data["message"]);
					$(".verifyimg").attr("src", __URL(SHOPMAIN + "/captcha?tag=1") + '&send=' + Math.random());
					return false;
				}
			})
		}
	})
});

var wait = 120;

function time() {
	if (wait == 0) {
		$(".send-out-code").removeAttr("disabled").val(lang_member_info.get_validation_code);
		wait = 120;
	} else {
		$(".send-out-code").attr("disabled", 'disabled').val(wait + 's, ' + lang_member_info.post_resend);
		wait--;
		setTimeout(function () {
			time();
		}, 1000);
	}
}

function removeBindQQ() {
	api('System.Member.removeBindQQ', {}, function (res) {
		if (res.data) {
			window.location.href = res.data;
		}
	})
}