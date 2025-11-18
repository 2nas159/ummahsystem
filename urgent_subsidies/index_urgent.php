<?php
include('db_connection.php');

// جلب الإحصائيات الإجمالية
$query_total_stats = "SELECT 
    COUNT(DISTINCT beneficiary_id) as total_beneficiaries,
    SUM(amount) as total_payments
    FROM payments";
$result_total_stats = $conn->query($query_total_stats);
$total_stats = $result_total_stats->fetch_assoc();
$total_beneficiaries = $total_stats['total_beneficiaries'];
$total_payments = $total_stats['total_payments'];

// جلب السنوات
$query_years = "SELECT 
    YEAR(payment_date) AS year,
    COUNT(DISTINCT beneficiary_id) AS total_beneficiaries,
    SUM(amount) AS yearly_total
    FROM payments 
    GROUP BY YEAR(payment_date) 
    ORDER BY year DESC";
$result_years = $conn->query($query_years);

$title = "الإعانات العاجلة";
ob_start();
?>

<!-- Font Awesome 6 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="css/index.css">

<!-- تحسين هيكل الصفحة -->
<div class="container-fluid p-0">
    <div class="dashboard">
        <!-- Header Section -->
        <div class="dashboard-header">
            <div class="header-title">
                <h1><?php echo $title; ?></h1>
            </div>
            <div class="header-stats">
                <div class="stat-box">
                    <div class="stat-content">
                        <div class="stat-value">
                            <span class="number"><?php echo number_format($total_beneficiaries); ?></span>
                            <span class="label">إجمالي المستفيدين</span>
                        </div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-content">
                        <div class="stat-value">
                            <span class="number">₺ <?php echo number_format($total_payments, 2); ?></span>
                            <span class="label">إجمالي المدفوعات</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- شريط البحث -->
        <div class="controls-bar">
            <div class="search-container">
                <div class="search-box">
                    <input type="text" id="beneficiarySearch" placeholder="البحث عن مستفيد...">
                    <button type="button" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div id="searchResults" class="search-results"></div>
            </div>
        </div>

        <!-- عرض السنوات والشهور -->
        <div class="years-container">
            <?php while ($row_year = $result_years->fetch_assoc()) { ?>
                <div class="year-section" data-year="<?php echo $row_year['year']; ?>">
                    <div class="year-card">
                        <div class="year-header">
                            <div class="year-info">
                                <h2>سنة <?php echo $row_year['year']; ?></h2>
                                <?php if ($row_year['year'] == date('Y')): ?>
                                    <span class="current-year-badge">السنة الحالية</span>
                                <?php endif; ?>
                            </div>
                            <div class="year-stats">
                                <div class="stat">
                                    <i class="fas fa-users"></i>
                                    <span><?php echo number_format($row_year['total_beneficiaries']); ?> مستفيد</span>
                                </div>
                                <div class="stat">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>₺ <?php echo number_format($row_year['yearly_total'], 2); ?></span>
                                </div>
                            </div>
                            <div class="year-actions">
                                <button class="action-btn print">
                                    <i class="fas fa-print"></i>
                                    <span>طباعة</span>
                                </button>
                                <button class="action-btn export">
                                    <i class="fas fa-file-excel"></i>
                                    <span>تصدير</span>
                                </button>
                            </div>
                        </div>

                        <div class="months-grid">
                            <?php
                            for ($month = 1; $month <= 12; $month++) {
                                $query_month = "SELECT 
                                    COUNT(DISTINCT beneficiary_id) AS paid_count, 
                                    SUM(amount) AS total_amount
                                    FROM payments 
                                    WHERE YEAR(payment_date) = ? AND MONTH(payment_date) = ?";
                                $stmt = $conn->prepare($query_month);
                                $stmt->bind_param('ii', $row_year['year'], $month);
                                $stmt->execute();
                                $result_month = $stmt->get_result()->fetch_assoc();
                                $isCurrentMonth = ($row_year['year'] == date('Y') && $month == date('n'));
                                ?>
                                <div class="month-card">
                                    <div class="month-content">
                                        <div class="month-header">
                                            <h3>
                                                <?php echo date('F', mktime(0, 0, 0, $month, 10)); ?>
                                                <?php if ($isCurrentMonth): ?>
                                                    <span class="current-month-badge">(الشهر الحالي)</span>
                                                <?php endif; ?>
                                            </h3>
                                            <span class="month-badge"><?php echo $month; ?></span>
                                        </div>
                                        <div class="month-stats">
                                            <div class="stat-row">
                                                <i class="fas fa-users"></i>
                                                <div class="stat-info">
                                                    <span
                                                        class="stat-number"><?php echo number_format($result_month['paid_count'] ?? 0); ?></span>
                                                    <span class="stat-label">مستفيد</span>
                                                </div>
                                            </div>
                                            <div class="stat-row">
                                                <i class="fas fa-money-bill-wave"></i>
                                                <div class="stat-info">
                                                    <span class="stat-number">₺
                                                        <?php echo number_format($result_month['total_amount'] ?? 0, 2); ?></span>
                                                    <span class="stat-label">المبلغ الإجمالي</span>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="month_details.php?year=<?php echo $row_year['year']; ?>&month=<?php echo $month; ?>"
                                            class="btn btn-view-details">
                                            <span>تفاصيل الشهر</span>
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php
                                $stmt->close();
                            } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$BASE_PATH_PREFIX = '../';
require_once __DIR__ . '/../layout.php';
?>

<!-- إضافة قالب لنتائج البحث -->
<template id="searchResultTemplate">
    <div class="search-result-item">
        <div class="result-header">
            <span class="beneficiary-name"></span>
            <span class="beneficiary-id"></span>
        </div>
        <div class="result-details">
            <div class="payment-date">
                <i class="fas fa-calendar"></i>
                <span class="date"></span>
            </div>
            <div class="payment-amount">
                <i class="fas fa-money-bill-wave"></i>
                <span class="amount"></span>
            </div>
        </div>
    </div>
</template>

<style>
    :root {
        --light-bg: #f8fafc;
        --primary-blue: #0061f2;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --border-radius: 12px;
        --box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .container-fluid {
        background: var(--light-bg);
    }

    .dashboard {
        background: var(--light-bg);
        padding: 20px;
    }

    .dashboard-header {
        display: flex;
        flex-direction: column;
        gap: 24px;
        padding: 24px;
        margin-bottom: 32px;
    }

    .header-title h1 {
        color: var(--text-dark);
        font-size: 32px;
        font-weight: 700;
        text-align: right;
        margin: 0;
    }

    .header-stats {
        display: flex;
        justify-content: flex-end;
        gap: 20px;
    }

    .stat-box {
        background: #ffffff;
        border-radius: var(--border-radius);
        padding: 16px 24px;
        min-width: 200px;
        box-shadow: var(--box-shadow);
        border: 1px solid #e2e8f0;
    }

    .stat-content {
        text-align: right;
    }

    .stat-value .number {
        color: var(--text-dark);
        font-size: 24px;
        font-weight: 700;
        display: block;
        margin-bottom: 4px;
    }

    .stat-value .label {
        color: var(--text-muted);
        font-size: 14px;
    }

    /* شريط البحث */
    .controls-bar {
        background: #ffffff;
        border-radius: var(--border-radius);
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        box-shadow: var(--box-shadow);
        border: 1px solid #e2e8f0;
    }

    .search-container {
        position: relative;
        width: 100%;
        max-width: 500px;
    }

    .search-box {
        position: relative;
        width: 100%;
    }

    .search-box input {
        width: 100%;
        padding: 12px 20px;
        padding-left: 40px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: var(--border-radius);
        color: var(--text-dark);
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .search-box input::placeholder {
        color: var(--text-muted);
    }

    .search-box input:focus {
        background: #ffffff;
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 97, 242, 0.1);
    }

    .search-btn {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
    }

    /* تحديث تنسيقات نتائج البحث */
    .search-results {
        top: 100%;
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: var(--border-radius);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        margin-top: 8px;
        max-height: 400px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }

    .search-result-item {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .search-result-item:hover {
        background: #f1f5f9;
    }

    .result-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .beneficiary-name {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-dark);
    }

    .beneficiary-id {
        background: rgba(0, 97, 242, 0.1);
        color: var(--primary-blue);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
    }

    .result-details {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
    }

    .payment-date,
    .payment-amount {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
        font-size: 14px;
        background: #f1f5f9;
        padding: 6px 12px;
        border-radius: 6px;
    }

    .payment-date i,
    .payment-amount i {
        color: var(--primary-blue);
        font-size: 14px;
    }

    /* رسائل الحالة */
    .loading,
    .no-results,
    .error {
        padding: 20px;
        text-align: center;
        font-size: 15px;
    }

    .loading {
        color: var(--text-muted);
    }

    .no-results {
        color: var(--text-muted);
    }

    .error {
        color: #ef4444;
    }

    /* تخصيص scrollbar */
    .search-results::-webkit-scrollbar {
        width: 8px;
    }

    .search-results::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .search-results::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .search-results::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* تحديث تنسيقات الكروت */
    .years-container {
        display: flex;
        flex-direction: column;
        gap: 32px;
    }

    .year-card {
        background: #ffffff;
        border-radius: var(--border-radius);
        padding: 24px;
        box-shadow: var(--box-shadow);
        border: 1px solid #e2e8f0;
    }

    .year-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .year-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .year-header h2 {
        font-size: 24px;
        font-weight: 700;
        margin: 0;
        color: var(--text-dark);
    }

    .current-year-badge {
        font-size: 12px;
        color: #0061f2;
        font-weight: 600;
        padding: 4px 8px;
        background: rgba(0, 97, 242, 0.1);
        border-radius: 4px;
    }

    .year-stats {
        display: flex;
        gap: 24px;
    }

    .year-stats .stat {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: var(--text-muted);
    }

    .year-stats i {
        color: var(--primary-blue);
    }

    .year-actions {
        display: flex;
        gap: 12px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .action-btn.print {
        background-color: #4f46e5;
        color: white;
    }

    .action-btn.export {
        background-color: #10b981;
        color: white;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .months-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .month-card {
        background: #ffffff;
        border-radius: var(--border-radius);
        padding: 20px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .month-card:hover {
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .month-content {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .month-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .month-header h3 {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .current-month-badge {
        font-size: 12px;
        color: #0061f2;
        font-weight: 600;
    }

    .month-badge {
        background: #f1f5f9;
        color: var(--text-muted);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
    }

    .month-stats {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .stat-row {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-muted);
    }

    .stat-row i {
        color: var(--primary-blue);
        width: 16px;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-number {
        color: var(--text-dark);
        font-weight: 500;
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
    }

    .btn-view-details {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 12px 16px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: var(--border-radius);
        color: var(--primary-blue);
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-view-details:hover {
        background: #dbeafe;
        border-color: #93c5fd;
        transform: translateY(-1px);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('beneficiarySearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                const searchTerm = this.value.trim();

                if (searchTerm.length < 2) {
                    searchResults.innerHTML = '';
                    searchResults.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    searchResults.innerHTML = '<div class="loading">جاري البحث...</div>';
                    searchResults.style.display = 'block';

                    fetch(`search_urgent_beneficiaries.php?term=${encodeURIComponent(searchTerm)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            searchResults.innerHTML = '';

                            if (data.length === 0) {
                                searchResults.innerHTML = '<div class="no-results">لا توجد نتائج</div>';
                                return;
                            }

                            data.forEach(result => {
                                const resultItem = document.createElement('div');
                                resultItem.className = 'search-result-item';

                                const date = new Date(result.payment_date);
                                const formattedDate = `${date.getDate()}/${date.getMonth() + 1}/${date.getFullYear()}`;

                                resultItem.innerHTML = `
                                <div class="result-header">
                                    <span class="beneficiary-name">${result.beneficiary_name}</span>
                                    <span class="beneficiary-id">#${result.beneficiary_id}</span>
                                </div>
                                <div class="result-details">
                                    <div class="payment-date">
                                        <i class="fas fa-calendar"></i>
                                        <span>${formattedDate}</span>
                                    </div>
                                    <div class="payment-amount">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>₺ ${Number(result.amount).toLocaleString('tr-TR', { minimumFractionDigits: 2 })}</span>
                                    </div>
                                </div>
                            `;

                                // Add click handler to navigate to month details
                                resultItem.addEventListener('click', function() {
                                    if (result.year && result.month) {
                                        window.location.href = `month_details.php?year=${result.year}&month=${result.month}`;
                                    }
                                });

                                searchResults.appendChild(resultItem);
                            });

                            searchResults.style.display = 'block';
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            searchResults.innerHTML = '<div class="error">حدث خطأ في البحث</div>';
                        });
                }, 300);
            });

            // إخفاء نتائج البحث عند النقر خارج منطقة البحث
            document.addEventListener('click', function (e) {
                if (!searchResults.contains(e.target) && e.target !== searchInput) {
                    searchResults.style.display = 'none';
                }
            });
        }

        // وظيفة الطباعة
        document.querySelectorAll('.action-btn.print').forEach(button => {
            button.addEventListener('click', function() {
                const yearCard = this.closest('.year-card');
                const yearNum = yearCard.closest('.year-section').getAttribute('data-year');
                const printContent = yearCard.cloneNode(true);

                // إزالة الأزرار من نسخة الطباعة
                printContent.querySelectorAll('.action-btn, .btn-view-details').forEach(el => el.remove());

                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html dir="rtl">
                    <head>
                        <title>تقرير الإعانات العاجلة - ${yearNum}</title>
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
                        <style>
                            @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap');
                            
                            body {
                                font-family: 'Cairo', sans-serif;
                                padding: 20px;
                                background: white;
                                color: #1a1f2d;
                            }
                            
                            h1 {
                                text-align: center;
                                margin-bottom: 30px;
                            }
                            
                            .year-card {
                                border: 1px solid #e2e8f0;
                                border-radius: 12px;
                                padding: 20px;
                                margin-bottom: 30px;
                            }
                            
                            .months-grid {
                                display: grid;
                                grid-template-columns: repeat(3, 1fr);
                                gap: 20px;
                            }
                            
                            .month-card {
                                border: 1px solid #e2e8f0;
                                border-radius: 8px;
                                padding: 15px;
                            }
                            
                            @media print {
                                .month-card { break-inside: avoid; }
                                .year-card { break-inside: avoid; }
                            }
                        </style>
                    </head>
                    <body>
                        <h1>تقرير الإعانات العاجلة - سنة ${yearNum}</h1>
                        ${printContent.outerHTML}
                    </body>
                    </html>
                `);
                printWindow.document.close();
                setTimeout(() => {
                    printWindow.print();
                }, 1000);
            });
        });

        // وظيفة التصدير إلى Excel
        document.querySelectorAll('.action-btn.export').forEach(button => {
            button.addEventListener('click', function() {
                const yearCard = this.closest('.year-card');
                const yearNum = yearCard.closest('.year-section').getAttribute('data-year');
                
                // جمع البيانات من البطاقات الشهرية
                const monthlyData = [];
                yearCard.querySelectorAll('.month-card').forEach(monthCard => {
                    const monthName = monthCard.querySelector('.month-header h3').textContent
                        .replace('(الشهر الحالي)', '').trim();
                    
                    const beneficiaries = monthCard.querySelector('.stat-row:first-child .stat-number')
                        .textContent.trim();
                    const amount = monthCard.querySelector('.stat-row:last-child .stat-number')
                        .textContent.trim();
                    
                    // إضافة البيانات فقط إذا كان هناك مستفيدين
                    if (beneficiaries !== '0') {
                        monthlyData.push({
                            month: monthName,
                            beneficiaries: beneficiaries,
                            amount: amount
                        });
                    }
                });

                // تحويل البيانات إلى CSV
                let csv = '\ufeff'; // BOM للدعم العربي
                csv += 'الشهر,عدد المستفيدين,المبلغ الإجمالي\n';
                monthlyData.forEach(row => {
                    csv += `${row.month},${row.beneficiaries},${row.amount}\n`;
                });

                // إنشاء وتنزيل ملف CSV
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', `تقرير_الإعانات_العاجلة_${yearNum}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
            });
        });
    });
</script>