<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: black;
            margin: 0;
            padding: 0;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid gray;
            padding-bottom: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            color: black;
        }

        .meta {
            margin-top: 5px;
            color: dimgray;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background-color: lightgray;
            color: black;
            text-align: center;
            padding: 8px;
            border: 1px solid silver;
            font-weight: bold;
        }

        td {
            padding: 8px;
            border: 1px solid silver;
            text-align: center;
            vertical-align: middle;
        }

        tr:nth-child(even) {
            background-color: whitesmoke;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">{{ $title }}</div>
        <div class="meta">Generated on: {{ $generatedAt }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>UUID</th>
                <th>Title</th>
                <th>Slug</th>
                <th>Category</th>
                <th>Publication Status</th>
                <th>Status</th>
                <th>Published At</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['uuid'] }}</td>
                    <td>{{ $row['title'] }}</td>
                    <td>{{ $row['slug'] }}</td>
                    <td>{{ $row['category'] }}</td>
                    <td>{{ $row['publication_status'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['published_at'] }}</td>
                    <td>{{ $row['created_at'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
