<?php

require_once "../helpers/ResponseHelper.php";

class CategoryController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Mendapatkan semua kategori
    public function getAllCategories() {
        $query = "SELECT * FROM category";
        $stmt = $this->conn->query($query);

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $data[] = $row;
        }

        response('success', 'List of Categories Retrieved Successfully', $data);
    }

    public function getCategoryById($id) {
        $query = "SELECT * FROM category WHERE category_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            response('success', 'Category Retrieved Successfully', $data);
        } else {
            response('error', 'Category Not Found', null, 404);
        }
    }

    public function createCategory() {
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (is_null($input) || !isset($input['category_name'])) {
            response('error', 'Invalid JSON or Missing category name', null, 400);
            return;
        }
    
        $query = "INSERT INTO category (category_name) VALUES (?)";
        $stmt = $this->conn->prepare($query);
    
        if ($stmt->execute([$input['category_name']])) {
            $id = $this->conn->lastInsertId();
    
            $selectQuery = "SELECT category_name FROM category WHERE category_id = ?";
            $selectStmt = $this->conn->prepare($selectQuery);
            $selectStmt->execute([$id]);
            $category = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
            response('success', 'Category Created Successfully', [
                'category_id' => $id,
                'category_name' => $category['category_name']
            ], 201);
        } else {
            response('error', 'Failed to Create Category', null, 500);
        }
    }
    

    public function updateCategory($id) {
        // Decode JSON input
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (is_null($input) || !isset($input['category_name'])) {
            response('error', 'Invalid JSON or Missing Category Name', null, 400);
            return;
        }
    
        $query = "UPDATE category SET category_name = ? WHERE category_id = ?";
        $stmt = $this->conn->prepare($query);
    
        if ($stmt->execute([$input['category_name'], $id])) {
            $selectQuery = "SELECT category_name FROM category WHERE category_id = ?";
            $selectStmt = $this->conn->prepare($selectQuery);
            $selectStmt->execute([$id]);
            $category = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
            response('success', 'Category Updated Successfully', [
                'category_id' => $id,
                'category_name' => $category['category_name']
            ], 200);
        } else {
            response('error', 'Failed to Update Category', null, 500);
        }
    }    

    public function deleteCategory($id) {
        $stmt = $this->conn->prepare("DELETE FROM category WHERE category_id = ?");

        if ($stmt->execute([$id])) {
            response('success', 'Category Deleted Successfully');
        } else {
            response('error', 'Failed to Delete Category', null, 500);
        }
    }
}
?>