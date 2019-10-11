/**
 * 砸金蛋游戏js
 */
/*
 * 为金蛋绑定点击操作 
 */
$(".egglist li").click(function () {
	eggClick($(this));
});

/*
 * 点击触发ajax和砸蛋操作
 */
function eggClick(obj) {
	api("System.Promotion.randAward", {'game_id': $("#game_id").val()}, function (data) {
		var data = data['data'];
		if (data.is_winning == -1) {
			toast(data.message);
		} else {
			smash_eggs(obj, data);
		}
	})
}

/*
 * 实施砸蛋操作
 */
function smash_eggs(obj, data) {
	var _this = obj;
	if (_this.hasClass("curr")) {
		var txt = "蛋已經碎了，就不要再砸！";
		toast(txt);
		return false;
	}
	//隐藏数字和移动锤子位置
	$(_this).children("span").hide();
	var posL = $(_this).position().left + $(_this).width() - 10;
	$("#hammer").show().css('left', posL);
	
	$(".hammer").css({"top": _this.position().top - 55, "left": _this.position().left + 150});
	$(".hammer").animate({
			"top": _this.position().top - 25,
			"left": _this.position().left + 125
		}, 150, function () {
			_this.addClass("curr"); //蛋碎效果
			_this.find("sup").show(); //金花四溅
			$(".hammer").hide();
			$('.resulttip').css({
				display: 'block',
				top: '100px',
				left: _this.position().left + 2,
				opacity: 0
			}).animate({top: '0px', opacity: 1}, 300, function () {
				if (data != null) {
					if (data.is_winning == 0) {
						$("#result").html(data.no_winning_instruction);
					} else if (data.is_winning == 1) {
						$("#result").html("恭喜您，獲得" + data['winning_info']['rule_name'] + "!");
					}
				}
			});
		}
	);
}