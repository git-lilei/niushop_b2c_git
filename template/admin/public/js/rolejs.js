function roleClose() {
    popupClose('gray-add-role');
}
function btn() {
	$("#btn").attr("disabled",true);
    sendDatas();
    $("#addSubmit").submit();
}
$(function () {
    $("#addrole").click(function () {
    	$('#add_user').modal(show);
        //popupOperate("gray-add-role", "用户组设置", "gray-add-role");
        $("[name = add_per]:checkbox").removeProp('checked');
    })
});
// 点击提交时将选中的数据提交到后台
function sendDatas() {
    // 方案
    // ①：要确定是哪个 title
    // ②：取到当前 son 的id
    // ③:往后台传递的数据为 title+ids
    // 截取后是0_8 就是平台  是9_77 就是其他
    var SendDatas = "";
    var checks=$("input[name=add_per]:checked");
    
    $("input[name=add_per]:checked").each(function(){
    	
    	var strCheckID = $(this).attr("id");
        if(strCheckID!='Add01')
        {	
            SendDatas += strCheckID + ",";
        }
    })
    SendDatas=SendDatas.substring(0,SendDatas.length-1);
        // 并放到隐藏域中
        $("#sendCheckDatas").val(SendDatas);   
        add_RoleManage();
}
// 注册"所有"复选框点击的时候
function AllCheckBoxClick(event) {
    event = event ? event : window.event;
    var eventSrc = event.srcElement ? event.srcElement : event.target;
    // 当选中的是title
    if ($(eventSrc).attr("dir") == "title") {
        // 控制旗下的所有复选框
        // 选中旗下所有的checkBox
        if ($(eventSrc).attr("checked") == "checked") {

        	$(eventSrc).parents("li").find('.mod-table-main').find("input[type=checkbox]").prop('checked', true);
            $(eventSrc).parents('li').find('.mod-table-main').find("input[type=checkbox]").parent().addClass('selected');
        } else {
        	$(eventSrc).parents("li").find('.mod-table-main').find("input[type=checkbox]").removeProp('checked');
            $(eventSrc).parents('li').find('.mod-table-main').find("input[type=checkbox]").parent().removeClass('selected');
        }
    }
    // 当选中的是parent
    if ($(eventSrc).attr("dir") == "parent") {
        // 选中旗下所有的checkBox
        if ($(eventSrc).attr("checked") == "checked") {
        	$(eventSrc).parents("tr").find('td:last-child').find("input[type=checkbox]").prop('checked', true);
        	$(eventSrc).parents("tr").find('td:last-child').find("input[type=checkbox]").parent().addClass('selected');
        } else {
        	$(eventSrc).parents("tr").find('td:last-child').find("input[type=checkbox]").removeProp('checked');
        	$(eventSrc).parents("tr").find('td:last-child').find("input[type=checkbox]").parent().removeClass('selected');
        }
    }
    // 当选中的是son
    if ($(eventSrc).attr("dir") == "son") {
    	if ($(eventSrc).attr("checked") == "checked") {
    		
            $(eventSrc).parents('tr.tr-Current').find("[dir='parent']").attr('checked',true).parent().addClass('selected');
            
            $(eventSrc).parents(".second").find(':not(:first-child)').find("input[type=checkbox]").prop('checked', true);
        	$(eventSrc).parents(".second").find(':not(:first-child)').find("input[type=checkbox]").parent().addClass('selected');
        } else {
        	
            $("input[type=checkbox]", $(eventSrc).parents("ul.second")[0]).not($(eventSrc)).removeProp('checked');
            $(eventSrc).parents('ul.second').find("input[type=checkbox]").parent().removeClass('selected');
            var two_obj = $(eventSrc).parents('tr.tr-Current').find("[dir='son']:checked");
            
            if(two_obj.length == 0){
            	$(eventSrc).parents('tr.tr-Current').find("[dir='parent']").attr('checked',false).parent().removeClass('selected');
            }
            var one_obj = $(eventSrc).parents('tbody').find("[dir='parent']");
            if(one_obj.length == 0){
            	$(eventSrc).parents('li').find("[dir='title']").attr('checked',false).parent().removeClass('selected');
            }
        }
    	// 选中旗下所有的checkBox
        
    }
    if ($(eventSrc).attr("dir") == "sonson") {   	
    	if ($(eventSrc).attr("checked") == "checked") {
    		
    		$(eventSrc).parents('ul.second').find("[dir='son']").prop("checked",true);
    		$(eventSrc).parents('tr.tr-Current').find("[dir='parent']").prop("checked",true);
    		//为二级添加选中状态
    		$(eventSrc).parents('ul.second').find("[dir='son']").parent().addClass('selected');
    		//为一级添加选中状态
    		$(eventSrc).parents('tr.tr-Current').find("[dir='parent']").parent().addClass('selected');
        } else {
        	//如果三级数量归零，则相应二级去除选中状态
        	var parentObj=$(eventSrc).parents('ul.second').find("[dir='sonson']:checked");
        	var num = parentObj.length;
        	if(num ==0){
        		$(eventSrc).parents('ul.second').find("[dir='son']").prop("checked",false);
        		$(eventSrc).parents('ul.second').find("[dir='son']").parent().removeClass('selected');
        	}
        	//如果二级数量归零，则相应一级去除选中状态
        	var parentsObj=$(eventSrc).parents('tr.tr-Current').find("[dir='son']:checked");
        	var nums = parentsObj.length;
        	if(nums ==0){
        		$(eventSrc).parents('tr.tr-Current').find("[dir='parent']").prop("checked",false);
        		$(eventSrc).parents('tr.tr-Current').find("[dir='parent']").parent().removeClass('selected');
        	}
        	
        }
    	// 选中旗下所有的checkBox
        
    }
}
//-------------------------------------------------------------------修改----------------------------------------------------------

// 点击提交时将选中的数据提交到后台
function EditsendDatas() {
    // 方案
    // ①：要确定是哪个 title
    // ②：取到当前 son 的id
    // ③:往后台传递的数据为 title+ids
    // 截取后是0_8 就是平台  是9_77 就是其他
    var SendDatas = "";
    var checks = $("input[name=permiss]");
    // 遍历所有的checkbox
    for (var i = 0; i < checks.length; i++) {
        // 首先必须是选中的
        if ($(checks[i]).attr("checked") == "checked") {
            // 里面的字符必须包含 ‘|’的
        	
            var strCheckID = $(checks[i]).attr("id");
            if(strCheckID!='Edit01')
            {	
                SendDatas += strCheckID + ",";
            }
        }
    }
    SendDatas=SendDatas.substring(0,SendDatas.length-1);
        // 并放到隐藏域中
        $("#EditsendCheckDatas").val(SendDatas);
        update_RoleManage();
}
// 点击取消的时候
function btnCancel() {
    popupClose('gray-edit-role');
}
// 注册"所有"复选框点击的时候
function EditAllCheckBoxClick(event) {
    event = event ? event : window.event;
    var eventSrc = event.srcElement ? event.srcElement : event.target;
    // 当选中的是title
    if ($(eventSrc).attr("dir") == "top") {
        // 控制旗下的所有复选框
        // 选中旗下所有的checkBox
        if ($(eventSrc).attr("checked") == "checked") {
            $("input[type=checkbox]", $(eventSrc).parent("li")).not($(eventSrc)).prop('checked', true);
        } else {
            $("input[type=checkbox]", $(eventSrc).parent("li")).not($(eventSrc)).removeProp('checked');
        }

    }
    // 当选中的是parent
    if ($(eventSrc).attr("dir") == "parent") {
        // 选中旗下所有的checkBox
        if ($(eventSrc).attr("checked") == "checked") {
            $("input[type=checkbox]", $(eventSrc).parents("tr")[0]).not($(eventSrc)).prop('checked', true);
        } else {
            $("input[type=checkbox]", $(eventSrc).parents("tr")[0]).not($(eventSrc)).removeProp('checked');
        }
    }
    // 当选中的是son
    if ($(eventSrc).attr("dir") == "son") {
        //alert('son');
    }
    if ($(eventSrc).attr("dir") == "sonson") {   	
    	if ($(eventSrc).attr("checked") == "checked") {
    		$(eventSrc).parent().parent().parent().parent().find("[dir='son']").prop("checked",true);
    		$(eventSrc).parent().parent().parent().parent().parent().parent().find("[dir='parent']").prop("checked",true);
           // $("input[type=checkbox]", $(eventSrc).parents("ul.second")[0]).not($(eventSrc)).prop('checked', true);
        }else {
        	var parentObj=$(eventSrc).parent().parent().parent().parent().find("[dir='sonson']:checked");
        	var parentsObj=$(eventSrc).parent().parent().parent().parent().parent().parent().find("[dir='parent']:checked");
        	var num = parentObj.length;
        	var nums = parentsObj.length;
        	if(num ==0){
        		$(eventSrc).parent().parent().parent().parent().find("[dir='son']").prop("checked",false);
        	}
        	if(nums ==0){
        		$(eventSrc).parent().parent().parent().parent().parent().parent().find("[dir='parent']").prop("checked",false);
        	}
        	
        	//$(eventSrc).parent().parent().parent().find("[dir='son']").prop("checked",true);
            //$("input[type=checkbox]", $(eventSrc).parents("ul.second")[0]).not($(eventSrc)).removeProp('checked');
        }
    }
}
function Editbtn() {
	EditsendDatas();
    $("#EditSubmit").submit();
}