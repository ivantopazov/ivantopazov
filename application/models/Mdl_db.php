<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mdl_db extends CI_Model
{
    
    function _query( $sql ){
        $query = $this->db->query($sql);
        $resli = $query->result_array();
        return $resli;
    }
    
    function _query_db($table)    {
        $query = $this->db->get($table);
        $resli = $query->row_array();
        return $resli;
    }
    
    function _query_db_2($table,$a,$b)    {
        $this->db->where($a,$b);
        $query = $this->db->get($table);
        $resli = $query->row_array();
        return $resli;
    }
    
    function _all_query_db($table)    {
        $query = $this->db->get($table);
        $resli = $query->result_array();
        return $resli;
    }
    
    function _all_query_db_2($table,$a,$b)    {
        $this->db->where($a,$b);
        $query = $this->db->get($table);
        $resli = $query->result_array();
        return $resli;
    }
    
    function _all_query_db_3($table,$a,$b,$c,$d)    {
        $this->db->where($a,$b);
        $this->db->where($c,$d);
        $query = $this->db->get($table);
        $resli = $query->result_array();
        return $resli;
    }
    
    function _update_db($table,$a,$b,$mass)    {
        $this->db->where($a,$b);
        $this->db->update($table,$mass);
    }
       
}

?>