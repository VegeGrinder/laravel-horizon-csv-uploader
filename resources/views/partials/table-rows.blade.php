@forelse ($files as $file)
    <tr>
        <td>
            {{ $file->created_at_formatted }}<br>
            ({{ $file->created_at->diffForHumans() }})
        </td>
        <td>{{ $file->filename }}</td>

        @if ($file->status == \App\Enums\CsvFileStatus::PROCESSING)
            <td>{{ $file->status }} ({{ number_format(Cache::get("index:$file->id", 0) / Cache::get("total:$file->id", 1) * 100, 2) }} %)</td>
        @else
            <td>{{ $file->status }}</td>
        @endif
    </tr>
@empty
    <tr>
        <td colspan="3" class="text-center">Empty.</td>
    </tr>
@endforelse
