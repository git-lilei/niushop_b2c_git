$(function () {
	$(".mask-layer-bg,.mask-layer-invite-friends").click(function () {
		$(".mask-layer-bg,.mask-layer-invite-friends").hide();
	});
	$("#invite_friends").click(function () {
		$(".mask-layer-bg,.mask-layer-invite-friends").show();
	});
	
	niushop.share();
});

commonCountDown($(".order-detail-wating-share time").attr("data-end-time"), $(".order-detail-wating-share time"));

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
			var str = s_hour + ":" + s_minute + ":" + s_second;
			obj.text(str);
		} else {
			obj.text("拼單已結束");
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
				var str = s_hour + ":" + s_minute + ":" + s_second;
				obj.text(str);
			} else {
				obj.text("拼單已結束");
				clearInterval(timer);
			}
		}, 1000);
	}
}