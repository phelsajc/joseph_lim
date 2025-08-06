<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .prescription {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border: 2px solid #000;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .patient-info, .doctor-info {
            margin-bottom: 20px;
        }
        .medications {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="prescription">
        <div class="header">
            <h2>Prescription</h2>
        </div>
        <div class="patient-info">
            <p><strong>Patient Name:</strong> John Doe</p>
            <p><strong>Date of Birth:</strong> January 1, 1980</p>
            <p><strong>Address:</strong> 123 Main St, City, Country</p>
        </div>
        <div class="medications">
            <h3>Medications:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Medication</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Medicine A</td>
                        <td>10mg</td>
                        <td>Once daily</td>
                    </tr>
                    <tr>
                        <td>Medicine B</td>
                        <td>20mg</td>
                        <td>Twice daily</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="doctor-info">
            <p><strong>Doctor Name:</strong> Dr. Jane Smith</p>
            <p><strong>License Number:</strong> 123456</p>
            <p><strong>Contact:</strong> (123) 456-7890</p>
        </div>
    </div>
</body>
</html>
