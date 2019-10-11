var OperateHandle = function () {
	function _bindEvent() {
		
		$("#captcha_hide").click(function () {
			$(".code").fadeOut("slow");
		});
		
		$("#captcha").focus(function () {
			$(".code").fadeIn("fast");
		});
		
		var flag = false;
		$("#consultSubmit").click(function () {
			var goods_id = $('#goods_id').val();
			var goods_name = $('#goods_name').val();
			var ct_id = $('input[name="class_id"]:checked').val();
			var consult_content = $('#consult_content').val();
			var random_code = $('#captcha').val();
			if (consult_content == "") {
				show('咨询信息不可为空!');
				return false;
			}
			if (consult_content.length > 200) {
				show('咨询信息超出最大限制');
				return false;
			}
			if (random_code == "") {
				show('验证码不可为空!');
				return false;
			}
			if (random_code.length != 4) {
				show('验证码不正确!');
				return false;
			}
			
			if (flag) return;
			flag = true;
			
			api('System.Goods.addGoodsConsult', {
				"goods_id": goods_id,
				"goods_name": goods_name,
				"ct_id": ct_id,
				"consult_content": consult_content,
				"random_code": random_code,
				"shop_name": $("#hidden_shop_name").val()
			}, function (res) {
				if (res.data > 0) {
					show('咨询信息发布成功！');
					location.href = __URL(SHOPMAIN + "/goods/consult?goods_id=" + goods_id);
				} else {
					show(res['message']);
					flag = false;
				}
			})
		});
		
		//字符个数动态计算
		$("#consult_content").charCount({
			allowed: 200,
			warning: 10,
			counterContainerID: 'consult_char_count',
			firstCounterText: '还可以输入',
			endCounterText: '字',
			errorCounterText: '已经超出',
		});
	}
	
	//外部可调用
	return {
		bindEvent: _bindEvent
	}
}();

$(function () {
	//页面绑定事件
	OperateHandle.bindEvent();
});

var str_num = location.href.split("&");
var goods_id = $('#goods_id').val();
if (typeof(str_num[1]) == 'undefined') {
	$('#myPager').pager({
		linkCreator: function (page, pager) {
			return __URL(SHOPMAIN + "/goods/consult?goods_id=" + goods_id + "&page=" + page);
		}
	});
} else {
	$('#myPager').pager({
		linkCreator: function (page, pager) {
			return __URL(SHOPMAIN + "/goods/consult?goods_id=" + goods_id + "&page=" + page + "&" + str_num[2]);
		}
	});
}

(function ($) {
	
	$.fn.charCount = function (options) {
		
		var defaults = {
			allowed: 140,
			warning: 25,
			css: 'counter',
			counterElement: 'span',
			counterContainerID: '',
			cssWarning: 'warning',
			cssExceeded: 'exceeded',
			firstCounterText: '',
			endCounterText: '',
			errorCounterText: '',
			errortype: 'positive'
		};
		var options = $.extend(defaults, options);
		
		function calculate(obj) {
			var count = $(obj).val().length;
			var counterText = options.firstCounterText;
			var _css = '';
			containerObj = $("#" + options.counterContainerID);
			var available = options.allowed - count;
			if (available <= options.warning && available >= 0) {
				_css = options.cssWarning;
			}
			if (available < 0) {
				if (options.errortype == 'positive') available = -available;
				counterText = options.errorCounterText;
				_css = options.cssExceeded;
			} else {
				counterText = options.firstCounterText;
			}
			$(containerObj).children().html(counterText + '<em class="' + _css + '">' + available + '</em>' + options.endCounterText);
		}
		
		this.each(function () {
			$("#" + options.counterContainerID).append('<' + options.counterElement + ' class="' + options.css + ' ns-text-color-gray"></' + options.counterElement + '>');
			calculate(this);
			$(this).keyup(function () {
				calculate(this)
			});
			$(this).change(function () {
				calculate(this)
			});
			$(this).focus(function () {
				calculate(this)
			});
		});
	};
	
})(jQuery);