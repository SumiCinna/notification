<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Task Assignment System</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
</head>
<body>

  <!-- HEADER -->
  <header class="site-header">
    <div class="container header-inner">
      <div class="brand">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="28" height="28" rx="6" fill="#1a4fba"/>
          <rect x="6" y="8" width="16" height="2" rx="1" fill="white"/>
          <rect x="6" y="13" width="12" height="2" rx="1" fill="white"/>
          <rect x="6" y="18" width="8" height="2" rx="1" fill="white"/>
        </svg>
        <span class="brand-name">ITAO Task Assignment</span>
      </div>
      <nav class="site-nav">
        <a href="index.php" class="nav-link active">Assign Task</a>
        <a href="status.php" class="nav-link">Track Status</a>
      </nav>
    </div>
  </header>

  

  <!-- ASSIGN FORM -->
  <section class="form-section" id="assign-form">
    <div class="container">
      <div class="form-card">
        <div class="form-card-header">
          <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
            <path d="M3 5h16M3 11h10M3 17h7" stroke="#1a4fba" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <h2>Task Assignment</h2>
        </div>

        <div id="form-alert" class="form-alert hidden"></div>

        <form id="taskForm" novalidate>
          <div class="form-row">
            <div class="form-group">
              <label for="sender_name">Your Name <span class="req">*</span></label>
              <input type="text" id="sender_name" name="sender_name" placeholder="Enter your full name" autocomplete="off"/>
              <span class="field-error" id="err_sender_name"></span>
            </div>
            <div class="form-group">
              <label for="sender_email">Your Email <span class="req">*</span></label>
              <input type="email" id="sender_email" name="sender_email" placeholder="your@email.com" autocomplete="off"/>
              <span class="field-error" id="err_sender_email"></span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="recipient_name">Recipient Name <span class="req">*</span></label>
              <input type="text" id="recipient_name" name="recipient_name" placeholder="Recipient's full name" autocomplete="off"/>
              <span class="field-error" id="err_recipient_name"></span>
            </div>
            <div class="form-group">
              <label for="recipient_email">Recipient Email <span class="req">*</span></label>
              <input type="email" id="recipient_email" name="recipient_email" placeholder="recipient@email.com" autocomplete="off"/>
              <span class="field-error" id="err_recipient_email"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="task_title">Task Title <span class="req">*</span></label>
            <input type="text" id="task_title" name="task_title" placeholder="Brief title of the task" autocomplete="off"/>
            <span class="field-error" id="err_task_title"></span>
          </div>

          <div class="form-group">
            <label for="task_description">Task Description <span class="req">*</span></label>
            <textarea id="task_description" name="task_description" rows="4" placeholder="Provide detailed instructions or context for this task..."></textarea>
            <span class="field-error" id="err_task_description"></span>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="task_deadline">Deadline <span class="req">*</span></label>
              <input type="date" id="task_deadline" name="task_deadline"/>
              <span class="field-error" id="err_task_deadline"></span>
            </div>
            <div class="form-group">
              <label for="task_priority">Priority Level <span class="req">*</span></label>
              <select id="task_priority" name="task_priority">
                <option value="">Select priority</option>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
                <option value="Urgent">Urgent</option>
              </select>
              <span class="field-error" id="err_task_priority"></span>
            </div>
          </div>

          <div class="form-footer">
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M2 9l13 0M10 4l5 5-5 5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Send Assignment
            </button>
            <p class="form-note">An email notification will be sent to the recipient immediately.</p>
          </div>
        </form>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="site-footer">
    <div class="container footer-inner">
      <span class="brand-name">ITAO Task Assignment</span>
      <span class="footer-copy">&copy; 2026 ITAO Task Assignment System. All rights reserved.</span>
    </div>
  </footer>

  <script src="assets/js/emailjs-config.js"></script>
  <script src="assets/js/assign.js"></script>
</body>
</html>