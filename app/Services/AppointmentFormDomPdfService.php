<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AppointmentFormDomPdfService
{
    /** Reserved @page bottom margin (mm) — matches fixed footer height + small gap. */
    public const FOOTER_RESERVE_MM = 25;

    /**
     * A5 portrait — same as RequestprescriptionA5 (FPDF AddPage('P'), 148×210 mm).
     * Build appointment form PDF bytes (sanitized HTML from form_content).
     *
     * @param  array{top?:int|float,right?:int|float,bottom?:int|float,left?:int|float}|null  $pageMarginMm
     */
    public static function binary(array $data, ?array $pageMarginMm = null): string
    {
        $appointment = $data['appointment_detail'] ?? null;
        $profile = $data['profile'] ?? null;
        $patient = $data['patient_detail'] ?? null;

        $rawHtml = $appointment ? (trim((string) $appointment->form_content)) : '';
        // #region agent log
        self::agentDebugLog('AppointmentFormDomPdfService.php:binary', 'raw_db_html', array_merge(self::fontSizeDebugMeta($rawHtml), self::contentFingerprint($rawHtml)), 'A');
        // #endregion
        $preparedHtml = RichTextSanitizer::prepareForPdf($rawHtml);
        // #region agent log
        self::agentDebugLog('AppointmentFormDomPdfService.php:binary', 'after_prepareForPdf', self::fontSizeDebugMeta($preparedHtml), 'C');
        // #endregion
        $formHtml = self::compactFormHtml($preparedHtml);
        $fitClass = self::resolveFormFitClass($formHtml);
        // #region agent log
        self::agentDebugLog('AppointmentFormDomPdfService.php:binary', 'final_formHtml', array_merge(self::fontSizeDebugMeta($formHtml), ['fitClass' => $fitClass]), 'E');
        // #endregion
        
        $signatureSrc = ($profile && ! empty($profile->signature))
            ? self::resolveSignatureDataUri((string) $profile->signature)
            : null;

        $pagePadding = self::pagePaddingMm($pageMarginMm);
        $footerReserveMm = self::footerReserveMm($pagePadding);

        $pdf = Pdf::loadView('pdfs.appointment-form', [
            'formHtml' => $formHtml,
            'fitClass' => $fitClass,
            'profile' => $profile,
            'patient' => $patient,
            'appointment' => $appointment,
            'signatureSrc' => $signatureSrc,
            'pagePadding' => $pagePadding,
            'footerReserveMm' => $footerReserveMm,
            'contentWidthMm' => 148 - $pagePadding['left'] - $pagePadding['right'],
            'patientPhotoUrl' => self::resolvePatientPhotoUrl($patient),
            'paths' => [
                'ant_logo' => self::safePublicImg('img/ant_logo.jpg'),
                'rx' => self::safePublicImg('img/rx.png'),
            ],
        ])
            ->setPaper('a5', 'portrait');

        return $pdf->output();
    }

    /**
     * @param  string  $dest  TCPDF-compat: 'S' raw string | 'I' inline | 'D' download
     * @param  array{top?:int|float,right?:int|float,bottom?:int|float,left?:int|float}|null  $pageMarginMm
     * @return string|\Illuminate\Http\Response
     */
    public static function respond(array $data, string $dest = 'I', string $filename = 'form.pdf', ?array $pageMarginMm = null)
    {
        $binary = self::binary($data, $pageMarginMm);

        if ($dest === 'S') {
            return $binary;
        }

        $disposition = $dest === 'D' ? 'attachment' : 'inline';

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Collapse extra blank lines from the editor so PDF uses less vertical space. */
    /**
     * @param  array{top?:int|float,right?:int|float,bottom?:int|float,left?:int|float}|null  $pageMarginMm
     */
    /**
     * Side margins: explicit mm width on .page-inner (see appointment-form.blade.php).
     *
     * @param  array{top?:int|float,right?:int|float,bottom?:int|float,left?:int|float}|null  $pageMarginMm
     * @return array{top:float,right:float,bottom:float,left:float}
     */
    private static function pagePaddingMm(?array $pageMarginMm): array
    {
        $m = $pageMarginMm ?? [];

        return [
            'top' => (float) ($m['top'] ?? 5),
            'right' => (float) ($m['right'] ?? 10),
            'bottom' => (float) ($m['bottom'] ?? self::FOOTER_RESERVE_MM),
            'left' => (float) ($m['left'] ?? 10),
        ];
    }

    /**
     * @param  array{top:float,right:float,bottom:float,left:float}  $pagePadding
     */
    private static function footerReserveMm(array $pagePadding): float
    {
        return max(self::FOOTER_RESERVE_MM, (float) $pagePadding['bottom']);
    }

    private static function compactFormHtml(string $html): string
    {
        if ($html === '') {
            return '';
        }
        $html = preg_replace('/(<p>\s*(?:<br\s*\/?>)?\s*<\/p>\s*)+/i', '', $html) ?? $html;
        $html = preg_replace('/(<br\s*\/?>\s*){2,}/i', '<br>', $html) ?? $html;

        return trim($html);
    }

    /**
     * Tighter typography when form HTML is longer — keeps more on one A5 page.
     */
    private static function resolveFormFitClass(string $html): string
    {
        $plain = trim(strip_tags($html));
        $len = strlen($plain);

        if ($len > 3500) {
            return 'fit-dense';
        }
        if ($len > 1200) {
            return 'fit-compact';
        }

        return 'fit-normal';
    }

    private static function safePublicImg(string $relative): ?string
    {
        $path = public_path($relative);

        return is_readable($path) ? $path : null;
    }

    private static function resolvePatientPhotoUrl($patient): ?string
    {
        if (! $patient || empty($patient->profile_name)) {
            return null;
        }

        $profileName = (string) $patient->profile_name;
        if (Str::startsWith($profileName, ['http://', 'https://'])) {
            return $profileName;
        }

        try {
            return Storage::disk('s3')->temporaryUrl(
                $patient->id.'/'.$profileName,
                Carbon::now()->addMinutes(180)
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * DomPDF embeds signatures reliably as data URIs (same as FormController / FPDF Image).
     */
    private static function resolveSignatureDataUri(string $signature): ?string
    {
        $signature = trim($signature);
        if ($signature === '') {
            return null;
        }

        if (Str::startsWith($signature, 'data:image/')) {
            return $signature;
        }

        if (str_contains($signature, 'base64,')) {
            if (! Str::startsWith($signature, 'data:')) {
                $payload = explode('base64,', $signature, 2)[1] ?? '';

                return $payload !== '' ? 'data:image/png;base64,'.$payload : null;
            }

            return $signature;
        }

        if (is_readable($signature)) {
            $raw = @file_get_contents($signature);
            if ($raw !== false && $raw !== '') {
                $mime = 'image/png';
                $ext = strtolower(pathinfo($signature, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg'], true)) {
                    $mime = 'image/jpeg';
                } elseif ($ext === 'gif') {
                    $mime = 'image/gif';
                }

                return 'data:'.$mime.';base64,'.base64_encode($raw);
            }
        }

        $raw = base64_decode($signature, true);

        return ($raw !== false && $raw !== '')
            ? 'data:image/png;base64,'.$signature
            : null;
    }

    // #region agent log
    public static function agentDebugLog(string $location, string $message, array $data, string $hypothesisId): void
    {
        $payload = json_encode([
            'sessionId' => '37c2da',
            'timestamp' => (int) round(microtime(true) * 1000),
            'location' => $location,
            'message' => $message,
            'data' => $data,
            'hypothesisId' => $hypothesisId,
            'runId' => 'post-fix',
        ]);
        if ($payload !== false) {
            @file_put_contents(base_path('debug-37c2da.log'), $payload."\n", FILE_APPEND);
        }
    }

    /** @return array{length:int,hash:int} */
    public static function contentFingerprint(string $html): array
    {
        $len = strlen($html);
        $hash = 0;
        for ($i = 0; $i < $len; $i++) {
            $hash = (($hash << 5) - $hash) + ord($html[$i]);
            $hash &= 0xFFFFFFFF;
            if ($hash > 0x7FFFFFFF) {
                $hash -= 0x100000000;
            }
        }

        return ['length' => $len, 'hash' => $hash];
    }

    /** @return array{fontSizeCount:int,fontSizes:array<int,string>,qlSizeClassCount:int,htmlLength:int} */
    public static function fontSizeDebugMeta(string $html): array
    {
        preg_match_all('/font-size\s*:\s*([^;"\'\s]+)/i', $html, $matches);
        preg_match_all('/ql-size-/i', $html, $classMatches);

        return [
            'fontSizeCount' => count($matches[0]),
            'fontSizes' => array_values(array_unique($matches[1] ?? [])),
            'qlSizeClassCount' => count($classMatches[0]),
            'htmlLength' => strlen($html),
        ];
    }
    // #endregion
}
