<?php
// Script untuk mengupdate format tingkat kelas dari numerik ke romawi
require_once 'db_connect.php';

try {
    // Update tingkat dari format numerik ke romawi
    $updates = [
        ['old' => '10', 'new' => 'X'],
        ['old' => '11', 'new' => 'XI'],
        ['old' => '12', 'new' => 'XII']
    ];
    
    foreach ($updates as $update) {
        $sql = "UPDATE kelas SET tingkat = ? WHERE tingkat = ?";
        $result = executeQuery($sql, [$update['new'], $update['old']]);
        
        if ($result) {
            echo "Berhasil mengupdate tingkat {$update['old']} menjadi {$update['new']}\n";
        } else {
            echo "Gagal mengupdate tingkat {$update['old']}\n";
        }
    }
    
    echo "Update tingkat kelas selesai.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>