<?php

require_once __DIR__ . '/../models/Statistic.php';
require_once __DIR__ . '/../config/Database.php';

$db = new Database();
$conn = $db->getConnection();

$statistic = new Statistic($conn);

$expiringProjectsResult = $statistic->getExpiringProjects();
$expiringProjects = ($expiringProjectsResult['success']) ? $expiringProjectsResult['data'] : [];

$topCreatorsResult = $statistic->getTopCreators();
$topCreators = ($topCreatorsResult['success']) ? $topCreatorsResult['data'] : [];

$topFundersResult = $statistic->getTopFunders();
$topFunders = ($topFundersResult['success']) ? $topFundersResult['data'] : [];
?>
