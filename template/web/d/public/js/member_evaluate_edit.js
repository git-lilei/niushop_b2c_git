var uploaderArr = [];

$(function () {
	
	$(".level-item .fa").mouseover(function () {
		var parent = $(this).parent().parent();
		var parent_index = parent.index();
		$(parent).find(".fa").removeClass("fa-star").addClass("fa-star-o");
		$(parent).find(".fa:lt(" + ($(this).index() + 1) + ")").removeClass("fa-star-o").addClass("fa-star");
	});
	
	/*字数限制*/
	$("textarea").on("input propertychange", function () {
		var $this = $(this),
			_val = $this.val(),
			count = "";
		if (_val.length > 150) {
			$this.val(_val.substring(0, 150));
		}
		count = 150 - $this.val().length;
		$(".evaluation-msg-num").text(count);
	});
	
	$('.member-evaluate-edit .evaluation-product-item').each(function (index) {
		uploaderArr[index] = WebUploader.create({
			// 文件接收服务端。
			auto: true,
			server: __URL(SHOPMAIN + '/member/uploadImage'),
			pick: '.head-img' + index,
			// 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
			resize: false,
			fileNumLimit: 5,
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
				if (error) {
					return;
				}
				
				var html = `<div class="file-item thumbnail">
					<img src="` + src + `">
					<div class="file-panel">
         				<span class="cancel">删除</span>
     				</div>
				</div>`;
				
				var liLength = _this.find('.upload-item-list .file-item').length;
				
				if (liLength <= 4) {
					_this.find('.upload-item-list').append(html);
					_this.find('.updata-count').text(liLength + 1);
					_this.find('.updata-surplus').text((4 - liLength));
					if (liLength == 4) {
						_this.find('[class^="head-img"]').addClass('hide');
					}
				}
			});
		});
		
		uploaderArr[index]['evaluateImg'] = [];
		uploaderArr[index].on('uploadSuccess', function (file, res) {
			if (res.code > 0) {
				uploaderArr[index]['evaluateImg'].push(res.data);
			} else {
				show(res.message);
			}
		});
	});
	
	// 删除队列中的图片
	$('body').on('click', '.evaluation-product-item .cancel', function () {
		
		var index = $('.member-evaluate-edit .evaluation-product-item').index($(this).parents('.evaluation-product-item')),
			img_index = $('.evaluation-product-item .upload-item-list .file-item').index($(this).parents('.file-item')),
			parentsEl = $(this).parents('.evaluation-product-item');
		uploaderArr[index].removeFile(uploaderArr[index]['fileQueued'][img_index], true);
		uploaderArr[index]['fileQueued'].slice(img_index, 1);
		
		$(this).parents('.file-item').remove();
		
		if (parentsEl.find('.upload-item-list .file-item').length <= 4) {
			parentsEl.find('[class^="head-img"]').removeClass('hide');
			parentsEl.find('.updata-count').text(parentsEl.find('.upload-item-list .file-item').length);
			parentsEl.find('.updata-surplus').text(5 - parentsEl.find('.upload-item-list .file-item').length);
		}
	})
	
});

var is_sub = false;

//保存评价 type 1评价 2追评
function doSubmit(type) {
	var ajaxUrl = type == 1 ? 'System.Order.addGoodsEvaluate' : 'System.Order.addGoodsReviewEvaluate',
		order_id = $("#order_id").val(),
		order_no = $("#order_no").val();
	
	var goodsEvaluate = [],
		is_have_error = false;
	
	$('.member-evaluate-edit .evaluation-product-item').each(function (index) {
		var content = $(this).find('textarea').val();
		
		if (content.search(/[\S]+/)) {
			show('请输入评价');
			is_have_error = true;
			return false;
		}
		var data = {
			content: content,  // 评价内容
			imgs: uploaderArr[index]['evaluateImg'].join(','),
			order_goods_id: $(this).data("order-goods-id") // 订单项id
		};
		
		if (type == 1) {
			var scores = $(this).find('.fa-2x.fa-star').length;
			if (scores == 1) {
				var explain_type = 3;
			}
			if (scores > 1 && scores < 4) {
				var explain_type = 2;
			}
			if (scores > 3 && scores < 6) {
				var explain_type = 1;
			}
			
			data.is_anonymous = $(this).find('input[type="checkbox"]').is(':checked') ? 1 : 0; // 是否匿名
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
			show('评价成功');
			location.href = __URL(SHOPMAIN + "/member/order");
		}
	});
}