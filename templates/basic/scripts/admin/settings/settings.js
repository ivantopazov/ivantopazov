$(function(){
	
	var init = function(){
		
        MEM.keyUp = false;
        MEM.unload_photo = [];
        
		window.Ev.stores_sett = {
            
            init : function(){
                
                $.getScript('/addons/scripts/plugins/loadfiles/js/jquery.uploadfile.js', function(){
                    
                    $( DOM.parent + ' .uploadfile1').uploadFile({
                        url:"/admin/settings/home/actEditFaviconImages",
                        allowedTypes: "png,gif,jpg,jpeg,ico",
                        multiple: false,
                        formData: {},
                        maxFileSize: 2108*2000,
                        fileName: "images[]",
                        afterUploadAll:function(){
                            $( DOM.parent + ' .ajax-file-upload-statusbar').remove();  
                            $( DOM.parent + ' .ev-photo_list1').empty();
                            $( DOM.parent + ' .ev-photo_list1').html('<img src="/uploads/favicons/'+MEM.unload_photo+'" width="24" />');
                        },
                        onSuccess: function( files, data, xhr ){
                            var resp = $.parseJSON( xhr.responseText );
                            if(resp.err < 1){
                                MEM.unload_photo = resp.response.name;
                            }
                        }
                    });
                    
                    $( DOM.parent + ' .uploadfile2').uploadFile({
                        url:"/admin/settings/home/actEditFonImages",
                        allowedTypes: "png,gif,jpg,jpeg,ico",
                        multiple: false,
                        formData: {},
                        maxFileSize: 2108*2000,
                        fileName: "images[]",
                        afterUploadAll:function(){
                            $( DOM.parent + ' .ajax-file-upload-statusbar').remove();  
                            $( DOM.parent + ' .ev-photo_list2').empty();
                            $( DOM.parent + ' .ev-photo_list2').html('<img src="/uploads/fons/'+MEM.unload_photo+'" width="100%" />');
                        },
                        onSuccess: function( files, data, xhr ){
                            var resp = $.parseJSON( xhr.responseText );
                            if(resp.err < 1){
                                MEM.unload_photo = resp.response.name;
                            }
                        }
                    });
                       
                    // ЕБЛЫНЬ ДОБАВЛЕНИЕ
                    $( DOM.parent + ' .uploadfile_baner_add').uploadFile({
                        url:"/admin/settings/home/actEditBanersImages",
                        allowedTypes: "png,gif,jpg,jpeg,ico",
                        multiple: false,
                        formData: {},
                        maxFileSize: 2108*2000,
                        fileName: "images[]",
                        afterUploadAll:function(){
                            if(  MEM.unload_photo !== false ){
                                $( DOM.parent + ' .ajax-file-upload-statusbar').remove();  
                                $( DOM.parent + ' .ev-photo_baner_add').empty();
                                $( DOM.parent + ' .ev-photo_baner_add').html('<img src="/uploads/baners/'+MEM.unload_photo+'" width="100%" />');
                            }
                        },
                        onSuccess: function( files, data, xhr ){
                            MEM.unload_photo = false;
                            var resp = $.parseJSON( xhr.responseText );
                            if(resp.err < 1){
                                MEM.unload_photo = resp.response.name;
                            }
                        }
                    });
                    
                    // ЕБЛЫНЬ РЕДАКТИРОВАНИЕ
                    $( DOM.parent + ' .uploadfile_baner_edit').uploadFile({
                        url:"/admin/settings/home/actEditBanersImages",
                        allowedTypes: "png,gif,jpg,jpeg,ico",
                        multiple: false,
                        formData: {},
                        maxFileSize: 2108*2000,
                        fileName: "images[]",
                        afterUploadAll:function(){ 
                            if(  MEM.unload_photo !== false ){
                                $( DOM.parent + ' .ajax-file-upload-statusbar').remove();  
                                $( DOM.parent + ' .ev-photo_baner-edit').empty();
                                $( DOM.parent + ' .ev-photo_baner-edit').html('<img src="/uploads/baners/'+MEM.unload_photo+'" width="100%" />');
                            }
                        },
                        onSuccess: function( files, data, xhr ){
                            MEM.unload_photo = false;
                            var resp = $.parseJSON( xhr.responseText );
                            if(resp.err < 1){
                                MEM.unload_photo = resp.response.name;
                            }
                        }
                    });
                });
                
                $( DOM.parent + ' #blockGenSettings input[name="aliace"]').on('keyup', function(){                    
                    var item = $(this);
                    var value = item.val();
                    if( value.length > 4 && FNC.validate( 'string_plus', value ) !== false ){                        
                        if( MEM.keyUp ) MEM.keyUp.abort();
                        MEM.keyUp = $.ajax({
                            type: "post",
                            url: "/admin/settings/home/actStoreChangeName",
                            data: {
                                text : value 
                            },
                            dataType : 'json',
                            success: function( response ){
                                if( response.result == 1 ){
                                    item.css('outline', '4px solid green');
                                    $( DOM.parent + ' #blockGenSettings button[type="submit"]').removeAttr('disabled');
                                }else{
                                    item.css('outline', '4px solid red');
                                    $( DOM.parent + ' #blockGenSettings button[type="submit"]').prop('disabled',true);
                                }
                            }
                        });
                    }else{
                        item.css('outline', '4px solid red');
                        $( DOM.parent + ' #blockGenSettings button[type="submit"]').prop('disabled',true);
                    }
                });
                
                $( DOM.parent + ' #blockBanersSettings_add select[name="position"] > option').on('click', function(){                    
                    if( $( this).val() === 'categoryLeftBar' ){
                        $( DOM.parent + ' #blockBanersSettings_add #showBanerSelectBlock').removeClass('hidden');
                    }else{
                        $( DOM.parent + ' #blockBanersSettings_add #showBanerSelectBlock').addClass('hidden');
                    }                    
                }); 
                
                $( DOM.parent + ' #blockBanersSettings_edit select[name="position"] > option').on('click', function(){                    
                    if( $( this).val() === 'categoryLeftBar' ){
                        $( DOM.parent + ' #blockBanersSettings_edit #showBanerSelectBlock').removeClass('hidden');
                    }else{
                        $( DOM.parent + ' #blockBanersSettings_edit #showBanerSelectBlock').addClass('hidden');
                    }                    
                }); 
                
            },
            
            RUN : {
                
                moduleBaner : {
                        
                    setView : function ( type ){
                        var type = type || 'add';                        
                        if( type === 'add' ){
                            $( DOM.parent  + ' #addNewBaner').removeClass('hidden');
                            $( DOM.parent  + ' #editNewBaner').addClass('hidden');
                        }                        
                        if( type === 'edit' ){
                            $( DOM.parent  + ' #addNewBaner').addClass('hidden');
                            $( DOM.parent  + ' #editNewBaner').removeClass('hidden');
                        }                        
                    },
                    
                    selectItem : function  ( setItem ){
                        var setItem = setItem || false;
                        var th = this;
                        if( setItem !== false ){
                            this.setView('edit');                            
                            $.ajax({
                                type: "post",
                                url: "/admin/settings/home/selectBanerItem",
                                data: {
                                    id : setItem
                                },
                                dataType : 'json',
                                success: function( response ){
                                    MEM.unload_photo = false;
                                    MEM.unload_photo = response.image;
                                    th.setEditForm( response );
                                }
                            });
                        }                        
                    },
                    
                    setEditForm : function ( data ){
                        
                        var data = data || false;
                        if( data !== false ){
                            
                            $( DOM.parent + ' #blockBanersSettings_edit input[name="baner_id"]').val( data.id );
                            $( DOM.parent + ' #blockBanersSettings_edit input[name="title"]').val( data.title );
                            $( DOM.parent + ' #blockBanersSettings_edit input[name="link"]').val( data.link );
                            $( DOM.parent + ' #blockBanersSettings_edit input[name="wes"]').val( data.wes );
                            $( DOM.parent + ' #blockBanersSettings_edit input[name="date_start"]').val( data.modules.dateFormat.Start_date_mdY );
                            $( DOM.parent + ' #blockBanersSettings_edit input[name="date_end"]').val( data.modules.dateFormat.End_date_mdY );
                            $( DOM.parent + ' #blockBanersSettings_edit div.ev-photo_baner-edit').html( (( data.image ) ? '<img src="/uploads/baners/'+data.image+'" width="100%"/>': '[ Стандартный фон ]') );
                            
                            
                            $( DOM.parent + ' #blockBanersSettings_edit select[name="cat"] > option').removeAttr('selected');                            
                            $( DOM.parent + ' #blockBanersSettings_edit select[name="position"] > option').removeAttr('selected');
                            $( DOM.parent + ' #blockBanersSettings_edit select[name="position"] > option[value="'+data.position+'"]').prop('selected', true );
                            
                            $( DOM.parent + ' #blockBanersSettings_edit select[name="view"] > option').removeAttr('selected');    
                            $( DOM.parent + ' #blockBanersSettings_edit select[name="view"] > option[value="'+data.view+'"]').prop('selected', true );
                            
                            if( data.position === 'categoryLeftBar' ){
                                $( DOM.parent + ' #showBanerSelectBlock-edit' ).removeClass('hidden');
                                $( DOM.parent + ' #blockBanersSettings_edit select[name="cat"] > option[value="'+data.cat+'"]').prop('selected', true );
                            }
                            
                            
                        }
                        
                    },
                    
                    removePhoto : function ( selectorName ){
                        var src = $( DOM.parent + ' ' + selectorName ).find('img').attr('src');
                        $.ajax({
                            type: "post",
                            url: "/admin/settings/home/removeBanerImage",
                            data: {
                                photoName : src
                            },
                            success: function(){
                                $( DOM.parent + ' ' + selectorName ).empty();
                            }
                        });                        
                    }
                    
                }
                
            },
            
            FORM : {
                
                set_info : {
                    before : function ( data ){                        
                        var err = 0;
                        for (var key in data) {
                            var val = data [key];
                            if( val.name === 'title' ){
                                if( val.value.length < 1 ){
                                    err++;
                                    $( DOM.parent + ' form#blockInfo input[name="'+val.name+'"]').css('border', '1px solid red');
                                    setTimeout(function(){
                                        $( DOM.parent + ' form#blockInfo input[name="'+val.name+'"]').css('border', 'auto');
                                    },5000);
                                }
                            }
                        }
                        return ( err < 1 ) ? true : false;                  
                    },
                    
                    success : function ( response ){
                        FNC.alert( ( response.err > 0 ) ? 'error' : 'success', response.mess );
                    }
                    
                },                
                
                set_store_aliace : {
                    before : function ( data ){                        
                        var err = 0;
                        for (var key in data) {
                            var val = data [key];
                            if( val.name === 'aliace' ){
                                if( val.value.length < 1 ){
                                    err++;
                                    $( DOM.parent + ' form#blockGenSettings input[name="'+val.name+'"]').css('border', '1px solid red');
                                    setTimeout(function(){
                                        $( DOM.parent + ' form#blockGenSettings input[name="'+val.name+'"]').css('border', 'auto');
                                    },5000);
                                }
                            }
                        }
                        return ( err < 1 ) ? true : false;                  
                    },
                    
                    success : function ( response ){
                        FNC.alert( ( response.err > 0 ) ? 'error' : 'success', response.mess );
                        if( response.err < 1 ){
                            setTimeout(function(){
                                window.location.replace( response.redirect );
                            }, 2000);
                        }
                    }
                    
                },
                
                setBaners : {
                    
                    add : {
                        
                        requare : function ( data, success ){
                            console.log( 'requare' );
                            data.push({
                                name : 'image',
                                value : MEM.unload_photo                                
                            });
                            success( ( MEM.unload_photo !== false ) ? true : false , data );
                        },
                        
                        before : function ( data ){   
                            console.log( 'before' );                         
                            var err = 0;
                            for (var key in data) {
                                var val = data [key];
                                if( val.name === 'title' ){
                                    if( val.value.length < 1 ){
                                        err++;
                                        $( DOM.parent + ' form#blockBanersSettings_add input[name="'+val.name+'"]').css('border', '1px solid red');
                                        setTimeout(function(){
                                            $( DOM.parent + ' form#blockBanersSettings_add input[name="'+val.name+'"]').css('border', 'auto');
                                        },5000);
                                    }
                                }
                            }
                            return ( err < 1 ) ? true : false;                             
                        },
                        
                        success : function ( response ){
                            
                            FNC.alert( ( response.err > 0 ) ? 'error' : 'success', response.mess );
                            if( response.err < 1 ){
                                setTimeout(function(){
                                    window.location.reload();
                                }, 2000);
                            }
                            
                        }
                    },
                    
                    edit : {
                                               
                        requare : function ( data, success ){
                            data.push({
                                name : 'image',
                                value : MEM.unload_photo                                
                            });
                            success( ( MEM.unload_photo !== false ) ? true : false , data );
                        },
                        
                        before : function ( data ){   
                            console.log( 'before' );                         
                            var err = 0;
                            for (var key in data) {
                                var val = data [key];
                                if( val.name === 'title' ){
                                    if( val.value.length < 1 ){
                                        err++;
                                        $( DOM.parent + ' form#blockBanersSettings_edit input[name="'+val.name+'"]').css('border', '1px solid red');
                                        setTimeout(function(){
                                            $( DOM.parent + ' form#blockBanersSettings_edit input[name="'+val.name+'"]').css('border', 'auto');
                                        },5000);
                                    }
                                }
                            }
                            return ( err < 1 ) ? true : false;                             
                        },
                        
                        success : function ( response ){ 
                        
                            FNC.alert( ( response.err > 0 ) ? 'error' : 'success', response.mess );
                            if( response.err < 1 ){
                                setTimeout(function(){
                                    window.location.reload();
                                }, 2000);
                            }
                            
                        }
                        
                    },
                    
                    remove : function ( THIS ){
                        
                        var baner_id = $( THIS ).parents('form').find('input[name="baner_id"]').val() || false;
                        if( baner_id !== false ){
                            $.ajax({
                                type: "post",
                                url: "/admin/settings/home/actStoreRemoveBaner",
                                data: {
                                    baner_id : baner_id
                                },
                                dataType: 'json',
                                success: function( response ){
                                    FNC.alert( ( response.err > 0 ) ? 'error' : 'success', response.mess );
                                    if( response.err < 1 ){
                                        setTimeout(function(){
                                            window.location.reload();
                                        }, 2000);
                                    }
                                }
                            });
                        }
                        
                    }
                    
                },
                
                set_contacts : {
                    
                    success : function ( response ){                        
                        FNC.alert( ( response.err > 0 ) ? 'error' : 'success', response.mess );
                        if( response.err < 1 ){
                            setTimeout(function(){
                                window.location.reload();
                            }, 2000);
                        }
                        
                    }
                    
                },
                
                set_seo_params : {
                    
                    success : function ( response ){                        
                        FNC.alert( ( response.err > 0 ) ? 'error' : 'success', response.mess );
                        if( response.err < 1 ){
                            setTimeout(function(){
                                window.location.reload();
                            }, 2000);
                        }
                        
                    }
                    
                }
                
                
            }
        };
        
        window.Ev.stores_sett.init();
	};
	
	var e = setInterval(function(){
		if( window.CORE ){
			clearInterval( e );
			init();
		}
	}, 10);
	
});