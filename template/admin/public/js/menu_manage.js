$(function(){
	init();
})

function init(){
	$('.draggable-element').arrangeable({}, function(){
		var data = [];
		$('.menu-list li:not(".add")').each(function(index, el) {
			data.push({id : $(el).attr('data-id'), sort : index + 1})
		});
		$.ajax({
			type : 'post',
			data : {value : JSON.stringify(data)},
			url : __URL(ADMINMAIN + '/config/entranceSortChange'),
			success : function(){
			}
		})
	});
	// 禁止图片被拖走
	$('img').on('mousedown',function (e) {
	    e.preventDefault()
	})
}

var vue = new Vue({
	el: '#app',
  	data: {
		lists : value,
		currEl : {},
		isExitsNewAdd : false	
	},
	methods : {
		selectLi : function(el, index){
			this.currEl = el;
			this.currEl.index = index;
		},
		newadd : function(){
			var item = { icon : 'upload/default/wap_nav/default-icon.png', id : 0, sort : (this.lists[this.lists.length - 1].sort + 1), status : 1, title : '菜单名称', type : 1, url : '', index : this.lists.length};
			this.lists.push(item);
			this.currEl = item;
			this.isExitsNewAdd = true;
		},
		deleteMemu : function(){
			if(this.currEl.id == 0){
				this.lists.splice(this.currEl.index, 1);
				this.currEl = {};
				this.isExitsNewAdd = false;
			}else{
				var self = this;
				$.ajax({
					type : 'post',
					url : __URL(ADMINMAIN + '/config/deleteWapEntrance'),
					data : {id : self.currEl.id},
					async : false,
					success : function(res){
						if (res.code > 0) {
							self.lists.splice(self.currEl.index, 1);
							self.currEl = {};
							showTip('删除成功', 'success');
						} else {
							showTip(res.message, 'error');
						}
					}
				})
			}
		},
		save : function(){
			if(this.currEl.title.search(/[\S]+/)) { showTip('请输入菜单名称', 'warning'); return; }
			if(this.currEl.url.search(/[\S]+/)) { showTip('请输入跳转链接', 'warning'); return; }
			if(this.currEl.url.search(/[\S]+/)) { showTip('请设置菜单图标', 'warning'); return; }	
			var self = this;
			$.ajax({
				type : 'post',
				url : __URL(ADMINMAIN + '/config/editWapEntrance'),
				data : { value : JSON.stringify(self.currEl)},
				async : false,
				success : function(res){
					if (res.code > 0) {
						if(self.currEl.id == 0){
							self.currEl.id = res.code;
							self.isExitsNewAdd = false;
						}
						self.lists[self.currEl.index] = self.currEl;
						self.currEl = {};
						showTip('保存成功', 'success');
					} else {
						showTip(res.message, 'error');
					}
					init();
				}
			})	
		}
	}
})

function imgUpload(event) {
	var fileid = $(event).attr("id");
	var id = $(event).next().attr("id");
	var data = { 'file_path' : "config" };
	uploadFile({
		url: __URL(ADMINMAIN + '/config/uploadimage'),
		fileId: fileid,
		data : data,
		callBack: function (res) {
			if(res.code){
				vue.currEl.icon = res.data.path;
				$("#" + id).val(res.data.path);
				$("#text_" + id).val(res.data.path);
				$("#preview_"+ id).attr("data-src",__IMG(res.data.path));
				showTip(res.message,"success");
			}else{
				showTip(res.message,"error");
			}
		}
	});
}