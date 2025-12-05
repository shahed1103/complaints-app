<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; direction: rtl; text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 8px; }
    </style>
</head>
<body>
    <h2>قائمة الشكاوى</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>العنوان</th>
                <th>الوصف</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($complaints as $complaint)
                <tr>
                    <td>{{ $complaint->id }}</td>
                    <td>{{ $complaint->title }}</td>
                    <td>{{ $complaint->description }}</td>
                    <td>{{ $complaint->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
