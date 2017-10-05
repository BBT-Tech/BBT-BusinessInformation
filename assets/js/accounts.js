;var Accounts = (function() {
	var user_type = $.cookie("user_type");
	if (!user_type)
		return location.href = "./login.html";

	var data_table_map = (function() {
		var tmp = [ "type", "username", "name", "update_time", "register_time" ];
		var r = {};
		for (var i=0;i<tmp.length;++i) {
			r[tmp[i]] = i;
			r[i] = tmp[i];
		}
		return r;
	})();

	$.get("./assets/API/accounts.php")
	.done(function(d) {
		if (d.code == 233)
			return location.href = "./login.html";
		if (d.code > 1)
			return alert(d.errMsg);
		Accounts.data = [];
		if (d.code == 0) {
			var template = $("#account-table-row").html();
			var target = $("tbody[data-target]");
			d.accounts.forEach(function(e) {
				Accounts.data[e.username] = e;
				e.type = e.is_minister == 1 ? "部长" : "干事";
				(function(t) {
					Object.keys(e).forEach(function(i) {
						t = t.replace(RegExp("\\{"+i+"\\}", 'g'), e[i]);
					})
					var item = $(t);
					var t = item.find("td:last-of-type");
					if ((user_type == "root" && e.is_minister == 1) ||
						(user_type == "minister" && e.is_minister == 0)) {
						t.append('<a href="javascript:Accounts.detail(\''+e.username+'\')">修改</a>&nbsp;&nbsp;');
						t.append('<a href="javascript:Accounts.password(\''+e.username+'\')">修改密码</a>');
					}
					item.appendTo(target);
				})(template);
			});
		}
		Accounts.table = $('#dataTables-main').DataTable({
			responsive: true,
			lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "全部"] ],
			order: [ [0, user_type == "root" ? 'desc' : 'asc'], [4, 'desc'] ],
			oLanguage: {
				"sLengthMenu": "每页显示条数： _MENU_",
				"sZeroRecords": "抱歉，没有找到",
				"sInfo": "显示第 _START_ 到 _END_ 条 / 共 _TOTAL_ 条数据",
				"sInfoEmpty": "没有数据",
				"sInfoFiltered": "（从 _MAX_ 条数据中检索）",
				"sZeroRecords": "没有数据",
				"sSearch": "搜索：",
				"oPaginate": {
					"sFirst": "<<",
					"sPrevious": "<",
					"sNext": ">",
					"sLast": ">>"
				}
			},
			columnDefs: [
				{ "searchable": false, "targets": 3 },
				{ "searchable": false, "targets": 4 }
			]
		});
	}).fail(function() {
		alert("服务器错误，请联系管理员");
	});	

	$("#form-modal .modal-footer .btn-primary").click(function() {
		$("#form-modal form").submit();
	});

	$('#form-modal').on('hide.bs.modal', function () {
		if (renderForm.ok)
			return;
		if (!confirm("取消填写将会丢失已填写的数据，确认取消？"))
			return false;
	});

	function renderForm(id, render_list) {
		renderForm.ok = false;
		var data = {};
		if (id) {
			data = Accounts.data[id];
			$("#formModalLabel").text("修改账号");
		} else {
			$("#formModalLabel").text("添加账号");
		}
		$("#form-modal .modal-body").html($("#form-template").html());
		Object.keys(render_list).forEach(function(i) {
			var t = $("#form-modal form [data-name="+i+"]");
			if (render_list[i].disabled)
				t.attr("disabled", true);
			if (render_list[i].fill) {
				if (render_list[i].value !== undefined)
					t.val(render_list[i].value);
				else if (render_list[i].target)
					t.val(data[render_list[i].target] || "");
				else
					t.val(data[t.data("name")] || "");
				if (!render_list[i].upload || render_list[i].upload == true)
					t.attr("name", render_list[i].name || t.data("name"));
			}
			if (render_list[i].show)
				t.parent(".form-group").removeClass("hidden");
		});
		$("#form-modal form").submit(function() {
			var blank = false;
			$(this).find("[name]").each(function(item) {
				if (this.value == "" && render_list[this.name].required !== false) {
					blank = true;
					return false;
				}
			});
			if (blank)
				return alert("请填写所有项！");
			if (render_list.password) {
				var t = $(this);
				if (t.find("[data-name=password]").val() != t.find("[data-name=repeat_password]").val()) {
					return alert("两次输入的密码不一样！");
				}
			}
			var postdata = $(this).serialize();
			if (id)
				postdata += "&operation=modify&username="+data["username"];
			else
				postdata += "&operation=create";
			$.post(this.action, postdata)
			.done(function(d) {
				if (d.code)
					return alert(d.errMsg);
				if (id) {
					var target = Accounts.table.row($("[data-target] [data-id="+id+"]"));
					var rd = target.data();
					Object.keys(render_list).forEach(function(i) {
						if (render_list[i].write) {
							rd[data_table_map[render_list[i].write]] = $("#form-modal form [data-name="+i+"]").val();
						}
					});
					target.data(rd);
				} else {
					location.reload(true);
				}
				renderForm.ok = true;
				$("#form-modal").modal("hide");
			})
			.fail(function() {
				alert("服务器错误，请联系管理员");
			});
		});
	}

	function detail(id) {
		if (id) {
			renderForm(id, {
				"username": {
					fill: true,
					show: true,
					disabled: true
				},
				"name": {
					name: "new_name",
					fill: true,
					target: "name",
					show: true,
					write: "name"
				},
				"set_new_password": {
					fill: true,
					value: "0"
				}
			});
		} else {
			renderForm(id, {
				"username": {
					show: true,
					fill: true
				},
				"name": {
					show: true,
					fill: true
				},
				"type": {
					show: true,
					fill: true,
					value: user_type == "root" ? "部长" : "干事",
					upload: false,
					disabled: true
				},
				"password": {
					show:true,
					fill: true
				},
				"repeat_password": {
					show: true
				}
			});
		}
		$("#form-modal").modal("show");
	}

	function password(id) {
		renderForm(id, {
			"username": {
				fill: true,
				show: true,
				disabled: true
			},
			"name": {
				name: "new_name",
				fill: true,
				target: "name"
			},
			"set_new_password": {
				fill: true,
				value: "1"
			},
			"password": {
				name: "new_password",
				show: true,
				fill: true
			},
			"repeat_password": {
				show: true
			}
		});
		$("#form-modal").modal("show");
	}

	function logout() {
		$.post("./assets/API/accounts.php", {
			operation: "logout"
		}).done(function(d) {
			if (d.code)
				return alert(d.errMsg);
			location.href = "./login.html";
		})
		.fail(function() {
			alert("服务器错误，请联系管理员");
		});
	}

	return {
		detail : detail,
		password : password,
		logout : logout
	}
})();