<?php

// called from login_library
Class Verify_login_model extends CI_Model
{
	
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	
	function buyer_login($str_email, $str_password)
	{
		$this -> db -> select('id, str_email, str_password');
		$this -> db -> from('props_buyers_users');
		$this -> db -> where('str_email', $str_email);
		$this -> db -> where('str_password', $str_password);
		$this -> db -> where('bool_permit_access', 1);
		$this -> db -> where('bool_has_paid', 1);
		$this -> db -> where('bool_permit_access', 1);
		$this -> db -> limit(1);

		$query = $this -> db -> get();

		if($query -> num_rows() == 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}


	function buyer_lite_login($str_email, $str_password)
	{
		$this -> db -> select('id, str_email, str_password');
		$this -> db -> from('props_buyers_users');
		$this -> db -> where('str_email', $str_email);
		$this -> db -> where('str_password', $str_password);
		$this -> db -> limit(1);
	
		$query = $this -> db -> get();
	
		if($query -> num_rows() == 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	
	function seller_login($str_email, $str_password)
	{
		$this -> db -> select('id, str_email, str_password');
		$this -> db -> from('props_sellers_users');
		$this -> db -> where('str_email', $str_email);
		$this -> db -> where('str_password', $str_password);
		$this -> db -> where('bool_permit_access', 1);
		$this -> db -> limit(1);
	
		$query = $this -> db -> get();
	
		if($query -> num_rows() == 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	
	function customer_login($str_email, $str_password) // this is for the cloud app
	{
		$this -> db -> select('cloud_customers.id, cloud_emails.str_email, cloud_customers.str_password');
		$this -> db -> from('cloud_customers');
		$this -> db -> join('cloud_emails', 'cloud_customers.int_email_id = cloud_emails.id');
		$this -> db -> where('cloud_emails.str_email', $str_email);
		$this -> db -> where('cloud_customers.str_password', $str_password);
		$this -> db -> where('cloud_customers.bool_permit_access', 1);
		$this -> db -> limit(1);
	
		$query = $this -> db -> get();
	
		if($query -> num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			return 0;
		}
	}	
	
	
	public function record_login_customer($str_email)
	{
		$this->db->set('str_email', $str_email);
		$this->db->set('int_time', time());
		$this->db->insert('cloud_login_attempts_customers');
	}
	
	public function clean_up_login_attempts()
	{
		// delete all entries greater than 24 hours ago
		$int_yesterday = time() - (60*60*24);
		$this->db->where('int_time <', $int_yesterday);
		$this->db->delete('cloud_login_attempts_customers');
	}
	
	public function get_login_attempts_customers($str_email)
	{
		// returns number of login attempts in the last 24 hours for the selected login
		$int_yesterday = time() - (60*60*24);
		$this -> db -> from('cloud_login_attempts_customers');
		$this -> db -> where('str_email', $str_email);
		$this -> db -> where('bool_admin_bypass', 0);
		$this -> db -> where('int_time >', $int_yesterday);
		$query = $this -> db -> get();
		return $query;
	}
	
	function admin_asst_login($str_email, $str_password)
	{
		$this -> db -> select('id, str_email, str_password');
		$this -> db -> from('props_admin_asst_users');
		$this -> db -> where('str_email', $str_email);
		$this -> db -> where('str_password', $str_password);
		$this -> db -> limit(1);

		$query = $this -> db -> get();

		if($query -> num_rows() == 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	
	function admin_login($str_email, $str_password)
	{
		$this -> db -> select('id, str_email, str_password');
		$this -> db -> from('props_admins');
		$this -> db -> where('str_email', $str_email);
		$this -> db -> where('str_password', $str_password);
		$this -> db -> limit(1);

		$query = $this -> db -> get();

		if($query -> num_rows() == 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	
	public function record_login($str_email, $int_group, $int_time)
	{

$this->db->set('str_email', $str_email);
		$this->db->set('int_time', $int_time);
		switch ($int_group)
		{
			case 1:
				$this->db->insert('props_login_attempts_admin');
				break;
			case 2:
				$this->db->insert('props_login_attempts_admin_assts');
				break;
			case 3:
				$this->db->insert('props_login_attempts_buyers');
				break;
			case 4:
				$this->db->insert('props_login_attempts_sellers');
				break;		
		}
	}
	
	public function get_login_attempts_admin($str_email, $int_yesterday)
	{
		// returns number of login attempts in the last 24 hours for the selected login
		$this -> db -> from('props_login_attempts_admin');
		$this -> db -> where('str_email', $str_email);
		$this -> db -> where('bool_admin_bypass', 0);
		$this -> db -> where('int_time >', $int_yesterday);
		$query = $this -> db -> get();
		return $query;
	}

	public function get_login_attempts_admin_assts($str_email, $int_yesterday)
	{
		// returns number of login attempts in the last 24 hours for the selected login
		$this -> db -> from('props_login_attempts_admin_assts');
		$this -> db -> where('str_email', $str_email);
		$this -> db -> where('bool_admin_bypass', 0);
		$this -> db -> where('int_time >', $int_yesterday);
		$query = $this -> db -> get();
		return $query;
	}
	
	public function get_login_attempts_buyers($str_email, $int_yesterday)
	{
		// returns number of login attempts in the last 24 hours for the selected login
		$this -> db -> from('props_login_attempts_buyers');
		$this -> db -> where('str_email', $str_email);
		$this -> db -> where('bool_admin_bypass', 0);
		$this -> db -> where('int_time >', $int_yesterday);
		$query = $this -> db -> get();
		return $query;
	}
	
	
	public function get_login_attempts_sellers($str_email, $int_yesterday)
	{
		// returns number of login attempts in the last 24 hours for the selected login
		$this -> db -> from('props_login_attempts_sellers');
		$this -> db -> where('str_email', $str_email);
		$this -> db -> where('bool_admin_bypass', 0);
		$this -> db -> where('int_time >', $int_yesterday);
		$query = $this -> db -> get();
		return $query;
	}
}