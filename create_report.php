<?php
include("reports_db.php");

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $file_name = $_POST['file_name'];  // Capture the file name
    $goals = $_POST['goals'];
    $tasks = $_POST['tasks'];
    $number_of_individuals = $_POST['number_of_individuals'];
    $negatives_and_obstacles = $_POST['negatives_and_obstacles'];
    $evaluation = $_POST['evaluation'];
    $amount = $_POST['amount'];
    $completion_percentage = $_POST['completion_percentage'];
    $notes_and_recommendations = $_POST['notes_and_recommendations'];
    $report_month = $_POST['report_month'];

    // Insert data into the database for each goal/task/etc.
    $sql = "INSERT INTO operation_plan_report 
            (file_name, goals, tasks, number_of_individuals, negatives_and_obstacles, evaluation, amount, completion_percentage, notes_and_recommendations, report_month) 
            VALUES 
            (:file_name, :goals, :tasks, :number_of_individuals, :negatives_and_obstacles, :evaluation, :amount, :completion_percentage, :notes_and_recommendations, :report_month)";

    $stmt = $pdo->prepare($sql);

    for ($i = 0; $i < count($goals); $i++) {
        $stmt->execute([
            ':file_name' => $file_name,  // Save the file name
            ':goals' => $goals[$i],
            ':tasks' => $tasks[$i],
            ':number_of_individuals' => $number_of_individuals[$i],
            ':negatives_and_obstacles' => $negatives_and_obstacles[$i],
            ':evaluation' => $evaluation[$i],
            ':amount' => $amount[$i],
            ':completion_percentage' => $completion_percentage[$i],
            ':notes_and_recommendations' => $notes_and_recommendations[$i],
            ':report_month' => $report_month,
        ]);
    }

    header ('Location: view_reports.php');
}
