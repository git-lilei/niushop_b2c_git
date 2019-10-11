$(function () {
	
	$(".sidebar .content-wrap .menu ul li").mouseover(function () {
		$(this).addClass('active ns-border-color ns-text-color').siblings().removeClass('active ns-border-color ns-text-color');
		$(".sidebar .content-wrap .item").eq($(this).index()).show().siblings(".item").hide();
		$(this).parents('.menu').find('.notice-more a').attr('href', $(this).attr('data-url'));
	});
	
	var top_active = $(".top-active").clone();
	$(".top-active").remove();
	$("body").prepend(top_active).resize(function () {
		floorEvent();
	});
	
	//顶部广告位
	if ($.cookie("index_top_adv") == null) {
		$(".top-active").show();
	}
	$(".top-active .top-active-wrap i").click(function () {
		$(".top-active").slideUp();
		$.cookie('index_top_adv', 1, {expires: 7, path: '/'});
	});
	
	var selcet_time = "";
	api("System.Shop.webFloating", {}, function(res){
		selcet_time = res['data']['modify_time'];	
	}, false)

	//首页浮层
	if ($.cookie("index_web_floating_layer"+selcet_time) == null) {
		$(".mask-layer").fadeIn();
		$(".web-floating").css("margin-top", "-" + ($(".web-floating").height() / 2) + "px").fadeIn();
	}
	
	$(".web-floating .close-wrap").click(function () {
		$(".mask-layer").fadeOut();
		$(".web-floating").fadeOut();
		$.cookie('index_web_floating_layer'+selcet_time, 1, {expires: 1});
	});
	
	
	//楼层
	floorEvent();
	
	//启动倒计时
	countDown();
	
});

//倒计时函数
function countDown() {
	var timer = setInterval(function () {
		var self = $(".discount-wrap section");
		var end_time = self.attr("data-end-time"); //结束时间字符串
		var start_time = self.attr("data-start-time"); //开始时间字符串
		var DATE = new Date();
		var current_time = Math.round(DATE.getTime() / 1000);
		
		if (end_time == '' || start_time == '') return;
		
		//未开始 统计距离开始还有多少时间
		if (current_time < start_time) {
			var interval = start_time - current_time;
			$(".discount-wrap section .desc").html('距离下一场开始还有');
			discountTimeShow(interval);
			//进行中 统计距离结束还有多少时间
		} else if (current_time >= start_time && current_time < end_time) {
			var interval = end_time - current_time;
			$(".discount-wrap section .desc").html('距离本场结束还有');
			discountTimeShow(interval);
			//已结束 不进行统计
		}
		
	}, 1000);
}

//时间显示
function discountTimeShow(interval) {
	
	var day = Math.floor((interval / 3600) / 24);
	var hour = Math.floor((interval / 3600) % 24);
	var minute = Math.floor((interval / 60) % 60);
	var second = Math.floor(interval % 60);
	
	if (day < 10) day = '0' + day;
	if (hour < 10) hour = '0' + hour;
	if (minute < 10) minute = '0' + minute;
	if (second < 10) second = '0' + second;
	
	$(".discount-wrap section .time-wrap .day").html(day);
	$(".discount-wrap section .time-wrap .hour").html(hour);
	$(".discount-wrap section .time-wrap .minute").html(minute);
	$(".discount-wrap section .time-wrap .second").html(second);
}

var derail = true;

function floorEvent() {
	//获取楼层监听器外层的div
	var elevatorfloor = $(".block-wap .block-elevator"),
		floorItem = $(".floor-item"), //楼层
		conTop = 0,
		floor_top_array = new Array(),  //楼层的位置
		floor_height_array = new Array(), //楼层的高度
		visual_height = $(window).height(); //可视窗口的高度
	
	if(elevatorfloor.length==0) return;
	
	//创建楼层
	if (derail) {
		derail = false;
		$("input[name = 'floor_name']").each((i, v) => {
			//获取楼层的名字
			var short_name = $(v).val(),
				//创建楼层监听器
				$el = $("<div class='elevator-item ns-bg-color-gray-shade-20'><span>" + short_name + "</span></div>");
			elevatorfloor.append($el);
		});
	}
	
	var floor_monitor_height = elevatorfloor.height(),  //楼层监听器的高度
		floorItem_one_top = floorItem.eq(0).offset().top,
		floorItem_one_last = floorItem.eq(floorItem.length - 1).offset().top + floorItem.eq(floorItem.length - 1).height() - floor_monitor_height,
		liftItem = $(".elevator-item"); //单个楼层监听器
	
	conTop = (visual_height - floor_monitor_height) / 2;
	
	floorItem.each((i, v) => {
		//楼层的位置
		floor_top_array.push($(v).offset().top - 5);
		
		//楼层的高度
		floor_height_array.push($(v).offset().top - conTop + 5);
	});
	
	$(window).scroll(() => {
		var scrt = $(window).scrollTop();
		if (floorItem_one_top - scrt <= conTop) {
			$(".block-wap .block-elevator").css({
				"display": "block",
				"-webkit-transform": "scale(1)",
				"-moz-transform": "scale(1)",
				"transform": "scale(1)",
				"opacity": 1
			})
		} else {
			$(".block-wap .block-elevator").css({
				"-webkit-transform": "scale(1.2)",
				"-moz-transform": "scale(1.2)",
				"transform": "scale(1.2)",
				"opacity": 0
			})
		}
		if (floorItem_one_last - scrt <= conTop) {
			$(".block-wap .block-elevator").css({
				"-webkit-transform": "scale(1.2)",
				"-moz-transform": "scale(1.2)",
				"transform": "scale(1.2)",
				"opacity": 0
			})
		}
		setTab();
	});
	
	liftItem.click(function () {
		var index = liftItem.index(this);
		$("html,body").stop().animate({
			"scrollTop": floor_height_array[index]
		});
		setTab();
	});
	
	function setTab() {
		var dd = $(window).scrollTop();
		for (var i = floor_top_array.length - 1; i >= 0; i--) {
			if (conTop >= floor_top_array[i] - dd) {
				liftItem.eq(i).addClass("ns-bg-color").removeClass("ns-bg-color-gray-shade-20").siblings().addClass("ns-bg-color-gray-shade-20").removeClass("ns-bg-color");
				break;
			}
		}
	}
}