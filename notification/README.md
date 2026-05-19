# TaskDesk — Task Assignment System

Complete setup and connection guide for XAMPP (Windows)

## 📋 System Architecture

```
Frontend Pages:
├── index.php → Assign task form
└── status.php → View & update task status

Backend Endpoints (JSON API):
├── assign.php → Save new task + PHP mail backup
├── fetch_tasks.php → Get all tasks
├── update_status.php → Update task status
└── update_task.php → Alternative update endpoint

Database:
├── db.php → PDO connection (root/DREAMTEAM)
└── taskdesk (MySQL database)

Email Service:
└── EmailJS (service_4si46bk / template_evgvrfc)
```

## 🚀 Quick Setup (XAMPP)

1. **Database Setup:**
   - Open: `http://localhost/notification/create_db.php`
   - Or run: `php create_db.php` in terminal
   - Or import `create_database.sql` via phpMyAdmin

2. **Verify MySQL is running:**
   - Check XAMPP Control Panel → MySQL (Start button)
   - Default: root / password: DREAMTEAM

3. **Access the application:**
   - Assign tasks: `http://localhost/notification/index.php`
   - View status: `http://localhost/notification/status.php`

## 📡 Page Connections

| Page | Calls | Purpose |
|------|-------|---------|
| **index.php** | assign.js | Task assignment form |
| **assign.js** | assign.php | Submit form → Save task |
| **assign.php** | db.php + PHP mail | Save to DB + send email |
| **EmailJS** | Browser → EmailJS API | Send email to recipient |
| **status.php** | status.js | Task tracking dashboard |
| **status.js** | fetch_tasks.php | Load all tasks |
| **status.js** | update_status.php | Change task status |
| **update_status.php** | db.php | Update DB status |

## 🔧 Configuration

**Database Credentials** (`db.php`):
```php
$host = '127.0.0.1';
$user = 'root';
$pass = 'DREAMTEAM';
$db   = 'taskdesk';
```

**EmailJS Config** (`assets/js/assign.js`):
```javascript
emailjs.init({ publicKey: '4ddxDf1zPYW-G2-LS' });
var serviceId  = 'service_4si46bk';
var templateId = 'template_evgvrfc';
```

**EmailJS Template variables** (must match in your template):
- `{{to_email}}` - Recipient email
- `{{receiver_name}}` - Recipient name
- `{{sender_name}}` - Sender name
- `{{task_title}}` - Task title
- `{{task_description}}` - Task description
- `{{task_deadline}}` - Deadline date
- `{{task_status}}` - Status (Assigned)

## ✅ Testing Checklist

- [ ] MySQL running with correct credentials
- [ ] Database `taskdesk` created with `tasks` table
- [ ] index.php form loads without errors
- [ ] Form submits successfully
- [ ] Email sent (check recipient inbox + spam)
- [ ] Tasks appear on status.php
- [ ] Can update task status
- [ ] Console has no errors (F12)

## 📧 Email Troubleshooting

**Emails not sending?**
1. Check browser console (F12) for errors
2. Verify EmailJS public key is correct
3. Ensure "To Email" field in template is `{{to_email}}`
4. Check server logs for PHP mail() errors
5. Verify recipient email is valid

**PHP mail() only works if:**
- XAMPP sendmail is configured (see `php.ini`)
- Or use EmailJS for reliable delivery

## 🆘 Common Issues

| Problem | Solution |
|---------|----------|
| "Database connection failed" | Check MySQL running + credentials in db.php |
| "Access denied for root" | Verify password is 'DREAMTEAM' in db.php |
| Form won't submit | Open F12 console → check errors |
| Emails not sent | Update EmailJS template "To Email" field |
| Tasks not showing | Verify database queries in fetch_tasks.php |

## 📞 Support

All files use consistent PDO connections via `db.php`.
Check `console.log` messages (F12) for debugging.

---

**Last Updated:** May 18, 2026

