<?php
include('config/db.php');

require('fpdf/fpdf.php');
ob_clean();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);

$pdf->Cell(190,10,'System Logs Report',0,1,'C');

$pdf->SetFont('Arial','B',10);

$pdf->Cell(20,10,'ID',1);
$pdf->Cell(30,10,'Role',1);
$pdf->Cell(30,10,'User ID',1);
$pdf->Cell(70,10,'Activity',1);
$pdf->Cell(40,10,'Date',1);

$pdf->Ln();

$pdf->SetFont('Arial','',10);

$query=mysqli_query($conn,"SELECT * FROM system_logs ORDER BY log_time DESC");

while($row=mysqli_fetch_assoc($query)){

$pdf->Cell(20,10,$row['id'],1);
$pdf->Cell(30,10,$row['user_role'],1);
$pdf->Cell(30,10,$row['user_id'],1);
$pdf->Cell(70,10,$row['activity'],1);
$pdf->Cell(40,10,$row['log_time'],1);

$pdf->Ln();
}

$pdf->Output();
?>