<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>madebymaa Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #5e1eaa, #8a2be2);
            height: 100vh;
            overflow: hidden;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .dashboard-container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            height: 90vh;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Left Panel Styles */
        .left-panel {
            width: 300px;
            background: rgba(255, 255, 255, 0.15);
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .brand {
            text-align: center;
            margin-bottom: 40px;
        }

        .brand h1 {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
        }

        .brand-subtitle {
            font-size: 14px;
            opacity: 0.8;
            font-weight: 500;
        }

        .login-buttons {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 100%;
            margin-bottom: 30px;
        }

        .login-btn {
            padding: 15px 25px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.2));
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .jibble-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .jibble-btn:hover {
            background: linear-gradient(135deg, #ff5252, #e84118);
        }

        /* App Download Section */
        .app-downloads {
            width: 100%;
            margin-top: 20px;
        }

        .app-downloads h3 {
            text-align: center;
            margin-bottom: 15px;
            font-size: 16px;
            opacity: 0.9;
        }

        .app-list {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .app-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            padding: 15px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            flex: 1;
        }

        .app-item:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.15);
        }

        .app-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .app-version {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 2px;
        }

        .app-update-date {
            font-size: 11px;
            opacity: 0.6;
        }

        /* Right Panel Styles */
        .right-panel {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
        }

        .notification-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .notification-header h2 {
            font-size: 32px;
            font-weight: 700;
            color: #fff;
        }

        .notification-panel {
            flex: 1;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow-y: auto;
        }

        .notification-box {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
        }

        .notification-box:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.25);
        }

        .notification-box.important {
            background: linear-gradient(135deg, rgba(255, 87, 87, 0.3), rgba(255, 87, 87, 0.1));
            border: 1px solid rgba(255, 87, 87, 0.5);
        }

        .notification-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .notification-heading {
            font-weight: 700;
            font-size: 18px;
            color: #fff;
            position: relative;
            padding-top: 5px;
            flex: 1;
        }

        .notification-date {
            font-size: 14px;
            opacity: 0.8;
            color: #e0e0e0;
            margin-left: 15px;
            white-space: nowrap;
        }

        .notification-description {
            font-size: 15px;
            line-height: 1.5;
            opacity: 0.9;
        }

        .new-badge {
            position: absolute;
            top: -8px;
            right: 15px;
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: #5e1eaa;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Scrollbar styling */
        .notification-panel::-webkit-scrollbar {
            width: 8px;
        }

        .notification-panel::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .notification-panel::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
        }

        .notification-panel::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Left Panel -->
        <div class="left-panel">
            <div class="brand">
                <h1>madeByMaa</h1>
                <div class="brand-subtitle">For Employee</div>
            </div>

            <div class="login-buttons">
                <!-- Only Employee Login button remains -->
                <a href="https://deshboard.madebymaa.store/login/employee" class="login-btn">
                    Employee Login
                </a>
                <a href="https://jibble.io" class="login-btn jibble-btn" target="_blank">
                    Attendance
                </a>
            </div>

            <!-- App Download Section -->
            <div class="app-downloads">
                <h3>Latest app details</h3>
                <div class="app-list">
                    <div class="app-item" onclick="downloadApp('seller')">
                        <div class="app-name">Seller App</div>
                        <div class="app-version">v2.4.1</div>
                        <div class="app-update-date">Updated: 2024-01-10</div>
                    </div>
                    <div class="app-item" onclick="downloadApp('customer')">
                        <div class="app-name">Customer App</div>
                        <div class="app-version">v3.1.0</div>
                        <div class="app-update-date">Updated: 2024-01-15</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <div class="notification-header">
                <h2>Notifications</h2>
            </div>
            
            <div class="notification-panel" id="notifications-container">
                <!-- Notifications will be loaded here by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Notifications data with heading, date and description
        const notifications = [
            {
                heading: 'System Maintenance Scheduled',
                date: new Date(Date.now() - 12 * 60 * 60 * 1000), // 12 hours ago
                description: 'Planned system maintenance will occur this weekend from 10 PM Saturday to 2 AM Sunday. All services will be temporarily unavailable during this time.',
                important: true
            },
            {
                heading: 'New Employee Onboarding',
                date: new Date(Date.now() - 24 * 60 * 60 * 1000), // 24 hours ago
                description: 'Welcome our new team members! Orientation session scheduled for Monday at 10 AM in the main conference room.',
                important: false
            },
            {
                heading: 'Quarterly Review Meeting',
                date: new Date(Date.now() - 36 * 60 * 60 * 1000), // 36 hours ago
                description: 'Quarterly performance review meeting scheduled for next Friday. Please prepare your reports and have them submitted by Wednesday.',
                important: true
            },
            {
                heading: 'Website Update Completed',
                date: new Date(Date.now() - 60 * 60 * 60 * 1000), // 60 hours ago (more than 48)
                description: 'The latest website update has been successfully deployed. New features include enhanced user dashboard and improved mobile responsiveness.',
                important: false
            },
            {
                heading: 'Security Audit Required',
                date: new Date(Date.now() - 6 * 60 * 60 * 1000), // 6 hours ago
                description: 'Annual security audit scheduled for next month. All departments should prepare their security documentation and access logs for review.',
                important: true
            },
            {
                heading: 'Team Building Event',
                date: new Date(Date.now() - 72 * 60 * 60 * 1000), // 72 hours ago (more than 48)
                description: 'Quarterly team building event scheduled for next month. More details about location and activities will be shared soon.',
                important: false
            }
        ];

        // Format date function
        function getFormattedDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            
            return `${year}-${month}-${day} ${hours}:${minutes}`;
        }

        // Check if notification is new (less than 48 hours old)
        function isNotificationNew(notificationDate) {
            const now = new Date();
            const diffInHours = (now - notificationDate) / (1000 * 60 * 60);
            return diffInHours < 48;
        }

        // App download function
        function downloadApp(appType) {
            const appLinks = {
                seller: '#', // Replace with actual download link
                customer: '#' // Replace with actual download link
            };
            
            const appNames = {
                seller: 'Seller App',
                customer: 'Customer App'
            };
            
            // In a real implementation, this would redirect to the actual download link
            alert(`Downloading ${appNames[appType]}...\n\nThis would redirect to the actual download page in a production environment.`);
            
            // Uncomment the line below for actual implementation
            // window.location.href = appLinks[appType];
        }

        // Load notifications
        function loadNotifications() {
            const container = document.getElementById('notifications-container');
            container.innerHTML = '';
            
            notifications.forEach(notification => {
                const isNew = isNotificationNew(notification.date);
                const notificationElement = document.createElement('div');
                notificationElement.className = notification.important ? 
                    'notification-box important' : 'notification-box';
                
                let badgeHtml = '';
                if (isNew) {
                    badgeHtml = '<div class="new-badge">NEW</div>';
                }
                
                notificationElement.innerHTML = `
                    ${badgeHtml}
                    <div class="notification-header-row">
                        <div class="notification-heading">${notification.heading}</div>
                        <span class="notification-date">${getFormattedDate(notification.date)}</span>
                    </div>
                    <div class="notification-description">${notification.description}</div>
                `;
                
                container.appendChild(notificationElement);
            });
        }

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
        });
    </script>
</body>
</html>