var sendtype = $("#tesing").val();
$(document).ready(function(){
	//检测手机手机是否已注册
	$("#mobile").change(function(){
		var mobile = $("#mobile").val();
		api("System.Login.findPassword",{ "info":mobile,"type":"mobile" },function(res){
			if(!res.data){
				toast('该手机号未注册');
				$("#mobile").focus();
				return false;
			}
		});
	});
	//检测邮箱是否存在
	$("#email").change(function(){
		var email = $("#email").val();
		api("System.Login.sendFindPasswordCode",{ "send_param":email,"type":"email" },function(res){
			if(!res.data){
				toast('该邮箱未注册');
				$("#email").focus();
				return false;
			}
		});
	});
	//发送手机邮箱验证码
	$(".sendcode").click(function(){
		if(sendtype == 1){
			var account = $("#mobile").val();
			var type ="sms";
			//验证手机号格式是否正确
			if(account.search(regex.mobile) == -1){
				toast('请输入正确的手机格式');
				$("#mobile").focus();
				return false;
			}
		}else{
			var account = $("#email").val();
			var type ="email";
			//验证手机号格式是否正确
			if(account.search(regex.email) == -1){
				toast('请输入正确的邮箱格式');
				$("#email").focus();
					return false;
			}
		}
		//验证手机号邮箱是否已经注册
		api("System.Login.sendFindPasswordCode",{"type":type,"send_param":account },function(res){
			var data = res.data;
			toast(data.message);
			if (data['code'] == 0) {
				if(sendtype == 1){
					$("#mobile").attr("disabled",true);
				}else{
					$("#email").attr("disabled",true);
				}
				time();
			}
		});
	});
});

var wait=120;
function time() {
	if (wait == 0) {
		$(".sendcode").removeAttr("disabled").val("获取验证码");
		wait = 120;
	} else {
		$(".sendcode").attr("disabled", 'disabled').val(wait+"s后重新发送");
		wait--;
		setTimeout(function() {time()},1000);
	}
}
$("#btn_submit").click(function(){
	if(sendtype == 1){
		var type = "mobile";
		var mobile = $("#mobile").val();
			var mobile_code = $("#mobile-code").val();
			var mobile_pass = $("#mobile-pass").val();
			var mobile_new_pass = $("#mobile-new-pass").val();
			if(mobile.length==0){
				toast("请输入您注册的手机号码");
				$("#mobile").focus();
				return false;
			}
			if(mobile_code.length==0){
				toast("请输入手机验证码");
				$("#mobile-code").focus();
				return false;
			}else{
				if(mobile_pass.length<6){
					toast('登录密码不能少于 6 个字符');
					$("#mobile-pass").focus();
					return false;
				}
				if(mobile_new_pass != mobile_pass){
					toast('两次输入的密码不一致');
					$("#mobile-new-pass").focus();
					return false;
				}
				api("System.Login.passwordReset",{"account":mobile,"password":mobile_pass,"type":"mobile", code : mobile_code},function(res){
					var data = res.data;
					if(data['code'] == 1){
						location.href=__URL(APPMAIN + "/login");
					}else{
						toast(data['message']);
						setTimeout(function(){
							location.reload()
						},2000);
					}
				});
			}
		}else{
			var type = "email";
			var email = $("#email").val();
			var email_code = $("#email-code").val();
			var email_pass = $("#email-pass").val();
			var email_new_pass = $("#email-new-pass").val();
			if(email.length==0){
				toast('请输入您注册的邮箱');
				$("#email").focus();
				return false;
			}
			if(email_code.length==0){
				toast('请输入邮箱验证码');
				$("#email-code").focus();
				return false;
			}else{
				if(email_pass.length<6){
					toast('登录密码不能少于 6 个字符');
					$("#email-pass").focus();
					return false;
				}
				if(email_new_pass != email_pass){
					toast('两次输入的密码不一致');
					$("#email-new-pass").focus();
					return false;}

				api("System.Login.passwordReset",{"account":email,"password":email_pass,"type":"email", code : email_code},function(res){
					var data =res.data;
					if(data["code"] > 0){
						location.href=__URL(APPMAIN + "/login");
				    }else{
						toast(data["message"]);
						setTimeout(function(){
							location.reload();
						},2000);
					}
				});
			}
		}
});