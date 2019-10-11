$(function () {
	
	$('#redux').eraser({
		
		size: 10, //擦除大小
		completeRatio: .6, // 擦除到50%时 回调方法
		completeFunction: showResetButton,
		firstClick: function () {
			api("System.Promotion.randAward", {'game_id': $("#hidden_game_id").val()}, function (data) {
				data = data['data'];
				if (data != null) {
					if (data.is_winning == 0) {
						var text = "真遺憾！沒有中獎";
						iniCanvas(text, data.is_winning, data.no_winning_instruction);
					} else if (data.is_winning == 1) {
						var text = data.winning_info.rule_name != undefined || data.winning_info.rule_name.length != 0 ? data.winning_info.rule_name : "中奖了";
						var desc = "";
						switch (data.winning_info.type) {
							case 1:
								desc = "恭喜您，獲得" + parseInt(data.winning_info.points) + "个积分";
								break;
							case 2:
								desc = "恭喜您，獲得" + data.winning_info.type_value;
								break;
							case 3:
								desc = "恭喜您，獲得" + data.winning_info.hongbao + "餘額紅包";
								break;
							case 4:
								desc = "恭喜您，獲得" + data.winning_info.type_value;
								break;
						}
						iniCanvas(text, data.is_winning, desc)
					} else if (data.is_winning == -1) {
						var text = data.message;
						iniCanvas(text, 0, null, "請聯系我們");
					}
				}
			})
		}
	});
	
	ini();
	
	iniCanvas(null, 1, null);
	
	$(".container").css({"min-height": $(window).height() + "px"});
	
	//清除达到百分之七十时清除所有遮罩内容
	function showResetButton() {
		$('#redux').eraser("clear");
	}
	
	function iniCanvas(text, type, desc) {
		var canvas = document.getElementById('scratch_card');
		var ctx = canvas.getContext('2d');
		
		var getPixelRatio = function (context) {
			var backingStore = context.backingStorePixelRatio ||
				context.webkitBackingStorePixelRatio ||
				context.mozBackingStorePixelRatio ||
				context.msBackingStorePixelRatio ||
				context.oBackingStorePixelRatio ||
				context.backingStorePixelRatio || 1;
			
			return (window.devicePixelRatio || 1) / backingStore;
		};
		var ratio = getPixelRatio(ctx);
		
		var realwidth = $("#scratch_card").width(),
			realheight = $("#scratch_card").height(),
			text = text != null ? text : '我們正在努力加載·····',
			desc = desc != null ? desc : '哎呀！與您擦肩而過了！',
			width = realwidth * ratio,
			height = realheight * ratio;
		
		canvas.width = realwidth;
		canvas.height = realheight;
		
		canvas.style.width = realwidth.toString() + "px";
		canvas.style.height = realheight.toString() + "px";
		
		//创建时先清除上一次画布
		ctx.clearRect(0, 0, width, height);
		//判断是否中奖 输出成功失败图片图片
		if (type != null) {
			var winning_img = new Image();
			if (type == 1) {
				winning_img.src = $("#hidden_winning_img").attr("src");
			} else {
				winning_img.src = $("#hidden_no_winning_img").attr("src");
			}
			var new_winning_img_length = 50; //因为图是正方形 所以宽高用一个参数
			ctx.drawImage(winning_img, 0, 0, winning_img.width, winning_img.height, 20, ((realheight - new_winning_img_length) / 2), new_winning_img_length, new_winning_img_length);
		}
		//设置字体 信息标题
		ctx.font = "16px Microsoft YaHei";
		//字体颜色
		ctx.fillStyle = "#FD5C40";
		// ctx.textAlign = 'center';
		ctx.textBaseline = 'middle';
		//输出文字
		ctx.fillText(text, 80, 50);
		// 详细描述
		ctx.font = "14px Microsoft YaHei";
		ctx.fillStyle = "#999";//字体颜色
		ctx.textBaseline = 'middle';
		//输出文字
		ctx.fillText(desc, 80, 75);
	}
	
	// 初始化
	function ini() {
		var member_point = parseInt($("#hidden_member_point").val()); //用户所有积分
		var need_point = parseInt($("#hidden_need_point").val()); //刮取一次所需积分
		//如果所需积分大于用户所有积分 则禁用刮卡图层
		if (need_point > member_point) {
			$('#redux').eraser('disable');
		} else {
			$('#redux').eraser('enable');
		}
	}
});