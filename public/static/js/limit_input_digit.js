//限制文本框只能输入数字或浮点数  例:<input type="text" id="input1" name="input1" onkeyup="javascript:CheckInputIntFloat(this);" />
function CheckInputIntFloat(oInput) {
	if ('' != oInput.value.replace(/\d{1,}\.{0,1}\d{0,}/, '')) {
		oInput.value = oInput.value.match(/\d{1,}\.{0,1}\d{0,}/) == null ? '' : oInput.value.match(/\d{1,}\.{0,1}\d{0,}/);
	}
}

//限制文本框只能输入数字或浮点数 (保留两位小数) 例:<input type="text" id="input1" name="input1" onkeyup="CheckInputIntFloatToTwo(this);" />
function CheckInputIntFloatToTwo(obj) {
	obj.value = obj.value.replace(/[^\d.]/g, "");
	obj.value = obj.value.replace(/^\./g, "");
	obj.value = obj.value.replace(/\.{2,}/g, ".");
	obj.value = obj.value.replace(".", "$#$").replace(/\./g, "").replace("$#$", ".");
	obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/, '$1$2.$3');
}