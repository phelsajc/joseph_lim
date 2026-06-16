<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A5 portrait;
            margin: 0 0 {{ $footerReserveMm ?? 25 }}mm 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 7pt;
            line-height: 1.2;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /*
         * Side inset: explicit mm width + margins (DomPDF splits 3-column tables equally).
         */
        .page-margin-wrap {
            padding-top: {{ ($pagePadding['top'] ?? 5) }}mm;
            padding-bottom: 2mm;
        }

        .page-inner {
            width: {{ $contentWidthMm ?? 128 }}mm;
            max-width: {{ $contentWidthMm ?? 128 }}mm;
            margin-left: {{ ($pagePadding['left'] ?? 10) }}mm;
            margin-right: {{ ($pagePadding['right'] ?? 10) }}mm;
            overflow: visible;
        }

        /* ----- Prescription header (CustomPrescriptiontestA5Portrait / reference PDF) ----- */
        .watermark-layer {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .watermark-tile {
            position: absolute;
            color: rgb(220, 220, 220);
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-weight: 700;
            font-size: 9pt;
            line-height: 1.15;
            white-space: nowrap;
            -webkit-transform: rotate(45deg);
            transform: rotate(-45deg);
        }

        .prescription-header {
            position: relative;
            width: 100%;
            min-height: 24mm;
            margin: 0 0 10mm;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.2;
        }

        .prescription-logo-wrap {
            position: absolute;
            left: 0;
            top: 1mm;
            width: 20mm;
            z-index: 1;
        }

        .prescription-logo {
            width: 20mm;
            height: 20mm;
            display: block;
        }

        .prescription-doc-block {
            text-align: center;
            width: 100%;
            padding: 0 0 1mm;
        }

        .prescription-doc-name {
            font-weight: 700;
            font-size: 12pt;
            margin: 0;
            line-height: 1.15;
        }

        .prescription-doc-spec {
            font-weight: 700;
            font-size: 9pt;
            margin: 0.4mm 0 0;
            text-transform: uppercase;
            line-height: 1.15;
        }

        .prescription-doc-addr {
            font-size: 8pt;
            font-weight: 400;
            margin: 0.5mm 0 0;
            line-height: 1.15;
        }

        .prescription-doc-phone {
            font-size: 8pt;
            font-weight: 400;
            margin: 0.5mm 0 0;
            line-height: 1.15;
        }

        /* Horizontal rule — stays within page content area */
        .prescription-rule {
            border: none;
            border-top: 0.5mm solid #000;
            margin: 6mm 0 4mm;
            width: 100%;
        }

        .prescription-meta {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            table-layout: auto;
        }

        .prescription-meta td {
            vertical-align: bottom;
            padding: 0 0.5mm 0.5mm 0;
        }

        .prescription-meta .meta-label {
            white-space: nowrap;
            padding-right: 0.5mm;
        }

        .prescription-meta .field-val {
            border-bottom: 0.25mm solid #000;
            vertical-align: bottom;
            line-height: 1.2;
            padding: 0 0 0.2mm;
        }

        .prescription-meta .name-val {
            width: 55%;
        }

        .prescription-meta .sex-label {
            text-align: right;
            padding-left: 1mm;
        }

        .prescription-meta .sex-val {
            text-align: right;
        }

        .prescription-meta .age-label {
            padding-left: 1mm;
            white-space: nowrap;
        }

        .prescription-meta .addr-val {
            width: auto;
        }

        .prescription-meta .date-val {
            white-space: nowrap;
            font-size: 7pt; /* small fallback to prevent DomPDF clipping */
        }

        .prescription-meta .date-label {
            padding-left: 1mm;
            white-space: nowrap;
        }

        .prescription-meta .addr-row td {
            padding-top: 3.5mm;
        }

        /* FPDF Image rx at (12, 53) and patient photo at (120, 45), page coords */
        .rx-icon {
            position: fixed;
            left: {{ ($pagePadding['left'] ?? 10) + 2 }}mm;
            top: 53mm;
            width: 9mm;
            height: 9mm;
            z-index: 2;
        }

        .rx-patient-photo {
            position: fixed;
            left: {{ 148 - ($pagePadding['right'] ?? 10) - 18.5 }}mm;
            top: 45mm;
            width: 18.5mm;
            height: 18.5mm;
            z-index: 2;
        }

        /* ----- Form body: default compact for single-page fit ----- */
        .form-body {
            font-size: 7pt;
            line-height: 1.22;
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
            overflow-wrap: break-word;
            word-wrap: break-word;
            word-break: break-word;
        }

        .form-body p,
        .form-body div,
        .form-body span,
        .form-body li,
        .form-body td,
        .form-body th {
            max-width: 100%;
            overflow-wrap: break-word;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal !important;
        }

        .form-body table {
            border-collapse: collapse;
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
            margin: 0.25em 0;
            font-size: inherit;
        }

        .form-body th,
        .form-body td {
            border: 0.3mm solid #ccc;
            padding: 0.6mm 1mm;
            vertical-align: top;
        }

        .form-body th {
            background: #f4f4f4;
            font-weight: 700;
        }

        .form-body ul,
        .form-body ol {
            margin: 0.2em 0;
            padding-left: 2.5mm;
        }

        .form-body li {
            margin: 0;
        }

        .form-body p {
            margin: 0 0 0.35em;
        }

        /* Default heading sizes only when Quill did not set an inline font-size */
        .form-body h1:not([style*="font-size"]) {
            font-size: 9pt;
            margin: 0.15em 0 0.25em;
            line-height: 1.15;
        }

        .form-body h2:not([style*="font-size"]) {
            font-size: 8pt;
            margin: 0.15em 0 0.2em;
            line-height: 1.15;
        }

        .form-body h3:not([style*="font-size"]) {
            font-size: 7.5pt;
            margin: 0.1em 0 0.15em;
        }

        .form-body h4:not([style*="font-size"]) {
            font-size: 7pt;
            margin: 0.1em 0 0.15em;
        }

        /* Quill inline font-size on spans/blocks is authoritative in PDF output */
        .form-body [style*="font-size"] {
            line-height: 1.15;
        }

        .form-body img {
            max-width: 100%;
            max-height: 22mm;
            height: auto;
        }

        .form-body hr {
            margin: 0.4em 0;
            border: none;
            border-top: 0.3mm solid #ccc;
        }

        /* Quill alignment (class backup if inline style was stripped) */
        .form-body .ql-align-center,
        .form-body p.ql-align-center,
        .form-body h1.ql-align-center,
        .form-body h2.ql-align-center,
        .form-body h3.ql-align-center,
        .form-body div.ql-align-center {
            text-align: center;
        }

        .form-body .ql-align-right,
        .form-body p.ql-align-right,
        .form-body h1.ql-align-right,
        .form-body h2.ql-align-right,
        .form-body h3.ql-align-right,
        .form-body div.ql-align-right {
            text-align: right;
        }

        .form-body .ql-align-justify,
        .form-body p.ql-align-justify,
        .form-body div.ql-align-justify {
            text-align: justify;
        }

        .form-body .ql-align-left {
            text-align: left;
        }

        .form-body [style*="text-align: center"],
        .form-body [style*="text-align:center"] {
            text-align: center !important;
        }

        .form-body [style*="text-align: right"],
        .form-body [style*="text-align:right"] {
            text-align: right !important;
        }

        .form-body [style*="text-align: justify"],
        .form-body [style*="text-align:justify"] {
            text-align: justify !important;
        }

        .empty {
            color: #888;
            font-style: italic;
        }

        /* Longer templates: step down font size */
        body.fit-compact .form-body {
            font-size: 6.5pt;
            line-height: 1.18;
        }

        body.fit-compact .form-body h1:not([style*="font-size"]) {
            font-size: 8pt;
        }

        body.fit-compact .form-body h2:not([style*="font-size"]) {
            font-size: 7.5pt;
        }

        body.fit-compact .form-body p {
            margin-bottom: 0.28em;
        }

        body.fit-dense .form-body {
            font-size: 6pt;
            line-height: 1.12;
        }

        body.fit-dense .form-body h1:not([style*="font-size"]) {
            font-size: 7.5pt;
        }

        body.fit-dense .form-body h2:not([style*="font-size"]),
        body.fit-dense .form-body h3:not([style*="font-size"]),
        body.fit-dense .form-body h4:not([style*="font-size"]) {
            font-size: 6.5pt;
        }

        body.fit-dense .form-body th,
        body.fit-dense .form-body td {
            padding: 0.4mm 0.6mm;
        }

        body.fit-dense .form-body p {
            margin-bottom: 0.2em;
        }

        body.fit-dense .prescription-header {
            margin-bottom: 14mm;
        }

        /* ----- Footer (CustomPrescriptiontestA5Portrait::Footer) ----- */
        .footer {
            position: fixed;
            bottom: -{{ $footerReserveMm ?? 25 }}mm;
            left: {{ ($pagePadding['left'] ?? 10) }}mm;
            right: {{ ($pagePadding['right'] ?? 10) }}mm;
            height: 23mm;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 7pt;
            line-height: 1.15;
        }

        .footer-sig {
            position: absolute;
            right: -120;
            bottom: 7mm;
            max-width: 105mm;
            max-height: 120mm;
            height: auto;
            width: auto;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-table td {
            vertical-align: bottom;
            padding: 0 0 0.3mm;
        }

        .footer-table .footer-name {
            font-weight: 700;
            text-align: right;
            text-transform: uppercase;
            padding-bottom: 0.8mm;
        }

        .footer-table .footer-label {
            text-align: right;
            white-space: nowrap;
            width: 72%;
        }

        .footer-table .footer-val {
            text-align: right;
            border-bottom: 0.25mm solid #000;
            width: 28%;
        }

        .footer-followup {
            margin-top: 1mm;
            font-size: 7pt;
            line-height: 1.2;
        }

        .footer-followup .followup-label {
            display: inline-block;
            width: 25mm;
            text-align: center;
        }

        .footer-followup .followup-date {
            display: inline-block;
            min-width: 40mm;
            margin-left: 1mm;
        }

        .footer-followup .followup-line {
            display: inline-block;
            border-bottom: 0.25mm solid #000;
            min-width: 40mm;
            height: 3mm;
            vertical-align: bottom;
            margin-left: 1mm;
        }
    </style>
</head>

<body class="{{ $fitClass ?? 'fit-normal' }}">
    @php
        $p = $profile ?? null;
        $pt = $patient ?? null;
        $specLine = strtoupper(trim((string) ($p?->specialization1 ?? '')));

        $patientDisplayName = '';
        $patientSex = '';
        $patientAge = '';
        $patientAddress = '';
        if ($pt) {
            $ln = ucwords(strtolower((string) ($pt->lastname ?? '')));
            $fn = ucwords(strtolower((string) ($pt->firstname ?? '')));
            $mi = strtoupper(substr((string) ($pt->middlename ?? ''), 0, 1));
            $patientDisplayName = trim($ln . ', ' . $fn . ($mi !== '' ? ' ' . $mi . '.' : ''));
            $patientSex = strtoupper((int) ($pt->sex ?? 0) === 2 ? 'Female' : 'Male');
            if (!empty($pt->birthdate)) {
                try {
                    $patientAge = (string) date_diff(date_create($pt->birthdate), date_create('now'))->y;
                } catch (\Throwable $e) {
                    $patientAge = '';
                }
            }
            $patientAddress = trim((string) ($pt->address ?? ''));
        }
        $printDate = date('m/d/Y');

        $showS2 = $appointment && !empty($appointment->withs2);
        $followupDate = '';
        if ($appointment && !empty($appointment->followup)) {
            try {
                $followupDate = date_format(date_create($appointment->followup), 'F d, Y');
            } catch (\Throwable $e) {
                $followupDate = '';
            }
        }
        $s2DateIssued = '';
        $s2ValidUntil = '';
        if ($showS2 && $p) {
            if (!empty($p->date_issued)) {
                try {
                    $s2DateIssued = date_format(date_create($p->date_issued), 'F d, Y');
                } catch (\Throwable $e) {
                    $s2DateIssued = '';
                }
            }
            if (!empty($p->s2_validity)) {
                try {
                    $s2ValidUntil = date_format(date_create($p->s2_validity), 'F d, Y');
                } catch (\Throwable $e) {
                    $s2ValidUntil = '';
                }
            }
        }

        $wmTiles = [];
        $tileW = max(42, strlen($patientDisplayName) * 2.2, strlen($printDate) * 2.2);
        $stepX = $tileW + 10;
        $stepY = 10;
        for ($wy = -10; $wy < 240; $wy += $stepY) {
            $row = (int) (($wy + 10) / $stepY);
            $offsetX = ($row % 2) * ($stepX / 2);
            for ($wx = -10 + $offsetX; $wx < 180; $wx += $stepX) {
                $wmTiles[] = ['x' => $wx, 'y' => $wy];
            }
        }
    @endphp

    <div class="footer">
        @if(!empty($signatureSrc))
            <img class="footer-sig" src="{{ $signatureSrc }}" alt="">
        @endif
        @if($p)
            <table class="footer-table" cellspacing="0" cellpadding="0">
                <tr>
                    <td colspan="2" class="footer-name">{{ strtoupper($p->name ?? '') }}</td>
                </tr>
                <tr>
                    <td class="footer-label">License No:</td>
                    <td class="footer-val">{{ $p->prc ?? '' }}</td>
                </tr>
                <tr>
                    <td class="footer-label">PTR. No.</td>
                    <td class="footer-val">{{ $p->ptr ?? '' }}</td>
                </tr>
                @if($showS2)
                    <tr>
                        <td class="footer-label">S2 No.</td>
                        <td class="footer-val">{{ $p->s2 ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="footer-label">Date Issued:</td>
                        <td class="footer-val">{{ $s2DateIssued }}</td>
                    </tr>
                    <tr>
                        <td class="footer-label">Valid Until:</td>
                        <td class="footer-val">{{ $s2ValidUntil }}</td>
                    </tr>
                @endif
            </table>
            <div class="footer-followup">
                <span class="followup-label">Next appointment:</span>
                @if($followupDate !== '')
                    <span class="followup-date">{{ $followupDate }}</span>
                @else
                    <span class="followup-line"></span>
                @endif
            </div>
        @endif
    </div>

    <div class="watermark-layer" aria-hidden="true">
        @foreach ($wmTiles as $tile)
            <div class="watermark-tile" style="left: {{ $tile['x'] }}mm; top: {{ $tile['y'] }}mm;">
                {{ $patientDisplayName }}<br>{{ $printDate }}
            </div>
        @endforeach
    </div>

    <div class="page-margin-wrap">
    <div class="page-inner">
        <header class="prescription-header">
            @if(!empty($paths['lim_fb']))
                <div class="prescription-logo-wrap">
                    <img class="prescription-logo" src="{{ $paths['lim_fb'] }}" alt="">
                </div>
            @endif

            <div class="prescription-doc-block">
                <div class="prescription-doc-name">JOSEPH PETER T. LIM, MD</div>
                <div class="prescription-doc-spec">{{ $specLine }}</div>
                <div class="prescription-doc-addr">Arts Building, BS Aquino Drive, Bacolod</div>
                <div class="prescription-doc-phone">jplimmd.clinic@gmail.com</div>
            </div>

            <hr class="prescription-rule">

            <table class="prescription-meta" cellspacing="0" cellpadding="0">
                <colgroup>
                    <col style="width: 9%">
                    <col style="width: 37%">
                    <col style="width: 15%">
                    <col style="width: 11%">
                    <col style="width: 10%">
                    <col style="width: 18%">
                </colgroup>
                <tr>
                    <td class="meta-label">Name:</td>
                    <td class="field-val name-val">{{ $patientDisplayName }}</td>
                    <td class="meta-label sex-label">Sex :</td>
                    <td class="field-val sex-val">{{ $patientSex }}</td>
                    <td class="meta-label age-label">Age:</td>
                    <td class="field-val age-val">{{ $patientAge }}</td>
                </tr>
                <tr class="addr-row">
                    <td class="meta-label">Address :</td>
                    <td class="field-val addr-val" colspan="3">{{ $patientAddress }}</td>
                    <td class="meta-label date-label">Date:</td>
                    <td class="field-val date-val">{{ $printDate }}</td>
                </tr>
            </table>
        </header>

        <div class="form-body">
            @if(trim(strip_tags($formHtml)) === '')
                <p class="empty">No form content.</p>
            @else
                {!! $formHtml !!}
            @endif
        </div>
    </div>
    </div>
</body>

</html>