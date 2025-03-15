<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .logo-section {
            text-align: center;
            padding: 25px;
            background-color: #006778;
        }
        .logo {
            max-width: 150px;
        }
        .content {
            padding: 30px;
        }
        .intro-text {
            text-align: center;
            margin-bottom: 25px;
        }
        .intro-text h2 {
            color: #006778;
            margin: 0 0 10px;
            font-size: 22px;
            font-weight: 600;
        }
        .message-card {
            background-color: #fff;
            border: 1px solid #eaeaea;
            border-radius: 4px;
        }
        .card-header {
            background-color: #f7f7f7;
            padding: 12px 20px;
            color: #006778;
            font-weight: 600;
            border-bottom: 1px solid #eaeaea;
        }
        .card-body {
            padding: 20px;
        }
        .info-row {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .label {
            font-weight: 600;
            color: #555;
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .value {
            color: #333;
            font-size: 16px;
        }
        .message-text {
            background-color: #f9f9f9;
            border-radius: 4px;
            padding: 15px;
            margin-top: 8px;
            border-left: 3px solid #006778;
        }
        .action-section {
            text-align: center;
            margin-top: 25px;
        }
        .button {
            display: inline-block;
            background-color: #006778;
            color: white;
            padding: 10px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 15px;
        }
        .footer {
            background-color: #f7f7f7;
            padding: 20px;
            text-align: center;
            color: #666;
            border-top: 1px solid #eaeaea;
        }
        .footer p {
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="logo-section">
            <img src="https://api.beautifulislandtours.lk/logo.png" alt="Beautiful Island Tours" class="logo">
        </div>
        
        <div class="content">
            <div class="intro-text">
                <h2>New Contact Form Submission</h2>
                <p>A visitor has submitted an inquiry through the website.</p>
            </div>
            
            <div class="message-card">
                <div class="card-header">
                    Contact Information
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Name</span>
                        <span class="value">{{ $data['name'] }}</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value">{{ $data['email'] }}</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Contact Number</span>
                        <span class="value">{{ $data['contact_number'] }}</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Message</span>
                        <div class="message-text">
                            {{ $data['message'] }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="action-section">
                <a href="mailto:{{ $data['email'] }}" class="button">
                    Reply to Inquiry
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Beautiful Island Tours</strong></p>
            <p>Your premier destination for Sri Lankan experiences</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
