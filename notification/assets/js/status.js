document.addEventListener('DOMContentLoaded', function () {
  const taskList = document.getElementById('taskList');
  const emptyState = document.getElementById('emptyState');
  const searchInput = document.getElementById('searchInput');
  const filterBtns = Array.from(document.querySelectorAll('.filter-btn'));
  const paginationWrap = document.getElementById('paginationWrap');
  const prevPageBtn = document.getElementById('prevPageBtn');
  const nextPageBtn = document.getElementById('nextPageBtn');
  const paginationPages = document.getElementById('paginationPages');
  const stats = { all: document.getElementById('stat-all'), assigned: document.getElementById('stat-assigned'), inprogress: document.getElementById('stat-inprogress'), done: document.getElementById('stat-done') };

  let tasks = [];
  let currentFilter = 'all';
  let currentPage = 1;
  const perPage = 5;

  async function loadTasks() {
    try {
      const res = await fetch('fetch_tasks.php');
      const json = await res.json();
      if (json.success) {
        tasks = json.tasks;
        render();
      } else {
        taskList.innerHTML = '<div class="empty-state">Failed to load tasks</div>';
      }
    } catch (err) {
      taskList.innerHTML = '<div class="empty-state">Network error</div>';
    }
  }

  function render() {
    const q = searchInput.value.trim().toLowerCase();
    const filtered = tasks.filter(t => {
      if (currentFilter !== 'all' && t.status !== currentFilter) return false;
      if (!q) return true;
      return (t.title && t.title.toLowerCase().includes(q)) || (t.recipient_name && t.recipient_name.toLowerCase().includes(q)) || (t.recipient_email && t.recipient_email.toLowerCase().includes(q));
    });

    const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
    if (currentPage > totalPages) {
      currentPage = totalPages;
    }
    const start = (currentPage - 1) * perPage;
    const pagedTasks = filtered.slice(start, start + perPage);

    taskList.innerHTML = '';
    if (filtered.length === 0) {
      emptyState.classList.remove('hidden');
      paginationWrap.classList.add('hidden');
    } else {
      emptyState.classList.add('hidden');
      pagedTasks.forEach(renderTask);
      renderPagination(totalPages);
    }

    // update stats
    stats.all.textContent = tasks.length;
    stats.assigned.textContent = tasks.filter(t => t.status === 'Assigned').length;
    stats.inprogress.textContent = tasks.filter(t => t.status === 'In Progress').length;
    stats.done.textContent = tasks.filter(t => t.status === 'Done').length;
  }

  function renderPagination(totalPages) {
    paginationWrap.classList.toggle('hidden', totalPages <= 1);

    prevPageBtn.disabled = currentPage <= 1;
    nextPageBtn.disabled = currentPage >= totalPages;

    paginationPages.innerHTML = '';
    for (let page = 1; page <= totalPages; page++) {
      const pageBtn = document.createElement('button');
      pageBtn.type = 'button';
      pageBtn.className = 'pagination-page-btn' + (page === currentPage ? ' active' : '');
      pageBtn.textContent = String(page);
      pageBtn.addEventListener('click', function () {
        if (currentPage !== page) {
          currentPage = page;
          render();
        }
      });
      paginationPages.appendChild(pageBtn);
    }
  }

  function renderTask(t) {
    const card = document.createElement('div');
    card.className = 'task-card';

    const left = document.createElement('div');
    const title = document.createElement('div'); title.className = 'task-card-title'; title.textContent = t.title;
    const meta = document.createElement('div'); meta.className = 'task-meta';
    const startedLabel = t.started_at ? formatDateTime(t.started_at) : '';
    const completedLabel = t.completed_at ? formatDateTime(t.completed_at) : '';
    const pendingDays = getPendingDays(t);
    const pendingLabel = t.status === 'Done' ? `Duration: ${pendingDays} day(s)` : `Pending: ${pendingDays} day(s)`;
    meta.innerHTML = `
      <div class="task-meta-item">${t.recipient_name} &lt;${t.recipient_email}&gt;</div>
      <div class="task-meta-item">Deadline: ${t.deadline || '—'}</div>
      <div class="task-meta-item">Priority: <span class="priority-tag ${escapeClass(t.priority)}">${t.priority}</span></div>
      <div class="task-meta-item">Started: ${startedLabel}</div>
      <div class="task-meta-item">Completed: ${completedLabel}</div>
      <div class="task-meta-item">${pendingLabel}</div>`;
    const desc = document.createElement('div'); desc.className = 'task-desc'; desc.textContent = t.description;

    left.appendChild(title);
    left.appendChild(meta);
    left.appendChild(desc);

    const right = document.createElement('div'); right.className = 'task-card-right';
    const statusBadge = document.createElement('div'); statusBadge.className = 'status-badge ' + sanitizeStatusClass(t.status); statusBadge.textContent = t.status;

    right.appendChild(statusBadge);

    card.appendChild(left);
    card.appendChild(right);

    taskList.appendChild(card);
  }

  function escapeClass(s) { return (s||'').replace(/[^a-zA-Z0-9_-]/g,''); }
  function sanitizeStatusClass(s) { return s.replace(/\s+/g, '-'); }

  function formatDateTime(value) {
    const date = new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString();
  }

  function daysBetween(startValue, endValue) {
    const start = startValue ? new Date(startValue) : null;
    const end = endValue ? new Date(endValue) : null;
    if (!start || !end || Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return 0;
    return Math.max(0, Math.floor((end - start) / 86400000));
  }

  function getPendingDays(task) {
    const baseline = task.started_at || task.created_at;
    const end = task.status === 'Done' && task.completed_at ? task.completed_at : new Date().toISOString();
    return daysBetween(baseline, end);
  }

  // FILTER/SEARCH
  filterBtns.forEach(b => b.addEventListener('click', () => {
    filterBtns.forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    currentFilter = b.dataset.filter === 'all' ? 'all' : b.dataset.filter;
    currentPage = 1;
    render();
  }));

  searchInput.addEventListener('input', debounce(() => {
    currentPage = 1;
    render();
  }, 250));

  prevPageBtn.addEventListener('click', function () {
    if (currentPage > 1) {
      currentPage -= 1;
      render();
    }
  });

  nextPageBtn.addEventListener('click', function () {
    currentPage += 1;
    render();
  });

  function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

  // initial load
  loadTasks();
});
