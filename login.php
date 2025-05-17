<?php
include "connect.php";

// Lấy dữ liệu từ request
$email = isset($_POST['email']) ? $_POST['email'] : '';
$pass = isset($_POST['password']) ? $_POST['password'] : '';

$query="SELECT * FROM users WHERE email='$email' AND password='$pass' AND status ='active'";
$data=mysqli_query($conn,$query);
$result=array();
while($row=mysqli_fetch_assoc($data)){
    $result[]=($row);

}
if(!empty($result)){
    $arr = [
        "success" => true,
        "result"=>$result
    ];
}else{
    $arr = [
        "success" => false
    ];
}
print_r(json_encode($arr));

?>