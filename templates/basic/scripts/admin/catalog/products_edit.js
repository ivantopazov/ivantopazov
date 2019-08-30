$(function () {

	var init = function () {

		MEM.unload_photo = [];

		window.Ev.products_edit = {

			init: function () {

				$.getScript('/addons/scripts/plugins/summernote/summernote.min.js', function () {
					$.getScript('/addons/scripts/plugins/summernote/summernote-ru-RU.js', function () {
						$(DOM.parent + ' .summernote').summernote({
							lang: 'ru-RU',
							toolbar: [
								['style', ['style']],
								['font', ['bold', 'italic', 'underline', 'clear']],
								['fontname', []],
								['color', ['color']],
								['para', ['ul', 'ol', 'paragraph']],
								['height', ['height']],
								['table', ['table']],
								['insert', ['link', 'picture', 'video']],
								['view', ['fullscreen', 'codeview']],
							],
						});
						$(DOM.parent + ' textarea[name="description"]').val($(DOM.parent + ' .note-editable').html());
						$(DOM.parent + ' .note-editable').bind('DOMSubtreeModified', function () {
							$(DOM.parent + ' textarea[name="description"]').val($(this).html());
						});
					});
				});

				$.getScript('/addons/scripts/plugins/iCheck/icheck.min.js', function () {
					$(DOM.parent + ' .i-checks').iCheck({
						checkboxClass: 'icheckbox_square-green',
						radioClass: 'iradio_square-green',
					});
				});

				$(DOM.parent + ' div[item_change] input[type="checkbox"]').on('ifChecked ifUnchecked', function (event) {
					if (event.type == 'ifChecked') {
						$(DOM.parent + ' div[item_checkbox="' + $(this).attr('id') + '"] input[type="text"]').removeAttr('disabled');
					}
					if (event.type == 'ifUnchecked') {
						$(DOM.parent + ' div[item_checkbox="' + $(this).attr('id') + '"] input[type="text"]').prop('disabled', true);
					}
				});

				$.getScript('/addons/scripts/plugins/loadfiles/js/jquery.uploadfile.js', function () {
					$(DOM.parent + ' .uploadfile').uploadFile({
						url: '/admin/catalog/products/actEditProductImages',
						allowedTypes: 'png,gif,jpg,jpeg',
						multiple: true,
						dynamicFormData: function () {
							return {
								product_id: $(DOM.parent + ' div#fileUploads input[name="product_id"]').val(),
							};
						},
						formData: {},
						maxFileSize: 2108 * 2000,
						fileName: 'images[]',
						afterUploadAll: function () {
							$(DOM.parent + ' .ajax-file-upload-statusbar').remove();
							TPL.GET_TPL('pages/admin/catalog/editProduct_imagesList', {items: MEM.unload_photo}, function (e) {
								$(DOM.parent + ' div#fileUploads .ev-photo_list').append(e);
								MEM.unload_photo = [];
							});
						},
						onSuccess: function (files, data, xhr) {
							var resp = $.parseJSON(xhr.responseText);
							if (resp.err < 1) {
								MEM.unload_photo.push(resp.response);
							}
						},
					});

				});

			},

			FORM: {

				set_info: {

					before: function (data) {
						var err = 0;
						for (var key in data) {
							var val = data [key];

							if (val.name === 'title') {
								if (val.value.length < 1) {
									err++;
									$(DOM.parent + ' form#blockInfo input[name="' + val.name + '"]').css('border', '1px solid red');
									setTimeout(function () {
										$(DOM.parent + ' form#blockInfo input[name="' + val.name + '"]').css('border', 'auto');
									}, 5000);
								}
							}

							if (val.name === 'shop_category') {
								if (!FNC.validate('integer', val.value) || val.value < 1) {
									err++;
									$(DOM.parent + ' form#blockInfo select[name="' + val.name + '"]').css('border', '1px solid red');
									setTimeout(function () {
										$(DOM.parent + ' form#blockInfo select[name="' + val.name + '"]').css('border', 'auto');
									}, 5000);
								}
							}

							if (val.name === 'qty') {
								if (!FNC.validate('integer', val.value) || val.value < 0) {
									err++;
									$(DOM.parent + ' form#blockInfo input[name="' + val.name + '"]').css('border', '1px solid red');
									setTimeout(function () {
										$(DOM.parent + ' form#blockInfo input[name="' + val.name + '"]').css('border', 'auto');
									}, 5000);
								}
							}

						}

						return ( err < 1 ) ? true : false;
					},

					success: function (response) {
						FNC.alert(( response.err > 0 ) ? 'error' : 'success', response.mess);
						var PID = Number($(DOM.parent + ' form#blockInfo input[name="product_id"]').val());
						if (response.err < 1 && PID < 1) {
							$(DOM.parent + ' .tabs-container ul.nav-tabs > li').removeClass('hidden');
							$(DOM.parent + ' .tabs-container .tab-content > div.tab-pane').removeClass('hidden');
							$(DOM.parent + ' input[name="product_id"]').val(response.response_id);
						}
					},

				},

				set_prices: {
					before: function (data) {
						var LIKE = function (str, text) {
							var r = text.toLowerCase().indexOf(str.toLowerCase());
							return ( r >= 0 ) ? true : false;
						};
						var priceCount = 0;
						var define_valuta = 0;
						var product_id = false;
						for (var key in data) {
							var val = data [key];
							if (LIKE('PRICE', val.name)) priceCount++;
							if (val.name === 'define_valuta') define_valuta++;
							if (val.name === 'product_id') product_id++;
						}
						//return ( priceCount > 0 && define_valuta > 0 && product_id  !== false ) ? true : false;
						return true; //todo здесь костыль - по условию выше кнопка не работает
					},
					success: function (response) {
						FNC.alert(( response.err > 0 ) ? 'error' : 'success', response.mess);
						window.location.reload();
					},
				},

				set_seo: {
					success: function (response) {
						FNC.alert(( response.err > 0 ) ? 'error' : 'success', response.mess);
					},
				},

				set_params: {
					success: function (response) {
						FNC.alert(( response.err > 0 ) ? 'error' : 'success', response.mess);
					},
				},

				set_filters: {

					requare: function (data, success) {
						var t = [];
						$(DOM.parent + ' #blockFilters div[data-block_name]').map(function (e, a) {
							var blockName = $(a).attr('data-block_name');
							var add = {
								item: blockName,
								values: [],
							};
							$(DOM.parent + ' #blockFilters div[data-block_name="' + blockName + '"] input[type="checkbox"]:checked ').map(function (w, r) {
								add.values.push($(r).attr('name'));
							});
							t.push(add);
						});

						var PID = false;
						for (var key in data) {
							var val = data [key];
							if (val.name == 'product_id') {
								PID = val.value;
							}
						}

						success(true, {f: t, product_id: PID});
					},

					success: function (response) {
						FNC.alert(( response.err > 0 ) ? 'error' : 'success', response.mess);
					},

				},

				set_images: {

					photo_remove: function (id) {
						if (id > 0) {
							$.ajax({
								type: 'post',
								url: '/admin/catalog/products/actEditProductImageRemove',
								data: {
									pid: id,
								},
								dataType: 'json',
								success: function (response) {
									FNC.alert(( response.err > 0 ) ? 'error' : 'success', response.mess);
									if (response.err < 1) {
										$(DOM.parent + ' div.ev-photo_list > div[item_id="' + response.remove_id + '"]').remove();
									}
								},
							});
						}
					},

					set_define: function (id) {
						if (id > 0) {
							$.ajax({
								type: 'post',
								url: '/admin/catalog/products/actEditProductImageSetDefine',
								data: {
									pid: id,
								},
								dataType: 'json',
								success: function (response) {
									FNC.alert(( response.err > 0 ) ? 'error' : 'success', response.mess);
									if (response.err < 1) {
										$(DOM.parent + ' .ph-defined i').removeClass('text-primary');
										$(DOM.parent + ' div.ev-photo_list > div[item_id="' + response.set_id + '"] .ph-defined i').addClass('text-primary');
									}
								},
							});
						}
					},

				},

			},
		};

		window.Ev.products_edit.init();

	};

	var e = setInterval(function () {
		if (window.CORE) {
			clearInterval(e);
			init();
		}
	}, 10);

});