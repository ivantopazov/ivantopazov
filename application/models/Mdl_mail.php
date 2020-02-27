<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mdl_mail extends CI_Model{
     
     protected $ot_kogo_from_name = false;
     protected $ot_kogo_from_email = false;
     
     protected $komu_to_name = false;
     protected $komu_to_email = false;
     
     protected $subject = false;
     protected $message_text = false;
     
     
     protected $hea_mime = 'MIME-Version: 1.0';
     protected $hea_content_type = 'text/html';
     protected $hea_charset = 'utf-8';
     
     
     public function set_ot_kogo_from($email, $name = ''){
        $this->ot_kogo_from_email = $email;
        $this->ot_kogo_from_name = (empty($name))? 'Имя не указанно' : $name ;
     }
     
     public function set_komu_to($email, $name = ''){
        $this->komu_to_email = $email;
        $this->komu_to_name = (empty($name))? 'Имя не указанно' : $name ;
     }
     
     public function set_tema_subject($subject_text){
        $this->subject = (empty($subject_text))? 'Тема не указана' : $subject_text ;
     }
     
     public function set_tema_message( $message_text = '' ){
        $this->message_text = (empty($message_text))? 'Текст не указан' : $message_text ;
     }
     
     
     public function send(){
        
        $to = "{$this->komu_to_name} <{$this->komu_to_email}>"; 
        $subject = $this->subject;
        $message = $this->message_text;
		
        $headers= "{$this->hea_mime}\r\n";
        $headers .= "Content-type: {$this->hea_content_type}; charset={$this->hea_charset}\r\n";
        $headers .= "From: {$this->ot_kogo_from_name} <{$this->ot_kogo_from_email}>\r\n";
         
        mail($to, $subject, $message, $headers);
     }
    
    
}

?>