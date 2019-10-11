var vue = new Vue({
	el: "#app",
	data: {
		isNeedInvoice: false, // 是否需要发票
		taxMoney: 0.00, // 税费
		totalMoney: 0.00, // 总金额
		point: 0, // 使用积分数
		shippingMoney: 0.00, // 运费
		promotionMoney: 0.00, // 优惠金额
		goodsMoney: 0.00, // 商品金额
		payMoney: 0.00, // 实际支付金额
		pointMoney: 0.00, // 积分抵现金额
		couponMoney: 0.00, // 优惠券优惠金额
		fullAddress: _params.full_address // 寄送地址
	}
});

$(function () {
	$('[data-toggle="tooltip"]').tooltip();
	
	$(".discount-cont .nav-tabs li:eq(0)").addClass('active');
	$(".discount-cont .tab-content .tab-pane:eq(0)").addClass('active');
	
	// 更多地址显示隐藏
	$(".address-cont .more-addr").click(function () {
		var _this = $(this);
		if ($(this).hasClass('show')) {
			$(".address-cont ul").animate({height: '46px'}, 300, function () {
				_this.text('更多地址').removeClass('show');
			});
		} else {
			var auto_height = $(".address-cont ul").css({'height': 'auto'}).height();
			$(".address-cont ul").height(46).animate({height: auto_height + 'px'}, 300, function () {
				_this.text('收起地址').addClass('show');
			});
		}
	});
	
	// 点击选择收货地址
	$('.address-cont li .addr-name').click(function (event) {
		$(this).addClass('ns-border-color').parents('li').addClass('active');
		$(this).parents('li').siblings('li').removeClass('active').find('.addr-name').removeClass('ns-border-color').addClass("ns-border-color-gray");
		Address.setDefaultAddr($(this).parents('li').attr('data-value'));
	});
	
	// 点击选择配送方式
	$('.shipping-type-cont li').click(function (event) {
		var addressJson = $('.address-cont li.active [data-json]').attr('data-json');
		if (addressJson != undefined) {
			var address_data = JSON.parse(addressJson);
			vue.fullAddress = address_data.full_address;
		}
		
		$(this).addClass('active ns-border-color').removeClass("ns-border-color-gray").siblings('li').removeClass('active ns-border-color').addClass("ns-border-color-gray");
		var type_value = $(this).attr('data-value');
		if (type_value == 1) {
			$('.express-company-cont,.logistics').removeClass('hide');
			$('.picksite-cont,.o2o').addClass('hide');
		} else if (type_value == 2) {
			var picksiteJson = $('.picksite-cont li.active [data-json]').attr('data-json');
			if (picksiteJson != undefined) {
				var address_data = JSON.parse(picksiteJson);
				vue.fullAddress = address_data.full_address;
			}
			$('.picksite-cont').removeClass('hide');
			$('.express-company-cont,.logistics,.o2o').addClass('hide');
		} else if (type_value == 3) {
			$('.o2o').removeClass('hide');
			$('.picksite-cont,.express-company-cont,.logistics').addClass('hide');
		}
		order.calculate();
	});
	
	// 点击选择发票
	$('.invoice-cont .is-need-invoice li').click(function (event) {
		$(this).addClass('active ns-border-color').removeClass("ns-border-color-gray").siblings('li').removeClass('active ns-border-color').addClass("ns-border-color-gray");
		if ($(this).attr('data-value') == 0) {
			vue.isNeedInvoice = false;
			$('.invoice-cont .form-horizontal').addClass('hide');
			$('.invoice-cont .form-horizontal [name]').val('');
		} else {
			vue.isNeedInvoice = true;
			$('.invoice-cont .form-horizontal').removeClass('hide');
		}
		order.calculate();
	});
	
	// 点击选择支付方式 发票内容 选择物流公司
	$('.payment-type-cont li,.invoice-cont .form-horizontal li,.express-company-cont li').click(function (event) {
		$(this).addClass('active ns-border-color').removeClass("ns-border-color-gray").siblings('li').removeClass('active ns-border-color').addClass("ns-border-color-gray");
		order.calculate();
	});
	
	// 修改配送时间
	$(".distribution-time .update").click(function () {
		$("#distributionTimeMoadl").modal();
	});
	
	$(".distribution-time-cont .select").click(function () {
		$(".distribution-time-cont .select").removeClass('active ns-bg-color').addClass("ns-text-color");
		$(this).addClass('active ns-bg-color').removeClass("ns-text-color");
	});
	
	$("#distributionTimeMoadl .btn").click(function () {
		var data = $(".distribution-time-cont .select.active").data();
		$(".logistics time").text('预计 ' + data.value + ' 送达').attr({
			'data-time': data.time,
			'data-time-slot': data.timeSlot
		});
		$("#distributionTimeMoadl").modal('hide');
	});
	
	// 修改自提点
	$(".picksite-cont .update").click(function () {
		$("#selfLiftingModal").modal();
	});
	
	$("#selfLiftingModal .btn").click(function () {
		var jsonStr = $('#selfLiftingModal [type="radio"]:checked').parents('li').find('[data-json]').attr('data-json');
		if (jsonStr != undefined) {
			var data = JSON.parse(jsonStr);
			$(".picksite-cont li").attr('data-value', data.id);
			$(".picksite-cont li .addr-name").attr('title', data.name).text(data.name);
			$(".picksite-cont li .addr-detail").text(data.full_address);
			// $(".picksite-cont li .addr-distance").text('距收货人 '+ data.distance +'米');
		}
		$("#selfLiftingModal").modal('hide');
		order.calculate();
	});
	
	// 使用积分
	$('.use-point').change(function (event) {
		var point = parseInt($(this).val()),
			max_use_point = $(this).attr('data-max-available');
		if (point < 0) point = 0;
		if (point > max_use_point) point = max_use_point;
		$(this).val(point);
		order.calculate();
	});
	
	// 优惠券选择
	$(".coupon-list .coupon-item").click(function () {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active ns-border-color-fade-40').addClass("ns-border-color-gray");
			if ($(".discount-info .info").length == 1) {
				$(".discount-info").hide();
			} else {
				$(".discount-info .coupon").hide();
			}
		} else {
			$(this).addClass('active ns-border-color-fade-40').removeClass("ns-border-color-gray").siblings('.coupon-item').removeClass('active ns-border-color-fade-40').addClass("ns-border-color-gray");
			if ($(".discount-info .info").length == 1) {
				$(".discount-info").show();
			} else {
				$(".discount-info .coupon").show();
			}
		}
		order.calculate();
	});
	
	// 预售是否全款
	$('[name="is_full_payment"]').click(function (event) {
		order.calculate();
	});
});

var is_sub = false;

function Order() {
	var data = {
		order_type: _params.order_type, // 订单类型
		goods_sku_list: _params.goods_sku_list, // 购买商品规格
		is_virtual: _params.is_virtual, // 是否是虚拟商品
		buyer_ip: _params.buyer_ip, // 购买人ip
		user_money: 0, // 用户余额
		platform_money: 0, // 平台余额
		shipping_info: {}, // 配送信息
		buyer_invoice: '', // 发票信息
		buyer_message: '', // 买家留言
		promotion_type: _params.promotion_type,
		coin: 0, // 购物币
		promotion_info: _params.promotion_info
	};
	
	this.getValue = function () {
		if (data.is_virtual == 0) {
			data.shipping_info = {}; // 每次获取前先清空原数据
			data.shipping_info.shipping_type = $('.shipping-type-cont li.active').attr('data-value');
			if (data.shipping_info.shipping_type == 1) {
				data.shipping_info.shipping_company_id = $('.express-company-cont li.active').attr('data-value') != undefined ? $('.express-company-cont li.active').attr('data-value') : 0;
				data.shipping_info.shipping_time = $('.logistics time').attr('data-time');
				data.shipping_info.distribution_time_out = $('.logistics time').attr('data-time-slot');
			} else if (data.shipping_info.shipping_type == 2) {
				data.shipping_info.pick_up_id = $('.picksite-cont li.active').attr('data-value') != undefined ? $('.picksite-cont li.active').attr('data-value') : 0;
			}
		} else {
			data.user_telephone = $('.account-cont [name="mobile"]').val();
		}
		if ($('.invoice-cont .is-need-invoice li.active').attr('data-value') == 1) {
			var invoiceFormObj = $('.invoice-cont .form-horizontal');
			data.buyer_invoice = invoiceFormObj.find('[name="invoice_title"]').val() + '$' + invoiceFormObj.find('li.active').text() + '$' + invoiceFormObj.find('[name="taxpayer_identification_number"]').val();
		} else {
			data.buyer_invoice = '';
		}
		data.pay_type = $('.payment-type-cont li.active').attr('data-value');
		data.buyer_message = $('.distribution-cont .user-message textarea').val();
		data.coupon_id = $('.discount-cont .coupon-item.active').attr('data-value') != undefined ? $('.discount-cont .coupon-item.active').attr('data-value') : 0;
		data.point = isNaN(parseInt($('.discount-cont .use-point').val())) ? 0 : parseInt($('.discount-cont .use-point').val());
		data.address_id = $('.address-cont li.active').attr('data-value') != undefined ? $('.address-cont li.active').attr('data-value') : 0;
		// 预售订单
		if (data.order_type == 6) {
			data.presell_info = {
				is_full_payment: $('[name="is_full_payment"]:checked').val()
			}
		}
	};
	
	// 订单计算
	this.calculate = function () {
		this.getValue();
	
		api('System.Order.orderCalculate', {'data': JSON.stringify(data)}, function (res) {
			if (res.code == 0) {
				vue.taxMoney = parseFloat(res.data.tax_money).toFixed(2);
				vue.totalMoney = parseFloat(res.data.total_money).toFixed(2);
				vue.shippingMoney = parseFloat(res.data.shipping_money).toFixed(2);
				
				if(res.data.coupon_money){
					vue.promotionMoney = Number(parseFloat(res.data.promotion_money).toFixed(2)) + Number(parseFloat(res.data.coupon_money).toFixed(2));
				}else{
					vue.promotionMoney = parseFloat(res.data.promotion_money).toFixed(2);
				}				
				
				vue.goodsMoney = parseFloat(res.data.goods_money).toFixed(2);
				vue.payMoney = parseFloat(res.data.pay_money).toFixed(2);
				vue.point = res.data.offset_money_array.point.num;
				vue.pointMoney = parseFloat(res.data.offset_money_array.point.offset_money).toFixed(2);
				vue.couponMoney = res.data.coupon_money;
				data.pay_money = res.data.pay_money;
				if (data.order_type == 6) {
					vue.payMoney = parseFloat(res.data.presell_order_pay_money).toFixed(2);
					data.pay_money = res.data.presell_order_pay_money;
				}
			} else {
				show(res.message);
			}
		}, false)
	};
	
	// 订单提交
	this.submit = function () {
		this.getValue();
		if (this.verify()) {
			if (is_sub) return;
			is_sub = true;
			api('System.Order.orderCreate', {'data': JSON.stringify(data)}, function (res) {
				if (res.code == 0) {
					//清除订单数据(本地数据) (如果是购物车购买,要删除购物车中的数据)
					$.ajax({
						type: 'post',
						url: __URL(SHOPMAIN + "/order/deleteCreateData"),
						dataType: "JSON",
						data: {data: data},
						success: function (res) {
						}
					});
					
					//如果实际付款金额为0，跳转到个人中心的订单界面中
					if (data.pay_type == 4) {
						location.href = __URL(SHOPMAIN + '/member/order');
					} else {
						window.location.href = __URL(APPMAIN + '/pay/pay?out_trade_no=' + res.data.out_trade_no);
					}
				} else {
					show(res.message);
					is_sub = false;
				}
			}, false)
		}
	};
	
	// 验证
	this.verify = function () {
		// 非虚拟商品
		if (data.is_virtual == 0) {
			if (data.address_id == 0) {
				show('请先选择收货地址');
				return false;
			}
			if (data.pay_type == undefined) {
				show('商家未配置支付方式');
				return false;
			}
			// 如果用户选择商家配送的话 不考虑配送方式有没有开启
			if (data.pay_type != 4) {
				if (data.shipping_info.shipping_type == undefined) {
					show('商家未启用配送方式');
					return false;
				}
			}
			if (data.shipping_info.shipping_type == 2 && data.shipping_info.pick_up_id == 0) {
				show('请先选择自提点');
				return false;
			}
		} else {
			if (data.user_telephone.search(regex.mobile) == -1) {
				show('请输入正确的手机号');
				return false;
			}
		}
		// 发票
		if ($('.invoice-cont .is-need-invoice li.active').attr('data-value') == 1) {
			if ($('.invoice-cont [name="invoice_title"]').val().search(/[\S]+/)) {
				show('请填写发票抬头');
				return false;
			}
			if ($('.invoice-cont [name="taxpayer_identification_number"]').val().search(/[\S]+/)) {
				show('请输入纳税人识别号');
				return false;
			}
		}
		return true;
	}
}

var order = new Order();
order.calculate();