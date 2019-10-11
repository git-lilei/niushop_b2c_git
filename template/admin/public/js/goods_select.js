$(function(){
	//公共下拉框
	$('.select-common').selectric();

	//公共单选框点击切换样式
	$(".radio-common").live("click",function(){
		var radio = $(this).children("input");
		var name = radio.attr("name");
		if(radio.is(":checked")){
			$(".radio-common>input[type='radio'][name='" + name + "']").parent().removeClass("selected");
			$(this).addClass("selected");
		}else{
			$(this).removeClass("selected");
		}
	});

	// 切换商品来源
	$(".set-style [name='from_type']").click(function(event) {
		var type = $(this).val();
		$('.set-style [data-type]').addClass('hide');
		$('.set-style [data-type="'+ type +'"]').removeClass('hide');
	});

	// 搜索商品
	$(".search-goods").click(function(event) {
		LoadingInfo(1);
	});

	// 单选
	$('body').on('change', '.table-class tbody [type="checkbox"]', function(){
		var val = parseInt($(this).val());
		if(limit.is_many_select == 1){
			if($(this).is(':checked')){
				if($.inArray(val, goodsIdArray) == -1){
					goodsIdArray.push(val);
				}
				$(this).parents('.checkbox-common').addClass('selected');
			}else{
				if($.inArray(val, goodsIdArray) != -1){
					goodsIdArray.splice($.inArray(val, goodsIdArray), 1);
				}
				$(this).parents('.checkbox-common').removeClass('selected');
			}
		}else{
			if($(this).is(':checked')){
				goodsIdArray[0] = val;
				$(this).parents('.checkbox-common').addClass('selected');
				$(this).parents('tr').siblings('tr').find('.checkbox-common').removeClass('selected');
				$(this).parents('tr').siblings('tr').find('.checkbox-common input').prop('checked', false);
			}else{
				goodsIdArray = [];
				$(this).parents('.checkbox-common').removeClass('selected');
			}
		}
	})

	// 全选
	$('.table-class thead [type="checkbox"]').change(function(){
		if(limit.is_many_select == 0) return;
		if($(this).is(':checked')){
			$(this).parents('.checkbox-common').addClass('selected');
			$('.table-class tbody [type="checkbox"]').each(function(index, el) {
				var val = parseInt($(el).val());
				if($.inArray(val, goodsIdArray) == -1){
					goodsIdArray.push(val);
				}
				$(el).parents('.checkbox-common').addClass('selected');
			});
		}else{
			$(this).parents('.checkbox-common').removeClass('selected');
			$('.table-class tbody [type="checkbox"]').each(function(index, el) {
				var val = parseInt($(el).val());
				if($.inArray(val, goodsIdArray) != -1){
					goodsIdArray.splice($.inArray(val, goodsIdArray), 1);
				}
				$(el).parents('.checkbox-common').removeClass('selected');
			});
		}
	})
})

function LoadingInfo(page){
	var field = getConditionValue();
	$.ajax({
		url: __URL(ADMINMAIN + '/promotion/goodsSelectList'),
		type: 'POST',
		data: {
			'page_index' : page,
			'page_size' : $('#showNumber').val(),
			'value' : JSON.stringify(field),
			'is_limit_sku' : limit.is_limit_sku,
			'is_limit_skock' : limit.is_limit_skock,
			'is_limit_state' : limit.is_limit_state,
			'is_limit_goods_type' : limit.is_limit_goods_type
		},
		success : function(data){
			if(data.data.length > 0){
				$(".table-class tbody").empty();
				for (var i = 0; i < data["data"].length; i++) {
					var item = data["data"][i];
						item.state_name = item.state == 1 ? '已上架' : '已下架';
						item.checked = $.inArray(item.goods_id, goodsIdArray) != -1 ? 'checked' : '';
						
					var html = `
						<tr>
							<td class="align-center">
								<label class="checkbox-common `+ (item.checked != '' ? 'selected' : '') +`">
									<input type="checkbox" value="`+ item.goods_id +`" `+ item.checked +`/>
								</label>
							</td>
							<td>
								<div class="goods-info">
									<img src="`+ __IMG(item.picture_info.pic_cover_micro) +`" alt="">
									<p class="goods-name"><a href="`+__URL(SHOPMAIN + '/goods/detail?goods_id=' + item.goods_id )+`">`+ item.goods_name +`</a></p>
									<p class="goods-price">￥`+ item.price +`</p>
								</div>
							</td>
							<td class="align-center">`+ item.stock +`</td>
							<td class="align-center">`+ item.type_config.title +`</td>
							<td class="align-center">`+ item.state_name +`</td>
						</tr>
					`;
					$(".table-class tbody").append(html);
				}
			}else{
				$('.table-class tbody').html('<tr class="align-center"><td colspan="5">暂无符合条件的数据记录</td></tr>');
			}
			initPageData(data["page_count"],data['data'].length,data['total_count']);
			$("#pageNumber").html(pagenumShow(jumpNumber,$("#page_count").val(), pageshow));
		}
	});	
}

function getConditionValue(){
	var field = {
		from_type : $('.set-style [name="from_type"]:checked').val()
	};

	if(field.from_type == 'category'){
		field[field.from_type] = $('.set-style [data-type="'+ field.from_type +'"] .select-category').attr('data-value');
	}else{
		field[field.from_type] = $('.set-style [data-type="'+ field.from_type +'"] [name="'+ field.from_type +'"]').val();
	}
	return field;
}

// 选取之后回调
function returnData(callback){
	try	{
		if(typeof callback == 'function'){
			callback(goodsIdArray);
		}
	} catch (e){
		console.error(e.message);
	}
}