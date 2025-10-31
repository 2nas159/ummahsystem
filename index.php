<?php include "secure_users_db.php" ?>

<link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon-16x16.png">
<link rel="manifest" href="assets/site.webmanifest">

<title>نظام جمعية أمة الخيرية</title>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Cairo', sans-serif;
        background-color: #f0f0f0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        text-align: right;
    }

    .container {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        width: 100%;
        max-width: 900px;
        display: flex;
        flex-direction: row-reverse;
    }

    .welcome-section {
        background-color: #000000;
        color: #ffffff;
        padding: 40px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .welcome-section h1 {
        font-size: 29px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .welcome-section p {
        font-size: 14px;
        opacity: 0.7;
        line-height: 1.6;
    }

    .form-section {
        background-color: #ffffff;
        padding: 40px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .logo {
        text-align: center;
        margin-bottom: 20px;
    }

    .logo img {
        max-width: 100px;
        height: auto;
    }

    .form-section h2 {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #000000;
        text-align: center;
    }

    .form-group {
        margin-bottom: 20px;
        position: relative;
    }

    .form-group input {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        font-size: 14px;
        transition: border-color 0.3s ease;
        text-align: right;
    }

    .form-group input:focus {
        outline: none;
        border-color: #000000;
    }

    .form-group input::placeholder {
        color: #999;
    }

    .form-group .icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .sign-up-button {
        background-color: #000000;
        color: #ffffff;
        border: none;
        border-radius: 5px;
        padding: 12px 20px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
        transition: background-color 0.3s ease;
    }

    .sign-up-button:hover {
        background-color: #333333;
    }

    .login-link {
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
        color: #666;
    }

    .login-link a {
        color: #000000;
        text-decoration: none;
        font-weight: 600;
    }

    .login-link a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .container {
            flex-direction: column;
            max-width: 400px;
        }

        .welcome-section,
        .form-section {
            padding: 30px;
        }

        .welcome-section {
            text-align: center;
        }
    }

    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #f5c6cb;
        margin-top: 10px;
        text-align: center;
        font-size: 14px;
        display:
            <?php echo !empty($error) ? 'block' : 'none'; ?>
        ;
    }
</style>

</head>

<body>
    <div class="container">
        <div class="form-section">
            <div class="logo">
                <img src="assets/logo2.png" alt="Logo">
            </div>
            <h2>تسجيل الدخول</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group">
                    <input type="text" id="username" name="username" placeholder="اسم المستخدم" required>
                    <span class="icon">👤</span>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="كلمة المرور" required>
                    <span class="icon">🔒</span>
                </div>
                <button type="submit" class="sign-up-button">تسجيل الدخول</button>

                <!-- منطقة رسائل الخطأ -->
                <div class="error-message">
                    <?php if (!empty($error)) {
                        echo $error;
                    } ?>
                </div>
            </form>
        </div>
        <div class="welcome-section">
            <h1>مرحبًا بك الي نظام جمعية أمة</h1>
            <p>يرجى إدخال بيانات الاعتماد الخاصة بك للوصول إلى حسابك</p>
        </div>
    </div>
</body>