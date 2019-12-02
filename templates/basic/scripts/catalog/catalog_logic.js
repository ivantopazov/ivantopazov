$(function () {

    $.getScript("/addons/scripts/plugins/iCheck/icheck.min.js", function () {
        $('input.i-checks')
            /*.on('ifChanged', function (event) {
                var db_block = $(this).parents('div[data-parent]').attr('data-parent');

                setTimeout(function(){
                    Ev.catalog.getFiltersParam( db_block );
                },100);
            })*/
            .iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green'
            });
    });

    var init = function () {

        window.Ev.catalog = {

            viewHide: function (blockName) {
                var block = $(DOM.parent + ' div.slideHide[data-item="' + blockName + '"]');

                var blockAttr = block.attr('data-status');

                if (blockAttr === 'hide') {
                    block.addClass('auto');
                    block.attr('data-status', 'show');
                } else {
                    block.removeClass('auto');
                    block.attr('data-status', 'hide');
                }

            },

            setOption_item: function (filter_option) {
                var filter_option = filter_option || false;
                if (filter_option !== false) {
                    $(DOM.parent + ' ul[data-parent="filter-option"] span[data-filter_option]').removeClass('active');
                    $(DOM.parent + ' ul[data-parent="filter-option"] span[data-filter_option="' + filter_option + '"]').addClass('active');
                    setTimeout(function () {
                        Ev.catalog.getFiltersParam();
                    }, 100);
                }
            },

            getFiltersParam: function (db_block) {

                var db_block = db_block || 'filter-block';

                var getParam = FNC.GET_parse(FNC.parseURL(location.href).search.replace('?', ''));

                var arr = {
                    f: {},
                    s: 'pop',
                    l: 44,
                    t: ''
                };

                console.log('db_block', db_block);

                $(DOM.parent + ' div[data-parent="' + db_block + '"] input[type="checkbox"]:checked').map(function (a, e) {
                    var parentBlock = $(e).parents('div[data-block_name]').attr('data-block_name');
                    if (!arr.f[parentBlock]) {
                        arr.f[parentBlock] = [];
                    }
                    arr.f[parentBlock].push($(e).attr('name'));
                });


                arr.f['Cena'] = [];
                arr.f['Cena'].push($(DOM.parent + '  div[data-parent="' + db_block + '"] input[name="price-ot"]').val());
                arr.f['Cena'].push($(DOM.parent + '  div[data-parent="' + db_block + '"] input[name="price-do"]').val());

                arr.f['weight'] = [];
                arr.f['weight'].push($(DOM.parent + '  div[data-parent="' + db_block + '"] input[name="weight-ot"]').val());
                arr.f['weight'].push($(DOM.parent + '  div[data-parent="' + db_block + '"] input[name="weight-do"]').val());

                for (var key in arr.f) {
                    var val = arr.f [key];
                    arr.f [key] = val.join('|');
                }

                arr.t = $(DOM.parent + ' input[name="t"]').val();

                arr.s = $(DOM.parent + ' ul[data-parent="filter-option"] span[data-filter_option].active').attr('data-filter_option');
                if (getParam['l']) arr.l = getParam.l;
                var setUrl = location.pathname + '?' + decodeURIComponent($.param(arr));

                FNC._set_url(setUrl);
                setTimeout(function () {
                    window.location.reload();
                }, 100);

            }

        };

    };

    var e = setInterval(function () {
        if (window.CORE) {
            clearInterval(e);
            init();
        }
    }, 10);

});