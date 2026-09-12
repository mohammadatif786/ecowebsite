<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>{{ $title }}</h2>
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>User Email</th>
                <th>Ticket Name</th>
                <th>Tickets</th>
                <th>Events</th>
                <th>Start</th>
                <th>End</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($content as $index => $item)
                <tr>
                    <td>{{ $item->user->name ?? '--' }}</td>
                    <td>{{ $item->user->email ?? '--' }}</td>
                    <td>{{ $item->ticket->name ?? '--' }}</td>
                    <td>{{ $item->no_of_tickets ?? '--' }}</td>
                    <td>{{ $item->event->title ?? '--' }}</td>
                    <td>{{ $item->event?->start_time ? $item->event->start_time->format('l, d M Y h:i A') : '--' }}</td>
                    <td>{{ $item->event?->end_time ? $item->event->end_time->format('l, d M Y h:i A') : '--' }}</td>

                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
