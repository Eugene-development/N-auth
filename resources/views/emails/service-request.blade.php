<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новая заявка на услугу</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .info-block {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
        }
        .info-row:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
            width: 140px;
            flex-shrink: 0;
        }
        .info-value {
            color: #111827;
            word-break: break-word;
        }
        .service-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .message-block {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin-top: 20px;
        }
        .message-block h3 {
            margin: 0 0 10px;
            color: #92400e;
            font-size: 14px;
        }
        .message-block p {
            margin: 0;
            color: #78350f;
        }
        .footer {
            background: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .footer a {
            color: #f59e0b;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Новая заявка на услугу</h1>
            <p>{{ $submitted_at }}</p>
        </div>
        
        <div class="content">
            <div class="info-block">
                <div class="info-row">
                    <span class="info-label">Услуга:</span>
                    <span class="info-value">
                        <span class="service-badge">{{ $service_type }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Имя клиента:</span>
                    <span class="info-value">{{ $name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Телефон:</span>
                    <span class="info-value">
                        <a href="tel:{{ $phone }}" style="color: #f59e0b; text-decoration: none; font-weight: 600;">{{ $phone }}</a>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Источник:</span>
                    <span class="info-value">{{ $source_url }}</span>
                </div>
            </div>

            @if($message && $message !== 'Не указано')
            <div class="message-block">
                <h3>💬 Сообщение от клиента:</h3>
                <p>{{ $message }}</p>
            </div>
            @endif
        </div>

        <div class="footer">
            <p>Это автоматическое уведомление от сайта <a href="https://novostroy.ru">Новострой</a></p>
            <p>Пожалуйста, свяжитесь с клиентом как можно скорее</p>
        </div>
    </div>
</body>
</html>
