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

        .header-logo img {
            width: 80px;
            height: auto;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
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
        }

        th {
            background-color: #e5e7eb;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        td ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        td ul li {
            display: flex;
            justify-content: space-between;
        }


        .signature-section {
            display: table;
            width: 100%;
            margin-top: 30px;
            page-break-before: avoid;
        }

        .signature-row {
            display: table-row;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: left;
            vertical-align: top;
            padding: 20px;
            font-size: 14px;
        }

        .signature-box p {
            margin: 5px 0;
        }

        .bold {
            font-weight: bold;
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
                <td>
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
                        <ul>
                            @foreach($folder['subfolders'] as $subfolder)
                            <li>{{ wordwrap($subfolder['folder_name'], 30, "<br>", true) }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        <ul>
                            @foreach($folder['subfolders'] as $subfolder)
                            <li>{{ $subfolder['total_size'] }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($folder['start_date'])->format('M. d, Y') }} –
                        {{ \Carbon\Carbon::parse($folder['end_date'])->format('M. d, Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-row">
                <!-- Prepared By -->
                <div class="signature-box">
                    <p class="bold">Prepared By:</p>
                    <p>Name: {{ $reportData['user']->first_name }} {{ $reportData['user']->last_name }}</p>
                    <p>
                        {{ $reportData['user']->position->department->name ?? 'N/A' }}
                        @if(!empty($reportData['user']->position->department->name) && !empty($reportData['user']->position->designation->designation))
                        |
                        @endif
                        {{ $reportData['user']->position->designation->designation ?? 'N/A' }}
                    </p>
                    <p>Date: _______________</p>
                </div>

                <!-- Checked By -->
                <div class="signature-box">
                    <p class="bold">Checked By:</p>
                    <p>Name: {{ $reportData['checkedBy']->first_name ?? 'N/A' }} {{ $reportData['checkedBy']->last_name ?? '' }}</p>
                    <p>
                        {{ $reportData['checkedBy']->position->department->name ?? 'N/A' }}
                        @if(!empty($reportData['checkedBy']->position->department->name) && !empty($reportData['checkedBy']->position->designation->designation))
                        |
                        @endif
                        {{ $reportData['checkedBy']->position->designation->designation ?? 'N/A' }}
                    </p>
                    <p>Date: _______________</p>
                </div>
            </div>
        </div>

    </div>

</body>

</html>