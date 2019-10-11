/**
 * 商品规格
 */
$(function(){
	
	/**
	 * 0 统一规格  1多规格
	 * 规格类型
	 */
	$("input[name='sku_type']").change(function() {
		if ($("input[name='sku_type']:checked").val() == 0) {
			$('.sku_type_1').show();
			$('.sku_type_2').hide();
			
			$('#txtProductCount').removeAttr('disabled');
		}else{
			$('.sku_type_1').hide();
			$('.sku_type_2').show();
			eachInput();
			$('#txtProductCount').attr('disabled', true);
		}
	})
	
	//批量修改
	$(".volume-set-sku-info a").click(function(){
		
		var tag = $(this).attr("data-tag");
		var placeholder = $(this).text();
		$(this).parent().children("a").hide();
		$(this).siblings("input,button").show();
		$(this).siblings("input").attr("placeholder",placeholder).attr("data-tag",tag).focus();
	});
	
	//回车事件
	$(".volume-set-sku-info input").keyup(function(event){
		if(event.keyCode == 13){
			$(".volume-set-sku-info .btn-common").click();
		}
	});
	
	//批量操作，确定按钮
	$(".volume-set-sku-info .btn-common").click(function(){

		var input = $(this).prev();
		var tag = input.attr("data-tag");
		var placeholder = input.attr("placeholder");
		var v = input.val();

		//批量修改规格信息
		var price_regex = /^\d+(.{0,1})\d{0,2}$/;//价格正则表达式
		var stock_regex = /^\d+$/;//库存正则表达式
		var is_update = true;//是否更新
		
		if(v.length>0){
			if(tag == "stock-num"){
				//验证库存
				if(!stock_regex.test(v)){
					showTip(placeholder + "格式错误" ,"warning");
					is_update = false;
					input.focus();
				}
			}else{
				//验证价格输入是否正确
				if(!price_regex.test(v)){
					showTip(placeholder + "格式错误","warning");
					input.focus();
					is_update = false;
				}
			}
			
			if(is_update){
				
				//更新价格、库存
				if(tag == "sku-price"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='sku_price']").val(v);
				}else if(tag == "market-price"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='market_price']").val(v);
				}else if(tag == "cost-price"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='cost_price']").val(v);
				}else if(tag == "stock-num"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='stock_num']").val(v);
				}else if(tag == "volume"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='volume']").val(v);
				}else if(tag == "weight"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='weight']").val(v);
				}
				
				$(".goods-sku-list tbody tr td input").change();
				
				//循环计算商品数据
				eachPrice();
				eachMarketPrice();
				eachCostPrice();
				eachInput();
				
				input.val("");
				$(".volume-set-sku-info .btn-common-cancle").click();
			}
		}else{
			input.focus();
			showTip("请输入" + placeholder,"warning");
		}
		
	});
	
	//批量操作，取消按钮
	$(".volume-set-sku-info .btn-common-cancle").click(function(){
		$(this).parent().children("a").show();
		$(this).parent().children("input,button").hide();
		$(this).parent().children("input").removeAttr("data-tag").removeAttr("placeholder");
	});
	
	/**
	 * 循环处理价格 不让价格为空
	 */
	$('input[name="sku_price"],input[name="market_price"],input[name="cost_price"],input[name="stock_num"],input[name="code"]').live('blur',function() {
		var $this = $(this);
		var reg = /^\d+(.{0,1})\d{0,2}$/;
		if($this.attr("name") == "sku_price" || $this.attr("name") == "market_price" || $this.attr("name") == "cost_price" || $this.attr("name") == "stock_num"){
			if($this.val().length>0){
				if(reg.test($this.val())){
					if ($this.val().replace(/(^\s*)|(\s*$)/g, "") == "") {
						if($this.attr("name") == "stock_num"){
							$this.val("0");
						}else{
							$this.val("0.00");
						}
						$this.parent().find(".help-inline").css("display","block");
					} else {
						num = parseInt($this.val());
						$this.css("border-color", "");
						$this.parent().find(".help-inline").hide();
					}
					switch($this.attr("name")){
					case "sku_price":
						eachPrice();
						break;
					case "market_price":
						eachMarketPrice();
						break;
					case "cost_price":
						eachCostPrice();
						break;
					case "stock_num":
						eachInput();
						break;
					}
				}else{
					if($this.attr("name") == "stock_num"){
						$this.val("0");
					}else{
						$this.val("0.00");
					}
				}
			}else{
				if($this.attr("name") == "stock_num"){
					$this.val("0");
				}else{
					$this.val("0.00");
				}
			}
		}else{
			//商家编码处理
			if($this.val().length>20) $this.val($this.val().substr(0,20));
		}
	});
	
	/**
	 * 离开焦点事件也要进行处理
	 */
	$('input[name="sku_price"],input[name="market_price"],input[name="cost_price"],input[name="stock_num"],input[name="code"]').live("blur",function(){
		$(this).keyup();
	});
	
	/**
	 * 循环 处理库存
	 */
	$('input[name="stock_num"]').live('keyup', function() {
		$stock = $(this);
		if ($stock.val().replace(/(^\s*)|(\s*$)/g, "") == "") {
			$stock.parent().find(".help-inline").show();
		} else {
			$stock.css("border-color", "");
			$stock.parent().find(".help-inline").hide();
		}
		eachInput();
	});
	
	sku_spec_sel_obj = null;
	//打开编辑商品规格弹出框
	edit_sku_popup = function(e){

		$(".edit-sku-popup header h3").text("选择规格");
		$(".edit-sku-popup-mask-layer").fadeIn();
		$(".edit-sku-popup-body").show();
		$(".edit-sku-popup").fadeIn();
		
		sku_spec_sel_obj = $(e).parents('.spec-item');
		if(typeof(sku_spec_sel_obj) != 'undefined'){
			
			spec_id = sku_spec_sel_obj.find('[name="spec_name"]').attr('spec_id');
			t_obj = $(`.original-sku-list li[data-spec-id="${spec_id}"]`).trigger('click');
			sku_spec_sel_obj.find('.spec-value-item').each(function(){
				v = $(this).find('[name="spec_value"]').attr('spec_value_id');
				$(`.edit-sku-popup .sku-value ul li[data-spec-value-id="${v}"]`).addClass('selected');
			})
		}
		
	};
	
	// 选择原始规格 显示对应规格值
	$(".original-sku-list li").live("click",function(){
		
		//判断如果是筛选框则不进行本动作
		if($(this).attr("class") == "goods-type-search-block") return;
		
		var index = $(this).index();
		$('.original-sku-list li:not([data-spec-value-id-array])').removeClass("selected");
		$(this).addClass("selected curr");
		$(this).siblings().removeClass("curr");

		//查看当前选择规格的规格值列表
		$(".edit-sku-popup-body article>div.sku-value .sku-value-list").show().siblings().hide();
		var html = '';
		var spec_value = eval($(this).attr("data-spec-value-json"));
		var spec_name = $(this).attr("data-spec-name");
		var show_type=  $(this).attr("data-show-type");
		var spec_id = $(this).attr("data-spec-id");
		if(spec_value!=null){
			
			var spec_value_id_array = eval($(".sku-value-list ul  li[data-spec-id='" + spec_id + "']").attr("data-spec-value-id-array"));
			$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-value-id]").hide();
			$(".edit-sku-popup-body article>div.sku-display-mode .empty-info").show().nextAll().hide();
			for(var i=0; i<spec_value.length; i++){
				var curr = spec_value[i];
				if($.inArray(parseInt(curr.spec_value_id),spec_value_id_array)>-1){
					//规格值的展示方式
					$(".edit-sku-popup-body article>div.sku-display-mode .empty-info").hide().nextAll().show();
					$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-value-id='" + curr.spec_value_id + "']").show();
					$(".edit-sku-popup-body article>div.sku-display-mode nav ul li label .radio-common input[value='" + show_type + "']").attr("checked","checked").parent().click();
					
					html += '<li class="selected" data-spec-value-id="' + curr.spec_value_id + '" data-spec-id="' + curr.spec_id + '" data-spec-name="' + spec_name + '" data-spec-value-name="' + curr.spec_value_name + '" data-show-type="' + show_type + '" data-spec-value-data="' + curr.spec_value_data + '">' + curr.spec_value_name + '<i class="shut">×</i></li>';
				}else{
					html += '<li data-spec-value-id="' + curr.spec_value_id + '" data-spec-id="' + curr.spec_id + '" data-spec-name="' + spec_name + '" data-spec-value-name="' + curr.spec_value_name + '" data-show-type="' + show_type + '" data-spec-value-data="' + curr.spec_value_data + '">' + curr.spec_value_name + '<i class="shut">×</i></li>';
				}
			}
		}

		if($(this).attr("data-is-update-img") == undefined){
			$(this).attr("data-is-update-img", 0);
		}
		
		 html += '<li class="add-sku-value"><i class="fa fa-plus"></i>添加规格值</li>';
		$(".edit-sku-popup-body article>div.sku-value ul").html(html);

		$(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").arrangeable("", againGenerateSkuJson);
		$(".sku-value-list ul  li.add-sku").css({ "border-color" : "" }).find("input").hide().siblings().show();

		//还原添加规格值，隐藏规格值输入框
		$(".edit-sku-popup-body article .add-sku-value-input").hide().val("");
		$(".edit-sku-popup-body article>div.sku-value label").show();
	});
	
	//选择商品规格弹出框中的规格值
	$(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").live("click",function(){
		
		var spec_id = $(this).attr("data-spec-id");
		var spec_value_id = $(this).attr("data-spec-value-id");
		var spec_value_name = $(this).attr("data-spec-value-name");
		var spec_value_data = $(this).attr("data-spec-value-data");
		var spec_name = $(this).attr("data-spec-name");
		var spec_show_type = parseInt($(this).attr("data-show-type"));
		
		var count = $(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").length;//规格值总数量
		var spec_id_array = $(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-spec-value-id-array");

		// 判断该规格是否存在于当前规格区中
		var spec = $(".sku-value-list ul  li[data-spec-id='" + spec_id + "']");
		var original_spec = $(".original-sku-list li[data-spec-id='" + spec_id + "']");
		
		if(spec_id_array == null) spec_id_array = new Array();
		else spec_id_array = eval(spec_id_array);
		
		if(!$(this).hasClass("selected")){
			//选中
			$(this).addClass("selected");

			var curr_selected_num = $(this).parent("ul").find(".selected").length;
			if(spec.length == 0 && curr_selected_num > 0 && spec_id > 0){
				$(".sku-value-list ul  li[data-spec-id]").removeClass("curr");
				var spec_html = '<li title="'+original_spec.attr("title")+'" data-spec-id="' + spec_id + '" data-spec-value-json="' + StringTransference(original_spec.attr("data-spec-value-json"), {" " : "&nbsp;", "\"" : "&quot;"}) + '" data-spec-name="' + original_spec.attr('data-spec-name') + '" data-show-type="' + original_spec.attr('data-show-type') + '" data-spec-value-length="' + curr_selected_num + '" class="selected curr"><span>' + original_spec.attr('data-spec-name') + '</span><span>[' + curr_selected_num + '/' + curr_selected_num + ']</span></li>';
				$(".sku-value-list ul  .add-sku").before(spec_html);
//				var html = '<li class="add-sku-value"><i class="fa fa-plus"></i>添加规格值</li>';
//				$(".edit-sku-popup-body article>div.sku-value ul li:last-child").after(html);
				$('.sku-value-list ul  li:not(.add-sku)').arrangeable();
			}

			//防止重复
			if($.inArray(parseInt(spec_value_id),spec_id_array) == -1) spec_id_array.push(parseInt(spec_value_id));

			//第一次添加
			if($(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-value-id='" + spec_value_id +"']").length == 0){
				var display_mode_html = '';
				
				display_mode_html = '<li data-spec-value-id="' + spec_value_id + '" data-spec-show-type="1">';
				display_mode_html += '<span>' + spec_value_name + '</span><strong>编辑</strong>';
				display_mode_html += '</li>';
				
				if(spec_show_type == 2){
					display_mode_html += '<li data-spec-value-id="' + spec_value_id + '" data-spec-show-type="2" class="selected">';
				}else{
					display_mode_html += '<li data-spec-value-id="' + spec_value_id + '" data-spec-show-type="2">';
				}
					display_mode_html += '<input type="color" class="input-common-color" data-spec-value-id="' + spec_value_id + '" value="' + (spec_value_data == "" ? "#000000" : spec_value_data) + '">';
				display_mode_html += '</li>';
				
				if(spec_show_type == 3){
					display_mode_html += '<li data-spec-value-id="' + spec_value_id + '" data-spec-show-type="3" class="selected">';
				}else{
					display_mode_html += '<li data-spec-value-id="' + spec_value_id + '" data-spec-show-type="3">';
				}
					display_mode_html += '<div class="upload-btn-common">';
						display_mode_html += '<div>';
							display_mode_html += '<input type="hidden" id="sku_value_' + spec_value_id + '">';
						display_mode_html += '</div>';
						display_mode_html += '<em>添加图片</em>';
						display_mode_html += '<img id="preview_sku_value_' + spec_value_id + '" src="' + STATIC + '/blue/img/upload-common-select.png" data-html="true" data-container="body" data-placement="top" data-trigger="manual" data-original-title="" title="">';
					display_mode_html += '</div>';
				display_mode_html += '</li>';
				
				$(".edit-sku-popup-body article>div.sku-display-mode nav ul li label .radio-common input[value='" + spec_show_type + "']").attr("checked","checked").parent().click();
				$(".edit-sku-popup-body article>div.sku-display-mode>ul").append(display_mode_html);
			}else{
				//显示已存在的规格值 
				$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-value-id='" + spec_value_id +"']").show();
				//更新图片修改值
				var spec_value_obj = $(".edit-sku-popup-body article>div.sku-value ul li[data-spec-value-id='"+spec_value_id+"']");
				if(spec_value_obj.attr("data-show-type") == 3 && spec_value_obj.attr("data-spec-value-data") != ""){
					var update_img_num = parseInt($(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-is-update-img")) + 1; 
					$(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-is-update-img", update_img_num);
				}
			}
		}else{
			//取消
			for(var i=0;i<spec_id_array.length;i++){
				if(spec_id_array[i] == spec_value_id){
					spec_id_array.splice(i,1);
					i--;
				}
			}
			$(this).removeClass("selected");

			$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-value-id='" + spec_value_id +"']").hide();
			//更新图片修改值
			if($(this).attr("data-show-type") == 3 && $(this).attr("data-spec-value-data") != ""){
				var update_img_num = $(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-is-update-img"); 
					update_img_num = update_img_num - 1 <= 0 ? 0 : update_img_num - 1;
					$(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-is-update-img", update_img_num);
			}
		}

		if(spec_id_array.length){
			if(spec_id_array.length == count) $(".edit-sku-popup-body article>div.sku-value label input").attr("checked","checked").parent().addClass("selected");
			else $(".edit-sku-popup-body article>div.sku-value label input").removeAttr("checked").parent().removeClass('selected');
			
			$(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-spec-value-id-array",JSON.stringify(spec_id_array)).find("span:last").text("[" + spec_id_array.length + "/" + count + "]");
			$(".edit-sku-popup-body article>div.sku-display-mode .empty-info").hide().nextAll().show();
		}else{
			$(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").removeAttr("data-spec-value-id-array").find("span:last").text("[" + spec_id_array.length + "/" + count + "]");;
			$(".edit-sku-popup-body article>div.sku-display-mode .empty-info").show().nextAll().hide();
		}
	});
	
	/**
	 * 添加规格值->打开输入框
	 * 创建时间：2018年4月14日11:33:55
	 */
	$(".edit-sku-popup-body article>div.sku-value ul li.add-sku-value").live("click",function(){
		var html = '<i class="fa fa-plus"></i>';
		if($(".edit-sku-popup-body article .add-sku-value-input").is(":visible")){
			$(".edit-sku-popup-body article .add-sku-value-input").hide();
			$(".edit-sku-popup-body article>div.sku-value label").show();
			html += '添加规格值';
		}else{
			$(".edit-sku-popup-body article .add-sku-value-input").show().find("input").focus();
			$(".edit-sku-popup-body article>div.sku-value label").hide();
			html += '取消';
		}
		$(this).html(html);
	});

	/**
	 * 添加规格->输入要添加的规格名称
	 * 修改时间：2018年4月14日11:33:23
	 */
	$(".sku-value-list ul  li.add-sku input").live("keyup",function(event){
		if(event.keyCode == 13){
			
			var $this = $(this);
			var spec_name = $this.val();

			if(spec_name.length==0){
				$(this).focus();
				showTip("请输入新规格名称","warning");
				return;
			}

			if(!vertifyStr(spec_name)){
				showTip("规格名称不能包含特殊字符", "error");
				return;
			}

			var space = new RegExp(" ","g");
				spec_name = spec_name.replace(space, "&nbsp;");
			
			var is_exist = false;
			$(".sku-value-list ul  li").each(function(){
				if($(this).attr("data-spec-name") == spec_name){
					is_exist = true;
					return false;
				}
			});
			
			if(is_exist){
				showTip("规格已存在，请勿重复添加","warning");
				return;
			}
			
			var predefined_id = 0;
			//判断是否重复
			do{
				predefined_id = -(($(".sku-value-list ul  li").length-1) + Math.floor(new Date().getSeconds()) + Math.floor(new Date().getMilliseconds()));
				if($(".sku-value-list ul  li[data-spec-id='" + predefined_id + "']").length==0) break;
				
			}while(true);
			
			var html = "<li data-spec-id="+ predefined_id +" data-spec-value-json='[]' data-spec-name='" + spec_name + "' data-show-type='" + 1 + "'><span>" + spec_name + "</span><span>[" + 0 + "/" + 0 + "]</span></li>";
			$(".sku-value-list ul  .add-sku").before(html);
			$('.sku-value-list ul  li:not(.add-sku)').arrangeable();
			$(".sku-value-list ul  li.add-sku").css({ "border-color" : "" }).find("input").hide().siblings().show();
			$(".sku-value-list ul ").animate({ "scrollTop" : $(".sku-value-list ul ")[0].scrollHeight },600);
			$this.val("");
			$(".sku-value-list ul  li:last").prev().click();
			showTip("添加规格成功","success");
		}
		// 按下esc
		if(event.keyCode == 27){
			$(".sku-value-list ul  li.add-sku").css({ "border-color" : "" }).removeClass('curr').find("input").hide().siblings().show();
			$(".sku-value-list ul  li:last").prev().click();
		}
	});

	//批量选择规格值
	$(".edit-sku-popup-body article>div.sku-value label input").live("click",function(){

		if(!$(this).prop("checked")){
			$(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").addClass("selected").click();
		}else{
			$(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").removeClass("selected").click();
		}
	});

	/**
	 * 添加规格值
	 */
	$(".edit-sku-popup-body article .add-sku-value-input input").live("keyup",function(event){
		if(event.keyCode == 13){
			var selected_spec = $(".edit-sku-popup-body aside .original-sku ul.original-sku-list li.selected.curr");
			var v = $(".edit-sku-popup-body article .add-sku-value-input input").val();
			
			if(v.length==0){
				showTip("请输入规格值名称","warning");
				return;
			}

			if(!vertifyStr(v)){
				showTip("规格值不能包含特殊字符", "error");
				return;
			}

			var space = new RegExp(" ","g");
			v = v.replace(space, "&nbsp;");

			var is_exist = false;
			$(".edit-sku-popup-body article>div.sku-value ul li[data-spec-value-id]").each(function(){
				if($(this).attr("data-spec-value-name") == v){
					is_exist = true;
					return false;
				}
			});
			
			if(is_exist){
				showTip("规格值已存在，请勿重复添加","warning");
				return;
			}
			
			var spec_value = {
				spec_id : selected_spec.attr("data-spec-id"), //规格id
				spec_name : selected_spec.attr("data-spec-name"),//规格名称
				show_type : selected_spec.attr("data-show-type"),//展示方式
				spec_value_name : v, //规格值 
				spec_value_data : "" //附加值,spec_value_data
			};
			
			var predefined_spec_value_id = 0;
			
			//判断是否重复
			do{
				var predefined_spec_value_id = spec_value.spec_id + Math.floor(new Date().getSeconds()) + Math.floor(new Date().getMilliseconds());
				//如果规格已经添到数据库中了，但是规格值还没有进库，需要改成负数
				if(predefined_spec_value_id>0) predefined_spec_value_id = -predefined_spec_value_id;
				if($(".edit-sku-popup-body article>div.sku-value ul li[data-spec-value-id='" + predefined_spec_value_id + "']").length==0)break;
				
			}while(true);

			var html = '<li data-spec-value-id="' + predefined_spec_value_id + '" data-spec-id="' + spec_value.spec_id + '" data-spec-name="' + spec_value.spec_name + '" data-spec-value-name="' + spec_value.spec_value_name + '" data-show-type="' + spec_value.show_type + '" data-spec-value-data="">' + spec_value.spec_value_name + '<i class="shut">×</i></li>';
			$(".edit-sku-popup-body article>div.sku-value ul .add-sku-value").before(html);
			$(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").arrangeable("", againGenerateSkuJson);
			
			//更新左侧对应的规格，需要修改总数量和data-spec-value-json对象
			var spec_value_json = eval(selected_spec.attr("data-spec-value-json"));
			var spec_value_id_array = eval(selected_spec.attr("data-spec-value-id-array"));
			var selected_count = 0;
			if(spec_value_id_array!=null) selected_count = spec_value_id_array.length;
			spec_value_json.push({
//				create_time : Math.floor(new Date().getTime()/1000),
//				sort : ($(".edit-sku-popup-body article>div.sku-value ul li").length-1),
				spec_id : spec_value.spec_id,
				spec_value_data : "",
				spec_value_id : predefined_spec_value_id,
				spec_value_name : spec_value.spec_value_name
			});
//			selected_spec.children("span:last").text("[" + selected_count + "/" + spec_value_json.length + "]");
			selected_spec.attr("data-spec-value-json",JSON.stringify(spec_value_json));
			selected_spec.attr("data-spec-value-length",spec_value_json.length);
			
			//还原输入框，防止重复添加
			$(".edit-sku-popup-body article .add-sku-value-input input").val("");
		}
	});
	
	/**
	 * 编辑商品规格值
	 * 创建时间：2018年4月14日09:21:25
	 */
	$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-show-type='1'] strong").live("click",function(){
		
		var curr_li = $(this).parent();
		
		if(curr_li.children("input").length==0){
			var html = '<input type="text" class="input-common middle" placeholder="规格值名称(回车保存)" value="' + $(this).prev().text() + '" />';
			curr_li.append(html);
		}
		if($(this).text() == "编辑"){
			$(this).text("取消").prev().hide().next().next().show().focus();
		}else{
			$(this).text("编辑").prev().show().next().next().hide();
		}
	});
	
	/**
	 * 输入要编辑的商品规格值
	 * 创建时间：2018年4月14日09:37:062
	 */
	$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-show-type='1'] input").live("keyup",function(event){
		if(event.keyCode == 13){
			var v = $(this).val();
			var curr_value_li = $(this).parent();
			var spec_value_id = curr_value_li.attr("data-spec-value-id");
			
			if(v == ""){
				showTip("请输入规格值","warning");
				$(this).focus();
				return;
			}

			if(!vertifyStr(v)){
				showTip("规格值不能包含特殊字符", "error");
				return;
			}

			var space = new RegExp(" ","g");
			v = v.replace(space, "&nbsp;");
			
			var is_exist = false;
			$(".edit-sku-popup-body article>div.sku-value ul li[data-spec-value-id]").each(function(){
				//排除自己的规格值
				if($(this).attr("data-spec-value-id") != spec_value_id){
					if($(this).attr("data-spec-value-name") == v){
						is_exist = true;
						return false;
					}
				}
			});

			if(is_exist){
				showTip("规格值已存在，请修改名称","warning");
				return;
			}
			
			var curr_li = $(".sku-value-list ul  li.selected.curr");
			var spec_value = eval(curr_li.attr("data-spec-value-json"));
			for(var i=0;i<spec_value.length;i++){
				if(spec_value[i].spec_value_id == spec_value_id){
					spec_value[i].spec_value_name = v;
					break;
				}
			}
			
			curr_li.attr("data-spec-value-json",JSON.stringify(spec_value)).click();
			
			$(this).parent().children("span").text(v);
			$(this).hide().prev().text("编辑").prev().show();
		}
	});
	
	//规格生成
	sku_popup_spec_generate = function(){
		
		get_obj = $('.original-sku-list li.selected,curr');
		set_obj = sku_spec_sel_obj.find('[name="spec_name"]'); 
		set_obj.val(get_obj.attr('data-spec-name'));
		set_obj.attr('spec_id', get_obj.attr('data-spec-id'));
		
		let sel_value_arr = new Array();
		sku_spec_sel_obj.find('.spec-content').html('');
		spec_show_type = sku_spec_sel_obj.find('[name="spec_value_subsidiary"]:checked').val();
		$('.sku-value-list ul li.selected').each(function(){
			
			v = $(this).attr('data-spec-value-name');
			id = $(this).attr('data-spec-value-id');
			sku_spec_sel_obj.find('.spec-value-item')
			h = `<div class="spec-value-item" title ="${v}">`;
			if(spec_show_type == 2){
		 		h += `<div class="value-item-affiliate upload-btn-common"> <div class="affiliate_img"><img src="${ADMINIMG}/spec_value_item_affiliate.png" alt=""></div></div>`;
		 	}else if(spec_show_type == 3){
		 		h += `<div class ="value-item-affiliate"><input type="color" class="input-common-color" value="#000000"></div>`;
		 	}
			h += `<div class="input_div"><span>${v}</span><input type="text" value = "${v}" name = "spec_value"  spec_value_id = "${id}"/></div>
			 		<i onclick = "spec_del(this, 'spec-value-item')" sku-data-generate>×</i>
			 	</div>`;
			 	sku_spec_sel_obj.find('.spec-content').append(h);
		})
		
		//规格表格生成
		sku_table_generate();
		
		//关闭弹出框
		$(".edit-sku-popup footer .btn-bottom").click();
	}
	
	//关闭商品规格弹出框
	$(".edit-sku-popup>header>span,.edit-sku-popup footer .btn-bottom").click(function(){
		$(".edit-sku-popup-mask-layer").fadeOut();
		$(".edit-sku-popup").fadeOut();
	});
	
	//规格值图片上传
	$(".value-item-affiliate.upload-btn-common").live('click', function(){
		
		var spec_id = 0;
		var spec_value_id = $(this).parents('.spec-value-item').find('[name="spec_value"]').attr('spec_value_id');
		OpenPricureDialog("PopPicure", ADMINMAIN, 1, 3, 0 , spec_value_id, "goods_sku");
	})
	
	//sku图片上传
	$(".ant-upload.upload-btn-common").live('click', function(){
		
		var spec_id = 0;
		var sku_id = $(this).parents('tr').attr('skuid');
		OpenPricureDialog("PopPicure", ADMINMAIN, 10, 4, 0 , "'"+sku_id+"'", "goods_sku");
	})

	$('.attribute-item .item-name').click(function(){

        var original_sku_obj = $(this).parent().find('.original-sku-list');
		if(original_sku_obj.is(':hidden')){
            original_sku_obj.show();
            $(this).children('span').removeClass('transform');
		}else{
            original_sku_obj.hide();
            $(this).children('span').addClass('transform');
		}
	})
})

/**
 * 修改商品时 更新$specObj,并编辑页面结构
 */
function updateSpecObjData(){
	
	$specObj = sku_spec_list();
	$(".sku-picture-dl").show();
	$(".sku-picture-dl-box").hide();
	var sel_spec_id = $('.sku-picture-span.sku-picture-active').attr('spec_id');
	$(".sku-picture-div").html('');
	for(var i = 0 ; i <$specObj.length; i++ ){
		
		//编辑是显示所选的规格按钮
		var html ='<span class="sku-picture-span" spec_id = "'+ $specObj[i]["spec_id"] +'">'+  $specObj[i]["spec_name"] +'</span>';
		$(".sku-picture-div").append(html);
	}
	
	$('.sku-picture-span[spec_id="'+sel_spec_id+'"]').trigger('click');
}

function againGenerateSkuJson(event){	
	var spec_id = $(event).attr("data-spec-id");
	var spec_value_arr = new Array();
	$(".sku-value-list ul li[data-spec-id='" + spec_id + "'].selected").each(function(){
		var spec_value_id = $(this).attr("data-spec-value-id"),
			spec_value_name = $(this).attr("data-spec-value-name"),
			spec_value_data = $(this).attr("data-spec-value-data");
		spec_value_arr.push({"spec_id" : spec_id,"spec_value_data" :spec_value_data, "spec_value_data" : spec_value_data, "spec_value_name" : spec_value_name, "spec_value_id" : spec_value_id});	
	})
	$(".sku-value-list ul  li[data-spec-id='" + spec_id + "']").attr("data-spec-value-json", JSON.stringify(spec_value_arr));
}

//验证字符串不能含有特殊字符
function vertifyStr(str){
	var regEn = /[`"'[\]\\]/im;
    
	if(regEn.test(str)) {
	    return false;
	}else{
		return true;
	}
}

/**
 * input  只能输入数字
 */
function inputKeyUpNumberValue(event){
	if(event.value.length==1){
		event.value=event.value.replace(/[^0-9]/g,'');
	}else{
		event.value=event.value.replace(/\D/g,'');
	}
}

/**
 * 循环价格
 */
function eachPrice() {
	var $price = 0;
	$.each($('input[name="sku_price"]'), function(i, item) {
		var $this = $(item);
		var num = $this.val();
		var numint = parseFloat(num);
		var priceint = parseFloat($price);
	
		if ($price == 0 || numint < priceint) $price = num;
	});
	$("#txtProductSalePrice").val($price);
}

/**
 * 循环市场价 2016年12月2日 11:55:30
 */
function eachMarketPrice() {
	var $price = 0;
	$.each($('input[name="market_price"]'), function(i, item) {
		var $this = $(item);
		var num = $this.val();
		var numint = parseFloat(num);
		var priceint = parseFloat($price);
		if ($price == 0 || numint < priceint) $price = num;
	});
	$("#txtProductMarketPrice").val($price);
}

/**
 * 循环成本价 2016年12月2日 12:14:27
 */
function eachCostPrice() {
	var $price = 0;
	$.each($('input[name="cost_price"]'), function(i, item) {
		var $this = $(item);
		var num = $this.val();
		var numint = parseFloat(num);
		var priceint = parseFloat($price);
		if ($price == 0 || numint < priceint) $price = num;
	});
	$("#txtProductCostPrice").val($price);
}

/**
 * 循环库存
 */
function eachInput() {
	var $stockTotal = 0;
	$.each($('input[name="stock_num"]'), function(i, item) {
		var $this = $(item);
		var num = 0;
		num = parseInt($this.val());
		$stockTotal = $stockTotal + num;
	});
	$("#txtProductCount").val($stockTotal);
}

/**
 * 压缩包文件上传
 */
function zipFileUpload(event){
	
	var fileid = $(event).attr("id");
	var data = {};
	hidden_obj = $(event).parents('.upload-btn-common').children('[name="extend_1"]');
	data.file_path = "download_goods";
	url = __URL(ADMINMAIN + '/goods/uploadcompressedfile');
	uploadFile({
		url: url,
		fileId: fileid,
		data: data,
		callBack: function (res) {
			if(res.code){
				hidden_obj.val(res.data.path);
				showTip(res.message,"success");
			}else{
				showTip(res.message,"error");
			}
		}
	});
}