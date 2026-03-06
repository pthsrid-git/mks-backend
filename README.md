# Prediction System - CodeIgniter 3

## Features
- ✅ Admin Panel with Admin LTE
- ✅ RESTful API for Flutter Mobile App
- ✅ Prediction System with Bonus Calculation
- ✅ User Management
- ✅ Match Management

## Installation

1. **Setup Database**
   - Create database `prediction_system`
   - Import `database.sql`

2. **Configure Database**
   - Edit `application/config/database.php`
   - Update database credentials

3. **Download CodeIgniter 3**
   - Download from: https://codeigniter.com/download
   - Copy `system` folder to project root

4. **Download REST_Controller**
   - Download from: https://github.com/chriskacerguis/codeigniter-restserver
   - Copy `REST_Controller.php` to `application/libraries/`

5. **Access Admin Panel**
   - URL: http://localhost/your-project/admin
   - Login: admin / admin123

## API Endpoints for Flutter

- `POST /api/login` - User login
- `POST /api/register` - User registration
- `GET /api/matches?league_id=X&week_number=Y` - Get matches
- `POST /api/prediction` - Submit prediction
- `GET /api/predictions?user_id=X` - Get user predictions
- `GET /api/stats?user_id=X` - Get user stats

## Bonus System
- Users get $100 bonus for 10 correct predictions in a week
- Use "Calculate Bonus" in admin panel to process bonuses# mks-backend
