<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['next_list_date'];

    include "database_access.php";

    if($connection) {
        $statement = $connection->prepare('Select * from civil_t where date_next_list = :next_date AND purpose_today = :purpose_id');
        $statement->execute(['next_date' => $date, 'purpose_id' => 2]);
        $admissionRecords = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statement->execute(['next_date' => $date, 'purpose_id' => 4]);
        $orderRecords = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statement->execute(['next_date' => $date, 'purpose_id' => 8]);
        $hearingRecords = $statement->fetchAll(PDO::FETCH_ASSOC);


        require('fpdf/fpdf.php');

        class PDF extends FPDF
        {
            protected $B = 0;
            protected $I = 0;
            protected $U = 0;
            protected $HREF = '';

            function Header()
            {
                $date = $_POST['next_list_date'];
                $header = strtoupper("High Court of Jammu & Kashmir At Srinagar");
                $judgeName = "HON'BLE MR. JUSTICE ALI MOHAMMAD  MAGREY";
                $humanDate = date("l jS, F Y", strtotime($date));
                $this->SetFont('Arial','',11);
                $this->Cell(0, 5, $header, 0, 1, 'C');
                $this->Cell(0, 5, $judgeName, 0, 1, 'C');
                $this->Cell(0, 5, $humanDate, 0, 1, 'C');
                $this->Ln(7);
                $this->SetFont('Arial', '', 10);
            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
            }

            function WriteHTML($html)
            {
                // HTML parser
                $html = str_replace("\n",' ',$html);
                $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
                foreach($a as $i=>$e)
                {
                    if($i%2==0)
                    {
                        // Text
                        if($this->HREF)
                            $this->PutLink($this->HREF,$e);
                        else
                            $this->Write(5,$e);
                    }
                    else
                    {
                        // Tag
                        if($e[0]=='/')
                            $this->CloseTag(strtoupper(substr($e,1)));
                        else
                        {
                            // Extract attributes
                            $a2 = explode(' ',$e);
                            $tag = strtoupper(array_shift($a2));
                            $attr = array();
                            foreach($a2 as $v)
                            {
                                if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                                    $attr[strtoupper($a3[1])] = $a3[2];
                            }
                            $this->OpenTag($tag,$attr);
                        }
                    }
                }
            }

            function OpenTag($tag, $attr)
            {
                // Opening tag
                if($tag=='B' || $tag=='I' || $tag=='U')
                    $this->SetStyle($tag,true);
                if($tag=='A')
                    $this->HREF = $attr['HREF'];
                if($tag=='BR')
                    $this->Ln(5);
            }

            function CloseTag($tag)
            {
                // Closing tag
                if($tag=='B' || $tag=='I' || $tag=='U')
                    $this->SetStyle($tag,false);
                if($tag=='A')
                    $this->HREF = '';
            }

            function SetStyle($tag, $enable)
            {
                // Modify style and select corresponding font
                $this->$tag += ($enable ? 1 : -1);
                $style = '';
                foreach(array('B', 'I', 'U') as $s)
                {
                    if($this->$s>0)
                        $style .= $s;
                }
                $this->SetFont('',$style);
            }

            function PutLink($URL, $txt)
            {
                // Put a hyperlink
                $this->SetTextColor(0,0,255);
                $this->SetStyle('U',true);
                $this->Write(5,$txt,$URL);
                $this->SetStyle('U',false);
                $this->SetTextColor(0);
            }
        }

        $html = 'Respondent Name : amina, Respondent Lawyer : nisar, Resident Name : amina, Resident Lawyer : nisar <br>';

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $pdf->WriteHTML("<b><i><u>For Admission : </u></i></b>");
        if(!empty($admissionRecords)) {
            $i =0;
            foreach ($admissionRecords as $admissionRecord) {
                $i++;
                $html = 'Respondent Name : '.$admissionRecord['res_name'].', Respondent Lawyer : '.$admissionRecord['res_adv'].
                    ',Resident Name : '.$admissionRecord['pet_name'].', Resident Lawyer : '.$admissionRecord['pet_adv'].' <br>';
                $pdf->Ln(7);
                $pdf->WriteHTML($i."- ".$html);
            }
        } else {
            $pdf->WriteHTML(" (No Record) ");
        }

        $pdf->Ln(7);

        $pdf->WriteHTML("<b><i><u>For Order : </u></i></b>");
        if(!empty($orderRecords)) {
            $i =0;
            foreach ($orderRecords as $orderRecord) {
                $i++;
                $html = 'Respondent Name : '.$orderRecord['res_name'].', Respondent Lawyer : '.$orderRecord['res_adv'].
                    ', Resident Name : '.$orderRecord['pet_name'].', Resident Lawyer : '.$orderRecord['pet_adv'].' <br>';
                $pdf->Ln(7);
                $pdf->WriteHTML($i."- ".$html);
            }
        } else {
            $pdf->WriteHTML(" (No Record) ");

        }

        $pdf->Ln(7);

        $pdf->WriteHTML("<b><i><u>For Final Hearing : </u></i></b>");
        if(!empty($hearingRecords)) {
            $i =0;
            foreach ($hearingRecords as $hearingRecord) {
                $i++;
                $html = 'Respondent Name : '.$hearingRecord['res_name'].', Respondent Lawyer : '.$hearingRecord['res_adv'].
                    ', Resident Name : '.$hearingRecord['pet_name'].', Resident Lawyer : '.$hearingRecord['pet_adv'].' <br>';
                $pdf->Ln(7);
                $pdf->WriteHTML($i."- ".$html);
            }
        } else {
            $pdf->WriteHTML(" (No Record) ");

        }

        $pdf->Output();
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Next list Report</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/jquery-ui.css">

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>


</head>
<body>
<script>
    $( function() {
        $( ".date-format" ).datepicker({
            maxDate: new Date()
        });
    });
</script>

<style>
    .spacing{
        margin: 100px;
    }
</style>
<div class="container-fluid">
    <div class="page-header">
        <div class="row">
            <a href="../" class="no-text-decoration">
                <img class="img-responsive" src="logocopy.jpg" />
            </a>
        </div>
    </div>

    <div class="row">
        <div class="spacing">
            <form action="" method="post" class="form-horizontal">
                <div class="form-group">
                    <label for="next_list_date" class="cols-sm-4 control-label">Next List Date</label>
                    <div class="cols-sm-2">
                        <input type="text" class="form-control date-format" name="next_list_date" id="next_list_date"  placeholder="Choose Next List Date" required/>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary"> Export Pdf</button>
                </div>
            </form>
        </div>

    </div>
</div>
