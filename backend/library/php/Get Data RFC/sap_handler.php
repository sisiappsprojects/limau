<?php
	/*
	*	Title			: SAP Handler Class
	*	Design Team		: Dicky Member of Limau Team (SiSi)
	*	Spesical thx	: Pak Afandi, Pak Andi, Dev SiSi 
	*	Testing			: Limau Team (SiSi)
	*/

	Class Sap_handler{
		
		/*
		*	connectionFile 	= merupakan variabel yang menyimpan lokasi file username, password, dan SED
		*	connectionSAP	= merupakan variabel yang menyimpan konetifitas SAP
		*	functionConn	= merupakan variabel yang digunakan untuk menyimpan variabel fce
		*	functaionName	= merupakan variabel yang digunakan untuk meyimpan nama file
		*	libraryFile		= merupakan variabel yang menyimpan lokasi libary project
		*	resultArray		= merupakan variabel yang menyimpan hasil pencarian 
		*/

		public $connectionFile;
		public $connectionSAP;
		public $functionConn;
		public $functionName;
		public $libraryFile;
		public $resultArray;
		
		public function __construct($connectionPos, $libraryPos){
		    require_once $libraryPos;

		    $this->connectionFile 	= $connectionPos;
		    $this->libraryPos		= $libraryPos;
		}

		public function connectSAP(){
			$sap = new SAPConnection(); 					//membuka koneksi SAP
			$sap->Connect($this->connectionFile); 			//Jika ingin melakukan koneksi SAP
			if ($sap->GetStatus() == SAPRFC_OK){
				$sap->Open();		//SAP open
				$this->connectionSAP = $sap;
			}
			else {
				$sap->PrintStatus();
            	exit;
			}
		}


		/*
		*	Struktur input array SAP
		*	[nama kolom] = [value] 							(untuk yang tidak array)
		*	[nama kolom] = arrays('nama kolom'=> value) 	(untuk inputan yang bisa di append)
		*	ex input:
		*	array(
		*		"XVKORG" 	=> 7000							(memasukkan nilai org)
		*		"lR_DATU"	=> array(						(memasukkan nilai multiple)
		*			array(
		*				"SIGN"	=> 	'I'
		*				"INPUT"	=> 	'BT'
		*				"LOW"	=>	'20170130'
		*				"HIGH"	=>	'20170228'
		*			),
		*			array(
		*				"SIGN"	=> 	'I'
		*				"INPUT"	=> 	'EQ'
		*				"LOW"	=>	'20170330'
		*			)
		*		)
		*	)
		*/


		public function executeRFC($functionName, $arrayInput,$arrayOutput){
			$fce = $this->connectionSAP->NewFunction(strtoupper($functionName));
		     
			if ($fce == false ) {
		    	$this->connectionSAP->PrintStatus();
		     	exit;
		    }
		    else {
				$this->functionName = $functionName;
		    	$this->functionConn	= $fce;
			}

		    $this->executeInput($arrayInput);
			$this->executeOutput($arrayOutput);
			$this->functionConn->close();
			
			return $this->resultArray;
		}

		private function executeInput($arrayInput)
		{
			foreach($arrayInput as $k => $ai){
				if(!is_array($ai)) {
					$this->functionConn->$k = $ai;
				}
				else {
					foreach($ai as $a)
					{
						if(!is_array($a))
							continue;
						else {
							foreach($a as $i => $v){
								$this->functionConn->$k->row[$i] = $v;
							}
							
							$this->functionConn->$k->Append($this->functionConn->$k->row);
						}
					}
				}
			}

			$this->functionConn->call();
		}
		
		private function outputArray($functionName,$fieldName = null)
		{
			$tempArray = array();
			if($fieldName == null){
				while($this->functionConn->$functionName->Next()){
					array_push($tempArray,$this->functionConn->$functionName->row);
				}	 
			}
			else {
				continue;
			}
			
			return $tempArray;
		}
		
		private function executeOutput($arrayOutput)
		{
			if($this->functionConn->GetStatus() == SAPRFC_OK ){
				
				foreach($arrayOutput as $k => $ao)
				{
					if(!is_array($ao)){
						$this->functionConn->$ao->Reset();
						$this->resultArray[$ao] = $this->outputArray($ao);
					}
					else {
						continue;
					}
				}
			}
			else {
				$this->functionConn->PrintStatus();
			}
		}
	}

	$send 			= new Sap_handler('sapclasses/logon_data30.conf','sapclasses/sap.php');
	$send->connectSAP();
	$arrayInput 	= array('XVKORG'=>'7000',
		'XFLAG'=>'O',
		'LR_EDATU'=>array(
			array(
				"SIGN" 		=> 	'I',
				"OPTION"	=> 	'BT',
				"LOW"		=>	'20170101',
				"HIGH"		=>	'20170221'
			),
			array(
				"SIGN" 		=> 	'I',
				"OPTION"	=> 	'BT',
				"LOW"		=>	'20170301',
				"HIGH"		=>	'20170421'
			)
		)
	);
	
	$arrayOutput	= array('RETURN_DATA');
	$result = $send->executeRFC('Z_ZAPPSD_SO_OPEN2',$arrayInput,$arrayOutput);
	echo '<pre>';
	print_r($result);
	echo '</pre>';
?>