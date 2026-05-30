<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Task Status Tracker</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
</head>
<body>

  <!-- HEADER -->
  <header class="site-header">
    <div class="container header-inner">
      <div class="brand">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
          <rect width="28" height="28" rx="6" fill="#1a4fba"/>
          <rect x="6" y="8" width="16" height="2" rx="1" fill="white"/>
          <rect x="6" y="13" width="12" height="2" rx="1" fill="white"/>
          <rect x="6" y="18" width="8" height="2" rx="1" fill="white"/>
        </svg>
        <span class="brand-name">ITAO Task Assignment</span>
      </div>
      <nav class="site-nav">
        <a href="index.php" class="nav-link">Assign Task</a>
        <a href="status.php" class="nav-link active">Track Status</a>
      </nav>
    </div>
  </header>

  <!-- STATUS LEGEND -->
  <section class="status-section">
    <div class="container">

      <!-- STATS ROW -->
      <div class="stats-row" id="statsRow">
        <div class="stat-box">
          <span class="stat-num" id="stat-all">0</span>
          <span class="stat-label">Total Tasks</span>
        </div>
        <div class="stat-box">
          <span class="stat-num accent-assigned" id="stat-assigned">0</span>
          <span class="stat-label">Assigned</span>
        </div>
        <div class="stat-box">
          <span class="stat-num accent-inprogress" id="stat-inprogress">0</span>
          <span class="stat-label">In Progress</span>
        </div>
        <div class="stat-box">
          <span class="stat-num accent-done" id="stat-done">0</span>
          <span class="stat-label">Done</span>
        </div>
        <div class="stat-box">
          <span class="stat-num accent-missing" id="stat-missing">0</span>
          <span class="stat-label">Missing</span>
        </div>
      </div>

      <!-- FILTERS -->
      <div class="filter-bar">
        <div class="search-wrap">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <circle cx="6.5" cy="6.5" r="4.5" stroke="#6b7b99" stroke-width="1.5"/>
            <path d="M10 10l3.5 3.5" stroke="#6b7b99" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <input type="text" id="searchInput" placeholder="Search task title or recipient..." />
        </div>
        <div class="filter-btns">
          <button class="filter-btn active" data-filter="all">All</button>
          <button class="filter-btn" data-filter="Assigned">Assigned</button>
          <button class="filter-btn" data-filter="In Progress">In Progress</button>
          <button class="filter-btn" data-filter="Done">Done</button>
          <button class="filter-btn" data-filter="Missing">Missing</button>
        </div>
      </div>

      <!-- TASK LIST -->
      <div id="taskList" class="task-list">
        <!-- Tasks rendered by JS -->
      </div>

      <div id="emptyState" class="empty-state hidden">
        <svg width="56" height="56" viewBox="0 0 56 56" fill="none">
          <rect width="56" height="56" rx="12" fill="#e8f0fe"/>
          <path d="M16 20h24M16 28h16M16 36h10" stroke="#1a4fba" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <p>No tasks found matching your criteria.</p>
      </div>

      <div class="pagination-wrap hidden" id="paginationWrap">
        <button class="pagination-btn" id="prevPageBtn" type="button">Previous</button>
        <div class="pagination-pages" id="paginationPages"></div>
        <button class="pagination-btn" id="nextPageBtn" type="button">Next</button>
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
  <script src="assets/js/status.js"></script>
</body>
</html>