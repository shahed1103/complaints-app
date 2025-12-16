    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>تقرير الشكاوى</title>
        <style>
            /* استدعاء خط عربي من Google Fonts */
            @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;700&display=swap'); /* يمكنك تغيير الخط حسب تفضيلك */

            body {
                font-family: 'Noto Sans Arabic', sans-serif; /* استخدم الخط المستدعى هنا */
                margin: 40px;
                direction: rtl;
                text-align: right;
            }
            .title {
                text-align: center;
                font-size: 24px;
                margin-bottom: 30px;
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 10px;
                text-align: right;
                font-family: 'Noto Sans Arabic', sans-serif; /* تأكد من أن الخط مطبق على خلايا الجدول أيضاً */
            }
            th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .footer {
                margin-top: 50px;
                text-align: center;
                font-size: 12px;
                color: #777;
                font-family: 'Noto Sans Arabic', sans-serif; /* طبّق الخط هنا أيضاً */
            }
        </style>
    </head>
    <body>

        <div class="title">complaints report</div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>the client</th>
                    <th>the type</th>
                    <th>the department</th>
                    <th>the status</th>
                    <th>the descrepsion </th>
                    <th>the location</th>
                    <th>date of complaint </th>
                </tr>
            </thead>
            <tbody>
                @foreach($complaints as $index => $complaint)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $complaint->user->name ?? 'غير معروف' }}</td>
                        <td>{{ $complaint->complaintType->type ?? 'غير معروف' }}</td>
                        <td>{{ $complaint->complaintDepartment->depatment_name ?? 'غير معروف' }}</td>
                        <td>{{ $complaint->complaintStatus->status ?? 'غير معروف' }}</td>

                        <td>{{ $complaint->problem_description }}</td>
                        <td>{{ $complaint->location }}</td>
                        <td>{{ $complaint->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
          complaint report created successfuly
        </div>

    </body>
    </html>
