;var Admin = (function() {
	var user_type = $.cookie("user_type");
	if (!user_type)
		return location.href = "./login.html";
	if (user_type == "root") {
		$("[data-manage-minister]").removeClass("hidden");
		$("a[data-add]").addClass("hidden");
	}
	if (user_type == "minister")
		$("[data-manage-user]").removeClass("hidden");

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
			return location.href = "./login.html";
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
					if (e.is_contacted == 0) {
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

	function renderForm(id, render_list, callback) {
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
		$("#form-modal form").submit(function() {
			var blank = false;
			$(this).find("[name]").each(function(item) {
				if (this.value == "" && render_list[this.name].required !== false) {
					blank = true;
					return false;
				}
			});
			if (blank)
				return alert("请填写所有必填项！");
			var postdata = $(this).serialize();
			if (id)
				postdata += "&operation=update";
			else
				postdata += "&operation=add";
			$.post(this.action, postdata)
			.done(function(d) {
				if (d.code)
					return alert(d.errMsg);
				return location.reload(true);
				if (id) {
					var target = Admin.table.row($("[data-target] [data-id="+id+"]"));
					var rd = target.data();
					if (!rd) {
						console.log($("[data-id="+id+"]"));
						console.log("[data-id="+id+"]");
						console.log(id);
					}
					Object.keys(render_list).forEach(function(i) {
						if (render_list[i].write) {
							var value = render_list[i].write_value || $("#form-modal form [data-name="+i+"]").val();
							rd[data_table_map[render_list[i].write]] = value;
						}
					});
					target.data(rd);
				} else {
					var rd = [];
					var n = Object.keys(data_table_map).length / 2;
					for (var i=0;i<n;++i) {
						var name = data_table_map[i];
						if (name == "is_contacted_text") {
							var value = $("#form-modal form [data-name=is_contacted]").val() == 1 ?
								"已联系" : "<span class='red'>未联系</span>";
						}
						else
							value = $("#form-modal form [data-name="+name+"]").val();
						rd[i] = (value || "");
						d[name] = value;
					}
					var bid = d.business_id || 100;
					rd[0] = bid;
					rd[data_table_map["update_time"]] = "刚刚";
					var str = "";
					d.is_contacted = 0;
					if (d.is_contacted == 0) {
						str += '<a href="javascript:Admin.contact('
							+ bid + ', true)">设为已联系</a>&nbsp;&nbsp;';
					}
					if (user_type == "minister") {
						if (d.is_contacted == 1) {
							str += '<a href="javascript:Admin.contact('
								+ bid + ', false)">设为未联系</a>&nbsp;&nbsp;';
						}
						str += '<a href="javascript:Admin.detail('
							+ bid + ')">修改</a>&nbsp;&nbsp;';
					}
					rd[n] = str;
					Admin.data[bid] = d;
					Admin.table.row.add(rd).draw().node();
				}
				renderForm.ok = true;
				$("#form-modal").modal("hide");
				if (callback)
					callback();
			})
			.fail(function() {
				alert("服务器错误，请联系管理员");
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
					show: true,
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
					show: true,
					fill: true,
					required: false
				}
			});
		}
		$("#form-modal").modal("show");
	}

	function contact(id, is) {
		if (!confirm("确认设为"+(is === false ? "未" : "已")+"联系吗？"))
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
			"contact_history": {
				fill: true,
				required: false
			}
		}, function() {
			var target = Admin.table.row($("[data-target] [data-id="+id+"]"));
			var rd = target.data();
			var n = Object.keys(data_table_map).length / 2;
			var str = "";
			if (!is) {
				str += '<a href="javascript:Admin.contact('
					+ id + ', true)">设为已联系</a>&nbsp;&nbsp;';
			}
			if (user_type == "minister") {
				if (is) {
					str += '<a href="javascript:Admin.contact('
						+ id + ', false)">设为未联系</a>&nbsp;&nbsp;';
				}
				str += '<a href="javascript:Admin.detail('
					+ id + ')">修改</a>&nbsp;&nbsp;';
			}
			rd[n] = str;
			target.data(rd).draw();
		});
		$("#form-modal form").submit();
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
		contact : contact,
		logout : logout,
		map: data_table_map
	}
})();