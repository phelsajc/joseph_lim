<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrganizePatientUploads extends Command
{
    protected $signature = 'patients:move-uploads';
    protected $description = 'Move uploads into patient folders';

    public function handle()
    {
        $rows = DB::table('patients_attachments')->get();

        foreach ($rows as $row) {

            $oldPath = 'uploads/' . $row->filename;

            if (!Storage::disk('public')->exists($oldPath)) {
                $this->warn("Missing: {$oldPath}");
                continue;
            }

            $folder = 'patients/' . $row->patientid;
            $newPath = $folder . '/' . $row->filename;

            Storage::disk('public')->makeDirectory($folder);

            Storage::disk('public')->move($oldPath, $newPath);

            $this->info("Moved: {$newPath}");
        }

        $this->line('Done');
    }
}