var isClick = false;

var memberInfoOperation = {
	field: {},
	url: {
		'password': 'System.Member.modifyPassword',
		'mobile': 'System.Member.modifyMobile',
		'email': 'System.Member.modifyemail',
	},
	confirm: function (event) {
		this.getFieldValue(event);
		
		if (this.verification(1)) {
			if (isClick) return;
			isClick = true;
			this.submit();
		}
	},
	// 表单提交
	submit: function () {
		var self = this;
		
		api(self.url[self.type], self.field, function (res) {
			sendCodeId = 0;
			isClick = false;
			if (res.code >= 0) {
				if(self.type == "password"){
					show("密码修改成功");
				}
				location.href = __URL(SHOPMAIN + '/member/security');
			} else {
				show(res.message);
			}
		});
	},
	// 数据验证
	verification: function (is_submit) {
		var self = this,
			field = this.field;
		
		if (self.type == 'password') {
			if (field.old_password == '') {
				show('请输入原密码');
				return false;
			}
			if (field.new_password == '') {
				show('请输入新密码');
				return false;
			}
			if (field.re_new_password != field.new_password) {
				show('两次输入的密码不一致');
				return false;
			}
		}
		
		if (self.type == 'mobile') {
			if (field.mobile == '') {
				show('手机号不能为空');
				return false;
			} else if (field.mobile.search(regex.mobile) == -1) {
				show('手机号格式不正确');
				return false;
			}
			
			if (is_submit) {
				if (field.code == '') {
					show('请输入动态码');
					return false;
				}
			}
		}
		
		if (self.type == 'email') {
			if (field.email == '') {
				show('邮箱不能为空');
				return false;
			} else if (field.email.search(regex.email) == -1) {
				show('邮箱格式不正确');
				return false;
			}
			if (is_submit) {
				if (field.code == '') {
					show('请输入动态码');
					return false;
				}
			}
		}
		
		if (field.captcha == '') {
			show('请输入验证码');
			return false;
		}
		
		if (field.captcha != '' && field.captcha != undefined) {
			var vertification_error = false;
			api("System.Login.checkCaptcha", {vertification: field.captcha}, function (res) {
				if (res.data.code < 0) {
					show(res.data.message);
					$(".verifyimg").attr("src", __URL(SHOPMAIN + '/captcha?tag=1&send=' + Math.random()));
					vertification_error = true;
				}
			}, false);
			if (vertification_error) return false;
		}
		
		return true;
	},
	// 获取表单数据
	getFieldValue: function (event) {
		var self = this;
		var formObj = $(event).parents('.form-horizontal');
		if (formObj.find('[name]').length > 0) {
			formObj.find('[name]').each(function () {
				var name = $(this).attr('name'),
					value = $(this).val();
				self['field'][name] = value;
			})
		}
	},
	// 发送短信验证码
	sendSmsCaptcha: function (event) {
		this.getFieldValue(event);
		if (this.verification(0)) {
			api('System.Member.sendBindCode', {
				"mobile": this.field.mobile,
				"type": "mobile"
			}, function (res) {
				if (res.data.code >= 0) {
					sendCodeId = res.data;
					countDown(event);
				} else {
					show(res.data.message);
				}
			}, false)
		}
	},
	// 发送邮箱验证码
	sendEmailCaptcha: function (event) {
		this.getFieldValue(event);
		api('System.Member.sendBindCode', {
			"email": this.field.email,
			"type": "email"
		}, function (res) {
			if (res.data.code >= 0) {
				sendCodeId = res.data;
				countDown(event);
			} else {
				show(res.data.message);
			}
		}, false)
	}
};

// 倒计时
function countDown(chageObj, oldText, time) {
	var time = time != undefined ? time : 120,
		oldText = oldText != undefined ? oldText : '获取动态码',
		text = time + 's后重新获取';
	if (time > 0) {
		$(chageObj).text(text).addClass('disabled');
		time -= 1;
		setTimeout(function () {
			countDown(chageObj, oldText, time);
		}, 1000);
	} else {
		$(chageObj).text(oldText).removeClass('disabled');
	}
}