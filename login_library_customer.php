<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login_library_customer
{
	private $obj_CI_super;
	static $int_max_failed_login_attempts;
	
	public function __construct()
	{
		$this->obj_CI_super =& get_instance();			
		$this->obj_CI_super->load->model('verify_login_model','',TRUE);
		$this->obj_CI_super->load->library('form_validation');
		$this->obj_CI_super->load->library('session');
		$this->int_max_failed_login_attempts = 5;
	}
	
	function first_login_attempt()
	{
	
		$bool_submitted = $this->obj_CI_super->input->post('bool_submitted_login');
		if((isset($bool_submitted)) && ($bool_submitted))
		{
			return 0;
		}
		else
		{
			return TRUE;
		}
	}
	
	function set_login_cookie()
	{
		$arr_cookie = array(
				'name'   => 'cookie_last_update_login',
				'value'  => 1,
				'expire' => '601'
		);
	
		$this->obj_CI_super->input->set_cookie($arr_cookie);
	}
	
	Function curr_sess_id_matches_orig($str_email, $str_curr_sess_id)
	{
		$orig_sess_id = $this->obj_CI_super->record_login_model->get_orig_sess_id($str_email);
		if ($curr_sess_id == $orig_sess_id)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	function login_time_limit_expired()
	{
		if ($this->obj_CI_super->input->cookie('login_cookie'))
		{
			return TRUE;
		}
		else
		{
			return 0;
		}
	}
	
	function set_session_id($str_email)
	{
		$str_session_id = $this->obj_CI_super->session->userdata('session_id');
		$this->obj_CI_super->record_login_model->record_last_update_time($str_email, $str_session_id);
	}
	
	function login_cookie_found()
	{
		$bool_cookie = $this->obj_CI_super->input->cookie(cookie_last_update_login, TRUE);
		if ($bool_cookie)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	function validate_IP($arr_str_IPs)
	{
		$curr_ip = $this->obj_CI_super->input->ip_address();
		$bool_acceptable_ip = 0;
		foreach($arr_str_IPs as $str_IP)
		{
			$str_pattern = $str_IP.'[0-9]{1,3}';
			if (preg_match($str_pattern, $curr_ip))
			{
				$bool_acceptable_ip = 1;
			}
		}
		return $bool_acceptable_ip;
	}
	

	public function get_login_attempts_remaining($str_email)
	{
		$query = $this->obj_CI_super->verify_login_model->get_login_attempts_customers($str_email);		
		$int_login_attempts = $query->num_rows();
		if ($int_login_attempts < $this->int_max_failed_login_attempts)
		{
			return ($this->int_max_failed_login_attempts - $int_login_attempts);
		}
		else
		{
			return 0;
		}
	}

	public function password_verified()
	{
		//Field validation succeeded.  Validate against database
		$str_email = $this->obj_CI_super->input->post('str_email');
		$str_password = $this->obj_CI_super->input->post('str_password');
	
		$row = $this->obj_CI_super->verify_login_model->customer_login($str_email, $str_password);
	
		if($row)
		{
			$sess_array = array();
			$sess_array = array(
				'id' => $row->id,
				'str_email' => $row->str_email
			);
			$this->obj_CI_super->session->set_userdata('customer_logged_in', $sess_array);
			$this->obj_CI_super->session->set_userdata('int_customer_id', $row->id);
			return TRUE;
		}
		else
		{
			return 0;
		}
	}
	
	function return_to_login($data)
	{
		$data['bool_login_failed'] = true;
		$data['str_login_failed_msg'] = 'Username or password is not valid.  Please try again, or contact the administrator<br>';
		$this->load_login_page($data);	
	}
	
	
	function load_login_page($str_page_path, $bool_login_failed, $int_login_attempts_remaining, $bool_multiple_login_problem = 0)
	{
		$data['title'] = 'Client login page';
		$data['str_page_path'] = $str_page_path;

		if ($bool_login_failed)
		{
			$str_login_failed_msg = 'Username or password is not valid, or you do not have permission to view this page.  Please try again, or contact the administrator<br>';
		}
		else
		{
			$str_login_failed_msg = '';
		}
		$data['bool_login_failed'] = $bool_login_failed;
		$data['str_login_failed_msg'] = $str_login_failed_msg;
		$data['int_login_attempts_remaining'] = $int_login_attempts_remaining;
		if ($int_login_attempts_remaining > 0)
		{
			$data['int_login_attempts_remaining_msg'] = 'This email address has '. $int_login_attempts_remaining .' failed login attempts remaining.';
		}
		else
		{
			$data['int_login_attempts_remaining_msg'] = 'You have exceeded the permitted number of login attempts for this email address.  Please contact the admininstrator to unlock your account.';
		}
		$data['str_float_table_bg_color'] = $this->obj_CI_super->common_vars->get_float_table_bg_color();
		$data['str_left_panel_spacer_size'] = $this->obj_CI_super->common_vars->get_left_panel_spacer_size();
		$data['str_right_panel_spacer_size'] = $this->obj_CI_super->common_vars->get_right_panel_spacer_size();
		$this->obj_CI_super->load->view('templates/wp_header', $data);
		$this->obj_CI_super->load->view('templates/title_view');
		$this->obj_CI_super->load->view('templates/floating_section_header');
		$this->obj_CI_super->load->view('templates/floating_section_transition');
		$this->obj_CI_super->load->view('cloud/login_customer_view', $data);
		$this->obj_CI_super->load->view('templates/floating_section_transition_two');
		$this->obj_CI_super->load->view('templates/floating_section_right_footer');
		$this->obj_CI_super->load->view('templates/floating_section_footer');
		$this->obj_CI_super->load->view('templates/wp_footer');		
	}
	
	
	function check_login($str_page_path)
	{
		if(!($this->obj_CI_super->session->userdata('customer_logged_in')))
		{
			$str_email = $this->obj_CI_super->input->post('str_email');
			$this->obj_CI_super->verify_login_model->clean_up_login_attempts();
			$this->obj_CI_super->verify_login_model->record_login_customer($str_email);
			$int_login_attempts_remaining = $this->get_login_attempts_remaining($str_email);
			if ($this->first_login_attempt())
			{
				$this->load_login_page($str_page_path, FALSE, $this->int_max_failed_login_attempts);
			}
			else  // if login attempt already made
			{
				$this->obj_CI_super->form_validation->set_rules('str_email', 'Email', 'trim|required|xss_clean');
				$this->obj_CI_super->form_validation->set_rules('str_password', 'Password', 'trim|required|xss_clean');
	
				if($this->obj_CI_super->form_validation->run() == FALSE)
				{
					//Field validation failed.  User redirected to login page
					$this->load_login_page($str_page_path, FALSE, $int_login_attempts_remaining, 4);
				}
				else // passed validation
				{
					if ($int_login_attempts_remaining < 1)
					{
						$this->load_login_page($str_page_path, FALSE, $int_login_attempts_remaining, 4);
					}
					else 
					{		
						if (!($this->password_verified()))
						{
							$this->load_login_page($str_page_path, TRUE, $int_login_attempts_remaining, 4);
						} // end if ! user_pass_verified
						else // if user and password authernticated, load intended page
						{
							$this->obj_CI_super->input->post('bool_submitted');
							return true;
						}// end else - password not verified
					}// end else, if greater thatn 1 login attempt remaining
				}  // end else - passed validation
			} // end else, if bool submitted, if login attempt made
		}  // end if user not looged in
		else  // if session var present, and user is logged in
		{
			return true;
		}
	}	
}
