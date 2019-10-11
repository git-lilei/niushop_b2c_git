/**
 * 大转盘js
 */
var turnplate = {
	restaraunts: [],				//大转盘奖品名称
	colors: [],					//大转盘奖品区块对应背景颜色
	outsideRadius: 192,			//大转盘外圆的半径
	textRadius: 155,				//大转盘奖品位置距离圆心的距离
	insideRadius: 68,			//大转盘内圆的半径
	startAngle: 0,				//开始角度
	randomRate: [],              //控制获奖率，百分制(相加需等于100%)，对应restaraunts(顺序需要保持一致)，
	bRotate: false				//false:停止;ture:旋转
};

var res_num = 0;
var no_winning_instruction = '';

function rnd(rate) {
	var random = Math.floor(Math.random() * 100);
	var myRandom = [];
	var randomList = [];
	var randomParent = [];
	for (var i = 0; i < 100; i++) {
		myRandom.push(parseInt([i]) + 1);
	}
	for (var i = 0; i < rate.length; i++) {
		var temp = [];
		var start = 0;
		var end = 0;
		randomList.push(parseInt(rate[i].split('%')[0]));
		for (var j = 0; j < randomList.length; j++) {
			start += randomList[j - 1] || 0
			end += randomList[j]
		}
		temp = myRandom.slice(start, end);
		randomParent.push(temp)
	}
	for (var i = 0; i < randomParent.length; i++) {
		if ($.inArray(random, randomParent[i]) > 0) {
			return (i + 1)
		}
	}
	
}

//页面所有元素加载完毕后执行drawRouletteWheel()方法对转盘进行渲染
window.onload = function () {
	
	var rule_json = JSON.parse($("#rule_json").val());
	var randomRate = new Array();
	var restaraunts = new Array();
	var colors = new Array();
	for (var i = 0; i < rule_json.length; i++) {
		if (i == 0) {
			randomRate[i] = "100%";
		} else {
			randomRate[i] = "0%";
		}
		restaraunts[i] = rule_json[i]['rule_name'] + rule_json[i]['rule_desc'];
		if (i % 2 == 0) {
			colors[i] = "#FFF4D6";
		} else {
			colors[i] = "#FFFFFF";
		}
	}
	turnplate.randomRate = randomRate;
	turnplate.restaraunts = restaraunts;
	turnplate.colors = colors;
	
	var rotateTimeOut = function () {
		$('#wheelcanvas').rotate({
			angle: 0,
			animateTo: 2160,
			duration: 8000,
			callback: function () {
				toast('网络超时，请检查您的网络设置！');
			}
		});
	};
	
	//旋转转盘 item:奖品位置; txt：提示语;
	var rotateFn = function (item, txt) {
		var angles = item * (360 / turnplate.restaraunts.length) - (360 / (turnplate.restaraunts.length * 2));
		if (angles < 270) {
			angles = 270 - angles;
		} else {
			angles = 360 - angles + 270;
		}
		$('#wheelcanvas').stopRotate();
		$('#wheelcanvas').rotate({
			angle: 0,
			animateTo: angles + 1800,
			duration: 8000,
			callback: function () {
				if (res_num == rule_json.length) {
					toast(no_winning_instruction);
				} else {
					toast(txt);
				}
				turnplate.bRotate = !turnplate.bRotate;
			}
		});
	};
	
	$('.pointer').click(function () {
		if (turnplate.bRotate) return;
		turnplate.bRotate = !turnplate.bRotate;
		api("System.Promotion.randAward", {'game_id': $("#game_id").val()}, function (data) {
			var data = data['data'];
			if (data != null) {
				if (data.is_winning == 0) {
					res_num = rule_json.length;
					no_winning_instruction = data.no_winning_instruction;
					rotateFn(res_num, turnplate.restaraunts[res_num - 1]);
				} else if (data.is_winning == 1) {
					for (var i = 0; i < rule_json.length; i++) {
						if (rule_json[i]['rule_id'] == data['winning_info']['rule_id']) {
							res_num = i + 1;
						}
					}
					rotateFn(res_num, turnplate.restaraunts[res_num - 1]);
				} else if (data.is_winning == -1 || data.is_winning == -2) {
					toast(data.message);
				}
			}
		})
	});
	
	drawRouletteWheel();
};

function drawRouletteWheel() {
	var canvas = document.getElementById("wheelcanvas");
	if (canvas.getContext) {
		//根据奖品个数计算圆周角度
		var arc = Math.PI / (turnplate.restaraunts.length / 2);
		var ctx = canvas.getContext("2d");
		//在给定矩形内清空一个矩形
		ctx.clearRect(0, 0, 422, 422);
		//strokeStyle 属性设置或返回用于笔触的颜色、渐变或模式
		ctx.strokeStyle = "#FFBE04";
		//font 属性设置或返回画布上文本内容的当前字体属性
		ctx.font = '16px Microsoft YaHei';
		for (var i = 0; i < turnplate.restaraunts.length; i++) {
			var angle = turnplate.startAngle + i * arc;
			ctx.fillStyle = turnplate.colors[i];
			ctx.beginPath();
			//arc(x,y,r,起始角,结束角,绘制方向) 方法创建弧/曲线（用于创建圆或部分圆）
			ctx.arc(211, 211, turnplate.outsideRadius, angle, angle + arc, false);
			ctx.arc(211, 211, turnplate.insideRadius, angle + arc, angle, true);
			ctx.stroke();
			ctx.fill();
			//锁画布(为了保存之前的画布状态)
			ctx.save();
			
			//----绘制奖品开始----
			ctx.fillStyle = "#E5302F";
			var text = turnplate.restaraunts[i];
			var line_height = 17;
			//translate方法重新映射画布上的 (0,0) 位置
			ctx.translate(211 + Math.cos(angle + arc / 2) * turnplate.textRadius, 211 + Math.sin(angle + arc / 2) * turnplate.textRadius);
			
			//rotate方法旋转当前的绘图
			ctx.rotate(angle + arc / 2 + Math.PI / 2);
			
			/** 下面代码根据奖品类型、奖品名称长度渲染不同效果，如字体、颜色、图片效果。(具体根据实际情况改变) **/
			if (text.indexOf("M") > 0) {
				//流量包
				var texts = text.split("M");
				for (var j = 0; j < texts.length; j++) {
					ctx.font = j == 0 ? 'bold 20px Microsoft YaHei' : '16px Microsoft YaHei';
					if (j == 0) {
						ctx.fillText(texts[j] + "M", -ctx.measureText(texts[j] + "M").width / 2, j * line_height);
					} else {
						ctx.fillText(texts[j], -ctx.measureText(texts[j]).width / 2, j * line_height);
					}
				}
			} else if (text.indexOf("M") == -1 && text.length > 6) {
				//奖品名称长度超过一定范围
				text = text.substring(0, 6) + "||" + text.substring(6);
				var texts = text.split("||");
				for (var j = 0; j < texts.length; j++) {
					ctx.fillText(texts[j], -ctx.measureText(texts[j]).width / 2, j * line_height);
				}
			} else {
				//在画布上绘制填色的文本。文本的默认颜色是黑色
				//measureText()方法返回包含一个对象，该对象包含以像素计的指定字体宽度
				ctx.fillText(text, -ctx.measureText(text).width / 2, 0);
			}
			
			//添加对应图标
			if (text.indexOf("猫币") > 0) {
				var img = document.getElementById("shan-img");
				img.onload = function () {
					ctx.drawImage(img, -15, 10);
				};
				ctx.drawImage(img, -15, 10);
			} else if (text.indexOf("谢谢参与") >= 0) {
				var img = document.getElementById("sorry-img");
				img.onload = function () {
					ctx.drawImage(img, -15, 10);
				};
				ctx.drawImage(img, -15, 10);
			}
			//把当前画布返回（调整）到上一个save()状态之前
			ctx.restore();
			//----绘制奖品结束----
		}
	}
}