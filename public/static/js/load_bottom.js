/**
 *  功能描述：加载底部的版权信息
 */
$(function () {
	api('System.Config.copyRight', {}, function (res) {
		if (res.data) {
			var data = res.data;
			var is_load = data["is_load"];
			var bottom_info = data["bottom_info"];
			var copyright_meta = "";
			$("#copyright_logo").attr("src", STATIC + data["default_logo"]);
			$("#copyright_companyname").attr("href", "http://www.niushop.com.cn").html("山西牛酷信息科技有限公司&nbsp;提供技术支持");
			$("#copyright_desc").html("Copyright © 2015-2019 NiuShop开源商城&nbsp;版权所有 保留一切权利");
			if (is_load > 0) {
				$("#copyright_logo").attr("src", __IMG(bottom_info["copyright_logo"]));
				if (bottom_info["copyright_link"] != "") {
					$("#copyright_companyname").attr("href", bottom_info["copyright_link"]);
				}
				if (bottom_info["copyright_companyname"] != "") {
					$("#copyright_companyname").html(bottom_info["copyright_companyname"]);
				}
				if (bottom_info["copyright_desc"] != "") {
					$("#copyright_desc").html(bottom_info["copyright_desc"]);
				}
				$("#login_copyright").hide();
				$("#rigister_copyright").hide();
			}
			
			//备案信息
			if (bottom_info["copyright_meta"] != "" && bottom_info["copyright_meta"] != null) {
				copyright_meta = "<a href='http://www.beian.miit.gov.cn' target='_blank' style='text-decoration: none;'>备案号：" + bottom_info["copyright_meta"] + '</a>';
			}
			
			$("#copyright_meta").html(copyright_meta);
			
			//网站公安备案信息
			if (bottom_info["web_gov_record_url"] != undefined) {
				if (bottom_info["web_gov_record_url"].length > 0) {
					$("#web_gov_record_wap").find("a").attr("href", bottom_info["web_gov_record_url"]);
				}
			}
			if (bottom_info["web_gov_record"] != undefined) {
				if (bottom_info["web_gov_record"].length > 0) {
					$("#web_gov_record_wap").find("span").text(bottom_info["web_gov_record"]);
					$("#web_gov_record_wap").show();
				} else {
					$("#web_gov_record_wap").hide();
				}
			}
			
			$("#copyright-main").show();
		}
	});
	
	//eval(function(p,a,c,k,e,d){e=function(c){return(c<a?"":e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)d[e(c)]=k[c]||e(c);k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1;};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p;}('1 5=f.5;1 4=q("4");8(4==r){1 b="S://E.F.C.D/I/J/G";$.H({x:"w",b:b,z:\'g\',d:{"y":5},g:\'A\',B:a(d){8(d["U"]>0){k("4",5)}}})}a k(6,l){1 m=Y;1 9=s X();9.R(9.V()+m*M*n*n*K);f.o=6+"="+h(l)+";N="+9.Q()}a h(7){1 3="";P(1 i=0;i<7.O;i++){8(3=="")3=7.j(i).t(p);v 3+=","+7.j(i).t(p)}e 3}a q(6){1 c,u=s L("(^| )"+6+"=([^;]*)(;|$)");8(c=f.o.W(u))e T(c[2]);v e r}',61,61,'|var||val|_0|domain|name|str|if|exp|function|url|arr|data|return|document|jsonp|stringToHex||charCodeAt|setCookie|value|Days|60|cookie|16|getCookie|null|new|toString|reg|else|get|type|web_url|dataType|callback|success|com|cn|www|niushop|addUserWebUrl|ajax|api|member|1000|RegExp|24|expires|length|for|toGMTString|setTime|http|unescape|code|getTime|match|Date|30'.split('|'),0,{}))
	
});