<?php

namespace App\Exports;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class CsvFilesExport implements ToCollection, WithHeadingRow, WithEvents, WithChunkReading
{
    use RemembersChunkOffset;

    public Carbon $jobTimestamp;
    public $jobId;

    public function __construct($jobId, Carbon $jobTimestamp)
    {
        $this->jobId = $jobId;
        $this->jobTimestamp = $jobTimestamp;
    }

    public function collection(Collection $rows)
    {
        Log::info('csvFileId: ' . $this->jobId . ', rowNumber: ' . $this->getChunkOffset());
        Cache::put('index:' . $this->jobId, $this->getChunkOffset(), 300);

        foreach ($rows as $row) {
            $uniqueKey = $this->removeNonUtf8($row['UNIQUE_KEY']);
            $productTitle = $this->removeNonUtf8($row['PRODUCT_TITLE']);
            $productDescription = $this->removeNonUtf8($row['PRODUCT_DESCRIPTION']);
            $style = $this->removeNonUtf8($row['STYLE#']);
            $sanmarMainframeColor = $this->removeNonUtf8($row['SANMAR_MAINFRAME_COLOR']);
            $size = $this->removeNonUtf8($row['SIZE']);
            $colorName = $this->removeNonUtf8($row['COLOR_NAME']);
            $piecePrice = $this->removeNonUtf8($row['PIECE_PRICE']);

            $product = Product::firstOrCreate(
                [
                    'unique_key' => $uniqueKey,
                ],
                [
                    'product_title' => $productTitle,
                    'product_description' => $productDescription,
                    'style' => $style,
                    'sanmar_mainframe_color' => $sanmarMainframeColor,
                    'size' => $size,
                    'color_name' => $colorName,
                    'piece_price' => $piecePrice,
                    // Timestamps are timed according to the datetime of CSV upload (Job created_at), not Laravel model creation now()
                    'created_at' => $this->jobTimestamp,
                    'updated_at' => $this->jobTimestamp,
                ]
            );

            // Newly created Product's updated_at must be greater than the timestamp of Job creation
            // Only the first() from above can possibly run the codes below
            if (($this->jobTimestamp)->greaterThan($product->updated_at)) {
                Log::info('productId: ' . $product->id . ', uniqueKey: ' . $product->unique_key . ' updated.');

                $product->update([
                    'product_title' => $productTitle,
                    'product_description' => $productDescription,
                    'style' => $style,
                    'sanmar_mainframe_color' => $sanmarMainframeColor,
                    'size' => $size,
                    'color_name' => $colorName,
                    'piece_price' => $piecePrice,
                    'updated_at' => $this->jobTimestamp,
                ]);
            }
        }
    }

    private function removeNonUtf8(string $string): string
    {
        return preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $string);
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function(BeforeImport $event) {
                $totalRows = $event->getReader()->getTotalRows()['Worksheet'];

                Cache::put('total:' . $this->jobId, $totalRows, 3600);
            },
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
