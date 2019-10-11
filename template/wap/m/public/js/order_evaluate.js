var uploaderArr = new Array(),
	is_sub = false;

$(function () {
	$('.star li').click(function (event) {
		var index = $(this).index();
		$(this).addClass('ns-text-color');
		$(this).parent('ul').find('li:lt(' + index + ')').addClass('ns-text-color');
		$(this).parent('ul').find('li:gt(' + index + ')').removeClass('ns-text-color');
	});
	
	// 是否匿名
	$('.evaluate-footer .pull-left').click(function (event) {
		if ($(this).find('.iconfont').hasClass('iconchecked')) {
			$(this).find('.iconfont').attr('class', 'iconfont iconcheckbox ns-text-color')
		} else {
			$(this).find('.iconfont').attr('class', 'iconfont iconchecked ns-text-color')
		}
	});
	
	$('.evaluate-container .evaluate-item').each(function (index) {
		uploaderArr[index] = WebUploader.create({
			// 文件接收服务端。
			auto: true,
			server: __URL(APPMAIN + '/order/uploadImage'),
			pick: '.upload' + index,
			// 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
			resize: false,
			fileNumLimit: 6,
			// 只允许选择图片文件。
			accept: {
				title: 'Images',
				extensions: 'gif,jpg,jpeg,bmp,png',
				mimeTypes: 'image/*'
			},
			formData: {
				param: JSON.stringify({})
			},
			thumb: {
				width: 100,
				height: 100,
				crop: false
			}
		});
		
		// 上传文件对象
		uploaderArr[index]['fileQueued'] = [];
		
		var _this = $(this);
		// 当有文件被添加进队列的时候
		uploaderArr[index].on('fileQueued', function (file) {
			uploaderArr[index]['fileQueued'].push(file);
			uploaderArr[index].makeThumb(file, function (error, src) {
				if (error) return;
				
				var html = `<li>
					<div class="box">
						<div class="evaluate-img">
							<img src="` + src + `">
							<a href="javascript:;" class="del"><i class="iconfont iconshanchu"></i></a>
						</div>
					</div>
				</li>`;
				
				var liLength = _this.find('.evaluate-img-group li').length;
				
				if (liLength > 6) {
					_this.find('.upload-li').addClass('hide');
				} else {
					_this.find('.upload-li').before(html);
					_this.find('.upload-shade .num').text((6 - liLength));
				}
			});
		});
		
		uploaderArr[index]['evaluateImg'] = [];
		uploaderArr[index].on('uploadSuccess', function (file, res) {
			if (res.code > 0) {
				uploaderArr[index]['evaluateImg'].push(res.data.path);
			} else {
				toast(res.message);
			}
		});
	});
	
	// 删除队列中的图片
	$('body').on('click', '.evaluate-img-group li .del', function () {
		var index = $('.evaluate-container .evaluate-item').index($(this).parents('.evaluate-item')),
			img_index = $('.evaluate-img-group li').index($(this).parents('li')),
			parentsEl = $(this).parents('.evaluate-item');
		
		uploaderArr[index].removeFile(uploaderArr[index]['fileQueued'][img_index], true);
		uploaderArr[index]['fileQueued'].slice(img_index, 1);
		
		$(this).parents('li').remove();
		
		if (parentsEl.find('.evaluate-img-group li').length < 7) {
			parentsEl.find('.evaluate-img-group .upload-li').removeClass('hide');
			parentsEl.find('.upload-shade .num').text((7 - parentsEl.find('.evaluate-img-group li').length));
		}
	});
	
	// 提交评价
	$('.sub-btn').click(function () {
		var ajaxUrl = again ? 'System.Order.addGoodsReviewEvaluate' : "System.Order.addGoodsEvaluate";
		var is_have_error = false;
		var goodsEvaluate = [];
		$('.evaluate-container .evaluate-item').each(function (index) {
			var content = $(this).find('textarea').val();
			if (content.search(/[\S]+/)) {
				toast('请输入评价');
				is_have_error = true;
				return false;
			}
			var data = {
				content: content,  // 评价内容
				scores: scores,// 评分
				explain_type: explain_type,// 评价类型
				imgs: uploaderArr[index]['evaluateImg'].join(','),
				order_goods_id: $(this).attr('data-value') // 订单项id
			};
			
			if (!again) {
				var scores = $(this).find('.star .ns-text-color:last-child').index() + 1;
				if (scores == 1) {
					var explain_type = 3;
				}
				if (scores > 1 && scores < 4) {
					var explain_type = 2;
				}
				if (scores > 3 && scores < 6) {
					var explain_type = 1;
				}
				
				data.is_anonymous = $('.evaluate-footer .iconfont').hasClass('iconchecked') ? 1 : 0; // 是否匿名
				data.scores = scores;
				data.explain_type = explain_type;
			}
			goodsEvaluate.push(data);
		});
		
		if (is_have_error) return;
		
		if (is_sub) return;
		is_sub = true;
		
		api(ajaxUrl, {
			"goods_evaluate": JSON.stringify(goodsEvaluate),
			"order_id": order_id,
			"order_no": order_no
		}, function (res) {
			if (res.data == 1) {
				toast('评价成功');
				location.href = __URL(APPMAIN + "/order/lists");
			}
		});
	});
	
});