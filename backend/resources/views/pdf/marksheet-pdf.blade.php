<!DOCTYPE html>
<html>
<head>

<style>

body{
font-family: DejaVu Sans;
}

table{
width:100%;
border-collapse:collapse;
}

th,td{
border:1px solid #000;
padding:8px;
text-align:center;
}

.header{
text-align:center;
margin-bottom:20px;
}

</style>

</head>

<body>

<div class="header">

<h2>School Marksheet</h2>

<p>
Student: {{ $student->name }}
</p>

<p>
Class: {{ $student->class->name }}
-
Section: {{ $student->section->name }}
</p>

<p>
Exam: {{ $exam->name }}
</p>

</div>

<table>

<thead>

<tr>
<th>Subject</th>
<th>Marks Obtained</th>
<th>Max Marks</th>
<th>Pass Marks</th>
</tr>

</thead>

<tbody>

@foreach($subjects as $subject)

<tr>

<td>{{ $subject['subject'] }}</td>

<td>{{ $subject['marks'] ?? '-' }}</td>

<td>{{ $subject['max_marks'] }}</td>

<td>{{ $subject['pass_marks'] }}</td>

</tr>

@endforeach

</tbody>

</table>

<br>

<p>Total Marks: {{ $total }} / {{ $maxTotal }}</p>

<p>Percentage: {{ $percentage }} %</p>

<p>Result: {{ $result }}</p>

</body>

</html>