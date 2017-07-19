<?php

    include "sapclasses/sap.php"; //Pastikan mempunyai library sapclasses pada directory project.

    public function getDataRFC() {
        $sap = new SAPConnection(); //Create new connection
       
        $sap->Connect("sapclasses/logon_dataclone.conf"); // Jika ingin melakukan koneksi ke clonning
       // $sap->Connect("sapclasses/logon_data.conf"); // Jika ingin melakukan koneksi ke prod


        if ($sap->GetStatus() == SAPRFC_OK)
            $sap->Open();

        if ($sap->GetStatus() != SAPRFC_OK) {
            $sap->PrintStatus();
            exit;
        }

        $fce = $sap->NewFunction("Z_ZCHR_GET_DWS"); //Masukkan nama RFC yang akan di panggil
        if ($fce == false) {
            $sap->PrintStatus();
            exit;
        }
       
        // Isi Parameter yang tersedia pada RFC tersebut 
        $fce->I_PERNR = $npeg;
        $fce->I_TANGGAL = $tanggal;
        $fce->Call();


        $SHIFT = array();
        if ($fce->GetStatus() == SAPRFC_OK) {
            $fce->T_PRESENSI->Reset(); // Reset parameter output yang terdapat pada RFC
            while ($fce->T_PRESENSI->Next()) { // Lakukan pengambilan data dengan menggunakan while
                $SHIFT[] = $fce->T_PRESENSI->row;
            }
        }

        $fce->Close();
        $sap->Close();

        return $SHIFT;

    }
        
?>

