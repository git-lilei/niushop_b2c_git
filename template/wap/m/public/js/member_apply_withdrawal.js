function btnSave() {
	var bank_account_id = $("#bank_account_id").val();
	var cash = $.trim($("#money").val());
	var MaxCashAmount = $.trim($("#Amount").text());
	var Minaamountcash = $.trim($("#Minaamountcash").text());
	var IntTimes = $.trim($("#IntTimes").html());
	MaxCashAmount = MaxCashAmount.substr(1);
	if (bank_account_id == null || bank_account_id == "") {
		toast(lang_member_apply_withdrawal.no_withdrawals_account_was_added);
		return;
	}
	var reg = /^\+?[1-9][0-9]*$/;
	if (reg.test(cash)) {
		cash = Number(cash);
		MaxCashAmount = Number(MaxCashAmount);
		Minaamountcash = Number(Minaamountcash);
		if (cash > 0) {
			if (cash <= MaxCashAmount) {
				if (cash >= Minaamountcash) {
					if (cash % parseInt(IntTimes) == 0) {
						api('System.Member.addWithdrawApply', {
							"bank_account_id": bank_account_id,
							"cash": cash
						}, function (res) {
							if (res.data > 0) {
								toast(lang_member_apply_withdrawal.member_submitted_for_review);
								window.location.href = __URL(APPMAIN + "/member/balance");
							} else {
								toast(res.message);
							}
						})
					} else {
						toast(lang_member_apply_withdrawal.member_withdrawals_must_be + IntTimes + lang_member_apply_withdrawal.member_integral_multiple);
						return false;
					}
				} else {
					toast(lang_member_apply_withdrawal.member_withdrawals_must_greater + Minaamountcash);
					return false;
				}
			} else {
				toast(lang_member_apply_withdrawal.member_maximum_amount + MaxCashAmount);
				return false;
			}
		} else {
			toast(lang_member_apply_withdrawal.member_not_present);
			return false;
		}
	} else {
		toast(lang_member_apply_withdrawal.member_amount_not_legal);
		return false;
	}
}