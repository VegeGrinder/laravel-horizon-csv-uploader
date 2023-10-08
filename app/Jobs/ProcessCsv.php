<?php

namespace App\Jobs;

use App\Enums\CsvFileStatus;
use App\Exports\CsvFilesExport;
use App\Models\CsvFile;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public CsvFile $csvFile;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(CsvFile $csvFile)
    {
        $this->csvFile = $csvFile;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('csvFileId: ' . $this->csvFile->id . ' job processing.');
            CsvFile::where('id', $this->csvFile->id)->update(['status' => CsvFileStatus::PROCESSING]);

            DB::beginTransaction();

            \Excel::import(new CsvFilesExport($this->csvFile->created_at), Storage::path($this->csvFile->path));
            CsvFile::where('id', $this->csvFile->id)->update(['status' => CsvFileStatus::COMPLETED]);

            DB::commit();
            Storage::delete($this->csvFile->path);
            Log::info('csvFileId: ' . $this->csvFile->id . ' job success.');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::info($ex->getMessage());
            Log::info('csvFileId: ' . $this->csvFile->id . ' job attempt failed.');
        }
    }

    public function failed(Throwable $exception): void
    {
        CsvFile::where('id', $this->csvFile->id)->update(['status' => CsvFileStatus::FAILED]);
        Log::error('csvFileId: ' . $this->csvFile->id . ' job fully failed.');
    }
}
