<?php

namespace App\Http\Controllers\Api;

use Codedge\Fpdf\Fpdf\Fpdf;
use App\Model\Rx;
use App\Model\Ancillary;

class ChartRecordPdf extends Fpdf
{
    private $data;
    private $widths;
    private $aligns;

    /** Left margin and content width (Legal portrait ≈ 216 mm). */
    private const MARGIN_X = 11;
    private const CONTENT_W = 193;

    public function __construct($data)
    {
        $this->data = $data;
        parent::__construct('P', 'mm', 'Legal');
        $this->SetTitle('CLINICAL CHART', true);
        $this->SetAuthor('TJGazel', true);
        $this->SetAutoPageBreak(true, 18);
        $this->AliasNbPages();
        $this->AddPage('P');
        $this->Body();
    }

    public function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, 'CHART', 0, 1, 'C');
        $this->Ln(2);
    }

    public function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 8, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
    }

    public function Body()
    {
        $history = $this->data['getHistory'] ?? [];
        $count = is_countable($history) ? count($history) : 0;

        if ($count === 0) {
            $this->demographicsHeader();
            $this->addressRow();
            $this->section('Note', 'No consultation records.');
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            if ($i > 0) {
                $this->AddPage('P');
            }
            $this->visitPage($history[$i]);
        }
    }

    private function visitPage($appointment): void
    {
        $this->demographicsHeader();
        $this->addressRow();
        $this->doctorLine($appointment);
        $this->vitalsBand($appointment);

        $this->section('CHIEF COMPLAINTS', $this->plainText($appointment->chiefcomplaints ?? ''));
        $this->section('HISTORY', $this->plainText($appointment->history ?? ''));
        $this->section('P.E', $this->plainText($appointment->pe ?? ''));
        $this->section('Diagnosis', $this->plainText($appointment->diagnosis ?? ''));
        $this->section('Remarks', $this->plainText($appointment->remarks ?? ''));
        $this->section('Medications', $this->buildPrescriptionsText((int) $appointment->id));
        $this->section('Diagnostics', $this->buildDiagnosticsText((int) $appointment->id));
    }

    private function demographicsHeader(): void
    {
        $patient = $this->data['patient_detail'];
        $age = '';
        if (!empty($patient->birthdate)) {
            $age = (string) date_diff(date_create($patient->birthdate), date_create('now'))->y;
        }
        $birth = '';
        if (!empty($patient->birthdate)) {
            $birth = date_format(date_create($patient->birthdate), 'M d, Y');
        }
        $middle = trim((string) ($patient->middlename ?? ''));
        $middleInitial = $middle !== '' ? strtoupper(mb_substr($middle, 0, 1)) : '';

        $cols = [
            ['label' => 'Last', 'value' => strtoupper((string) ($patient->lastname ?? '')), 'w' => 45],
            ['label' => 'First', 'value' => strtoupper((string) ($patient->firstname ?? '')), 'w' => 45],
            ['label' => 'Middle', 'value' => $middleInitial, 'w' => 25],
            ['label' => 'Age', 'value' => $age, 'w' => 12],
            ['label' => 'Sex', 'value' => ((int) ($patient->sex ?? 0) === 1) ? 'M' : 'F', 'w' => 12],
            ['label' => 'Civil Status', 'value' => strtoupper((string) ($patient->civil_status ?? '')), 'w' => 24],
            ['label' => 'Birth Date', 'value' => $birth, 'w' => 30],
        ];

        $y = $this->GetY();
        $h = 14;
        $x = self::MARGIN_X;

        foreach ($cols as $col) {
            $this->Rect($x, $y, $col['w'], $h);
            $this->SetXY($x, $y + 0.5);
            $this->SetFont('Arial', '', 8);
            $this->Cell($col['w'], 5, $col['label'], 0, 0, 'C');
            $this->SetXY($x, $y + 5.5);
            $this->SetFont('Arial', '', 9);
            $this->Cell($col['w'], 7, $this->pdfText($col['value']), 0, 0, 'C');
            $x += $col['w'];
        }

        $this->SetY($y + $h);
    }

    private function addressRow(): void
    {
        $address = strtoupper((string) ($this->data['patient_detail']->address ?? ''));
        $y = $this->GetY();
        $labelH = 5;
        $this->SetFont('Arial', 'B', 9);
        $this->SetXY(self::MARGIN_X + 2, $y + 1);
        $this->Cell(self::CONTENT_W - 4, $labelH, 'Patient Address', 0, 1, 'L');

        $this->SetFont('Arial', '', 9);
        $this->SetXY(self::MARGIN_X + 2, $y + $labelH + 1);
        $startContentY = $this->GetY();
        $this->MultiCell(self::CONTENT_W - 4, 4, $this->pdfText($address !== '' ? $address : '—'), 0, 'L');
        $endY = max($this->GetY(), $startContentY + 4);
        $boxH = max(12, $endY - $y + 2);
        $this->Rect(self::MARGIN_X, $y, self::CONTENT_W, $boxH);
        $this->SetY($y + $boxH);
    }

    private function doctorLine($appointment): void
    {
        $doctor = trim((string) ($appointment->doctor ?? ''));
        if ($doctor === '' && !empty($this->data['profile']->name)) {
            $doctor = (string) $this->data['profile']->name;
        }
        if ($doctor === '') {
            return;
        }

        $this->Ln(2);
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(self::MARGIN_X);
        $this->Cell(20, 5, 'Doctor:', 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, $this->pdfText($doctor), 0, 1, 'L');
    }

    private function vitalsBand($appointment): void
    {
        $this->Ln(2);
        $this->CheckPageBreak(16);

        $this->SetFillColor(211, 211, 211);
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(self::MARGIN_X);
        $this->Cell(self::CONTENT_W, 6, 'Date, Time & Vital Signs', 0, 1, 'L', true);

        $date = '—';
        if (!empty($appointment->appointment_dt)) {
            $date = date_format(date_create($appointment->appointment_dt), 'M d, Y');
        }

        $sys = trim((string) ($appointment->vit_sys ?? ''));
        $dia = trim((string) ($appointment->vit_dia ?? ''));
        if ($sys !== '' || $dia !== '') {
            $bp = ($sys !== '' ? $sys : '—') . '/' . ($dia !== '' ? $dia : '—');
        } else {
            $bp = '—';
        }

        $parts = [
            'Date: ' . $date,
            'Weight: ' . $this->displayValue($appointment->weight ?? null),
            'Height: ' . $this->displayValue($appointment->height ?? null),
            'Temperature: ' . $this->displayValue($appointment->vit_temp ?? null),
            'BMI: ' . $this->displayValue($appointment->bmi ?? null),
            'BP: ' . $bp,
        ];

        $hr = trim((string) ($appointment->vit_cr ?? ''));
        if ($hr !== '') {
            $parts[] = 'HR: ' . $hr;
        }

        $this->SetFont('Arial', '', 9);
        $this->SetX(self::MARGIN_X);
        $y = $this->GetY();
        $this->MultiCell(self::CONTENT_W, 4.5, $this->pdfText(implode('    ', $parts)), 0, 'L');
        $endY = $this->GetY();
        $this->Rect(self::MARGIN_X, $y - 6, self::CONTENT_W, ($endY - $y) + 6);
        $this->SetY($endY);
    }

    private function section(string $title, string $text): void
    {
        $this->Ln(3);
        $this->CheckPageBreak(18);

        $this->SetFillColor(211, 211, 211);
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(self::MARGIN_X);
        $this->Cell(self::CONTENT_W, 6, $title, 0, 1, 'L', true);

        $body = trim($text) !== '' ? $text : '—';
        $this->SetFont('Arial', '', 10);
        $this->SetX(self::MARGIN_X);
        $this->MultiCell(self::CONTENT_W, 4, $this->pdfText($body), 0, 'L');
    }

    private function buildPrescriptionsText(int $appointmentId): string
    {
        $rows = Rx::where(['appointment_id' => $appointmentId])
            ->orderBy('rx_id', 'asc')
            ->get();

        if ($rows->isEmpty()) {
            return '';
        }

        $lines = [];
        foreach ($rows as $key => $value) {
            if ((int) ($value->medicine_id ?? 0) === 0) {
                $name = trim(strtoupper((string) ($value->generic_name ?? '')));
                $med = trim(strtoupper((string) ($value->medicine ?? '')));
                $line = $name !== '' && $med !== ''
                    ? $name . ' (' . $med . ')'
                    : ($name !== '' ? $name : $med);
            } else {
                $line = strtoupper((string) ($value->medicine ?? ''));
            }

            $extras = [];
            $qty = trim((string) ($value->qty ?? ''));
            $remarks = trim((string) ($value->remarks ?? ''));
            if ($qty !== '') {
                $extras[] = 'Qty: ' . $qty;
            }
            if ($remarks !== '') {
                $extras[] = $remarks;
            }
            if ($extras) {
                $line .= ' — ' . implode('; ', $extras);
            }

            $lines[] = ($key + 1) . '.) ' . $line;
        }

        return implode("\n", $lines);
    }

    private function buildDiagnosticsText(int $appointmentId): string
    {
        $rows = Ancillary::where(['appointment_id' => $appointmentId])->get();
        if ($rows->isEmpty()) {
            return '';
        }

        $lines = [];
        foreach ($rows as $key => $value) {
            $lines[] = ($key + 1) . '.) ' . strtoupper((string) ($value->ancillary ?? ''));
        }

        return implode("\n", $lines);
    }

    private function plainText(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $withBreaks = preg_replace('/<(br|\/p|\/div|\/li|\/tr)\s*\/?>/i', "\n", $html) ?? $html;
        $text = html_entity_decode(strip_tags($withBreaks), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t\x{00A0}]+/u", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function displayValue($value): string
    {
        $v = trim((string) ($value ?? ''));

        return $v !== '' ? $v : '—';
    }

    /**
     * Convert UTF-8 text for core FPDF fonts (ISO-8859-1).
     */
    private function pdfText(string $text): string
    {
        if ($text === '') {
            return '';
        }

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                return $converted;
            }
        }

        return utf8_decode($text);
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }
            $l += $cw[$c] ?? 0;
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }

        return $nl;
    }
}
