<?php

namespace App\Http\Controllers;

use App\Exports\CsvFilesExport;
use App\Jobs\ProcessCsv;
use App\Models\CsvFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\error;

class CsvFileController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    /**
     * Stores the uploaded CSV file and dispatch a ProcessCsv Job into the queue.
     */
    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
        ]);

        try {
            DB::beginTransaction();

            $filename = $request->file('csv')->getClientOriginalName();
            $path  = $request->file('csv')->store('csv');

            if ($path === false) {
                return response()->json(['message' => 'Server error, unable to store file.'], 500);
            }

            $csvFile = CsvFile::create([
                'filename' => $filename,
                'path' => $path,
            ]);

            if (Storage::exists($path) && $csvFile->exists) {
                DB::commit();
                ProcessCsv::dispatch($csvFile);

                return response()->json(['message' => 'File uploaded successfully, job added to queue.'], 200);
            }

            throw new Exception('Reached end of uploadCsv function.');
        } catch (Exception $ex) {
            DB::rollBack();
            Storage::delete($path);
            Log::error($ex->getMessage());

            return response()->json(['message' => 'Server error, please check the error log.'], 500);
        }
    }
}
