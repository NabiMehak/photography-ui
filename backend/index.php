
<?php
include 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$request_method = $_SERVER["REQUEST_METHOD"];


switch($request_method) {
  case 'GET':
    getUsers($conn);
      break;
  case 'POST':

      break;
  case 'PUT':
      break;
  case 'DELETE':
      break;
  default:
      http_response_code(405);
      break;
}

function getUsers($conn) {
  $sql = "SELECT * FROM userauthentication";
  $result = $conn->query($sql);
  $users = [];

  if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $users[] = $row;
    }
    echo json_encode($users);
  } else {
    echo json_encode([]);
  }
  $conn->close();
}

?>