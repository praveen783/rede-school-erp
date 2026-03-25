<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header img {
            height: 60px;
        }
        .box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background: #f0f0f0;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    @if(!empty($school) && !empty($school->logo_path))
        <img src="{{ public_path($school->logo_path) }}">
    @endif

    <h2>{{ $school->name ?? 'School Name' }}</h2>
    <p>{{ $school->address ?? '' }}</p>
</div>

<div class="title">HALL TICKET / ADMIT CARD</div>

<!-- STUDENT DETAILS -->
<div class="box">
    <table>
        <tr>
            <td><strong>Hall Ticket No</strong></td>
            <td>{{ $hallTicketNo ?? '-' }}</td>

            <td rowspan="3" colspan="2" align="center">
                <div style="width:120px;height:150px;border:1px solid #000;">
                    @if(!empty($studentPhoto))
                        <img src="{{ $studentPhoto }}"
                             style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <span style="font-size:10px;">No Photo</span>
                    @endif
                </div>
            </td>
        </tr>

        <tr>
            <td><strong>Student Name</strong></td>
            <td>{{ $student->name ?? '-' }}</td>
        </tr>

        <tr>
            <td><strong>Admission No</strong></td>
            <td>{{ $student->admission_no ?? '-' }}</td>
        </tr>

        <tr>
            <td><strong>Class & Section</strong></td>
            <td colspan="3">
                Class {{ $student->class->name ?? '-' }} -
                Section {{ $student->section->name ?? '-' }}
            </td>
        </tr>

        <tr>
            <td><strong>Exam Name</strong></td>
            <td colspan="3">{{ $exam->name ?? '-' }}</td>
        </tr>
    </table>
</div>


<!-- EXAM SCHEDULE -->
<div class="box">
    <h4>Exam Schedule</h4>

    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Date</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($schedules as $row)
                <tr>
                    <td>{{ $row->subject->name ?? 'N/A' }}</td>
                    <td>
                        {{ !empty($row->exam_date)
                            ? \Carbon\Carbon::parse($row->exam_date)->format('d-m-Y')
                            : '-' }}
                    </td>
                    <td>{{ $row->start_time ?? '-' }} - {{ $row->end_time ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center;">
                        No exam schedule available
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- INSTRUCTIONS -->
<div class="box">
    <strong>Instructions:</strong>
    <ol>
        <li>Carry this admit card to the examination hall.</li>
        <li>Student must arrive at least 30 minutes before exam time.</li>
        <li>Electronic devices are strictly prohibited.</li>
    </ol>
</div>

<!-- SIGNATURE -->
<div style="margin-top: 50px; text-align: right;">
    <p>________________________</p>
    <p>Principal Signature</p>
</div>

</body>
</html>
