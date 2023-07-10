(function ($) {
	$(document).ready(function () {
		$(".inputContainer").hide();
		if ($(".woocommerce-cart").length > 0) {
			$(".select_student").each(function (i, obj) {
				var self = $(this);
				var val = self.find("option:selected").val();
				console.log("vavava", val);
				var cart_id = $(this).data("cart-id");
				// // if (self.val() !== "other") {
				$.ajax({
					type: "POST",
					url: prefix_vars.ajaxurl,
					data: {
						action: "prefix_update_cart_notes",
						security: $("#woocommerce-cart-nonce").val(),
						assignTo: self.val(),
						cart_id: cart_id,
						fromselect: true,
					},
					success: function (resp) {
						if (resp["success"]) {
							// window.location.reload();
							// self.prop('disabled', true);
							// self.parent().after('<div class="assignTo"><p>شراء لـ ' + resp['name'] + '</p><div>')
							$(".spinner-border").remove();
						}
					},
				});
				// } else {
				//     $(".inputContainer").show();
				// }
			});
		}
		$(".select_student").on("change", function () {
			var self = $(this);
			var cart_id = $(this).data("cart-id");
			if (self.val() !== "other") {
				$.ajax({
					type: "POST",
					url: prefix_vars.ajaxurl,
					data: {
						action: "prefix_update_cart_notes",
						security: $("#woocommerce-cart-nonce").val(),
						assignTo: self.val(),
						cart_id: cart_id,
						fromselect: true,
					},
					success: function (resp) {
						if (resp["success"]) {
							window.location.reload();
							// self.prop('disabled', true);
							// self.parent().after('<div class="assignTo"><p>شراء لـ ' + response['name'] + '</p><button><i class="fa fa-times"></i></button><div>')
							$(".spinner-border").remove();
						}
					},
				});
			} else {
				$(".inputContainer").show();
			}
		});
		$(".prefix-cart-notes").on("change", function () {
			var self = $(this);
			var cart_id = $(this).data("cart-id");
			var value = $(this).val();
			var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
			$(".email_error").remove();

			if (value != "" && !emailReg.test(value)) {
				$(this).after(
					'<p class="email_error">برجاء ادخال صيغة بريد إلكتروني صحيحة</p>'
				);
			} else {
				self.parent().append(
					'<div class="spinner-border" role="status"></div>'
				);
				$(".cart_totals").block({
					message: null,
					overlayCSS: {
						background: "#fff",
						opacity: 0.6,
					},
				});
				$.ajax({
					type: "POST",
					url: prefix_vars.ajaxurl,
					data: {
						action: "check_user_exists",
						security: $("#woocommerce-cart-nonce").val(),
						email: value,
					},
					success: function (response) {
						if (response["success"]) {
							$.ajax({
								type: "POST",
								url: prefix_vars.ajaxurl,
								data: {
									action: "prefix_update_cart_notes",
									security: $(
										"#woocommerce-cart-nonce"
									).val(),
									assignTo: $("#cart_notes_" + cart_id).val(),
									cart_id: cart_id,
								},
								success: function (resp) {
									if (resp["success"]) {
										self.prop("disabled", true);
										self.parent().after(
											'<div class="assignTo"><p>شراء لـ ' +
												response["name"] +
												'</p><button><i class="fa fa-times"></i></button><div>'
										);
										$(".spinner-border").remove();
									}
								},
							});
						} else {
							$(".spinner-border").remove();
							self.after(
								'<p class="email_error">البريد الإلكتروني غير مسجل لدينا</p>'
							);
						}
					},
				});

				$(".cart_totals").unblock();
			}
		});
		$(".assignTo button").on("click", function (e) {
			e.preventDefault();
			var parent = $(this).parent().parent();
			var input = parent.find("input");
			var email = input.val();
			var cart_id = input.data("cart-id");
			// inputContainer
			parent
				.find(".inputContainer")
				.append('<div class="spinner-border" role="status"></div>');
			$.ajax({
				type: "POST",
				url: prefix_vars.ajaxurl,
				data: {
					action: "prefix_remove_cart_notes",
					security: $("#woocommerce-cart-nonce").val(),
					cart_id: cart_id,
				},
				success: function (response) {
					input.prop("disabled", false);
					input.val("");
					parent.find(".assignTo").remove();
					$(".spinner-border").remove();
				},
			});
		});
		$("form#student-registration").on("submit", function (e) {
			e.preventDefault();
			if (!$(this).valid()) return false;
			$(".status").empty();

			ctrl = $(this);
			$.ajax({
				type: "POST",
				dataType: "json",
				url: prefix_vars.ajaxurl,
				data: {
					action: "ajaxregister",
					first_name: $("input[name=first_name]").val(),
					last_name: $("input[name=last_name]").val(),
					user_login: $("input[name=user_login]").val(),
					email: $("input[name=email]").val(),
					password: $("input[name=password]").val(),
					parent_email: $("input[name=parent_email]").val(),

					security: $("#security").val(),
				},
				success: function (data) {
					// $('status', ctrl).text(data.message);
					// if (data.loggedin == true) {
					//     document.location.href = prefix_vars.redirecturl;
					// }
					if (!data["success"]) {
						$(".status").append(
							"<p class='error'>" + data["message"] + "</p>"
						);
					} else {
						$(".status").append(
							"<p class='success'>تم اضافة الطالب بنجاح</p>"
						);
						setTimeout(() => {
							location.reload();
						}, 300);
					}
					$("html, body").animate({
						scrollTop: 0,
					});
				},
			});
		});
		$(".deleteuser").on("click", function (e) {
			e.preventDefault();
			$("#myModal").css("display", "block");
		});
		$("#myModal .close").on("click", function (e) {
			e.preventDefault();
			$("#myModal").css("display", "none");
		});
		$("#myModal .closebutton").on("click", function (e) {
			e.preventDefault();
			$("#myModal").css("display", "none");
		});
		$("#myModal .deleteuser").on("click", function (e) {
			e.preventDefault();
			let user_id = $(this).data("user-id");
			$.ajax({
				type: "POST",
				dataType: "json",
				url: prefix_vars.ajaxurl,
				data: {
					action: "ajaxuserdelete",
					user_id: user_id,
				},
				success: function (data) {
					// $('status', ctrl).text(data.message);
					// if (data.loggedin == true) {
					//     document.location.href = prefix_vars.redirecturl;
					// }
					if (!data["success"]) {
						$(".status").append(
							"<p class='error'>" + data["message"] + "</p>"
						);
					} else {
						$(".status").append(
							"<p class='error'>" + data["message"] + "</p>"
						);
						$("#myModal").css("display", "none");
						setTimeout(() => {
							location.reload();
						}, 300);
					}
					$("html, body").animate({
						scrollTop: 0,
					});
				},
			});
		});
		$("button.login").on("click", function (e) {
			e.preventDefault();
			let login_url = $(this).data("login-url");
			window.open(login_url);
			window.location.reload();
		});
		$(".showorders").on("click", function () {
			let ordersContent = $(this)
				.parent()
				.parent()
				.next()
				.find(".ordersContent");
			let changepassword = $(this)
				.parent()
				.parent()
				.next()
				.find(".changepassword");
			//    if($(this).hasClass('active')){
			//     changepassword.hide()
			//         ordersContent.hide()
			//         $(this).removeClass('active')
			//    }else{
			//     $(this).addClass('active');
			// $(this).parent().parent().siblings(".ordersContent").hide()
			$(this)
				.parent()
				.parent()
				.siblings(".userContent")
				.find(".changepassword")
				.hide();
			$(this)
				.parent()
				.parent()
				.siblings(".userContent")
				.find(".ordersContent")
				.hide();
			changepassword.hide();
			ordersContent.show();
			//    }
		});
		$(".edituserpassword").on("click", function () {
			let changepassword = $(this)
				.parent()
				.parent()
				.next()
				.find(".changepassword");
			let ordersContent = $(this)
				.parent()
				.parent()
				.next()
				.find(".ordersContent");

			//    if($(this).hasClass('active')){
			//         ordersContent.hide()
			//         changepassword.hide()
			//         $(this).removeClass('active')
			//    }else{
			//     $(this).addClass('active');
			$(this)
				.parent()
				.parent()
				.siblings(".userContent")
				.find(".changepassword")
				.hide();
			$(this)
				.parent()
				.parent()
				.siblings(".userContent")
				.find(".ordersContent")
				.hide();
			ordersContent.hide();
			changepassword.show();
			//    }
		});
		$(".reBuy").on("click", function (e) {
			e.preventDefault();
			$(".status").empty();

			$.ajax({
				type: "POST",
				dataType: "json",
				url: prefix_vars.ajaxurl,
				data: {
					action: "reordertocart",
					order_id: $(this).data("order_id"),
				},
				success: function (data) {
					$(".status").append(
						"<p class='success'>" + data["message"] + "</p>"
					);
					setTimeout(() => {
						location.reload();
					}, 300);

					$("html, body").animate({
						scrollTop: 0,
					});
				},
			});
		});
		$(".editpasswordsubmit").on("click", function (e) {
			e.preventDefault();
			let parent = $(this).parent().parent();
			let password = parent.find("input[name=password]").val();
			let email = parent.find("input[name=email]").val();
			let security = parent.find("#security").val();

			$(".status").empty();

			ctrl = $(this);
			$.ajax({
				type: "POST",
				dataType: "json",
				url: prefix_vars.ajaxurl,
				data: {
					action: "ajaxediteuser",
					password: password,
					email: email,
					security: security,
				},
				success: function (data) {
					if (!data["success"]) {
						$(".status").append(
							"<p class='error'>" + data["message"] + "</p>"
						);
					} else {
						$(".status").append(
							"<p class='error'>" + data["message"] + "</p>"
						);
					}
					$("html, body").animate({
						scrollTop: 0,
					});
				},
			});
		});
		$(".countdown-item").each(function () {
			var startDate = parseInt($(this).data("start-date")) * 1000;
			var endDate = parseInt($(this).data("end-date")) * 1000;
			var countdownElement = $(this).find(".countdown");

			function updateCountdown() {
				var now = new Date();
				var timeDifference = endDate - now;

				if (timeDifference <= 0) {
					clearInterval(countdownInterval);
					countdownElement.text("Countdown has ended.");
				} else {
					var days = Math.floor(
						timeDifference / (1000 * 60 * 60 * 24)
					);
					var hours = Math.floor(
						(timeDifference % (1000 * 60 * 60 * 24)) /
							(1000 * 60 * 60)
					);
					var minutes = Math.floor(
						(timeDifference % (1000 * 60 * 60)) / (1000 * 60)
					);
					var seconds = Math.floor(
						(timeDifference % (1000 * 60)) / 1000
					);

					countdownElement.text(
						days +
							" يوم و  " +
							hours +
							" ساعة و  " +
							minutes +
							" دقيقة " +
							seconds +
							" ثانية"
					);
				}
			}

			// Initial update
			updateCountdown();

			// Update every second
			var countdownInterval = setInterval(updateCountdown, 1000);
		});
	});
})(jQuery);
