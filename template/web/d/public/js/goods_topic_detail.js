/**
 * 计算每次偏移值
 */
$(function(){
	var d_li=$('.pinpaijngxuan_pro_title li');
	var li_count=d_li.length;
	var d_width=d_li.width();
	$('.brand-showcase-list>ul').css('width',li_count*d_width+'px');
	$('.focus-next').click(function(){
		var this_left=$('.brand-showcase-list>ul').css('left');
		this_left=this_left=='auto' ? 0: this_left;
		var width_len=(li_count-5)*d_width;
		if(this_left!='0'){
			this_left=this_left.substr(1,this_left.length-3);
		}
		if(this_left>=width_len)return false;
		var left=Number(this_left)+Number(d_width);
		$('.brand-showcase-list>ul').css('left','-'+left+'px');
	})
	$('.focus-prev').click(function(){
		var this_left=$('.brand-showcase-list>ul').css('left');
		this_left=this_left=='auto' ? 0: this_left;
		if(this_left!='0'){
			this_left=this_left.substr(1,this_left.length-3);

		}
		var left=Number(this_left)-Number(d_width);
		$('.brand-showcase-list>ul').css('left','-'+left+'px');
	})
});