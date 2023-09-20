<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tỉ giá ngoại tệ</title>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

</head>
<body>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "currency";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$api_url = "https://portal.vietcombank.com.vn/Usercontrols/TVPortal.TyGia/pXML.aspx?b=8";
$response = file_get_contents($api_url);

$data = simplexml_load_string($response);

foreach ($data->Exrate as $exrate) {
    $currency_code = (string)$exrate['CurrencyName'];
    $buy_rate = (float)str_replace(',', '', $exrate['Buy']);
    $transfer_rate = (float)str_replace(',', '', $exrate['Transfer']);
    $sell_rate = (float)str_replace(',', '', $exrate['Sell']);
    
    $sql_check = "SELECT * FROM exchange WHERE currency_code = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $currency_code);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        $sql_insert = "INSERT INTO exchange (currency_code, buy_rate, transfer_rate, sell_rate) VALUES (?, ?, ?, ?)";
        
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sddd", $currency_code, $buy_rate, $transfer_rate, $sell_rate);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    $stmt_check->close();
}

$sql = "SELECT * FROM exchange";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    ?> 
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <table id="table" class="table table-hover table-responsive">
                <thead class="table-info">
                    <tr>
                        <th>Currency Code</th>
                        <th>Buy Rate</th>
                        <th>Transfer Rate</th>
                        <th>Sell Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row["currency_code"] ?></td>
                            <td><?php echo $row["buy_rate"] ?></td>
                            <td><?php echo $row["transfer_rate"] ?></td>
                            <td><?php echo $row["sell_rate"] ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
} else {
    echo "Không có dữ liệu tỷ giá ngoại tệ.";
}

$conn->close();
?>
</body>
</html>

<script>
    function reloadPage() {
        location.reload();
    }

    setInterval(reloadPage, 600000);

    $(document).ready(function() {
        $('#table').DataTable();
    });
</script>






