$(function(){
	    
	var init = function(){
		
        MEM.CSV = {
            send : {
                package_size : 100,
                all_size : function (){
                    return MEM.CSV.extract.length;
                },
                package_steps : function(){
                    return ( parseInt( MEM.CSV.send.all_size() / MEM.CSV.send.package_size ) + 1 );
                },
                active_step : 1,
                package_arrays : [],
                package_success : [],
                package_errors : [],
                interval : {
                    intv: false,
                    status: true
                }
            },
            extract : []
        };
        
		window.Ev.parser_estet = {
			
            query : function(){
                $("#est_extract").text('Загрузка прайс листа...');                
                $.ajax({
                    type: "post",
                    url: "/admin/parser/estet/estet_parser_get_xml",
                    dataType: 'json',
                    success: function( json ){
                        MEM.CSV.extract = json;
                        $("#est_extract").text('Извлечь данные');                        
                        setTimeout( function(){
                            Ev.parser_estet.run_import();
                        }, 1500 );
                    },
                    error : function (){
                        $("#est_extract").text('Ошибка!');
                        setTimeout(function (){
                            $("#est_extract").text('Извлечь данные');
                        }, 2000 );
                    }
                });
            },
            
            run_import : function (){
                
                if ( MEM.CSV.send.package_arrays.length < 1 ) {
                    var step = []; 
                    var _i = 0;
                    for (var key in MEM.CSV.extract) {
                        var val = MEM.CSV.extract[key];
                        if ( _i < MEM.CSV.send.package_size ) {
                            step.push( val );
                            _i++;
                        }                    
                        if( step.length == MEM.CSV.send.package_size ){
                            MEM.CSV.send.package_arrays.push( step );
                            _i = 0;
                            step = [];
                        }
                    }        
                }
                
                
                MEM.CSV.send.interval.status = true;
                var setClear = 1;
                
                $(DOM.parent + ' #pack_pr').html('Прогресс: <b>0 / '+(MEM.CSV.extract.length / MEM.CSV.send.package_size )+'</b>');
                var status_sender = function(){
                    $(DOM.parent + ' #pack_pr').html('Прогресс: <b>'+((MEM.CSV.send.active_step > MEM.CSV.send.package_steps())?MEM.CSV.send.package_steps(): MEM.CSV.send.active_step)+' / '+MEM.CSV.extract.length/ MEM.CSV.send.package_size+'</b>');
                    $(DOM.parent + ' #pack_succ').html('Успешно: <b>'+((MEM.CSV.send.package_success.length > MEM.CSV.send.package_steps())?MEM.CSV.send.package_steps():MEM.CSV.send.package_success.length)+'</b>');
                    $(DOM.parent + ' #pack_err').html('Пакетов с ошибкой: <b>'+((MEM.CSV.send.package_errors.length > MEM.CSV.send.package_steps())?MEM.CSV.send.package_steps():MEM.CSV.send.package_errors.length)+'</b>');
                };
                
                MEM.CSV.send.interval.intv = setInterval(function () {
                    if ( MEM.CSV.send.interval.status ) {
                        MEM.CSV.send.interval.status = false;
                        if ( MEM.CSV.send.package_arrays.length > 0 ) {  

/*
                            console.log( 'send.. ', MEM.CSV.send.package_arrays[0], setClear );
                           
                            setClear = 0;
                            MEM.CSV.send.package_success.push( MEM.CSV.send.package_arrays[0] );
                            MEM.CSV.send.package_arrays.shift();
                            MEM.CSV.send.active_step++;
                            status_sender();
                            MEM.CSV.send.interval.status = true;
                               */
                             $.ajax({
                                type: "post",
                                url: "/admin/parser/estet/parseEstet",
                                data: { 
                                    clear : setClear,
                                    pack: MEM.CSV.send.package_arrays[0]
                                },
                                dataType : 'json',
                                success: function( stat ){
                                    if( stat.err < 1 ){
                                    console.log( 'send.. ', stat );
                                        setClear = 0;
                                        MEM.CSV.send.package_success.push( MEM.CSV.send.package_arrays[0] );
                                        MEM.CSV.send.package_arrays.shift();
                                        MEM.CSV.send.active_step++;
                                        status_sender();
                                        setTimeout(function(){
                                            MEM.CSV.send.interval.status = true;
                                        }, 300);
                                    }else{
                                    console.log( 'send.. ', stat );
                                        MEM.CSV.send.interval.status = true;
                                    }
                                },
                                error : function(){
                                    MEM.CSV.send.interval.status = true;
                                }
                            });
                        } else {
                            MEM.CSV.send.active_step++;
                            status_sender();
                            if( MEM.CSV.send.interval.intv ) clearInterval( MEM.CSV.send.interval.intv );
                        }
                    }
                }, 100);
                 
            }
			
		};
           
	};
	
	var e = setInterval(function(){
		if( window.CORE ){
			clearInterval( e );
			init();
		}
	}, 10);
	
});