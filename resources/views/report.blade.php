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

        .header img {
            width: 120px;
            height: 120px;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-info {
            font-size: 12px;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #e5e7eb;
            color: #333;
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
    </style>
</head>

<body>

    <div class="container">

        <div class="header">
            <div class="logo">
                <img src="{{ asset('http://127.0.0.1:8000/img/logo.png') }}" alt="PAGCOR Logo">
            </div>
            <div class="title">
                <div class="header-title">Records Digitization Monitoring Form</div>
            </div>
            <div class="header-info">
                <p><strong>Page No.:</strong> Page 1 of 1</p>
                <p><strong>Form No.:</strong> RMD - 409</p>
                <p><strong>Revision No.:</strong> 2</p>
                <p><strong>Effectivity:</strong> Jan. 30, 2024</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Folder Name (Main)</th>
                    <th>Folder Name (Sub)</th>
                    <th>Folder Size</th>
                    <th>Folder Location</th>
                    <th>Coverage Period</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td rowspan="6">Logistics Management Section</td>
                    <td rowspan="6">LMS_BACKUP_01102025 (36.1 MB)</td>
                    <td>ACCOMPLISHMENT REPORT DECEMBER 2024</td>
                    <td>1.29 MB</td>
                    <td rowspan="6">D:\OneDrive\OneDrive - pagcor.ph\RECORDS DIGITIZATION FOLDER</td>
                    <td rowspan="6">DECEMBER 1–31, 2024</td>
                </tr>
                <tr>
                    <td>DIS & IAR DECEMBER 2024</td>
                    <td>13.2 MB</td>
                </tr>
                <tr>
                    <td>EOM RSM DECEMBER 2024</td>
                    <td>923 KB</td>
                </tr>
                <tr>
                    <td>FAMS DECEMBER 2024</td>
                    <td>6.27 MB</td>
                </tr>
                <tr>
                    <td>REQUISITION AND ISSUE SLIP DECEMBER 2024</td>
                    <td>2.56 MB</td>
                </tr>
                <tr>
                    <td>RV TRANSACTIONS DECEMBER 2024</td>
                    <td>12.3 MB</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-box">
                <p class="bold">Prepared By:</p>
                <p>LEXTER R. ODUCAYEN</p>
                <p>STOCKMAN I</p>
                <p>Date: _______________</p>
            </div>
            <div class="signature-box">
                <p class="bold">Checked By:</p>
                <p>KATHYREEN C. GONZALES</p>
                <p>LMO II</p>
                <p>Date: _______________</p>
            </div>
        </div>

    </div>

</body>

</html>