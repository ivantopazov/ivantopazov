<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Images{
    
    /*
        file_item( './uploads/products/file.jpg', 'newName.jpg' )
    */
    function file_item( $item_image = false, $new_name = false ){
        
        $file = [];
        
        $file['tmp_name'] = $item_image;
        $file['name'] = $new_name;
        
        $size_image = @getimagesize( $item_image );
        
        $file['width'] = $size_image[0];
        $file['height'] = $size_image[1]; 
        
        if($file['width'] == '' or $file['width'] == 0){$file['width'] = imagesx($file['tmp_name']);}
        if($file['height'] == '' or $file['height'] == 0){$file['height'] = imagesy($file['tmp_name']);} 
          
        return $file;
        
    }
    
    function files_array( $FILES = array(), $_index = 'userfile', $new_name = false ){
        
        $CI = &get_instance();
        $CI->load->helper("string");
		
		if( !$new_name ){
			$new_name = random_string('alnum', 6)."_".time();
		}
		
        $fname = $FILES[$_index]['name'];
        $file['tmp_name'] = $FILES[$_index]['tmp_name'];
		
        $ext = strtolower(strrchr(basename($fname), "."));
        $file['name'] = $new_name.$ext;
        
        $size_image = @getimagesize( $file['tmp_name'] );
        
        $file['width'] = $size_image[0];
        $file['height'] = $size_image[1]; 
        
        if($file['width'] == '' or $file['width'] == 0){$file['width'] = imagesx($file['tmp_name']);}
        if($file['height'] == '' or $file['height'] == 0){$file['height'] = imagesy($file['tmp_name']);} 
          
        return $file;
		
    }


	/** 
     *  Принимает фотки в формате JPG, PNG, GIF
     *  Копирует файлы во временную директорию
     *  Уменьшает размер файла и создает из него изображение в формате *.JPG
     *  =====================================================================
     *  ПАРАМЕТРЫ:
     *      $file -     Массив $_FILES() + array(height + width);
     *      $path -     Временная папка для хранения фоток              "./img/users/test/" 
     *      $folder -   Папка для вывода файлов                         "./img/users/"
     *      $f_name -   Имя файла вывода( без расширения)               "1"
     *      $qua =      Качество изображения                            100
     *      $size =     Необходимая ширина фотографии                   500
     *      $min_size   Минимальная ширина фотогравии                   100
     */
    function resize_jpeg($file, $path, $folder, $f_name, $qua, $size, $min_size){
         //   ИЗМЕНЕНИЕ РАЗМЕРА фото и смена ФОРМАТА ФОТО НА JPEG.
        if(preg_match('/[.](jpg)|(JPG)|(jpeg)|(JPEG)|(gif)|(GIF)|(png)|(PNG)$/',$file['name'])){                 
           
           @copy($file['tmp_name'], $path.$file['name']);
           $filename = $path.$file['name'];
           
            if(preg_match('/[.](GIF)|(gif)$/',$filename)){
                $im    = imagecreatefromgif($filename) ; 
            }
            
            if(preg_match('/[.](PNG)|(png)$/',$filename)) {
                $im =    imagecreatefrompng($filename) ;
            }
            
            if(preg_match('/[.](JPG)|(jpg)|(jpeg)|(JPEG)$/', $filename)) {
                $im =    imagecreatefromjpeg($filename); 
            }   
            
            if($file['width'] < $min_size){ 
                @unlink($path.$file['name']);
                return "error_min_size"; 
                exit();
            }else{
                
                if( $size === 'max' ){
                    $size = $file['width'];
                }
                
                $user_width = $size; // необходимая ширина
                                    $ddss = $file['height'] * $user_width;
                $user_height =   $ddss / $file['width']; // Необходимая высота
               
                $dest = imagecreatetruecolor( $user_width, $user_height );  
               
               
                $src_image = $im; // прямоугольный участок из
                $src_x = 0; // на координатах src_x
                $src_y = 0; // на координатах src_y
                $src_w = $file['width']; // с шириной 
                $src_h = $file['height']; // высотой
                
                $dst_image = $dest; // Куда вывести
                $dst_x = 0; // на координатах src_x
                $dst_y = 0; // на координатах src_y
                $dst_w = $user_width; // с шириной
                $dst_h = $user_height; // высотой
                
                imagecopyresampled($dst_image,$src_image,$dst_x,$dst_y,$src_x,$src_y,$dst_w,$dst_h,$src_w,$src_h);
                
                if($path == $folder){
                    @unlink($filename);
                }
                
                
                imagejpeg($dst_image, $folder.$f_name.".jpg", $qua);
                imagedestroy($dst_image); 
                
                return $f_name.".jpg";
                exit();
            }
       }
       else{
            @unlink($path.$file['name']);
            return "error_ext"; 
            exit();
        }
    }


    function imageresize($outfile,$infile,$neww,$newh,$quality) {
        
        $im=imagecreatefromjpeg($infile);
        $k1=$neww/imagesx($im);// 400 / 430 = '0,9302325581395349'
        $k2=$newh/imagesy($im);// 400 / 600 = '0,6666666666666667'
        $k=$k1>$k2?$k2:$k1; // '0,6666666666666667'

        $w=intval(imagesx($im)*$k); // 430 * 0,6666666666666667 = 286,6666666666667
        $h=intval(imagesy($im)*$k); // 600 * 0,6666666666666667 = 400
        
        $xl = ( $w < $neww )?(($neww-$w)/2):0;
        $yt = ( $h < $newh )?(($newh-$h)/2):0;
                
        
        $im1=imagecreatetruecolor($neww,$newh);
        imagefill($im1, 0, 0, 0xFFFFFF );
        imagecopyresampled($im1,$im,$xl,$yt,0,0,$w,$h,imagesx($im),imagesy($im));

        imagejpeg($im1,$outfile,$quality);
        imagedestroy($im);
        imagedestroy($im1);
        
    }


	
}	