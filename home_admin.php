<?php
$BASE_PATH_PREFIX = '';
require_once __DIR__ . '/layout.php';
include "db_conn.php";
include "donators_db.php";
include "reports_db.php";
include "urgent_subsidies/db_connection.php";
include "customized_subsidies/db_connection.php";

// Get donators count
try {
    // Select the donators database first
    $conn->select_db('u850876726_donators_help');

    $donators_query = "SELECT COUNT(*) as donator_count FROM donators";
    $donators_result = $conn->query($donators_query);
    if ($donators_result && $donators_result->num_rows > 0) {
        $donators_count = $donators_result->fetch_assoc()['donator_count'];
    } else {
        $donators_count = 0;
    }

    // Get beneficiaries count from tablename table
    $beneficiaries_query = "SELECT COUNT(*) as count FROM tablename";
    if (!$conn) {
        $tablename_count = 0;
    } else {
        $beneficiaries_result = $conn->query($beneficiaries_query);
        if (!$beneficiaries_result || $beneficiaries_result->num_rows == 0) {
            $tablename_count = 0;
        } else {
            $tablename_count = $beneficiaries_result->fetch_assoc()['count'];
        }
    }
} catch (Exception $e) {
    $donators_count = 0;
    $tablename_count = 0;
}

// Initialize variables for error cases
$urgent_beneficiaries_count = 0;
$urgent_monthly_data = array();
$urgent_max_amount = 0;
$urgent_all_time_total = 0;
$urgent_true_monthly_average = 0;
$urgent_highest_amount = 0;
$urgent_highest_month = 0;
$urgent_highest_year = '';
$urgent_beneficiary_counts = array();

$customized_beneficiaries_count = 0;
$customized_monthly_data = array();
$customized_max_amount = 0;
$customized_all_time_total = 0;
$customized_true_monthly_average = 0;
$customized_highest_amount = 0;
$customized_highest_month = 0;
$customized_highest_year = '';
$customized_beneficiary_counts = array();

// Get urgent subsidies data
try {
    include "urgent_subsidies/db_connection.php";
    $urgent_conn = $conn;

    // Get urgent beneficiaries count
    $urgent_beneficiaries_query = "SELECT COUNT(DISTINCT beneficiary_id) as beneficiary_count FROM payments";
    $urgent_beneficiaries_result = $urgent_conn->query($urgent_beneficiaries_query);
    if ($urgent_beneficiaries_result && $urgent_beneficiaries_result->num_rows > 0) {
        $urgent_beneficiaries_count = $urgent_beneficiaries_result->fetch_assoc()['beneficiary_count'];
    } else {
        $urgent_beneficiaries_count = 0;
    }

    // Get last 6 months data for chart
    $chart_query = "SELECT 
        DATE_FORMAT(payment_date, '%Y-%m') as month_year,
        COUNT(DISTINCT beneficiary_id) as beneficiary_count,
        SUM(amount) as total_amount
    FROM payments 
    WHERE payment_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY month_year ASC";

    $result = $urgent_conn->query($chart_query);

    // Initialize arrays for urgent data
    for ($i = 5; $i >= 0; $i--) {
        $month_key = date('Y-m', strtotime("-$i months"));
        $urgent_monthly_data[$month_key] = 0;
        $urgent_beneficiary_counts[$month_key] = 0;
    }

    while ($row = $result->fetch_assoc()) {
        $urgent_monthly_data[$row['month_year']] = (float)$row['total_amount'];
        $urgent_beneficiary_counts[$row['month_year']] = (int)$row['beneficiary_count'];
        if ($row['total_amount'] > $urgent_max_amount) {
            $urgent_max_amount = (float)$row['total_amount'];
        }
    }

    // Get all-time total and average
    $stats_query = "SELECT 
        SUM(amount) as all_time_total,
        COUNT(DISTINCT DATE_FORMAT(payment_date, '%Y-%m')) as total_months
    FROM payments";

    $stats_result = $urgent_conn->query($stats_query);
    $stats_row = $stats_result->fetch_assoc();
    $urgent_all_time_total = (float)$stats_row['all_time_total'];
    $urgent_total_months = (int)$stats_row['total_months'];
    $urgent_true_monthly_average = $urgent_total_months > 0 ? $urgent_all_time_total / $urgent_total_months : 0;

    // Get highest monthly amount
    $highest_query = "SELECT 
        DATE_FORMAT(payment_date, '%Y-%m') as month_year,
        DATE_FORMAT(payment_date, '%m') as month_num,
        DATE_FORMAT(payment_date, '%Y') as year,
        SUM(amount) as total_amount
    FROM payments 
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY total_amount DESC
    LIMIT 1";

    $highest_result = $urgent_conn->query($highest_query);
    $highest_row = $highest_result->fetch_assoc();
    $urgent_highest_amount = (float)$highest_row['total_amount'];
    $urgent_highest_month = (int)$highest_row['month_num'];
    $urgent_highest_year = $highest_row['year'];

    $urgent_conn->close();
} catch (Exception $e) {
    error_log("Error in urgent subsidies data: " . $e->getMessage());
}

// Get customized subsidies data
try {
    include "customized_subsidies/db_connection.php";
    $customized_conn = $conn;

    // Get customized beneficiaries count
    $customized_beneficiaries_query = "SELECT COUNT(DISTINCT beneficiary_id) as beneficiary_count FROM payments";
    $customized_beneficiaries_result = $customized_conn->query($customized_beneficiaries_query);
    if ($customized_beneficiaries_result && $customized_beneficiaries_result->num_rows > 0) {
        $customized_beneficiaries_count = $customized_beneficiaries_result->fetch_assoc()['beneficiary_count'];
    } else {
        $customized_beneficiaries_count = 0;
    }

    // Get last 6 months data for chart
    $chart_query = "SELECT 
        DATE_FORMAT(payment_date, '%Y-%m') as month_year,
        COUNT(DISTINCT beneficiary_id) as beneficiary_count,
        SUM(amount) as total_amount
    FROM payments 
    WHERE payment_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY month_year ASC";

    $result = $customized_conn->query($chart_query);

    // Initialize arrays for customized data
    for ($i = 5; $i >= 0; $i--) {
        $month_key = date('Y-m', strtotime("-$i months"));
        $customized_monthly_data[$month_key] = 0;
        $customized_beneficiary_counts[$month_key] = 0;
    }

    while ($row = $result->fetch_assoc()) {
        $customized_monthly_data[$row['month_year']] = (float)$row['total_amount'];
        $customized_beneficiary_counts[$row['month_year']] = (int)$row['beneficiary_count'];
        if ($row['total_amount'] > $customized_max_amount) {
            $customized_max_amount = (float)$row['total_amount'];
        }
    }

    // Get all-time total and average
    $stats_query = "SELECT 
        SUM(amount) as all_time_total,
        COUNT(DISTINCT DATE_FORMAT(payment_date, '%Y-%m')) as total_months
    FROM payments";

    $stats_result = $customized_conn->query($stats_query);
    $stats_row = $stats_result->fetch_assoc();
    $customized_all_time_total = (float)$stats_row['all_time_total'];
    $customized_total_months = (int)$stats_row['total_months'];
    $customized_true_monthly_average = $customized_total_months > 0 ? $customized_all_time_total / $customized_total_months : 0;

    // Get highest monthly amount
    $highest_query = "SELECT 
        DATE_FORMAT(payment_date, '%Y-%m') as month_year,
        DATE_FORMAT(payment_date, '%m') as month_num,
        DATE_FORMAT(payment_date, '%Y') as year,
        SUM(amount) as total_amount
    FROM payments 
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY total_amount DESC
    LIMIT 1";

    $highest_result = $customized_conn->query($highest_query);
    $highest_row = $highest_result->fetch_assoc();
    $customized_highest_amount = (float)$highest_row['total_amount'];
    $customized_highest_month = (int)$highest_row['month_num'];
    $customized_highest_year = $highest_row['year'];

    $customized_conn->close();
} catch (Exception $e) {
    error_log("Error in customized subsidies data: " . $e->getMessage());
}

// Define Arabic months array
$arabic_months = [
    1 => 'يناير',
    2 => 'فبراير',
    3 => 'مارس',
    4 => 'أبريل',
    5 => 'مايو',
    6 => 'يونيو',
    7 => 'يوليو',
    8 => 'أغسطس',
    9 => 'سبتمبر',
    10 => 'أكتوبر',
    11 => 'نوفمبر',
    12 => 'ديسمبر'
];

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>جمعية أمة - لوحة المعلومات</title>
    <style>
        :root {
            --bg-color: #f8fafc;
            --text-color: #1e293b;
            --card-bg-color: #ffffff;
            --card-hover-color: #f1f5f9;
            --muted-color: #64748b;
            --accent-color: #0ea5e9;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --primary-gradient: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
            --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            transition: var(--transition);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--card-bg-color);
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .theme-toggle {
            display: none !important;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--card-bg-color);
            border-radius: 1rem;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-card-title {
            font-size: 1rem;
            color: var(--muted-color);
            font-weight: 500;
        }

        .stat-card-icon {
            background: var(--primary-gradient);
            padding: 0.75rem;
            border-radius: 0.75rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card-icon svg {
            color: white;
            width: 1.5rem;
            height: 1.5rem;
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card-trend {
            font-size: 0.875rem;
            color: var(--success-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
        }

        .dashboard-card {
            background-color: var(--card-bg-color);
            border-radius: 1rem;
            padding: 2rem;
            transition: var(--transition);
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .dashboard-card-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-color);
        }

        .dashboard-card-title svg {
            color: var(--accent-color);
            width: 1.5rem;
            height: 1.5rem;
        }

        .chart-container {
            margin: 2rem 0;
            padding: 2rem;
            position: relative;
            background: var(--card-bg-color);
            border-radius: 1rem;
            min-height: 400px;
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow);
        }

        .chart {
            height: 320px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            position: relative;
            margin-bottom: 3rem;
        }

        .chart-bar {
            width: 50px;
            background: var(--primary-gradient);
            border-radius: 0.5rem 0.5rem 0 0;
            position: relative;
            min-height: 2px;
            transform-origin: bottom;
            animation: growBar 1s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
        }

        .chart-bar-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            width: calc(100% / 7);
            min-width: 70px;
            max-width: 120px;
        }

        .amount-label {
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            white-space: nowrap;
            font-weight: 600;
            z-index: 10;
            opacity: 0;
            animation: fadeIn 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            animation-delay: 0.5s;
            box-shadow: var(--card-shadow);
        }

        .beneficiary-count {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            color: var(--muted-color);
            font-size: 0.875rem;
            text-align: center;
            width: 100%;
            opacity: 0;
            animation: fadeIn 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            animation-delay: 0.7s;
        }

        .month-label {
            margin-top: 1rem;
            color: var(--text-color);
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            opacity: 0;
            animation: fadeIn 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            animation-delay: 0.9s;
            margin-left: 37px;
        }

        .chart-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 0 0 2rem 0;
        }

        .stat-item {
            background: var(--card-bg-color);
            padding: 1.5rem;
            border-radius: 1rem;
            text-align: center;
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--muted-color);
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .highest-details {
            font-size: 0.875rem;
            color: var(--muted-color);
            font-weight: 500;
        }

        .event-list {
            list-style-type: none;
        }

        .event-item {
            background-color: var(--card-bg-color);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            transition: var(--transition);
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
        }

        .event-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .event-info {
            flex-grow: 1;
        }

        .event-title {
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
        }

        .event-location {
            font-size: 0.875rem;
            color: var(--muted-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .event-date {
            font-size: 0.875rem;
            color: var(--accent-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
            background: rgba(14, 165, 233, 0.1);
            border-radius: 0.5rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .chart-stats {
                grid-template-columns: 1fr;
            }

            .chart-bar {
                width: 30px;
            }

            .amount-label {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
        }

        @keyframes growBar {
            0% {
                transform: scaleY(0);
                opacity: 0;
            }

            100% {
                transform: scaleY(1);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, 10px);
            }

            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="container">
            <header>
                <h1>جمعية أمة</h1>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">إجمالي المساعدات</span>
                        <span class="stat-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </span>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($urgent_all_time_total + $customized_all_time_total, 0); ?> ليرة</div>
                    <div class="stat-card-trend">+5٪</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">المستفيدون من المساعدات العاجلة</span>
                        <span class="stat-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </span>
                    </div>
                    <div class="stat-card-value"><span class="counter" data-target="<?php echo $urgent_beneficiaries_count; ?>"><?php echo $urgent_beneficiaries_count; ?></span></div>
                    <div class="stat-card-trend">+12%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">المستفيدون من المساعدات الخاصة</span>
                        <span class="stat-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </span>
                    </div>
                    <div class="stat-card-value"><span class="counter" data-target="<?php echo $customized_beneficiaries_count; ?>"><?php echo $customized_beneficiaries_count; ?></span></div>
                    <div class="stat-card-trend">+12%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">المستفيدون</span>
                        <span class="stat-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </span>
                    </div>
                    <div class="stat-card-value"><span class="counter" data-target="<?php echo $tablename_count; ?>"><?php echo $tablename_count; ?></span></div>
                    <div class="stat-card-trend">0</div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h2 class="dashboard-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                            <polyline points="17 6 23 6 23 12"></polyline>
                        </svg>
                        اتجاهات المساعدات الخاصة
                    </h2>

                    <div class="chart-stats">
                        <div class="stat-item">
                            <div class="stat-label">إجمالي المساعدات الخاصة (الكلي)</div>
                            <div class="stat-value"><?php echo number_format($customized_all_time_total, 0); ?> ليرة</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">متوسط المساعدات الشهري (الكلي)</div>
                            <div class="stat-value">
                                <?php echo number_format($customized_true_monthly_average, 0); ?> ليرة
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">أعلى قيمة شهرية مسجلة</div>
                            <div class="stat-value">
                                <?php echo number_format($customized_highest_amount, 0); ?> ليرة
                                <div class="highest-details">
                                    <?php echo $arabic_months[$customized_highest_month]; ?> - <?php echo $customized_highest_year; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="chart-container">
                        <div class="chart">
                            <?php
                            foreach ($customized_monthly_data as $month => $amount):
                                $height = $customized_max_amount > 0 ? ($amount / $customized_max_amount * 250) : 0;
                                $month_name = date('n', strtotime($month));
                                $delay = ($month_name - 1) * 0.1;
                            ?>
                                <div class="chart-bar-wrapper">
                                    <div class="chart-bar" style="height: <?php echo max(2, $height); ?>px; animation-delay: <?php echo $delay; ?>s">
                                        <div class="amount-label" style="animation-delay: <?php echo $delay + 0.5; ?>s">
                                            <?php echo number_format($amount, 0); ?>
                                        </div>
                                    </div>
                                    <div class="beneficiary-count" style="animation-delay: <?php echo $delay + 0.7; ?>s">
                                        <?php echo $customized_beneficiary_counts[$month]; ?> مستفيد
                                    </div>
                                    <div class="month-label" style="animation-delay: <?php echo $delay + 0.9; ?>s">
                                        <?php echo $arabic_months[$month_name]; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h2 class="dashboard-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                            <polyline points="17 6 23 6 23 12"></polyline>
                        </svg>
                        اتجاهات المساعدات العاجلة
                    </h2>

                    <div class="chart-stats">
                        <div class="stat-item">
                            <div class="stat-label">إجمالي المساعدات العاجلة (الكلي)</div>
                            <div class="stat-value"><?php echo number_format($urgent_all_time_total, 0); ?> ليرة</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">متوسط المساعدات الشهري (الكلي)</div>
                            <div class="stat-value">
                                <?php echo number_format($urgent_true_monthly_average, 0); ?> ليرة
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">أعلى قيمة شهرية مسجلة</div>
                            <div class="stat-value">
                                <?php echo number_format($urgent_highest_amount, 0); ?> ليرة
                                <div class="highest-details">
                                    <?php echo $arabic_months[$urgent_highest_month]; ?> - <?php echo $urgent_highest_year; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="chart-container">
                        <div class="chart">
                            <?php
                            foreach ($urgent_monthly_data as $month => $amount):
                                $height = $urgent_max_amount > 0 ? ($amount / $urgent_max_amount * 250) : 0;
                                $month_name = date('n', strtotime($month));
                                $delay = ($month_name - 1) * 0.1;
                            ?>
                                <div class="chart-bar-wrapper">
                                    <div class="chart-bar" style="height: <?php echo max(2, $height); ?>px; animation-delay: <?php echo $delay; ?>s">
                                        <div class="amount-label" style="animation-delay: <?php echo $delay + 0.5; ?>s">
                                            <?php echo number_format($amount, 0); ?>
                                        </div>
                                    </div>
                                    <div class="beneficiary-count" style="animation-delay: <?php echo $delay + 0.7; ?>s">
                                        <?php echo $urgent_beneficiary_counts[$month]; ?> مستفيد
                                    </div>
                                    <div class="month-label" style="animation-delay: <?php echo $delay + 0.9; ?>s">
                                        <?php echo $arabic_months[$month_name]; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card" style="margin-top: 2rem;">
                <h2 class="dashboard-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    الفعاليات القادمة
                </h2>
                <ul class="event-list">
                    <li class="event-item">
                        <div class="event-info">
                            <div class="event-title">حملة رمضان الخيرية</div>
                            <div class="event-location">الرياض</div>
                        </div>
                        <div class="event-date">2025/03/10</div>
                    </li>
                    <li class="event-item">
                        <div class="event-info">
                            <div class="event-title">يوم اليتيم العالمي</div>
                            <div class="event-location">جدة</div>
                        </div>
                        <div class="event-date">01/04/2025</div>
                    </li>
                    <li class="event-item">
                        <div class="event-info">
                            <div class="event-title">مؤتمر العمل الخيري</div>
                            <div class="event-location">الدمام</div>
                        </div>
                        <div class="event-date">15/05/2025</div>
                    </li>
                </ul>
            </div>
        </div>
    </main>

    <script>
        // Counter animation for statistics
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            counter.innerText = '0';
            const updateCounter = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const increment = target / 200;
                if (count < target) {
                    counter.innerText = `${Math.ceil(count + increment)}`;
                    setTimeout(updateCounter, 50);
                } else {
                    counter.innerText = target;
                }
            };
            updateCounter();
        });
    </script>
</body>

</html>