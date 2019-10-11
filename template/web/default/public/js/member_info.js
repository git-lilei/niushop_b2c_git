var uploader;
var curr_file;

// 修改用户基础信息
function updateBasicsInfo() {
	if (verify()) {
		var info_data = {
			user_name: $('[name="nick_name"]').val(),
			user_qq: $('[name="user_qq"]').val(),
			real_name: $('[name="real_name"]').val(),
			sex: $('[name="sex"]:checked').val(),
			birthday: $('[name="birthday"]').val(),
			location: $('[name="location"]').val()
		};
		api('System.Member.updateMemberInformation', info_data, function (res) {
			if (res.data == 1) {
				$.ajax({
					url: __URL(SHOPMAIN + '/member/updateMemberInfo'),
					type: 'POST',
					success: function (res) {
						location.reload();
					}
				})
			}
		})
	}
}

// 验证
function verify() {
	if ($("input[name=nick_name]").val().search(/[\S]+/)) {
		show('请输入昵称');
		return false;
	}
	if ($("input[name=real_name]").val().search(/[\S]+/)) {
		show('请输入真实姓名');
		return false;
	}
	return true;
}

$(function () {
	
	uploader = WebUploader.create({
		auto: false,
		// 文件接收服务端。
		server: __URL(SHOPMAIN + '/member/uploadImage'),
		
		// 选择文件的按钮。可选。
		// 内部根据当前运行是创建，可能是input元素，也可能是flash.
		pick: '.head-img',
		
		fileNumLimit: 1,
		
		// 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
		resize: false,
		
		// 只允许选择图片文件。
		accept: {
			title: 'Images',
			extensions: 'gif,jpg,jpeg,bmp,png',
			mimeTypes: 'image/*'
		},
		
		formData: {
			param: JSON.stringify({
				// app_key :APP_KEY
			})
		},
		
		thumb: {
			width: 300,
			height: 300,
			crop: false
		}
		
	});
	
	// 当有文件被添加进队列的时候
	uploader.on('fileQueued', function (file) {
		curr_file = file;
		uploader.makeThumb(file, function (error, src) {
			if (error) {
				$("#headimg").replaceWith('<span>不能预览</span>');
				return;
			}
			$("#headimg").attr('src', src);
			$('.js-save').removeClass('upload-save');
		});
	});
	
	uploader.on('uploadSuccess', function (file, res) {
		api('System.Member.modifyFace', {"user_headimg": res.data}, function (res) {
			//删除文件
			if (curr_file) uploader.removeFile(curr_file);
			if (res.data == 1) {
				show('保存成功');
				$.ajax({
					url: __URL(SHOPMAIN + '/member/updateMemberInfo'),
					type: 'POST',
					success: function (res) {
					}
				})
			} else {
				show(res.message);
			}
		});
	});
	
	$(".js-save").click(function () {
		if (uploader.getFiles().length) uploader.upload();
		else location.reload();
	});
});