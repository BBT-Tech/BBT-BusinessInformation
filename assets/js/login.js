$("form").submit(function() {
	$.post(this.action, $(this).serialize())
	.done(function(d) {
		if (d.code)
			return alert(d.errMsg);
		if (d.username == "root")
			$.cookie("user_type", "root");
		else if(d.is_minister == 1)
			$.cookie("user_type", "minister");
		else
			$.cookie("user_type", "user");
		location.href = "./";
	})
	.fail(function() {
		alert("服务器错误，请联系管理员");
	});
});