<?php
require_once '../app/core/Model.php';

class FileModel {
    private $db;
    private $table = "files";

    public function __construct() {
        $this->db = Database::connect();
    }

    // Get all files
    public function getAll() {
        $stmt = $this->db->query("SELECT f.*, u.firstName, u.lastName, u.position 
                                  FROM ".$this->table." f 
                                  LEFT JOIN UserTbl u ON f.uploader = u.id 
                                  ORDER BY f.uploadedat DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single file by id
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM ".$this->table." WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Insert file
    public function create($filename, $filepath, $description, $uploaded_by, $fileCategory, $position, $directionFrom, $directionTo) {
        $stmt = $this->db->prepare("INSERT INTO ".$this->table." 
            (`filename`, `filepath`, `desc`, `uploader`, `category`, `position`, `directionFrom`, `directionTo`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        return $stmt->execute([$filename, $filepath, $description, $uploaded_by, $fileCategory, $position, $directionFrom, $directionTo]);
    } 

    // Log Insert file upload
    public function newLog($userID, $fileName, $uploaded_by, $position, $fileCategory, $directionFrom, $directionTo, $uploader, $description = '') {                   
        $customDesc = "New file uploaded: Filename: $fileName • Category: $fileCategory • Description: $description • Uploaded by User ID: $uploader";
        
        $stmt2 = $this->db->prepare("INSERT INTO systemLogs (`userName`, `logDesc`, `module`, `logDate`) VALUES (?, ?, ?, ?)");
        $stmt2->execute([$userID, $customDesc, "File Management", date("Y-m-d H:i:s")]);
    }

    // Update file
    public function update($id, $filename, $filepath, $description, $fileCategory) {
        $stmt = $this->db->prepare("UPDATE ".$this->table." 
            SET `filename`=?, `filepath`=?, `desc`=?, `category`=? 
            WHERE `id`=?");
        
        return $stmt->execute([$filename, $filepath, $description, $fileCategory, $id]);
    }

    // Log Update file
    public function updateLog($id, $userID, $filename, $filepath, $description, $fileCategory, $uploader){                   
        $customDesc = "File updated: ID $id • New Filename: $filename • New Category: $fileCategory • New Description: $description • Updated by User ID: $uploader";
        
        $stmt2 = $this->db->prepare("INSERT INTO systemLogs (`userName`, `logDesc`, `module`, `logDate`) VALUES (?, ?, ?, ?)");
        $stmt2->execute([$userID, $customDesc, "File Management", date("Y-m-d H:i:s")]);
    }

    // Log Delete file
    public function deleteLog($id, $userID, $filename, $filepath, $description, $fileCategory, $uploader){                   
        $customDesc = "File deleted: Upload number: $id • Deleted by User ID: $uploader";
        
        $stmt2 = $this->db->prepare("INSERT INTO systemLogs (`userName`, `logDesc`, `module`, `logDate`) VALUES (?, ?, ?, ?)");
        $stmt2->execute([$userID, $customDesc, "File Management", date("Y-m-d H:i:s")]);
    }

    // Delete file
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM ".$this->table." WHERE id=?");
        return $stmt->execute([$id]);   
    }
}