<?php

namespace App\Console\Commands;

use App\Model\Attachments;
use App\Model\Patients;
use App\Services\ImageOrientationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixPatientImageOrientation extends Command
{
    protected $signature = 'patients:fix-image-orientation
                            {--dry-run : Report only, do not upload fixed files}
                            {--patient= : Limit to one patients.id numeric primary key}';

    protected $description = 'Bake EXIF orientation into patient profile and attachment images on S3';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $patientId = $this->option('patient');

        if ($dryRun) {
            $this->warn('Dry run — no files will be changed.');
        }

        $profileFixed = 0;
        $attachmentFixed = 0;

        $patientsQuery = Patients::query()->whereNotNull('profile_name')->where('profile_name', '!=', '');
        if ($patientId) {
            $patientsQuery->where('id', $patientId);
        }

        foreach ($patientsQuery->cursor() as $patient) {
            $key = $patient->id . '/' . $patient->profile_name;
            if ($this->fixS3Object($key, $dryRun)) {
                $profileFixed++;
                $this->info("Fixed profile: {$key}");
            }
        }

        $attachmentsQuery = Attachments::query();
        if ($patientId) {
            $patient = Patients::find($patientId);
            if ($patient) {
                $attachmentsQuery->where('patientid', $patient->patientid);
            }
        }

        foreach ($attachmentsQuery->cursor() as $att) {
            $patient = Patients::where('patientid', $att->patientid)->first();
            if (!$patient) {
                continue;
            }
            $ext = strtolower(pathinfo($att->filename, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true)) {
                continue;
            }
            $key = $patient->id . '/' . $att->filename;
            if ($this->fixS3Object($key, $dryRun)) {
                $attachmentFixed++;
                $this->info("Fixed attachment: {$key}");
            }
        }

        $this->line("Done. Profiles fixed: {$profileFixed}, attachments fixed: {$attachmentFixed}.");

        return 0;
    }

    private function fixS3Object(string $s3Key, bool $dryRun): bool
    {
        $disk = Storage::disk('s3');
        if (!$disk->exists($s3Key)) {
            return false;
        }

        $tmpIn = tempnam(sys_get_temp_dir(), 'fix_in_');
        $tmpOut = tempnam(sys_get_temp_dir(), 'fix_out_') . '.jpg';
        if ($tmpIn === false) {
            return false;
        }

        try {
            file_put_contents($tmpIn, $disk->get($s3Key));
            $changed = ImageOrientationService::bakeExifOrientationToJpegFile($tmpIn, $tmpOut);
            if (!$changed) {
                return false;
            }
            if ($dryRun) {
                return true;
            }
            $disk->put($s3Key, file_get_contents($tmpOut));
            return true;
        } catch (\Throwable $t) {
            $this->error("Failed {$s3Key}: " . $t->getMessage());
            return false;
        } finally {
            @unlink($tmpIn);
            @unlink($tmpOut);
        }
    }
}
