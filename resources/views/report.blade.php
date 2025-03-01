<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Records Digitization Monitoring Form</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .logo {
            flex: 0 0 auto;
        }

        .title {
            flex: 1;
            text-align: center;
        }

        .header-info {
            font-size: 12px;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 12px;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
            word-wrap: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }

        tbody {
            display: block;
            max-height: 300px;
            overflow-y: auto;
        }

        thead,
        tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        th {
            background-color: #e5e7eb;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 12px;
        }

        .signature-box {
            width: 45%;
        }

        .signature-box p {
            margin: 5px 0;
        }

        .bold {
            font-weight: bold;
        }

        .subfolder-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
        }

        .subfolder-item {
            display: flex;
            justify-content: space-between;
            border: 1px solid #ccc;
            padding: 5px;
            align-items: center;
        }

        .subfolder-name {
            flex: 1;
        }

        .subfolder-size {
            text-align: right;
            min-width: 80px;
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="header">
            <div class="logo">
                <img src="{{ public_path('img/logo.png') }}" alt="Logo" width="150" height="150">
            </div>
            <div class="title">
                <div class="header-title">Records Digitization Monitoring Form</div>
            </div>
            <div class="header-info">
                <p><strong>Page No.:</strong> Page 1 of 1</p>
                <p><strong>Form No.:</strong> RMD - 409</p>
                <p><strong>Revision No.:</strong> 2</p>
                <p><strong>Effectivity:</strong> {{ \Carbon\Carbon::parse($reportData['effectiveDate'])->format('M. d, Y') }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Folder Name (Main)</th>
                    <th>Folder Name (Sub)</th>
                    <th>Size</th>
                    <th>Folder Location</th>
                    <th>Coverage Period</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['folders'] as $folder)
                <tr>
                    <td>
                        @foreach($folder['departments'] as $department)
                        {{ $department['name'] }}@if(!$loop->last), @endif
                        @endforeach
                    </td>

                    <td>{{ $folder['folder_name'] }}</td>

                    <td>
                        <ul class="subfolder-list">
                            @foreach($folder['subfolders'] as $subfolder)
                            <li class="subfolder-item">
                                <div class="subfolder-name">
                                    {{ wordwrap($subfolder['folder_name'], 30, "<br>", true) }}
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </td>

                    <td>
                        <ul class="subfolder-list">
                            @foreach($folder['subfolders'] as $subfolder)
                            <li class="subfolder-item">
                                <div class="subfolder-name">
                                    {{ $subfolder['total_size'] }}
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </td>

                    <td>{{ $folder['local_path'] }}</td>

                    <td>
                        {{ \Carbon\Carbon::parse($folder['start_date'])->format('M. d, Y') }} –
                        {{ \Carbon\Carbon::parse($folder['end_date'])->format('M. d, Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-box">
                <p class="bold">Prepared By:</p>
                <p>{{ $reportData['user']['first_name'] }} {{ $reportData['user']['last_name'] }}</p>
                <p>{{ $reportData['user']['designation']['designation'] ?? '' }}</p>
                <p>Date: _______________</p>
            </div>
            <div class="signature-box">
                <p class="bold">Checked By:</p>
                <p>{{ $reportData['checkedBy']['first_name'] }} {{ $reportData['checkedBy']['last_name'] }}</p>
                <p>{{ $reportData['checkedBy']['designation']['designation'] ?? '' }}</p>
                <p>Date: _______________</p>
            </div>
        </div>
    </div>

</body>

</html>