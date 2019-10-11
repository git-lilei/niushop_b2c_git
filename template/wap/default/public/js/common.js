// 公共验证规则
var regex = {
	mobile: /^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[0135678]|9[89])\d{8}$/,
	email: /^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/,
	chinese_characters: /.*[\u4e00-\u9fa5]+.*$/
};

function api(method, param, callback, async) {
	// async true为异步请求 false为同步请求
	var async = async != undefined ? async : true;
	$.ajax({
		type: 'post',
		url: __URL(APPMAIN + "/index/ajaxapi"),
		dataType: "JSON",
		async: async,
		data: {method: method, param: JSON.stringify(param)},
		success: function (res) {
			if (callback) callback(res);
		}
	});
}

/**
 * 外部js获取语言包接口
 * 创建时间：2018年12月28日09:17:31
 */
function langApi(data, callback) {
	$.ajax({
		type: 'post',
		url: __URL(APPMAIN + "/index/langapi"),
		dataType: "JSON",
		async: false,
		data: {data: data.toString()},
		success: function (res) {
			if (callback) callback(res);
		}
	});
}

function __URL(url) {
	url = url.replace(SHOPMAIN, '');
	url = url.replace(APPMAIN, 'wap');
	if (url == '' || url == null) {
		return SHOPMAIN;
	} else {
		var str = url.substring(0, 1);
		if (str == '/' || str == "\\") {
			url = url.substring(1, url.length);
		}
		if ($("#niushop_rewrite_model").val() == 1 || $("#niushop_rewrite_model").val() == true) {
			return SHOPMAIN + '/' + url;
		}
		var action_array = url.split('?');
		//检测是否是pathinfo模式
		url_model = $("#niushop_url_model").val();
		if (url_model == 1 || url_model == true) {
			var base_url = SHOPMAIN + '/' + action_array[0];
			var tag = '?';
		} else {
			var base_url = SHOPMAIN + '?s=/' + action_array[0];
			var tag = '&';
		}
		if (action_array[1] != '' && action_array[1] != null) {
			return base_url + tag + action_array[1];
		} else {
			return base_url;
		}
	}
}

//处理图片路径
function __IMG(img_path) {
	var path = "";
	if (img_path != undefined && img_path != "") {
		if (img_path.indexOf("http://") == -1 && img_path.indexOf("https://") == -1) {
			path = UPLOAD + "\/" + img_path;
		} else {
			path = img_path;
		}
	}
	return path;
}

/**
 * 消息弹框
 *
 * @param msg    消息
 * @param duration    显示时长
 * @param url    跳转地址
 */
function toast(msg, url, duration) {
	var type = 'info';
	if (duration == undefined || duration == "") duration = "long";
	if (url) {
		setTimeout(function () {
			location.href = url;
		}, 1000);
	}
	new $.Display({
		display: 'messager',
		autoHide: 1500,
		placement: "center",
		closeButton: false
	}).show({
		content: msg,
		type: type,
	});
}

//时间戳转时间类型
function timeStampTurnTime(timeStamp) {
	if (timeStamp > 0) {
		var date = new Date();
		date.setTime(timeStamp * 1000);
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		m = m < 10 ? ('0' + m) : m;
		var d = date.getDate();
		d = d < 10 ? ('0' + d) : d;
		var h = date.getHours();
		h = h < 10 ? ('0' + h) : h;
		var minute = date.getMinutes();
		var second = date.getSeconds();
		minute = minute < 10 ? ('0' + minute) : minute;
		second = second < 10 ? ('0' + second) : second;
		return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second;
	} else {
		return "";
	}
	
}

/**
 * 遮罩层对象
 * 创建时间：2018年11月15日10:57:27  xxs
 * @param dom    在遮罩层之上的DOM
 * @param callback    点击遮罩层触发回调
 * @constructor
 */
function MaskLayer(dom, callback) {
	this.dom = $(dom);
	this.callback = callback;
	this.created();
}

MaskLayer.prototype = {
	id: "",
	zIndex: 19961213,
	created: function () {
		this.id = "js-" + genNonDuplicate(3);
		var h = '<div class="niu-mask-layer ' + this.id + '" style="display:none;position: fixed;top: 0;right: 0;bottom: 0;left: 0;z-index: ' + this.zIndex + ';background-color: rgba(0,0,0,.6);cursor:pointer"></div>';
		$("body").append(h);
		if (this.callback) {
			var self = this;
			$("body").on("click", "." + this.id, function () {
				self.hide();
				self.callback();
			});
		}
	},
	show: function () {
		this.dom.css("z-index", ++this.zIndex);
		$("." + this.id).show();
		//防止遮罩层之下滑动
		ModalHelper.afterOpen();
	},
	hide: function () {
		this.dom.css("z-index", "");
		$("." + this.id).hide();
		ModalHelper.beforeClose();
	}
};


//解决遮罩层防止穿透问题
var ModalHelper = (function (bodyCls) {
	var scrollTop;
	return {
		afterOpen: function () {
			scrollTop = document.scrollingElement.scrollTop;
			document.body.classList.add(bodyCls);
			document.body.style.top = -scrollTop + 'px';
		},
		beforeClose: function () {
			document.body.classList.remove(bodyCls);
			// scrollTop lost after set position:fixed, restore it back.
			document.scrollingElement.scrollTop = scrollTop;
		}
	};
})('mask-layer-open');


/**
 * 上下拉刷新滚动列表
 * 创建时间：2018年11月24日14:47:25
 * @param id
 * @param load_list
 * @param page_size
 * @returns {MeScroll}
 * @constructor
 */
function ScrollList(id, load_list, page_size) {
	page_size = page_size || 15;
	
	var mescroll = new MeScroll(id, {
		down: {
			auto: false, //是否在初始化完毕之后自动执行下拉回调callback; 默认true
			callback: function () {
				//下拉刷新的回调
				load_list(1, false);
			}
		},
		up: {
			auto: true, //是否在初始化时以上拉加载的方式自动加载第一页数据; 默认false
			isBounce: true, //此处禁止ios回弹
			callback: function (page) {
				//上拉加载的回调 page = {num:1, size:10}; num:当前页 从1开始, size:每页数据条数
				load_list(page.num, true);
			},
			page: {
				num: 0,
				size: page_size
			},
			toTop: { //配置回到顶部按钮图标
				src: WAPPLUGIN + "/mescroll/img/mescroll_to_top.png"
			}
		},
		lazyLoad: {
			use: true, // 是否开启懒加载,默认false
			attr: 'lazy-url' // 标签中网络图的属性名 : <img imgurl='网络图  src='占位图''/>
		}
	});
	return mescroll;
}

function genNonDuplicate(len) {
	return Number(Math.random().toString().substr(3, len) + Date.now()).toString(36);
}

var niushop = {
	share: function (params) {
		if (params == null) params = {};
		api('System.Member.shareContents', params, function (res) {
			var data = res.data;
			wx.config({
				debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
				appId: $("#appId").val(), // 必填，公众号的唯一标识
				timestamp: $("#jsTimesTamp").val(), // 必填，生成签名的时间戳
				nonceStr: $("#jsNonceStr").val(), // 必填，生成签名的随机串
				signature: $("#jsSignature").val(),// 必填，签名，见附录1
				jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
			});
			
			wx.ready(function () {
				var title = data['share_title'];
				var share_contents = data['share_contents'] + '\r\n';
				var share_nick_name = data['share_nick_name'] + '\r\n';
				var desc = share_contents + share_nick_name + "收藏热度：★★★★★";
				var share_url = data['share_url'];
				var img_url = data['share_img'];
				wx.onMenuShareAppMessage({
					title: title,
					desc: desc,
					link: share_url,
					imgUrl: img_url,
					trigger: function (res) {
						//alert('用户点击发送给朋友');
					},
					success: function (res) {
						//alert('已分享');
						api('NsMemberShare.MemberShare.shareReward', {}, function () {
						});
					},
					cancel: function (res) {
						//alert('已取消');
					},
					fail: function (res) {
						//alert(JSON.stringify(res));
					}
				});
				
				// 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
				wx.onMenuShareTimeline({
					title: title,
					link: share_url,
					imgUrl: img_url,
					trigger: function (res) {
						// alert('用户点击分享到朋友圈');
					},
					success: function (res) {
						//alert('已分享');
						api('NsMemberShare.MemberShare.shareReward', {}, function () {
						});
					},
					cancel: function (res) {
						//alert('已取消');
					},
					fail: function (res) {
						// alert(JSON.stringify(res));
					}
				});
				
				// 2.3 监听“分享到QQ”按钮点击、自定义分享内容及分享结果接口
				wx.onMenuShareQQ({
					title: title,
					desc: desc,
					link: share_url,
					imgUrl: img_url,
					trigger: function (res) {
						//alert('用户点击分享到QQ');
					},
					complete: function (res) {
						//alert(JSON.stringify(res));
					},
					success: function (res) {
						//alert('已分享');
						api('NsMemberShare.MemberShare.shareReward', {}, function () {
						});
					},
					cancel: function (res) {
						//alert('已取消');
					},
					fail: function (res) {
						//alert(JSON.stringify(res));
					}
				});
				
				// 2.4 监听“分享到微博”按钮点击、自定义分享内容及分享结果接口
				wx.onMenuShareWeibo({
					title: title,
					desc: desc,
					link: share_url,
					imgUrl: img_url,
					trigger: function (res) {
						//alert('用户点击分享到微博');
					},
					complete: function (res) {
						//alert(JSON.stringify(res));
					},
					success: function (res) {
						//alert('已分享');
						api('NsMemberShare.MemberShare.shareReward', {}, function () {
						});
					},
					cancel: function (res) {
						//alert('已取消');
					},
					fail: function (res) {
						//alert(JSON.stringify(res));
					}
				});
			});
		});
	}
};