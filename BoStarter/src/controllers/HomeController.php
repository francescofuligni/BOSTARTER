<?php

require_once __DIR__ . '/../models/Statistic.php';
require_once __DIR__ . '/../config/Database.php';

$db = new Database();
$conn = $db->getConnection();

$statistic = new Statistic($conn);
$expiringProjects = $statistic->getExpiringProjects();
$topCreators = $statistic->getTopCreators();
$topFunders = $statistic->getTopFunders();
?>
