<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Goh Shang Shein | Laravel Horizon CSV Uploader</title>

    <!-- Fonts -->
    {{-- <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" /> --}}

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.css" integrity="sha512-8D+M+7Y6jVsEa7RD6Kv/Z7EImSpNpQllgaEIQAtqHcI0H6F4iZknRj0Nx1DCdB+TwBaS+702BGWYC0Ze2hpExQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .max-md-width {
            max-width: 768px;
        }
    </style>
</head>

<body>
    <div class="container-md my-3">
        <div class="input-group mb-3 mx-auto max-md-width">
            <input type="file" class="form-control" id="inputGroupFile">
            <label class="input-group-text d-none d-md-block" for="inputGroupFile">Upload</label>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <th>Time</th>
                    <th>File Name</th>
                    <th>Status</th>
                </thead>
                <tbody id="csvRows">
                    @include('partials.table-rows')
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script -->
    <script
        src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"
        integrity="sha512-zlWWyZq71UMApAjih4WkaRpikgY9Bz1oXIW5G0fED4vk14JjGlQ1UmkGM392jEULP8jbNMiwLWdM8Z87Hu88Fw=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#inputGroupFile').change(function() {
                var files = $('#inputGroupFile')[0].files;

                if (files.length > 0) {
                    var formData = new FormData();

                    formData.append('csv', files[0]);
                    formData.append('_token', '{{ csrf_token() }}');

                    $.ajax({
                        url: "{{ route('csv.uploadCsv') }}",
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(response) {
                            alert(response.message);
                        },
                        error: function(response) {
                            alert(response.responseJSON.message);
                        },
                        complete: function() {
                            $('#inputGroupFile').val('');
                        },
                    });
                } else {
                    alert("Please select a file.");
                }
            });

            setTimeout(refreshTableRows, 8000);
        });

        function refreshTableRows() {
            $.ajax({
                url: "{{ route('csv.refreshRows') }}",
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#csvRows').html(response.tableRows);
                    $.toast(response.message);
                },
                error: function(response) {
                    alert('Table refresh failed.');
                },
                complete: function() {
                    setTimeout(refreshTableRows, 8000);
                },
            });
        }
    </script>
</body>

</html>
