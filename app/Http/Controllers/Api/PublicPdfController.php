<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Ancillary;
use App\Model\Appointments;
use App\Model\Patients;
use App\Model\Profile;
use App\Model\Rx;
use Illuminate\Http\Request;

class PublicPdfController extends Controller
{
    public function show(Request $request, string $doc, int $id, $type = null)
    {
        // NOTE: This endpoint is intentionally unauthenticated, protected by the "signed" middleware.
        // It must not leak data without a valid signature.

        $appointment = Appointments::where(['id' => $id])->first();
        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found.'], 404);
        }

        $profile = Profile::where(['id' => 1])->first();
        $patient = Patients::where(['patientid' => $appointment->patientid])->first();

        $filenameBase = $doc . '-' . $id . '.pdf';

        switch ($doc) {
            case 'rx': {
                $payload = app(PatientController::class)->buildPrescriptionPdfPayload(
                    $id,
                    $request->query('group_id', 'all')
                );
                $data = array_merge($payload, [
                    'appointment_detail' => $appointment,
                    'profile' => $profile,
                    'patient_detail' => $patient,
                ]);

                $pdf = new CustomPrescriptiontestA5Portrait($data);
                $content = $pdf->Output('', 'S');
                return $this->pdfResponse($content, $filenameBase);
            }

            case 'diagnostics': {
                $reqType = $type !== null ? (int) $type : (int) $request->query('type', 1);

                $controller = app(PatientController::class);
                $data = $controller->buildDiagnosticPdfPayload(
                    (int) $id,
                    $request->query('group_id', 'all')
                );
                $data['appointment_detail'] = $appointment;
                $data['profile'] = $profile;
                $data['patient_detail'] = $patient;
                $data['type'] = $reqType;

                $pdf = new RequestprescriptionA5($data);
                $content = $pdf->Output('', 'S');
                return $this->pdfResponse($content, $filenameBase);
            }

            case 'referral': {
                $data = [];
                $data['appointment_detail'] = $appointment;
                $data['profile'] = $profile;
                $data['patient_detail'] = $patient;

                $pdf = new Referral($data);
                $content = $pdf->Output('', 'S');
                return $this->pdfResponse($content, $filenameBase);
            }

            case 'medcert': {
                $data = [];
                $data['appointment_detail'] = $appointment;
                $data['profile'] = $profile;
                $data['patient_detail'] = $patient;

                $pdf = new MedCertA5($data);
                $content = $pdf->Output('', 'S');
                return $this->pdfResponse($content, $filenameBase);
            }

            case 'form': {
                $data = [];
                $data['appointment_detail'] = $appointment;
                $data['profile'] = $profile;
                $data['patient_detail'] = $patient;

                $pdfService = new PdfService($data);
                $content = $pdfService->generatePdf('S', 'form-' . $id . '.pdf');
                return $this->pdfResponse($content, 'form-' . $id . '.pdf');
            }

            default:
                return response()->json(['error' => 'Invalid document.'], 400);
        }
    }

    private function pdfResponse($content, string $filename)
    {
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

