$sql = file_get_contents('wikitrip.sql');
try {
    $pdo->exec($sql);
    echo "Update berhasil";
} catch(PDOException $e) {
    echo "Update error" . $e->getMessage();
}