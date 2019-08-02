$(function(){
	
	var init = function(){
		
		window.Ev.cats_treeList = {
			
            treeUploads : false,
            
            init : function(){
                console.log( 'dataTree :: ', Ev.cats_treeList.treeUploads );
                this.treeDrevoCreate();
                this.uploads_photos();
            },
            
            uploads_photos : function(){
                
                $.getScript('/addons/scripts/plugins/loadfiles/js/jquery.uploadfile.js', function(){
                    $( DOM.parent + ' .uploadfile_add_cats').uploadFile({
                        url:"/admin/catalog/cats/actAddCategoryImages",
                        allowedTypes: "png,gif,jpg,jpeg",
                        multiple: true,
                        formData: {},
                        maxFileSize: 2108*2000,
                        fileName: "images[]",
                        afterUploadAll:function(){
                            $( DOM.parent + ' .ajax-file-upload-statusbar').remove();
                            TPL.GET_TPL('pages/admin/catalog/addCatsListImages', { image: MEM.unload_photo }, function( e ){
                                $( DOM.parent + ' .ev-uploadfile_add_cats_list' ).html( e );
                            });
                        },
                        onSuccess: function( files, data, xhr ){
                            var resp = $.parseJSON( xhr.responseText );
                            if(resp.err < 1){
                                MEM.unload_photo = resp.response;
                            }
                        }
                    });
                    
                });
                
                $.getScript('/addons/scripts/plugins/loadfiles/js/jquery.uploadfile.js', function(){
                    $( DOM.parent + ' .uploadfile_edit_cats').uploadFile({
                        url:"/admin/catalog/cats/actEditCategoryImages",
                        allowedTypes: "png,gif,jpg,jpeg",
                        multiple: true,
                        formData: {},
                        dynamicFormData : function(){
                            var src = $( DOM.parent + ' .ev-uploadfile_edit_cats_list img' ).attr('scr');
                            var cid = $( DOM.parent + ' input[name="cid"]' ).val();
                            return ( src ) ? {
                                alt : src,
                                cid : cid
                            } : {
                                alt : 0,
                                cid : cid
                            };
                        },
                        maxFileSize: 2108*2000,
                        fileName: "images[]",
                        afterUploadAll:function(){
                            $( DOM.parent + ' .ajax-file-upload-statusbar').remove();
                            TPL.GET_TPL('pages/admin/catalog/editCatsListImages', { image: MEM.unload_photo_edit }, function( e ){
                                $( DOM.parent + ' .ev-uploadfile_edit_cats_list' ).html( e );
                            });
                        },
                        onSuccess: function( files, data, xhr ){
                            var resp = $.parseJSON( xhr.responseText );
                            if(resp.err < 1){
                                MEM.unload_photo_edit = resp.response;
                            }
                        }
                    });
                    
                });
                
            },
            
            getUploadsChilds : function ( parent_node ){
                var parent_node = parent_node || '0';                
                $.ajax({
                    type: "post",
                    url: "/admin/catalog/cats/getStoreCatsData",
                    data: {
                        'parent_node' : parent_node
                    },
                    dataType : 'json',
                    success: function( response ){
                        Ev.cats_treeList.treeUploads = response;
                    }
                });
            },
            
            treeDrevoCreate : function(){                
                $('#treeCats').jstree({
                    'core':{
                        'data' : {
                            'url' : function (node) {
                              return node.id === '#' ?
                                '/admin/catalog/cats/getStoreCatsData/0' :
                                '/admin/catalog/cats/getStoreCatsData/' + node.id;
                            },
                            'dataType' : 'json'
                        }
                    }
                });                
                this.EventTreeChange();                
            },
            
            EventTreeChange : function(){
                $('#treeCats').on("changed.jstree", function (e, data) {
                    Ev.cats_treeList.TreeItemChange( data.node  );
                });
            },
            
            TreeItemChange : function( data ){
                console.log( 'TreeItemChange :: ', data);
                
                $( DOM.parent + ' #addChild input[name="parent_id"]').val( ( data.id === 'j1_1' ) ? '0' : data.id );
                
                if( data.id !== 'j1_1' ){
                    $( DOM.parent + ' #removeChild input[name="cid"]').val( data.id );
                    $( DOM.parent + ' #editChild input[name="cid"]').val( data.id );
                    $( DOM.parent + ' #editChild input[name="parent_id"]').val( data.original.labels.parent_id );
                    $( DOM.parent + ' #editChild input[name="name"]').val( data.original.labels.name );
                    $( DOM.parent + ' #editChild input[name="aliase"]').val( data.original.labels.aliase );
                    $( DOM.parent + ' #editChild textarea[name="description"]').val( data.original.labels.description );
                    $( DOM.parent + ' #editChild input[name="seo_title"]').val( data.original.labels.seo_title );
                    $( DOM.parent + ' #editChild textarea[name="seo_desc"]').val( data.original.labels.seo_desc );
                    $( DOM.parent + ' #editChild input[name="seo_keys"]').val( data.original.labels.seo_keys );
                    
                    
                    if( data.original.labels.image !== null ){
                        
                        TPL.GET_TPL('pages/admin/catalog/editCatsListImages', { image: data.original.labels.image }, function( e ){
                            $( DOM.parent + ' #editChild .ev-uploadfile_edit_cats_list' ).html( e );
                        });
                    }else{
                        $( DOM.parent + ' #editChild .ev-uploadfile_edit_cats_list' ).empty();
                    }
                    
                }else{
                    $( DOM.parent + ' form#editChild')[0].reset();
                    $( DOM.parent + ' #removeChild input[name="cid"]').val( 0 );
                    $( DOM.parent + ' #editChild input[name="cid"]').val( 0 );
                }
            },

            catAdd : {
                form_requare : function ( data, success ){                    
                    data.push({
                        name : 'image',
                        value : MEM.unload_photo                                
                    });
                    success( true, data );
                },
                form_before : function( data ){
                    var err = 0;
                    
                    if( FNC.in_array( 'name', data, 'name', false ) ){
                        var name = FNC.in_array( 'name', data, 'name', true ); 
                        if( !FNC.validate( 'string_cylric_plus', name.value ) ){
                            $( DOM.parent + ' form#addChild input[name="name"]').css( 'outline', '1px solid red' );
                            setTimeout(function (){
                                $( DOM.parent + ' form#addChild input[name="name"]').css( 'outline', 'none' );
                            },1000);
                            err++;
                        } 
                    }
                    
                    if( FNC.in_array( 'aliase', data, 'name', false ) ){
                        var aliase = FNC.in_array( 'aliase', data, 'name', true ); 
                        if( !FNC.validate( 'string_plus', aliase.value ) ){
                            $( DOM.parent + ' form#addChild input[name="aliase"]').css( 'outline', '1px solid red' );
                            setTimeout(function (){
                                $( DOM.parent + ' form#addChild input[name="aliase"]').css( 'outline', 'none' );
                            },1000);
                            err++;
                        } 
                    }
                    if( err < 1 ){
                        $( DOM.parent + ' form#addChild input[type="submit"]').prop('disabled', 'true');
                        $( DOM.parent + ' form#addChild input[type="submit"]').val( 'Идет отправка...' );
                    }
                    
                    return ( err < 1 ) ? true : false;
                },
                
                form_success : function( response ){
                    if( response.err < 1 ){
                        FNC.alert('success', response.mess );
                        setTimeout(function(){
                            window.location.reload();
                        }, 3000);
                    }else{
                        FNC.alert('error', response.mess );
                    }
                    $( DOM.parent + ' form#addChild button[type="submit"]').removeAttr('disabled');
                    $( DOM.parent + ' form#addChild button[type="submit"]').text( 'Добавить' );
                }
			},

            catEdit : {
                
                form_requare : function ( data, success ){                    
                    if( MEM['unload_photo_edit'] ){
                        data.push({
                            name : 'image',
                            value : MEM.unload_photo_edit                                
                        });
                    }
                    success( true, data );
                },
                
                form_before : function( data ){
                    var err = 0;
                    
                    if( FNC.in_array( 'name', data, 'name', false ) ){
                        var name = FNC.in_array( 'name', data, 'name', true ); 
                        if( !FNC.validate( 'string_cylric_plus', name.value ) ){
                            $( DOM.parent + ' form#editChild input[name="name"]').css( 'outline', '1px solid red' );
                            setTimeout(function (){
                                $( DOM.parent + ' form#editChild input[name="name"]').css( 'outline', 'none' );
                            },1000);
                            err++;
                        } 
                    }
                    
                    if( err < 1 ){
                        $( DOM.parent + ' form#editChild input[type="submit"]').prop('disabled', 'true');
                        $( DOM.parent + ' form#editChild input[type="submit"]').val( 'Идет отправка...' );
                    }
                    
                    return ( err < 1 ) ? true : false;
                },
                
                form_success : function( response ){
                    if( response.err < 1 ){
                        FNC.alert('success', response.mess );
                        setTimeout(function(){
                            window.location.reload();
                        }, 3000);
                    }else{
                        FNC.alert('error', response.mess );
                    }
                    $( DOM.parent + ' form#editChild button[type="submit"]').removeAttr('disabled');
                    $( DOM.parent + ' form#editChild button[type="submit"]').text( 'Обновить' );
                }
			}
		};
        
        
        Ev.cats_treeList.getUploadsChilds( 0 );
        var r = setInterval(function(){
            if( Ev.cats_treeList.treeUploads !== false ){
                clearInterval( r );
                Ev.cats_treeList.init();
            }
        }, 10);
        
        
	};
	
	var e = setInterval(function(){
		if( window.CORE ){
			clearInterval( e );
			init();
		}
	}, 10);
	
});