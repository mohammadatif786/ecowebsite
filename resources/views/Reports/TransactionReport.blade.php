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
                <th>Transaction ID</th>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Amount</th>
                <th>Commission</th>
                <th>Received</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($content as $index => $item)
                <tr>
                    <td>{{ $item->uuid ?? '--' }}</td>
                    <td>{{ optional($item->from)->name ?? (optional($item->from)->email ?? 'System') }}</td>
                    <td>{{ optional($item->to)->name ?? (optional($item->to)->email ?? 'System') }}</td>
                    <td>{{ $item->amount ? $item->amount . ' ' . ($item->currency ?? '') : '--' }}</td>
                    <td>{{ $item->commission ? $item->commission . ' ' . ($item->currency ?? '') : '--' }}</td>
                    <td>{{ $item->received ? $item->received . ' ' . ($item->currency ?? '') : '--' }}</td>
                    <td>{{ $item->status ?? '--' }}</td>
                    <td>{{ $item->created_at ? $item->created_at->format('l, d M Y h:i A') : '--' }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>
</body>

</html>
