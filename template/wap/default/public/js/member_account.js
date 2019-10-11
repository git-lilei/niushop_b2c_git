function checkAccount(id) {
	api("System.Member.modifyAccountDefault", {"id": id}, function (data) {
		var res = data['data'];
		toast(data.message);
		if (res > 0) {
			if (flag == 0) {
				window.location.href = __URL(APPMAIN + "/member/applywithdrawal");
			}
			if (flag == 2) {
				window.location.href = __URL(APPMAIN + "/distribution/towithdraw");
			}
		}
	})
}

function account_delete(id) {
	api("System.Member.deleteAccount", {"id": id}, function (data) {
		var res = data['data'];
		toast(res.message);
		if (res == 1) {
			window.location.reload();
		}
	})
}