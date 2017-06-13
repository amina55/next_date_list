<?php
require('fpdf/fpdf.php');

class PDF extends FPDF
{
    protected $B = 0;
    protected $I = 0;
    protected $U = 0;
    protected $HREF = '';

    function Header()
    {

        $header = strtoupper("High Court of Jammu & Kashmir At Srinagar");
        $judgeName = "HON'BLE MR. JUSTICE ALI MOHAMMAD  MAGREY";
        $date = '10/23/80909';
        $this->SetFont('Arial','',11);
        $this->Cell(0, 5, $header, 0, 1, 'C');
        $this->Cell(0, 5, $judgeName, 0, 1, 'C');
        $this->Cell(0, 5, $date, 0, 1, 'C');
        $this->Ln(10);
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

$pdf->WriteHTML("<b><i><u>For Admission</u></i></b><br>");

for ($i=0 ; $i<20 ; $i++) {
    $pdf->Ln(10);
    $pdf->WriteHTML($i."- ".$html);
}

$pdf->Output();
?>


if(!empty($admissionRecords)) {
foreach ($admissionRecords as $admissionRecord) {
$html = 'Respondent Name : '.$admissionRecord['res_name'].', Respondent Lawyer : '.$admissionRecords['res_adv'].',
Resident Name : '.$admissionRecords['pet_name'].', Resident Lawyer : '.$admissionRecord['pet_adv'].' <br>';
$pdf->Ln(10);
$pdf->WriteHTML($i."- ".$html);
}
} else {
$pdf->WriteHTML(" (No Record) ");

}
