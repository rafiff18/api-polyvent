<?php

require_once "../database/Database.php";
require_once "../helpers/ResponseHelper.php";

class EventController {
    private $conn;

    public function __construct($conn) {
        if (!$conn) {
            response(false, 'Database connection failed');
        }
        $this->conn = $conn;
    }

    public function getAllEvent() {
        $query = "SELECT * FROM event_main";
        $data = array();

        $stmt = $this->conn->query($query);

        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $data[] = $row;
            }
            response(true, 'List of Events Retrieved Successfully', $data);
        } else {
            response(false, 'Failed to Retrieve Events', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }

    public function getEventById($id = 0) {
        if ($id != 0) {
            $query = "SELECT * FROM event_main WHERE event_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(PDO::FETCH_OBJ);
                response(true, 'Event Retrieved Successfully', $data);
            } else {
                response(false, 'Event not found', null, [
                    'code' => 404,
                    'message' => 'The requested resource could not be found'
                ]);
            }
        } else {
            response(false, 'Invalid ID', null, [
                'code' => 401,
                'message' => 'Bad request: ID is required'
            ]);
        }
    }

    public function createEvent() {
        $arrcheckpost = array(
            'title' => '', 
            'date_add' => '', 
            'category_id' => '', 
            'desc_event' => '',
            'poster' => '',
            'location' => '',
            'quota' => '',
            'date_start' => '',
            'date_end' => ''
        );

        $count = count(array_intersect_key($_POST, $arrcheckpost));
        
        if ($count == count($arrcheckpost)) {
            $query = "INSERT INTO event_main (title, date_add, category_id, desc_event, poster, location, quota, date_start, date_end) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([
                $_POST['title'], 
                $_POST['date_add'], 
                $_POST['category_id'], 
                $_POST['desc_event'],
                $_POST['poster'],
                $_POST['location'],
                $_POST['quota'],
                $_POST['date_start'],
                $_POST['date_end'],
            ])) {
                $insert_id = $this->conn->lastInsertId();

                $result_stmt = $this->conn->prepare("SELECT * FROM event_main WHERE event_id = ?");
                $result_stmt->execute([$insert_id]);
                $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);

                response(true, 'Event Added Successfully', $new_data);
            } else {
                response(false, 'Failed to Add Event', null, [
                    'code' => 500,
                    'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
                ]);
            }
        } else {
            response(false, 'Missing Parameters', null, [
                'code' => 402,
                'message' => 'Bad request: Missing required parameters'
            ]);
        }
    }

    public function updateEvent($id) {
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            response(false, 'Invalid JSON Format', null, [
                'code' => 400,
                'message' => 'Bad request: JSON parsing error'
            ]);
            return;
        }
    
        $required_fields = ['title', 'date_add', 'category_id', 'desc_event', 'poster', 'location', 'quota', 'date_start', 'date_end'];
        $missing_fields = array_diff($required_fields, array_keys($input));
    
        if (!empty($missing_fields)) {
            response(false, 'Missing Parameters', null, [
                'code' => 403,
                'message' => 'Missing required parameters: ' . implode(', ', $missing_fields)
            ]);
            return;
        }
    
        $query = 'UPDATE event_main SET title = ?, date_add = ?, category_id = ?, desc_event = ?, poster = ?, location = ?, quota = ?, date_start = ?, date_end = ? WHERE event_id = ?';
        $stmt = $this->conn->prepare($query);
    
        if ($stmt->execute([
            $input['title'], 
            $input['date_add'], 
            $input['category_id'], 
            $input['desc_event'],
            $input['poster'],
            $input['location'],
            $input['quota'],
            $input['date_start'],
            $input['date_end'],
            $id
        ])) {
            $query = "SELECT * FROM event_main WHERE event_id = ?";
            $result_stmt = $this->conn->prepare($query);
            $result_stmt->execute([$id]);
            $updated_data = $result_stmt->fetch(PDO::FETCH_OBJ);
    
            response(true, 'Event Updated Successfully', $updated_data);
        } else {
            response(false, 'Failed to Update Event', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }
    

    public function deleteEvent($id) {
        $stmt = $this->conn->prepare('DELETE FROM event_main WHERE event_id = ?');

        if ($stmt->execute([$id])) {
            response(true, 'Event Deleted Successfully');
        } else {
            response(false, 'Failed to Delete Event', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }
}
?>