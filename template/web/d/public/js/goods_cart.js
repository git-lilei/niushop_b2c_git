function Cart() {
	this.cart_id = [];
	this.init();
}

//初始化
Cart.prototype.init = function () {
	this.updateData();
};

//更新数据
Cart.prototype.updateData = function () {
	var arr = [];
	$(".list-item input[type='checkbox']:checked").each(function (i, v) {
		var data = {
			cart_id: $(v).val(),
			price: $(v).parents(".list-item").find(".price span").attr("data-value"),
			num: $(v).parents(".list-item").find(".num").attr("data-value"),
			sku_id: $(v).parents(".list-item").find("[name='sku_id']").val(),
			promotion_price: $(v).parents(".list-item").find(".price").attr("data-promotion-price"),
			goods_id: $(v).parent().attr("data-goods-id")
		};
		arr.push(data);
	});
	
	this.cart_id = arr;
	
	var goods_ladder_preferential = [];
	if ($("#hidden_goods_ladder_preferential").val()) {
		var goods_ladder_preferential = JSON.parse($("#hidden_goods_ladder_preferential").val());
		if (goods_ladder_preferential[0]) {
			goods_ladder_preferential = goods_ladder_preferential[0];
		}
	}
	
	//更新价格
	var total_price = 0;
	if (this.cart_id.length > 0) {
		$(this.cart_id).each(function (i, v) {
			var unit_price = parseFloat(v.promotion_price) * v.num;
			var price = parseFloat(v.promotion_price);
			for (var k = 0; k < goods_ladder_preferential.length; k++) {
				if (goods_ladder_preferential[k].goods_id == v.goods_id) {
					if (v.num >= goods_ladder_preferential[k].quantity) {
						unit_price = (v.promotion_price - goods_ladder_preferential[k].price) * v.num;
						price = v.promotion_price - goods_ladder_preferential[k].price;
						break;
					}
				}
			}
			$("#subtotal_" + v.cart_id).text("￥" + unit_price.toFixed(2));
			$(".js_price_" + v.cart_id).text("￥" + price.toFixed(2));
			total_price += unit_price;
		})
	}
	$(".foot-right .total-price").text("￥" + total_price.toFixed(2));
};

//复选框的单选和全选
Cart.prototype.checked = function (event, type) {
	if (type == "one") {
		if ($(event).is(':checked')) $(event).parents(".list-item").addClass("selected");
		else $(event).parents(".list-item").removeClass("selected");
		
		//当单选按钮全部选中
		if ($(".list-item input[type='checkbox']:checked").length == $(".list-item input[type='checkbox']").length)
			$(".cart-foot input[type='checkbox'],.cart-check input[type='checkbox']").prop("checked", true);
		else
			$(".cart-foot input[type='checkbox'],.cart-check input[type='checkbox']").prop("checked", false);
	} else if (type == "all") {
		var sign = $(event).is(':checked');
		
		$(".cart-list input[type='checkbox']").prop("checked", sign);
		if (sign) {
			$(".list-item").addClass("selected");
		} else {
			$(".list-item").removeClass("selected");
		}
	}
	this.updateData();
};

//改变数量
Cart.prototype.changeNumber = function (event, type) {
	
	var _this = this;
	numObj = $(event).parent(".item-counter").find(".num"),
		num = parseInt(numObj.val()),
		min_buy = numObj.attr("min"),
		max_buy = numObj.attr("max"),
		cart_id = $(event).parents(".list-item").find("input[type=checkbox]").val(),
		old_num = parseInt(numObj.attr('data-value')),
		reg = /^[1-9]\d*$|^0$/;
	if (type == "change") {
		if (num > max_buy) {
			num = max_buy;
		} else if (num <= min_buy && min_buy > 0) {
			num = min_buy;
		} else if (num <= 0 && min_buy == 0) {
			num = 1;
		}
		if (!reg.test(num)) {
			show("输入的格式不正确");
			numObj.val(old_num);
			return;
		}
	} else {
		/*减数量*/
		if ($(event).hasClass("reduce")) {
			if (num > min_buy && min_buy > 0 || num > 1 && min_buy == 0) {
				num--;
			} else {
				return;
			}
		}
		/*加数量*/
		else if ($(event).hasClass("plus")) {
			if (num < max_buy) {
				num++;
			} else {
				return;
			}
		}
	}
	api('System.Goods.modifyCartNum', {"cart_id": cart_id, "num": num}, function (res) {
		if (res.data > 0) {
			numObj.val(num);
			numObj.attr("data-value", num);
			_this.updateData();
		} else {
			show("操作失败")
		}
	})
};

//删除购物车
Cart.prototype.deleteCart = function (event, type) {
	var _this = this;
	switch (type) {
		case"one":
			var cart_id = $(event).parents(".list-item").find("input[type='checkbox']").val();
			api('System.Goods.deleteCart', {"cart_id_array": cart_id}, function (res) {
				var data = res.data;
				if (data > 0) {
					if ($('.cart-body .list-item').length == 1) {
						location.reload();
					} else {
						$(event).parents(".list-item").remove();
						_this.updateData();
					}
				} else {
					show("删除失败");
				}
			});
			break;
		case"selected":
			var arr = [];
			$(this.cart_id).each(function (i, v) {
				arr.push(v.cart_id);
			});
			api('System.Goods.deleteCart', {"cart_id_array": arr.toString()}, function (res) {
				var data = res.data;
				if (data > 0) {
					if ($('.cart-body .list-item').length > 0) {
						$('.cart-body .list-item input[type="checkbox"]:checked').each(function () {
							$(this).parents(".list-item").remove();
						});
						_this.updateData();
					} else {
						location.reload();
					}
				} else {
					show("删除失败");
				}
			});
			break;
		case"all":
			var arr = [];
			$(this.cart_id).each(function (i, v) {
				arr.push(v.cart_id);
			});
			api('System.Goods.deleteCart', {"cart_id_array": arr.toString()}, function (res) {
				var data = res.data;
				if (data > 0) {
					location.reload();
				} else {
					show("删除失败");
				}
			});
			break;
	}
};

//结算
Cart.prototype.Settlement = function () {
	if (this.cart_id.length > 0) {
		var arr = [];
		$(this.cart_id).each(function (i, v) {
			arr.push(v.sku_id + ':' + v.num);
		});
		var data = JSON.stringify({
			order_type: 1,
			goods_sku_list: arr.join(','),
			order_tag: 2
		});
		
		$.ajax({
			type: 'post',
			url: __URL(SHOPMAIN + "/order/addOrderCreateData"),
			dataType: "JSON",
			data: {data: data},
			success: function (res) {
				location.href = __URL(SHOPMAIN + "/member/payment");
			}
		});
	} else {
		show("您还没有选择商品哦")
	}
};

var cart = new Cart();

$(function () {
	getMyFootprint();
})

function getMyFootprint() {
	api('System.Member.footprint', {"page_index": 1, "page_size": 0, "category_id": ""}, function (res) {
		var data = res['data'];
		if (data['data'].length > 0) {
			var html = "";
			for (var i = 0; i < data['data'].length; i++) {
				if (i % 5 == 0 && i == 0) {
					if (i == 0) {
						html += "<div class='item active'>";
					} else {
						html += "<div class='item'>";
					}
					html += "<div class='box'>";
				}
				html += "<div class='product-item ns-border-color-gray-shade-20'>";
				html += "<div class='p-img'>";
				html += "<a target='_blank' href='" + __URL(SHOPMAIN + '/goods/detail?goods_id=' + data['data'][i]['goods_id']) + "'>";
				html += "<img width='160' height='160' src='" + __IMG(data['data'][i]['goods_info']['picture_info']['pic_cover_mid']) + "' alt='" + data['data'][i]['goods_info']['goods_name'] + "'>";
				html += "</a>";
				html += "</div>";
				html += "<div class='p-name'>";
				html += "<a target='_blank' href='" + __URL(SHOPMAIN + '/goods/detail?goods_id=' + data['data'][i]['goods_id']) + "'>" + data['data'][i]['goods_info']['goods_name'] + "</a>";
				html += "</div>";
				html += "<div class='p-price'>";
				html += "<strong class='ns-text-color'>";
				html += "<em>￥</em><i>" + data['data'][i]['goods_info']['price'] + "</i>";
				html += "</strong>";
				html += "</div>";
				html += "<div class='p-btn'>";
				html += "<a href='" + __URL(SHOPMAIN + '/goods/detail?goods_id=' + data['data'][i]['goods_id']) + "' class='add-cart ns-border-color ns-text-color'>查看详情</a>";
				html += "</div>";
				html += "</div>";
				if (i % 5 == 4 && i != 0) {
					html += "</div>";
					html += "</div>";
					if (i != (data['data'].length - 1)) {
						html += "<div class='item'>";
						html += "<div class='box'>";
					}
				}
				if (i == (data['data'].length - 1)) {
					html += "</div>";
					html += "</div>";
				}
			}
			if (data['data'].length > 5) {
				var html_w = "";
				html_w += "<a class='left carousel-control' href='#history' data-slide='prev'>";
				html_w += "<span class='icon icon-chevron-left'></span>";
				html_w += "</a>";
				html_w += "<a class='right carousel-control' href='#history' data-slide='next'>";
				html_w += "<span class='icon icon-chevron-right'></span>";
				html_w += "</a>";
				
				$("#history .carousel-container .switch-box").html(html_w);
			}
			
			$("#history.history .carousel-container .carousel-inner").html(html);
		} else {
			var html = "";
			html += "<div class='empty'>您还没有浏览足迹！</div>"
			
			$("#history .empty_wai").html(html);
		}
	});
}