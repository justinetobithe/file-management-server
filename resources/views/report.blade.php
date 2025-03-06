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

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }

        .header-logo {
            width: 120px;
            text-align: center;
        }

        .header-logo img {
            width: 80px;
            height: auto;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-info {
            font-size: 12px;
            text-align: left;
        }

        .header-info td {
            text-align: left;
            padding: 5px;
        }

        .header-info strong {
            display: inline-block;
            width: 90px;
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
            justify-content: center;
            gap: 50px;
            margin-top: 20px;
            font-size: 12px;
        }

        .signature-box {
            width: 250px;
            text-align: center;
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

        .text-left {
            text-align: "left";
        }
    </style>
</head>

<body>

    <div class="container">

        <table class="header-table">
            <tr>
                <td class="header-logo">
                    <img src="{{ public_path('img/logo.png') }}" alt="Logo">
                    <br>PAGCOR
                </td>
                <td class="header-title">
                    RECORDS DIGITIZATION <br> MONITORING FORM
                </td>
                <td class="header-info">
                    <table>
                        <tr>
                            <td><strong>Page No.:</strong></td>
                            <td>Page 1 of 1</td>
                        </tr>
                        <tr>
                            <td><strong>Form No.:</strong></td>
                            <td>RMD - 409</td>
                        </tr>
                        <tr>
                            <td><strong>Revision No.:</strong></td>
                            <td>2</td>
                        </tr>
                        <tr>
                            <td><strong>Effectivity:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($reportData['effectiveDate'])->format('M. d, Y') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Folder Name (Main)</th>
                    <th>Folder Name (Sub)</th>
                    <th>Size</th>
                    <!-- <th>Folder Location</th> -->
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

                    <!-- <td>{{ $folder['local_path'] }}</td> -->

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
                <p class="text-left">Name: {{ $reportData['user']['first_name'] }} {{ $reportData['user']['last_name'] }}</p>
                <p class="text-left">
                    {{ $reportData['user']['department']['name'] ?? '' }}
                    @if(!empty($reportData['user']['department']['name']) && !empty($reportData['user']['designation']['designation']))
                    |
                    @endif
                    {{ $reportData['user']['designation']['designation'] ?? '' }}
                </p>
                <p class="text-left">Date: _______________</p>
            </div>
            <div class="signature-box">
                <p class="bold">Checked By:</p>
                <p class="text-left">Name: {{ $reportData['checkedBy']['first_name'] ?? '' }} {{ $reportData['checkedBy']['last_name'] ?? '' }}</p>
                <p class="text-left">
                    {{ $reportData['checkedBy']['department']['name'] ?? '' }}
                    @if(!empty($reportData['checkedBy']['department']['name']) && !empty($reportData['checkedBy']['designation']['designation']))
                    |
                    @endif
                    {{ $reportData['checkedBy']['designation']['designation'] ?? '' }}
                </p>
                <p class="text-left">Date: _______________</p>
            </div>
        </div>

    </div>

</body>

</html>