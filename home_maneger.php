<?php
$BASE_PATH_PREFIX = '';
require_once __DIR__ . '/layout.php';
include "db_conn.php";
include "donators_db.php";
include "reports_db.php";
include "urgent_subsidies/db_connection.php";
include "productive_family/db_connection.php";
include "customized_subsidies/db_connection.php";
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<title>جمعية أمة - لوحة المعلومات</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Tajawal', sans-serif;
        background-color: #111827;
        color: #ffffff;
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    h1 {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(to left, #a78bfa, #ec4899);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .donate-btn {
        background: linear-gradient(to left, #8b5cf6, #ec4899);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        font-size: 1rem;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }

    .donate-btn:hover {
        opacity: 0.9;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background-color: #1f2937;
        border-radius: 0.5rem;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
    }

    .stat-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .stat-card-title {
        font-size: 0.875rem;
        color: #9ca3af;
    }

    .stat-card-icon {
        background-color: #374151;
        padding: 0.5rem;
        border-radius: 50%;
    }

    .stat-card-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .stat-card-trend {
        font-size: 0.75rem;
        color: #34d399;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .dashboard-card {
        background-color: #1f2937;
        border-radius: 0.5rem;
        padding: 1.5rem;
    }

    .dashboard-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }

    .dashboard-card-title svg {
        margin-left: 0.5rem;
        color: #a78bfa;
    }

    .donation-list,
    .event-list {
        list-style-type: none;
    }

    .donation-item,
    .event-item {
        background-color: #374151;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        transition: background-color 0.3s ease;
    }

    .donation-item:hover,
    .event-item:hover {
        background-color: #4b5563;
    }

    .donation-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background-color: #a78bfa;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin-left: 1rem;
    }

    .donation-info,
    .event-info {
        flex-grow: 1;
    }

    .donation-name,
    .event-title {
        font-weight: 700;
    }

    .donation-amount,
    .donation-date,
    .event-location,
    .event-date {
        font-size: 0.875rem;
        color: #9ca3af;
    }

    .chart {
        height: 200px;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .chart-bar {
        width: 2rem;
        background: linear-gradient(to top, #8b5cf6, #ec4899);
        border-radius: 0.25rem 0.25rem 0 0;
        transition: height 0.3s ease;
    }

    .chart-labels {
        display: flex;
        justify-content: space-between;
        color: #9ca3af;
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="container">
        <header>
            <h1>جمعية أمة</h1>
            <button class="donate-btn"><a href="add_donators.php">سجل تبرع</a></button>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">إجمالي التبرعات</span>
                    <span class="stat-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </span>
                </div>
                <div class="stat-card-value">1.155.156 ليرة</div>
                <div class="stat-card-trend">+5٪</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">المتبرعون</span>
                    <span class="stat-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z">
                            </path>
                        </svg>
                    </span>
                </div>
                <div class="stat-card-value"><span class="counter"
                data-target="<?= getCount_donators("donators") ?>">0</span></div>
                <div class="stat-card-trend">+2</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">المستفيدون</span>
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
                <div class="stat-card-value"> <span class="counter"
                data-target="<?= getCount_help("tableName") ?>">0</span>
                </div>
                <div class="stat-card-trend">+12%</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">الاعانات الخاصة</span>
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
                <div class="stat-card-value">52</div>
                <div class="stat-card-trend">0</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2 class="dashboard-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path>
                        <path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path>
                        <path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path>
                    </svg>
                    آخر التبرعات
                </h2>
                <ul class="donation-list">
                    <li class="donation-item">
                        <div class="donation-avatar">أ</div>
                        <div class="donation-info">
                            <div class="donation-name">أحمد محمد</div>
                            <div class="donation-amount">1000 ليرة</div>
                        </div>
                        <div class="donation-date">30/09/2023</div>
                    </li>
                    <li class="donation-item">
                        <div class="donation-avatar">ف</div>
                        <div class="donation-info">
                            <div class="donation-name">فاطمة علي</div>
                            <div class="donation-amount">500 ليرة</div>
                        </div>
                        <div class="donation-date">29/09/2024</div>
                    </li>
                    <li class="donation-item">
                        <div class="donation-avatar">ع</div>
                        <div class="donation-info">
                            <div class="donation-name">عمر خالد</div>
                            <div class="donation-amount">2000 ليرة</div>
                        </div>
                        <div class="donation-date">08/09/2024</div>
                    </li>
                </ul>
            </div>

            <div class="dashboard-card">
                <h2 class="dashboard-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                    اتجاهات التبرعات
                </h2>
                <div class="chart">
                    <div class="chart-bar" style="height: 60%;"></div>
                    <div class="chart-bar" style="height: 80%;"></div>
                    <div class="chart-bar" style="height: 40%;"></div>
                    <div class="chart-bar" style="height: 100%;"></div>
                    <div class="chart-bar" style="height: 75%;"></div>
                    <div class="chart-bar" style="height: 55%;"></div>
                </div>
                <div class="chart-labels">
                    <span>يناير</span>
                    <span>فبراير</span>
                    <span>مارس</span>
                    <span>أبريل</span>
                    <span>مايو</span>
                    <span>يونيو</span>
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
            const increment = target / 10;
            if (count < target) {
                counter.innerText = `${Math.ceil(count + increment)}`;
                setTimeout(updateCounter, 1000);
            } else {
                counter.innerText = target;
            }
        };
        updateCounter();
    });
</script>