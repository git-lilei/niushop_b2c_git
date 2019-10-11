$(function () {
	GetDataList(1);
	
	//滑动到底部加载
	$(window).scroll(function () {
		var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
		var content_box_height = parseFloat($("#list_content").height());
		if (totalheight - content_box_height >= 80) {
			if (!is_load) {
				var page = parseInt($("#page").val()) + 1;//页数
				var total_page_count = $("#page_count").val(); // 总页数
				var status = $('#status').val();
				if (page > total_page_count) {
					return false;
				} else {
					GetDataList(page);
				}
			}
		}
	});
});

var is_load = false;//防止重复加载
function GetDataList(page) {
	if (page == undefined || page == "") page = 1;
	$("#page").val(page);//设置当前页
	$("#status").val(status);//保存当前状态
	if (is_load) return;
	is_load = true;
	api("NsPintuan.Pintuan.pintuanOrder", {"page": page}, function (res) {
		var data = res.data;
		$("#page_count").val(data['page_count']);//总页数
		var html = '';
		if (data['data'].length == 0) {
			html += '<p style="padding: 50px;background: #ffffff;text-align: center;">您当前还没有拼单哦</p>';
		} else {
			html += '<ul>';
			for (var i = 0; i < data['data'].length; i++) {
				var curr = data['data'][i];
				html += '<li>';
				
				html += '<header>';
				html += '<label>';
				html += '<span>发起了拼单</span>';
				
				if (curr.status == 1) {
					html += '<a class="ns-text-color" href="#">待分享</a>';
				} else if (curr.status == 2) {
					html += '<a class="ns-text-color" href="#">拼单成功</a>';
				} else if (curr.status == -1) {
					html += '<a class="ns-text-color" href="#">拼单失败</a>';
				}
				html += '</label>';
				html += '<time class="ns-text-color-gray">' + timeStampTurnTime(curr.create_time) + '</time>';
				html += '</header>';
				
				html += '<article class="ns-bg-color-gray-fadeout-60" onclick="location.href=\'' + __URL(APPMAIN + "/goods/detail?goods_id=" + curr.goods_id) + '\'">';
				html += '<div class="goods-img">';
				html += '<img src="' + __IMG(curr.picture_info.pic_cover_micro) + '"/>';
				html += '</div>';
				
				html += '<div class="goods-info">';
				html += '<label>' + curr.goods_name + '</label>';
				html += '<span class="money">￥' + curr.tuangou_money + '<span class="num ns-text-color-gray">' + curr.tuangou_num + '人拼单</span></span>';
				html += '</div>';
				html += '</article>';
				
				html += '<footer>';
				if (curr.status == 1) {
					html += '<span>还剩<strong class="ns-text-color">' + (curr.tuangou_num - curr.real_num) + '</strong>人，<time data-end-time="' + curr.end_time + '">剩余00:00:00</time></span>';
					html += '<button class="btn-invitation-friend ns-bg-color" data-goods-id="' + curr.goods_id + '" data-group-id="' + curr.group_id + '">邀请好友拼单</button>';
				} else if (curr.status == 2) {
					html += '<ul>';
					html += '<li>';
					html += '<div>';
					html += '<img src="' + __IMG(curr.group_user_head_img) + '"/>';
					html += '</div>';
					html += '</li>';
					html += '</ul>';
					html += '<button class="btn-order-info ns-border-color-gray" onclick="location.href=\'' + __URL(APPMAIN + '/order/detail?order_id=' + curr.order_id) + '\'">查看订单详情</button>';
				}
				html += '</footer>';
				html += '</li>';
			}
			html += '</ul>';
			// var user_img = WAPIMG + "/member_default.png";
			// if (data['data'][0].group_user_head_img != "") {
			// 	user_img = __IMG(data['data'][0].group_user_head_img);
			// }
			// $(".user-info img").attr("src", user_img);
			// $(".user-info h4").text(data['data'][0].group_name);
			$(".user-info").show();
		}
		$(".spelling-order-list").html(html);
		CountDown();
		$(".btn-invitation-friend").click(function () {
			location.href = __URL(APPMAIN + "/pintuan/share?goods_id=" + $(this).attr("data-goods-id") + "&group_id=" + $(this).attr("data-group-id"));
		});
		is_load = false;
	});
}

function CountDown() {
	$(".spelling-order-list li time").each(function () {
		var time = $(this).attr("data-end-time");
		var obj = $(this);
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
				obj.text("剩余" + str);
			} else {
				obj.text("拼单已结束");
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
					obj.text("剩余" + str);
				} else {
					obj.text("拼单已结束");
					clearInterval(timer);
				}
			}, 1000);
		}
	});
}