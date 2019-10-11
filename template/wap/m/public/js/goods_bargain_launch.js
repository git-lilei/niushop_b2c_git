$(function () {
	$("body").addClass("goods-bargain-launch").css("min-height", window.screen.height - 45);
	$(".mask-layer-bg,.mask-layer-invite-friends").click(function () {
		$(".mask-layer-bg,.mask-layer-invite-friends").hide();
	});
	$("#invite_friends,#share_friends").click(function () {
		$(".mask-layer-bg,.mask-layer-invite-friends").show();
	});
	
	niushop.share({flag: "bargain", "launch_id": $('#launch_id').val()});
	
	commonCountDown($(".bargain-info time").attr("data-end-time"), $(".bargain-info time"));
});

function jump_goods(goods_id, bargain_id) {
	window.location.href = __URL(APPMAIN + '/goods/detail?goods_id=' + goods_id + '&bargain_id=' + bargain_id);
}

function jump_bargain() {
	window.location.href = __URL(APPMAIN + '/goods/bargain');
}

var flag = false;

function friend_brafain(launch_id) {
	if ($("#uid").val() == null || $("#uid").val() == "") {
		window.location.href = __URL(APPMAIN + "/login");
		return;
	}
	if (flag) return;
	flag = true;
	api('NsBargain.Bargain.helpBargain', {'launch_id': launch_id}, function (res) {
		if (res.code == 0) {
			if (res.data.data == "-9001") {
				toast("當前砍價已結束");
				flag = true;
			} else if (res.data.data == "-9002") {
				toast("您已參加過當前砍價");
				flag = true;
			} else if (res.data.data > 0) {
				flag = true;
				toast("幫好友砍價成功");
				location.href = __URL(APPMAIN + '/goods/bargainlaunch?launch_id=' + launch_id);
			} else {
				flag = false;
				toast("砍價失敗");
			}
		} else {
			flag = false;
			toast("砍價失敗");
		}
	}, false)
}

function commonCountDown(time, obj) {
	if (null != time && "" != time) {
		var sys_second = (time - $("#ms_time").val());///1000;
		if (sys_second > 1) {
			sys_second -= 1;
			var day = Math.floor((sys_second / 3600) / 24);
			var hour = Math.floor((sys_second / 3600) % 24);
			var minute = Math.floor((sys_second / 60) % 60);
			var second = Math.floor(sys_second % 60);
			var s_hour = hour < 10 ? "0" + hour : hour;
			var s_minute = minute < 10 ? "0" + minute : minute;
			var s_second = second < 10 ? "0" + second : second;
			var s_day = day > 0 ? day + "天" : "";
			var str = s_day + s_hour + ":" + s_minute + ":" + s_second;
			obj.text(str);
		} else {
			obj.parent().text("砍價已結束");
		}
		var timer = setInterval(function () {
			if (sys_second > 1) {
				sys_second -= 1;
				var day = Math.floor((sys_second / 3600) / 24);
				var hour = Math.floor((sys_second / 3600) % 24);
				var minute = Math.floor((sys_second / 60) % 60);
				var second = Math.floor(sys_second % 60);
				var s_hour = hour < 10 ? "0" + hour : hour;
				var s_minute = minute < 10 ? "0" + minute : minute;
				var s_second = second < 10 ? "0" + second : second;
				var s_day = day > 0 ? day + "天" : "";
				var str = s_day + s_hour + ":" + s_minute + ":" + s_second;
				obj.text(str);
			} else {
				obj.parent().text("砍價已結束");
				clearInterval(timer);
			}
		}, 1000);
	}
}