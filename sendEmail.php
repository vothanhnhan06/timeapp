<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "connect.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Default response
$arr = [
    "success" => false,
    "error" => "Unknown error"
];

if (isset($_POST['email']) && isset($_POST['code'])) {
    $email = $_POST['email'];
    $code = $_POST['code'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $numrow = $result->num_rows;

    if ($numrow == 0) {
        $arr = [
            "success" => false,
            "error" => "User not found"
        ];
    } else {
        // Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'nhanvohere@gmail.com';
            $mail->Password = 'refc cjch pmpe igfv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->setFrom("nhanvohere@gmail.com", 'TimerApp');
            $mail->addAddress($email);
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Verify CODE';
            $mail->Body = "Mã code của bạn là: $code. Mã code sẽ hết hạn trong vòng 3 phút!.";
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $mail->send();

            // Insert into Verify table
            $stmt1 = $conn->prepare("INSERT INTO otp_verification (email, otp, status) VALUES (?, ?, 'pending')");
            $stmt1->bind_param("ss", $email, $code);
            if ($stmt1->execute()) {
                $arr = [
                    "success" => true
                ];
            } else {
                $arr = [
                    "success" => false,
                    "error" => "Failed to insert OTP into database"
                ];
            }
        } catch (Exception $e) {
            $arr = [
                "success" => false,
                "error" => "Failed to send email: " . $mail->ErrorInfo
            ];
        }
    }
} else {
    $arr = [
        "success" => false,
        "error" => "Missing email or code in request"
    ];
}

// Always output JSON
header('Content-Type: application/json');
echo json_encode($arr);
?>