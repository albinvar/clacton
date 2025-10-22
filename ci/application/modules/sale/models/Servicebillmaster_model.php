<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Servicebillmaster_model extends MY_Model {

    public $_table               = 'ub_servicebillmaster';
    public $protected_attributes = array('sb_servicebillid');
    public $primary_key          = 'sb_servicebillid';

    public $selectedfields = 'sb_servicebillid, sb_billno, sb_date, sb_time, sb_existcustomer, sb_customerid, sb_customername, sb_phone, sb_customergst, sb_place, sb_billdate, sb_freight, sb_totalamount, sb_grandtotal, sb_paymethod, sb_pagesize, sb_updatedby, sb_updatedon, sb_isactive';

    public function getbilldetailsbyid($billid)
    {
    	$this->db->select($this->selectedfields);
        $this->db->from('ub_servicebillmaster');
        $this->db->where('sb_servicebillid', $billid);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return FALSE;
    }

    public function getretailbilllist($buid, $fromdate, $todate)
    {
    	$this->db->select($this->selectedfields);
        $this->db->from('ub_servicebillmaster');
        $this->db->where('sb_buid', $buid);
        $this->db->where('DATE(sb_date) >=', $fromdate);
        $this->db->where('DATE(sb_date) <=', $todate);
        $this->db->where('sb_isactive', 0);
        $this->db->order_by('sb_servicebillid', 'desc');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return FALSE;
    }
    public function getnextservicebillno($buid)
    {
        $this->db->select('sb_billno');
        $this->db->from('ub_servicebillmaster');
        $this->db->where('sb_buid', $buid);
        $this->db->order_by('sb_servicebillid', 'desc');
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $purdet = $query->row();
            return $purdet->sb_billno + 1;
        }else{
            return 1;
        }
    }
}
?>