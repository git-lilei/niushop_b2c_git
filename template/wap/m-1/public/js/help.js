function showChild(obj, id) {
	$(".custom-tag-list-side-menu li a").removeClass("selected ns-text-color ns-border-color");
	$(obj).addClass("selected ns-text-color ns-border-color");
	$(".two-list-menu").css({'top':$(obj).position().top+'px'}).show();
	$("#grouGoods_listmask").show();
	$("#two_menu li[pid]").hide();
	$("#two_menu li[pid='" + id + "']").show();
}

$('.custom-tag-list .mask').click(function () {
	$(this).hide();
	$('.two-list-menu').hide();
});