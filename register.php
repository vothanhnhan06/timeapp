<?php
include "connect.php";


// Đặt header để trả về JSON
header('Content-Type: application/json; charset=UTF-8');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    $arr = [
        "success" => false
    ];
    echo json_encode($arr);
    exit();
}

// Lấy dữ liệu từ request
$email = isset($_POST['email']) ? $_POST['email'] : '';
$username = isset($_POST['username']) ? $_POST['username'] : '';
$pass = isset($_POST['password']) ? $_POST['password'] : '';


// Kiểm tra dữ liệu đầu vào
if (empty($email) || empty($username) || empty($pass)) {
    $arr = [
        "success" => false
    ];
    echo json_encode($arr);
    exit();
}

// Check email/username đã tồn tại
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR name = ?");
$stmt->bind_param("ss", $email, $username);
$stmt->execute();
$result = $stmt->get_result();
$numrow = $result->num_rows;

if ($numrow != 0) {
    $arr = [
        "success" => false
    ];
} else {
    // Thêm người dùng mới
    $stmt = $conn->prepare("INSERT INTO users (email, password, name,status) VALUES (?, ?, ?,'pending')");
    $stmt->bind_param("sss", $email, $pass, $username);
    if ($stmt->execute()) {
        $arr = [
            "success" => true
        ];
    } else {
        $arr = [
            "success" => false
        ];
    }
}

// Trả về JSON
echo json_encode($arr);

// Đóng kết nối
$stmt->close();
$conn->close();
?>