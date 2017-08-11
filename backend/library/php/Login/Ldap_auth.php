<?php
	
	/*
	*	Title			: LDAP AUTH PHP
	*	Design Team		: Dicky Member of Limau Team (SiSi)
	*/

	Class Ldap_auth{
		/*
		*	ipldap = variabel yang menyimpan ip server ldap
		*	domain = variabel yang menyimpan domain dari ldap
		* 	dcName = variabel yang menyimpan data DC
		*	username = variabel yang menyimpan username
		*	password = variabel yang menyimpan password
		*	bind 	= variabel yang menyimpan hasil bind
		* 	conn 	= variabel yang menyimpan koneksi
		*/

		public $ipldap;
		public $port;
		public $domain;
		public $dcName;
		public $username;
		public $password;
		public $result;
		public $bind;
		public $conn;

		public function __construct($ipldap,$port, $domain,$dcName){
		     /*
			*	Instansiasi Variabel
		    */
		    $this->ipldap = $ipldap;
		    $this->port   = $port;
		    $this->domain = $domain;
		    $this->dcName = $dcName;
		}

		private function checkLogon($username,$password,$option){
			$conn 		= ldap_connect($this->ipldap,$this->port);
			$ldaprdn	= $this->domain."\\".$username;

			//LDAP set option
			foreach ($option as $o => $v) {
				if(isset($v[0]) && isset($v[1]))
					ldap_set_option($conn, $v[0], $v[1]);
				else
					return array('ERROR','DATA OPTION TIDAK SESUAI');
			}

			//LDAP Connection
			$bind = @ldap_bind($conn, $ldaprdn, $password);
			
			if($bind){
				$this->bind = $bind;
				$this->conn = $conn;
			} else {
				return array('ERROR','ERROR LOGIN LDAP');
			}
		}

		public function login($username="",$password="",$option=array()){
			if($username == null || $password == null)
				return array('ERROR','USERNAME DAN PASSWORD TIDAK BOLEH KOSONG');	
			else{
				$resultLogin = $this->checkLogon($username,$password,$option);
				
				return $resultLogin;
			}
		}

		public function ldapFunction($listFunction){
			if(!$this->bind)
				return array('ERROR', 'BELUM LOGIN LDAP');

			$result = null;
			foreach ($listFunction as $lf => $v) {
				if(strtoupper($v[0]) == 'SEARCH')
					$result = ldap_search($this->conn,$dcName,$v[1]);
				elseif(strtoupper($v[0]) == 'SORT')
					$result = ldap_sort($this->conn,$result,$v[1]);
				else
					continue;
			}

			if($result == null)
				return array('ERROR','FUNGSI SEARCH BELUM ADA');
			else
				return array('SUKSES',ldap_get_entries($ldap, $result));
		}
	}


	/*
	*	TESTING CODE
	*/
	$ipldap 	= "ldap://10.15.3.120";
	$domain 	='SMIG';
	$dcName 	="dc=SMIG,dc=CORP";
	$port 		= 389;
	
	/*
	* LDAP AUTHORIZATION
	*/

	$ldap = new Ldap_auth($ipldap,$port, $domain,$dcName);
	$mess = $ldap->login('user0311','Semenindonesia2015');

	print_r($mess);

?>