<?php
include "connect.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Thiết lập múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Lấy dữ liệu từ request
$email = isset($_POST['email']) ? $_POST['email'] : '';
$otp = isset($_POST['code']) ? $_POST['code'] : '';

// Default response
$arr = [
    "success" => false,
    "message" => "Unknown error"
];

// Kiểm tra nếu email hoặc otp không được cung cấp
if (empty($email) || empty($otp)) {
    $arr = [
        "success" => false,
        "message" => "Email or OTP code is missing"
    ];
    exit;
}

// Truy vấn để lấy OTP từ bảng otp_verification
$query = "SELECT * FROM otp_verification WHERE email = ? AND otp = ? AND status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Kiểm tra nếu OTP tồn tại
if (!empty($data)) {
    $otpRecord = $data[0]; // Lấy bản ghi OTP đầu tiên

    // Lấy thời gian hiện tại
    $currentTime = time(); // 2025-05-16 23:14:00

    // Lấy thời gian tạo OTP
    $createdAt = strtotime($otpRecord['created_at']);
    if ($createdAt === false) {
        $arr = [
            "success" => false,
            "message" => "Invalid created_at timestamp"
        ];
        exit;
    }

    // Kiểm tra nếu có expires_at, nếu không thì tính toán
    if (isset($otpRecord['expires_at']) && !empty($otpRecord['expires_at'])) {
        $expiredAt = strtotime($otpRecord['expires_at']);
        if ($expiredAt === false) {
            $arr = [
                "success" => false,
                "message" => "Invalid expires_at timestamp"
            ];
            exit;
        }
    } else {
        // Nếu không có expires_at, tính toán: created_at + 3 phút (180 giây)
        $expiredAt = $createdAt + 180;
    }

    // Kiểm tra nếu OTP còn hiệu lực
    if ($currentTime >= $createdAt && $currentTime <= $expiredAt) {
        // OTP còn hiệu lực
        // Cập nhật trạng thái OTP thành 'used'
        $updateQuery = "UPDATE otp_verification SET status = 'used' WHERE email = ? AND otp = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ss", $email, $otp);
        $updateStmt->execute();

        //Cập nhật trạng thái của user thành active trong trường hợp check OTP đăng kí
        $updateQuery1 = "UPDATE users SET status = 'active' WHERE email = ?";
        $updateStmt1 = $conn->prepare($updateQuery1);
        $updateStmt1->bind_param("s", $email);
        $updateStmt1->execute();

        $arr = [
            "success" => true,
            "message" => "OTP verified successfully"
        ];
    } else {
        // OTP đã hết hạn
        $arr = [
            "success" => false,
            "message" => "OTP has expired",
        ];
    }
} else {
    // OTP không tồn tại hoặc đã được sử dụng
    $arr = [
        "success" => false,
        "message" => "Invalid OTP or OTP already used"
    ];
}
header('Content-Type: application/json');
echo json_encode($arr);
?>