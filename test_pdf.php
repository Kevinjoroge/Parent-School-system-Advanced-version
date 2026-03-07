<?php

require('C:/xampp/htdocs/Parent-School-System-Advanced-Version/fpdf/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(40,10,'PDF working successfully!');
$pdf->Output();

?>