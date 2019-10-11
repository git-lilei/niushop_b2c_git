/**
 * 购物车相关
 */
$(function () {
	updateMoney(true);
	$(".num").blur("input propertychange", function () {
		$cart = $(this);
		var num = $cart.val() * 1;// 购买数量
		var default_num = $cart.attr("data-default-num");
		var max_buy = $cart.attr('max_buy') * 1;// 限购数量
		var min_buy = $cart.attr("min_buy") * 1;//最少购买数量
		var nummax = $cart.attr('max') * 1;// 库存数量
		var cartid = $cart.attr('data-cartid');
		if (isNaN(num) || $cart.val().indexOf(".") != -1) {
			toast("格式错误");
			$cart.val(default_num);
			return;
		}
		if (min_buy != 0 && min_buy > num) {
			toast("该商品最少购买" + min_buy + "件");
			$cart.val(min_buy);
			return;
		} else if (num == 0 || num < 0) {
			$cart.val(1);
			return;
		}
		
		if (max_buy != 0 && num > max_buy) {
			// 限购
			$cart.val(max_buy);
			toast("每个用户限购" + max_buy + "件");
			return;
		}
		
		if (num > nummax) {
			$cart.val(nummax);
			toast("已达到最大库存");
			return;
		}
		
		api('System.Goods.modifyCartNum', {"cart_id": cartid, "num": num}, function (res) {
			if (res.code == 0) {
				$cart.val(num);
			} else {
				toast(res.message);
			}
		}, false);
	});
	
	// 选择按钮触发事件
	$(".checkbox").click(function () {
		if ($("#cart_edit").is(":hidden")) {
			// 删除操作
			if ($(this).attr("is_del") == 'no') {
				$(this).addClass("fa-check-circle ns-text-color").removeClass("fa-circle-o ns-text-color-gray").attr("is_del", "yes");
				$(".btn.btn-buy").addClass("ns-bg-color").removeClass("ns-bg-color-gray-shade-20");
				var select_count = 0;
				$(".checkbox").each(function () {
					if ($(this).attr("is_del") == 'yes') {
						select_count++;
					}
				});
				if ($(".checkbox").length == select_count) {
					$("#select_all").addClass("fa-check-circle ns-text-color").removeClass("fa-circle-o ns-text-color-gray").attr("is_del", "yes");
					$(".btn.btn-buy").addClass("ns-bg-color").removeClass("ns-bg-color-gray-shade-20");
				}
			} else {
				$(this).addClass("fa-circle-o ns-text-color-gray").removeClass("fa-check-circle ns-text-color").attr("is_del", "no");
				var is_dis = 'no';// 是否选中
				var select_count = 0;
				$(".checkbox").each(function () {
					if ($(this).attr("is_del") == 'yes') {
						is_dis = 'yes';
						select_count++;
					}
				});
				if (is_dis == 'no') {
					$(".btn.btn-buy").addClass("ns-bg-color-gray-shade-20").removeClass("ns-bg-color");
				} else {
					$(".btn.btn-buy").addClass("ns-bg-color").removeClass("ns-bg-color-gray-shade-20");
				}
				if ($(".checkbox").length == select_count) {
					$("#select_all").addClass("fa-check-circle ns-text-color").removeClass("fa-circle-o ns-text-color-gray").attr("is_del", "yes");
				} else {
					$("#select_all").addClass("fa-circle-o ns-text-color-gray").removeClass("fa-check-circle ns-text-color").attr("is_del", "no");
				}
			}
		} else {
			// 结算操作
			if ($(this).attr("is_check") == 'no') {
				$(this).addClass("fa-check-circle ns-text-color").removeClass("fa-circle-o ns-text-color-gray").attr("is_check", "yes");
			} else {
				$(this).addClass("fa-circle-o ns-text-color-gray").removeClass("fa-check-circle ns-text-color").attr("is_check", "no");
			}
			var check_count = 0;//总数量
			var select_check_count = 0;//所选数量
			$(".checkbox").each(function () {
				check_count++;
				if ($(this).attr("is_check") == "yes") {
					is_check = true;
					select_check_count++;
				}
			});
			if (check_count == select_check_count) {
				$("#select_all").addClass("fa-check-circle ns-text-color").removeClass("fa-circle-o ns-text-color-gray").attr("is_check", "yes").attr("is_del", "no");
			} else {
				$("#select_all").addClass("fa-circle-o ns-text-color-gray").removeClass("fa-check-circle ns-text-color").attr("is_check", "no").attr("is_del", "no");
			}
			updateMoney(true);
		}
	});
	
	// 点击全选触发事件
	$("#div_selected").click(function () {
		var select_all = $("#select_all");
		var is_check = select_all.attr("is_check");
		var is_del = select_all.attr("is_del");
		var sel_text = $("#sel_text");//全选文本
		if ($("#cart_edit").is(":hidden")) {
			// 删除
			if (is_del == 'no') {
				select_all.addClass("fa-check-circle ns-text-color").removeClass("fa-circle-o ns-text-color-gray").attr("is_del", "yes");
				$(".checkbox").each(function () {
					$(this).addClass("fa-check-circle ns-text-color").removeClass("fa-circle-o ns-text-color-gray").attr("is_del", "yes");
				});
				$(".btn.btn-buy").addClass("ns-bg-color").removeClass("ns-bg-color-gray-shade-20");
			} else {
				select_all.addClass("fa-circle-o ns-text-color-gray").removeClass("fa-check-circle ns-text-color").attr("is_del", "no");
				$(".checkbox").each(function () {
					$(this).addClass("fa-circle-o ns-text-color-gray").removeClass("fa-check-circle ns-text-color").attr("is_del", "no");
				});
				$(".btn.btn-buy").addClass("ns-bg-color-gray-shade-20").removeClass("ns-bg-color");
			}
		} else {
			// 结算
			var temp_is_check_value = "";
			if (is_check == 'no') {
				temp_is_check_value = "yes";
				select_all.addClass("fa-check-circle ns-text-color").removeClass("fa-circle-o ns-text-color-gray");
			} else {
				temp_is_check_value = "no";
				select_all.addClass("fa-circle-o ns-text-color-gray").removeClass("fa-check-circle ns-text-color");
			}
			select_all.attr("is_check", temp_is_check_value);
			$(".checkbox").each(function () {
				$(this).attr("is_check", temp_is_check_value);
				if (is_check == 'no') {
					$(this).addClass("fa-check-circle ns-text-color").removeClass("fa-circle-o ns-text-color-gray");
				} else {
					$(this).addClass("fa-circle-o ns-text-color-gray").removeClass("fa-check-circle ns-text-color");
				}
			});
			updateMoney(true);
		}
		
	});
	
});

/**
 * 获取去重后的数组
 */
function getHeavyArray(arr) {
	var hash = {},
		len = arr.length,
		result = [];
	for (var i = 0; i < len; i++) {
		if (!hash[arr[i]]) {
			hash[arr[i]] = true;
			result.push(arr[i]);
		}
	}
	return result;
}

// 点击结算或者删除触发事件
function settlement() {
	if ($("#cart_edit").is(":visible") && sum_num() > 0) {
		var arr = [];
		
		$('.cart-list-li').each(function () {
			if ($(this).find('.checkbox').attr('is_check') == 'yes') {
				var str = $(this).find('[name="sku_id"]').val() + ':' + $(this).find('.num').val();
				arr.push(str);
			}
		});
		
		if (arr.length == 0) {
			toast("您还没有选择商品哦");
			return;
		}
		
		var data = JSON.stringify({
			order_type: 1,
			goods_sku_list: arr.join(','),
			order_tag: 2
		});
		
		$.ajax({
			type: 'post',
			url: __URL(APPMAIN + "/order/addOrderCreateData"),
			dataType: "JSON",
			data: {data: data},
			success: function (res) {
				location.href = __URL(APPMAIN + "/order/payment");
			}
		});
	} else {
		// 删除
		var del_id_array = [];
		var flag = false;
		$(".cart-list-li").each(function () {
			var is_check = $(this).find(".checkbox").attr("is_del");
			// 计算每家店铺中购物车的商品数量
			if (is_check == 'yes') {
				$(this).find(".checkbox").attr("is_check", "no");
				var del_id = $(this).find("input[name='quantity']").attr("data-cartid");
				del_id_array.push(del_id);
				$(this).remove();
				if ($(".cart-prolist-ul li").length == 0) {
					// alert("我这家店的商品都没了，还不快删除我");
					$(".cart-prolist-ul li").remove();
					flag = true;
				}
			}
		});
		if (flag) {
			updateMoney(true);
		}
		
		$(".btn.btn-buy").addClass("ns-bg-color-gray-shade-20").removeClass("ns-bg-color");
		$("#select_all").addClass("fa-circle-o ns-text-color-gray").removeClass("fa-check-circle ns-text-color").attr("is_del", "no").attr("is_check", "no");
		
		if (del_id_array.length > 0) {
			del_goods(del_id_array.toString());
		} else {
			toast("请选择要删除的商品");
		}
	}
}

// 删除按钮
function del_goods(del_id) {
	api('System.Goods.deleteCart', {"cart_id_array": del_id}, function (res) {
		if (res.code == 0) {
			if (($(".cart-list-li").length) == 0) {
				$(".cart-detail").hide();
				$(".cart-none").show();
				$('.bottom').hide();
				$('.bottom-menu').show();
			}
		} else {
			toast(res.message);
		}
	}, false)
}

// 点击编辑触发事件
function cart_edit(obj) {
	if ($("#cart_edit").is(":hidden")) {
		toast("请先完成之前的操作");
	} else {
		$(obj).hide();
		$(obj).next().show();
		$(".checkbox").addClass("fa-circle-o ns-text-color-gray").removeClass("fa-check-circle ns-text-color");
		$("#select_all").addClass("fa-circle-o ns-text-color-gray").removeClass("fa-check-circle ns-text-color").attr("is_del", "no").attr("is_check", "no");
		$(".btn.btn-buy").addClass("ns-bg-color-gray-shade-20").removeClass("ns-bg-color");
		$("#settlement").text("删除");
		//初始化
		$(".checkbox").each(function () {
			$(this).attr("is_del", "no").attr("is_check", "no");
		});
		updateMoney(false);
	}
}

// 点击完成触发事件
function cart_succ(obj) {
	$(obj).hide();
	$(obj).prev().show();
	$(".cart-prolist-ul").find("input[name='quantity']").each(function () {
		var value = $(this).val();
		$(this).parent().parent().parent().find("span[name='succ_amount']").text(value);// 重新计算数量
	});
	$("#select_all").addClass("fa-check-circle ns-text-color").removeClass("fa-circle-o ns-text-color-gray").attr("is_check", "yes").attr("is_del", "no");
	$(".checkbox").each(function () {
		$(this).addClass("fa-check-circle ns-text-color").removeClass("fa-circle-o ns-text-color-gray").attr("is_del", "no").attr("is_check", "yes");
	});
	updateMoney(true);
}

// 更新价格,flag：true，编辑操作，显示价格信息，false：删除操作，隐藏价格信息
function updateMoney(flag) {
	var vis = flag ? "visible" : "hidden";
	$("#price_info").css("visibility", vis);
	if (flag && $("#cart_edit").is(":visible")) {
		var money = sum_money();//金额
		var num_count = sum_num();//数量
		var num = "结算(" + num_count + ")";
		var integral = get_integral();//积分
		$("#orderprice").text(money);
		$("#settlement").text(num);
		//$("#orderintegral").text("+"+integral+"积分");
		if (num_count > 0) {
			$(".btn.btn-buy").addClass("ns-bg-color").removeClass("ns-bg-color-gray-shade-20");
		} else {
			$(".btn.btn-buy").addClass("ns-bg-color-gray-shade-20").removeClass("ns-bg-color");
		}
	}
}

//计算积分
function get_integral() {
	var integral = 0;
	$(".cart-list-li").each(function () {
		var is_check = $(this).find(".checkbox").attr("is_check");
		if (is_check == 'yes') {
			var temp = $(this).find("span[name='goods_integral']").attr("data-point");
			if (temp != undefined && temp != "") {
				integral += parseInt(temp);
			}
		}
	});
	return integral;
}

// 计算合计金额
function sum_money() {
	var summoney = 0;
	$(".cart-list-li").each(function () {
		var is_check = $(this).find(".checkbox").attr("is_check");
		if (is_check == 'yes') {
			var amount = $(this).find("span[name='succ_amount']").text() * 1;
			var price = $(this).find("span[name='goods_price']").text() * 1;
			summoney = summoney + amount * price;
		}
	});
	return summoney.toFixed(2);
}

// 计算合计数量
function sum_num() {
	var sumnum = 0;
	$(".cart-list-li").each(function () {
		var is_check = $(this).find(".checkbox").attr("is_check");
		if (is_check == 'yes') {
			var amount = $(this).find("span[name='succ_amount']").text() * 1;
			sumnum = sumnum + amount;
		}
	});
	return sumnum;
}

//检测商品限购，是否允许购买
function getGoodsPurchaseRestrictionForCurrentUser(goods_id, num, callBack) {
	api('System.Goods.goodsPurchaseRestriction', {"goods_id": goods_id, "num": num}, function (res) {
		if (res.code == 0) {
			if (callBack) callBack(res.data);
		} else {
			toast(res.message);
		}
	}, false)
}

var Cart = {
	changeBar: function (type, skuId, obj, goods_id) {
		var txtC = null;
		var change = 0;
		var default_num = 0;
		if (type == '+') {
			txtC = $(obj).prev();
			default_num = $(obj).prev().attr("data-default-num");
			change = 1;
		}
		if (type == '-') {
			txtC = $(obj).next();
			default_num = $(obj).next().attr("data-default-num");
			change = -1;
		}
		var num = parseInt(txtC.val());
		if (num + change < 0) {
			art.dialog({
				time: 3000,
				lock: true,
				title: '提示消息',
				content: '您输入的数字已经超出的最小值！'
			});
			return;
		}
		var nummax = txtC.attr('max') * 1;
		var max_buy = txtC.attr('max_buy') * 1;
		var min_buy = txtC.attr("min_buy") * 1;
		num = num + change;
		if (min_buy != 0 && min_buy > num) {
			num = min_buy;
			toast("该商品最少购买" + min_buy + "件");
			return;
		}
		else if (num == 0) {
			num = 1;
			toast("最小数量为1");
			return;
		}
		
		if (max_buy != 0 && num > max_buy) {
			toast("该商品每人限购" + max_buy + "件");
			return;
		}
		
		if (num > nummax) {
			num = nummax;
			toast("已达到最大库存");
			return;
		}
		
		var is_allow = true;
		if (type == '+') {
			getGoodsPurchaseRestrictionForCurrentUser(goods_id, num, function (call) {
				if (call.code == 0) {
					is_allow = false;
					num = default_num;
					toast(call.message);
				}
			});
		}
		
		txtC.val(num);
		
		$(obj).parent(".ui-number").next("[name='succ_amount']").text(num);
		
		calculated_price(num, obj, goods_id);
		
		if (is_allow) this.changeProductCount(skuId, txtC[0], change);
		
		updateMoney(true);
	},
	changeProductCount: function (cartid, tmpObj, change) {
		var obj = $(tmpObj);
		api('System.Goods.modifyCartNum', {"cart_id": cartid, "num": obj.val()}, function (res) {
			if (res.code < 0) {
				toast(res.message);
			}
		}, false);
	}
	
};

//计算阶梯优惠
function calculated_price(num, obj, goodsid) {
	var goods_ladder_preferential = $("#goods_ladder_preferential").val();
	var arr = JSON.parse(goods_ladder_preferential);
	var price_obj = $(obj).parents(".cart-list-li").find("[name='goods_price']");
	var price = price_obj.attr("data-promotion-price") * 1;
	if (arr.length > 0) {
		for (var i = 0; i < arr.length; i++) {
			var item = arr[i];
			for (var v = 0; v < item.length; v++) {
				if (num >= item[v]['quantity'] && item[v]['goods_id'] == goodsid) {
					price -= item[v]['price'];
					break;
				}
			}
		}
	}
	price_obj.text(price);
}