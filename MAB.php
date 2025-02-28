<?php

function get_fuel_prices() {
    $url = "https://www.pvoil.com.vn/tin-gia-xang-dau";
    $options = ["http" => ["header" => "User-Agent: Mozilla/5.0"]];
    $context = stream_context_create($options);
    $html = file_get_contents($url, false, $context);

    if ($html === false) {
        return ["time" => "Không xác định", "prices" => []];
    }

    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    // Tìm dữ liệu giá nhiên liệu
    $rows = $xpath->query("//tr");
    $prices = [];

    foreach ($rows as $index => $row) {
        $cols = $row->getElementsByTagName("td");
        if ($index == 0){
            if ($cols->length >= 3) {
                $time_node = trim($cols->item(2)->textContent);
            }
            continue;
        }

        if ($cols->length >= 3) {
            $item = trim($cols->item(1)->textContent);
            $price = trim($cols->item(2)->textContent);
            $prices[] = [$item, $price];
        }
    }
    preg_match('/(\d{2}:\d{2}) ngày (\d{2}\/\d{2}\/\d{4})/', $time_node, $matches);

    $time = isset($matches[1]) && isset($matches[2]) ? $matches[1] . " - " . $matches[2] : "Không xác định";



    return ["time" => $time, "prices" => $prices];
}

$data = get_fuel_prices();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng giá xăng dầu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 20px;
        }
        table {
            width: 50%;
            margin: 0 auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<h2>Bảng giá xăng dầu</h2>
<h3>Cập nhật lúc: <?= htmlspecialchars($data["time"]) ?></h3>

<?php if (!empty($data["prices"])): ?>
    <table>
        <tr>
            <th>Loại nhiên liệu</th>
            <th>Giá</th>
        </tr>
        <?php foreach ($data["prices"] as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row[0]) ?></td>
                <td><?= htmlspecialchars($row[1]) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Không có dữ liệu.</p>
<?php endif; ?>

</body>
</html>
