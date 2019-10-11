var sendtype = $("#tesing").val();
$(document).ready(function(){
	//检测手机手机是否已注册
	$("#mobile").change(function(){
		var mobile = $("#mobile").val();
		api("System.Login.findPassword",{ "info":mobile,"type":"mobile" },function(res){
			if(!res.data){
				toast('該號碼未注冊');
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
				toast('譔郵箱未注冊');
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
				toast('請輸入正確的行動號碼格式');
				$("#mobile").focus();
				return false;
			}
		}else{
			var account = $("#email").val();
			var type ="email";
			//验证手机号格式是否正确
			if(account.search(regex.email) == -1){
				toast('請輸入正確的郵箱');
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
		$(".sendcode").removeAttr("disabled").val("獲取驗證碼");
		wait = 120;
	} else {
		$(".sendcode").attr("disabled", 'disabled').val(wait+"s後重新發送");
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
				toast("請輸入您注冊時的行動號碼");
				$("#mobile").focus();
				return false;
			}
			if(mobile_code.length==0){
				toast("請輸入手機驗證碼");
				$("#mobile-code").focus();
				return false;
			}else{
				if(mobile_pass.length<6){
					toast('登錄密碼不能少於 6 個字符');
					$("#mobile-pass").focus();
					return false;
				}
				if(mobile_new_pass != mobile_pass){
					toast('兩次輸入的密碼不一致');
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
				toast('請輸入您的郵箱');
				$("#email").focus();
				return false;
			}
			if(email_code.length==0){
				toast('請輸入郵箱驗證碼');
				$("#email-code").focus();
				return false;
			}else{
				if(email_pass.length<6){
					toast('登錄密碼不能少於 6 個字符');
					$("#email-pass").focus();
					return false;
				}
				if(email_new_pass != email_pass){
					toast('兩次輸入的密碼不一致');
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