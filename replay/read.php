<?php   
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();

$result = $db->query("SELECT * FROM replay_comment");

if ($result->num_rows > 0) {
    $replay_arr = array();
    while ($row = $result->fetch_assoc()) {
        $replay_item = array(
            "replay_id" => $row["replay_id"],
            "comment_id" => $row["comment_id"],
            "users_id" => $row["users_id"],
            "content_replay" => $row["content_replay"],
        );
        array_push($replay_arr, $replay_item);
    }
    http_response_code(200);
    echo json_encode($replay_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No replay found."));
}
?>