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
$pass = isset($_POST['password']) ? $_POST['password'] : '';


// Kiểm tra dữ liệu đầu vào
if (empty($email) || empty($pass)) {
    $arr = [
        "success" => false
    ];
    echo json_encode($arr);
    exit();
}

// Check email đã tồn tại
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if (!empty($data)) {
    $old_pass=$data[0];
    $str_old_pass = $old_pass['password'];

    if($str_old_pass==$pass){
        $arr = [
        "success" => false,
        "message"=> "Không dùng mật khẩu cũ"
    ];
    }else{
        $updateQuery = "UPDATE users SET password = '$pass' WHERE email = '$email'";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->execute();

        $arr = [
            "success" => true,
            "message" => "Password updated!"
        ];
    }
    
    
} else {
   $arr = [
        "success" => false,
        "message" => "Password not updated!"
    ];
}

// Trả về JSON
echo json_encode($arr);

// Đóng kết nối
$stmt->close();
$conn->close();
?>