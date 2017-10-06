;var Admin = (function() {
	var user_type = $.cookie("user_type");
	if (!user_type)
		return updateUrl("./login.html");
	if (user_type == "root") {
		$("[data-manage-minister]").removeClass("hidden");
		$("a[data-add]").addClass("hidden");
	}
	if (user_type == "minister") {
		$("[data-manage-user]").removeClass("hidden");
		$("a[data-refresh]").removeClass("hidden");
	}
	if (user_type == "root" || user_type == "minister") {
		$("a[data-download]").removeClass("hidden");
	}

	var data_table_map = (function() {
		var tmp = [ "business_id", "is_contacted_text", "name", "industry", "contact",
			"address", "willingness", "sponsorship_content", "charge_history",
			"business_evaluation", "remarks", "contact_history", "update_time" ];
		var r = {};
		for (var i=0;i<tmp.length;++i) {
			r[tmp[i]] = i;
			r[i] = tmp[i];
		}
		return r;
	})();

	$.get("./assets/API/businesses.php")
	.done(function(d) {
		if (d.code == 233)
			return updateUrl("./login.html");
		if (d.code > 1)
			return alert(d.errMsg);
		Admin.data = [];
		if (d.code == 0) {
			var template = $("#business-table-row").html();
			var target = $("tbody[data-target]");
			d.businesses.forEach(function(e) {
				Admin.data[e.business_id] = e;
				e.is_contacted_text = e.is_contacted == 1 ? "已联系" : "<span class='red'>未联系</span>";
				(function(t) {
					Object.keys(e).forEach(function(i) {
						t = t.replace(RegExp("\\{"+i+"\\}", 'g'), e[i]);
					})
					var item = $(t);
					var optd = item.find("td:last-of-type");
					if (user_type != "root" && e.is_contacted == 0) {
						optd.append('<a href="javascript:Admin.contact('
							+ e.business_id + ', true)">设为已联系</a>&nbsp;&nbsp;');
					}
					if (user_type == "minister") {
						if (e.is_contacted == 1) {
							optd.append('<a href="javascript:Admin.contact('
								+ e.business_id + ', false)">设为未联系</a>&nbsp;&nbsp;');
						}
						optd.append('<a href="javascript:Admin.detail('
							+ e.business_id + ')">修改</a>&nbsp;&nbsp;');
					}
					item.appendTo(target);
				})(template);
			});
		}
		Admin.table = $('#dataTables-main').DataTable({
			responsive: true,
			lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "全部"] ],
			order: [ 
				[data_table_map["is_contacted_text"], 'desc'],
				[data_table_map["business_id"], 'desc']
			],
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
				{ "searchable": false, "targets": data_table_map["is_contacted_text"] },
				{ "orderable": false, "targets": data_table_map["update_time"] },
				{ "searchable": false, "targets": data_table_map["update_time"] },
				{ "orderable": false, "targets": 13 },
				{ "searchable": false, "targets": 13 }
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

	function renderForm(id, render_list, callback, silent) {
		renderForm.ok = false;
		var data = {};
		if (id) {
			data = Admin.data[id];
			$("#formModalLabel").text("修改商家信息");
		} else {
			$("#formModalLabel").text("添加商家信息");
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
		(function() {
			var t = $("#form-modal form [data-name=old_contact_history]");
			if (t.val())
				t.val(t.val().replace(/<br>/g, "\n"));
		})();
		$("#form-modal form").submit(function() {
			var blank = false;
			$(this).find("[name]").each(function(item) {
				if (this.value == "" && render_list[this.name].required !== false) {
					console.log(this.name);
					blank = true;
					return false;
				}
			});
			if (blank)
				return alert("请填写所有必填项！");
			(function() {
				var t = $("#form-modal form [data-name=contact_history]");
				if (t.val())
					t.val(t.val() + "<br>");
			})();
			var postdata = $(this).serialize();
			if (id)
				postdata += "&operation=update";
			else
				postdata += "&operation=add";
			$.post(this.action, postdata)
			.done(function(d) {
				if (d.code)
					return alert(d.errMsg);
				if (!silent)
					updateUrl();
				return;
			})
			.fail(function() {
				alert("服务器错误，请联系管理员");
			})
			.always(function(status, data) {
				if (callback)
					callback(status, data);
			});
		});
	}

	function detail(id) {
		if (id) {
			renderForm(id, {
				"business_id": {
					fill: true
				},
				"name": {
					show: true,
					fill: true,
					write: "name"
				},
				"industry": {
					show: true,
					fill: true
				},
				"contact": {
					show: true,
					fill: true
				},
				"address": {
					show: true,
					fill: true
				},
				"willingness": {
					show: true,
					fill: true
				},
				"sponsorship_content": {
					show: true,
					fill: true
				},
				"charge_history": {
					show: true,
					fill: true,
					required: false
				},
				"business_evaluation": {
					show: true,
					fill: true,
					required: false
				},
				"remarks": {
					show: true,
					fill: true,
					required: false
				},
				"is_contacted": {
					fill: true
				},
				"old_contact_history": {
					show: true,
					target: "contact_history",
					fill: true,
					required: false,
					disabled: true
				},
				"contact_history": {
					show: Admin.data[id]["is_contacted"] == 1 ? true : false,
					fill: true,
					value: "",
					required: false
				}
			});
		} else {
			renderForm(id, {
				"name": {
					show: true,
					fill: true
				},
				"industry": {
					show: true,
					fill: true
				},
				"contact": {
					show: true,
					fill: true
				},
				"address": {
					show: true,
					fill: true
				},
				"willingness": {
					show: true,
					fill: true
				},
				"sponsorship_content": {
					show: true,
					fill: true
				},
				"charge_history": {
					show: true,
					fill: true,
					required: false
				},
				"business_evaluation": {
					show: true,
					fill: true,
					required: false
				},
				"remarks": {
					show: true,
					fill: true,
					required: false
				},
				"is_contacted": {
					fill: true,
					value: 0
				},
				"contact_history": {
					fill: true,
					required: false
				}
			});
		}
		$("#form-modal").modal("show");
	}

	function contact(id, is, callback, silent) {
		if (!silent && !is && !confirm("设为未联系将删除所有联系历史，确认继续吗？"))
			return;
		renderForm(id, {
			"business_id": {
				fill: true
			},
			"name": {
				fill: true
			},
			"industry": {
				fill: true
			},
			"contact": {
				fill: true
			},
			"address": {
				fill: true
			},
			"willingness": {
				fill: true
			},
			"sponsorship_content": {
				fill: true
			},
			"charge_history": {
				fill: true,
				required: false
			},
			"business_evaluation": {
				fill: true,
				required: false
			},
			"remarks": {
				fill: true,
				required: false
			},
			"is_contacted": {
				fill: true,
				value: is ? 1 : 0,
				write: "is_contacted_text",
				write_value: is ? "已联系" : "<span class='red'>未联系</span>"
			},
			"old_contact_history": {
				show: true,
				target: "contact_history",
				fill: true,
				required: false,
				disabled: true
			},
			"contact_history": {
				show: true,
				fill: true,
				value: "",
				required: false
			}
		}, callback, silent);
		if (is)
			$("#form-modal").modal("show");
		else
			$("#form-modal form").submit();
	}

	function logout() {
		$.post("./assets/API/accounts.php", {
			operation: "logout"
		}).done(function(d) {
			if (d.code)
				return alert(d.errMsg);
			updateUrl("./login.html");
		})
		.fail(function() {
			alert("服务器错误，请联系管理员");
		});
	}

	function uncontactAll() {
		var total = 0;
		var suc = 0;
		var done = 0;
		Admin.data.forEach(function(item) {
			if (item.is_contacted == 1)
				total ++;
		});
		if (!total)
			return alert("没有需要设为未联系的项");
		if (!confirm("将会全部 "+total+" 项设为未联系并删除所有联系历史，确认继续吗？"))
			return;
		if (total > 10)
			alert("正在操作，请耐心等待，不要进行其他操作...");
		Admin.data.forEach(function(item) {
			if (item.is_contacted == 1) {
				contact(item.business_id, false, function(data, status) {
					done ++;
					if (status == "success" && data.code == 0)
						suc ++;
					if (done == total) {
						alert("共有 "+total+" 项\n成功 "+suc+" 项\n失败 "+(total-suc)+" 项");
						updateUrl();
					}
				}, true);
			}
		});
	}

	return {
		detail: detail,
		contact: contact,
		uncontactAll: uncontactAll,
		logout: logout,
		map: data_table_map
	}
})();