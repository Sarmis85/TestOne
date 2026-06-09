/* ═══════════════════════════════════════════════════════════════════
   OBECNÍ DŮM HOLČOVICE — ADMIN JAVASCRIPT
   ═══════════════════════════════════════════════════════════════════ */

'use strict';

/* ─── Sidebar ────────────────────────────────────────────────────── */
const AdminSidebar = (() => {
  const sidebar = document.querySelector('.admin-sidebar');
  const toggle  = document.querySelector('.admin-sidebar__toggle');
  const mobileToggle = document.querySelector('.admin-mobile-toggle');
  let collapsed = localStorage.getItem('od_admin_sidebar') === 'collapsed';

  function applyState() {
    if (!sidebar) return;
    sidebar.classList.toggle('collapsed', collapsed);
  }

  function init() {
    applyState();
    toggle?.addEventListener('click', () => {
      collapsed = !collapsed;
      localStorage.setItem('od_admin_sidebar', collapsed ? 'collapsed' : 'expanded');
      applyState();
    });
    mobileToggle?.addEventListener('click', () => {
      sidebar?.classList.toggle('mobile-open');
    });
    // Close sidebar overlay on mobile backdrop click
    document.addEventListener('click', e => {
      if (window.innerWidth <= 1024) {
        if (!sidebar?.contains(e.target) && !mobileToggle?.contains(e.target)) {
          sidebar?.classList.remove('mobile-open');
        }
      }
    });
    // Mark active nav item
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    sidebar?.querySelectorAll('.admin-nav-item').forEach(item => {
      const href = item.getAttribute('href') || '';
      if (href.endsWith(currentPage)) item.classList.add('active');
    });
  }
  return { init };
})();

/* ─── Data Tables ────────────────────────────────────────────────── */
const DataTable = (() => {
  function initSort(table) {
    table.querySelectorAll('th[data-sort]').forEach(th => {
      th.classList.add('admin-table__sort');
      th.innerHTML += `<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6-6 6 6M6 15l6 6 6-6"/></svg>`;
      th.addEventListener('click', () => {
        const col = th.dataset.sort;
        const asc = th.classList.contains('asc');
        table.querySelectorAll('th').forEach(t => t.classList.remove('asc', 'desc'));
        th.classList.add(asc ? 'desc' : 'asc');
        sortTable(table, col, !asc);
      });
    });
  }

  function sortTable(table, col, asc) {
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    const rows = Array.from(tbody.querySelectorAll('tr'));
    rows.sort((a, b) => {
      const aVal = a.querySelector(`[data-col="${col}"]`)?.textContent.trim() || '';
      const bVal = b.querySelector(`[data-col="${col}"]`)?.textContent.trim() || '';
      const numA = parseFloat(aVal.replace(/[^0-9.-]/g, ''));
      const numB = parseFloat(bVal.replace(/[^0-9.-]/g, ''));
      if (!isNaN(numA) && !isNaN(numB)) return asc ? numA - numB : numB - numA;
      return asc ? aVal.localeCompare(bVal, 'cs') : bVal.localeCompare(aVal, 'cs');
    });
    rows.forEach(row => tbody.appendChild(row));
  }

  function init() {
    document.querySelectorAll('.admin-table').forEach(table => initSort(table));
  }
  return { init };
})();

/* ─── Modal ──────────────────────────────────────────────────────── */
const Modal = (() => {
  function open(id) {
    const backdrop = document.getElementById(id);
    backdrop?.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function close(id) {
    const backdrop = id
      ? document.getElementById(id)
      : document.querySelector('.modal-backdrop.open');
    backdrop?.classList.remove('open');
    document.body.style.overflow = '';
  }
  function init() {
    document.querySelectorAll('[data-modal-open]').forEach(btn => {
      btn.addEventListener('click', () => open(btn.dataset.modalOpen));
    });
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
      btn.addEventListener('click', () => close(btn.dataset.modalClose));
    });
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
      backdrop.addEventListener('click', e => { if (e.target === backdrop) close(); });
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  }
  return { init, open, close };
})();

/* ─── Toast ──────────────────────────────────────────────────────── */
const Toast = (() => {
  function show({ title, message, type = 'info', duration = 4000 }) {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.innerHTML = `<div><div class="toast__title">${title}</div>${message ? `<div class="toast__msg">${message}</div>` : ''}</div>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.animation = 'fadeIn 0.3s ease reverse both'; setTimeout(() => toast.remove(), 300); }, duration);
    toast.addEventListener('click', () => toast.remove());
  }
  return { show };
})();

/* ─── Confirm Dialog ─────────────────────────────────────────────── */
function confirmAction(message, callback) {
  if (window.confirm(message)) callback();
}

/* ─── Status Change ──────────────────────────────────────────────── */
function changeStatus(id, newStatus, type) {
  confirmAction(`Změnit stav na "${newStatus}"?`, () => {
    // In production: API call
    const row = document.querySelector(`[data-id="${id}"]`);
    if (row) {
      const statusEl = row.querySelector('.status');
      if (statusEl) {
        statusEl.className = `status status--${newStatus.toLowerCase()}`;
        statusEl.querySelector('span') && (statusEl.querySelector('span').textContent = newStatus);
      }
    }
    Toast.show({ title: 'Stav aktualizován', type: 'success' });
  });
}

/* ─── Delete Row ─────────────────────────────────────────────────── */
function deleteRow(id, type) {
  confirmAction(`Opravdu smazat tento záznam? Akce je nevratná.`, () => {
    const row = document.querySelector(`[data-id="${id}"]`);
    if (row) {
      row.style.animation = 'fadeIn 0.3s ease reverse both';
      setTimeout(() => row.remove(), 300);
    }
    Toast.show({ title: 'Záznam smazán', type: 'success' });
  });
}

/* ─── Toggle Availability ────────────────────────────────────────── */
function toggleAvailability(id) {
  const toggle = document.querySelector(`[data-toggle-id="${id}"]`);
  if (!toggle) return;
  const isActive = toggle.classList.contains('active');
  toggle.classList.toggle('active');
  toggle.textContent = isActive ? 'Nedostupné' : 'Dostupné';
  Toast.show({ title: isActive ? 'Označeno jako nedostupné' : 'Označeno jako dostupné', type: 'success' });
}

/* ─── Image Upload Preview ───────────────────────────────────────── */
function initImageUpload() {
  document.querySelectorAll('[data-img-upload]').forEach(input => {
    input.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) return;
      const preview = document.querySelector(`[data-img-preview="${input.dataset.imgUpload}"]`);
      if (preview) {
        const reader = new FileReader();
        reader.onload = ev => { preview.src = ev.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(file);
      }
    });
  });
}

/* ─── Drag & Drop (Menu Categories) ─────────────────────────────── */
function initDragDrop() {
  const list = document.querySelector('[data-draggable-list]');
  if (!list) return;
  let dragging = null;
  list.querySelectorAll('[data-draggable]').forEach(item => {
    item.draggable = true;
    item.addEventListener('dragstart', () => { dragging = item; item.style.opacity = '0.4'; });
    item.addEventListener('dragend',   () => { dragging = null; item.style.opacity = ''; });
    item.addEventListener('dragover',  e => { e.preventDefault(); });
    item.addEventListener('drop', () => {
      if (dragging && dragging !== item) {
        const items = [...list.querySelectorAll('[data-draggable]')];
        const fromIdx = items.indexOf(dragging);
        const toIdx   = items.indexOf(item);
        if (fromIdx < toIdx) item.after(dragging);
        else item.before(dragging);
        Toast.show({ title: 'Pořadí aktualizováno', type: 'success' });
      }
    });
  });
}

/* ─── Charts (Simple Bar Chart) ──────────────────────────────────── */
function initCharts() {
  document.querySelectorAll('[data-chart]').forEach(container => {
    const data = JSON.parse(container.dataset.chart || '[]');
    if (!data.length) return;
    const max = Math.max(...data.map(d => d.value));
    container.innerHTML = data.map(d => `
      <div style="display:flex;flex-direction:column;align-items:center;gap:4px;flex:1;min-width:0">
        <span style="font-size:10px;color:var(--c-text-muted);font-weight:600">${d.value}</span>
        <div class="chart-bar ${d.accent ? 'chart-bar--accent' : ''}" style="height:${(d.value/max)*100}%" title="${d.label}: ${d.value}"></div>
        <span style="font-size:10px;color:var(--c-text-faint);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;text-align:center">${d.label}</span>
      </div>
    `).join('');
  });
}

/* ─── Calendar (Month View) ──────────────────────────────────────── */
function initCalendar() {
  const cal = document.querySelector('.calendar');
  if (!cal) return;

  const now = new Date();
  let year = now.getFullYear();
  let month = now.getMonth();

  const monthEl = cal.querySelector('.calendar__month');
  const gridEl  = cal.querySelector('.calendar__grid');
  const prevBtn = cal.querySelector('[data-cal-prev]');
  const nextBtn = cal.querySelector('[data-cal-next]');

  const sampleEvents = {
    [now.getDate()]: [{ title: 'Rezervace Novák', type: 'green' }],
    [now.getDate() + 2]: [{ title: 'Hudební večer', type: 'gold' }, { title: 'Rezervace Horák', type: 'green' }],
    [now.getDate() + 5]: [{ title: 'Narozeniny — 30 os.', type: 'blue' }],
  };

  const months = ['Leden','Únor','Březen','Duben','Květen','Červen','Červenec','Srpen','Září','Říjen','Listopad','Prosinec'];
  const days   = ['Po','Út','St','Čt','Pá','So','Ne'];

  function render() {
    if (!gridEl) return;
    if (monthEl) monthEl.textContent = `${months[month]} ${year}`;

    const firstDay = new Date(year, month, 1).getDay();
    const offset = (firstDay + 6) % 7; // Mon = 0
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrev  = new Date(year, month, 0).getDate();

    const weekdayRow = days.map(d => `<div class="calendar__weekday">${d}</div>`).join('');
    let cells = weekdayRow;

    for (let i = offset - 1; i >= 0; i--) {
      cells += `<div class="calendar__day calendar__day--other-month"><div class="calendar__day-num">${daysInPrev - i}</div></div>`;
    }
    for (let d = 1; d <= daysInMonth; d++) {
      const isToday = d === now.getDate() && month === now.getMonth() && year === now.getFullYear();
      const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const events = sampleEvents[d] || [];
      cells += `
        <div class="calendar__day ${isToday ? 'calendar__day--today' : ''}" onclick="calendarDayClick('${dateStr}')">
          <div class="calendar__day-num">${d}</div>
          ${events.map(ev => `<div class="calendar__event calendar__event--${ev.type}">${ev.title}</div>`).join('')}
        </div>`;
    }
    const remaining = 7 - ((offset + daysInMonth) % 7);
    if (remaining < 7) {
      for (let d = 1; d <= remaining; d++) {
        cells += `<div class="calendar__day calendar__day--other-month"><div class="calendar__day-num">${d}</div></div>`;
      }
    }
    gridEl.innerHTML = cells;
  }

  prevBtn?.addEventListener('click', () => { month--; if (month < 0) { month = 11; year--; } render(); });
  nextBtn?.addEventListener('click', () => { month++; if (month > 11) { month = 0; year++; } render(); });
  render();
}

window.calendarDayClick = function(dateStr) {
  // Default handler — can be overridden by page-specific JS (e.g. events.html)
  const d = new Date(dateStr);
  Toast.show({ title: `${d.getDate()}.${d.getMonth()+1}.${d.getFullYear()}`, message: 'Klikněte pro přidání události', type: 'info' });
};

/* ─── Filters ────────────────────────────────────────────────────── */
function initFilters() {
  document.querySelectorAll('[data-filter-input]').forEach(input => {
    const target = input.dataset.filterInput;
    const rows = document.querySelectorAll(`[data-filter-row="${target}"]`);
    input.addEventListener('input', () => {
      const q = input.value.toLowerCase();
      rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });

  document.querySelectorAll('[data-filter-select]').forEach(select => {
    const target = select.dataset.filterSelect;
    const rows = document.querySelectorAll(`[data-filter-row="${target}"]`);
    select.addEventListener('change', () => {
      const val = select.value;
      rows.forEach(row => {
        if (!val) { row.style.display = ''; return; }
        const rowVal = row.dataset.filterValue || '';
        row.style.display = rowVal.includes(val) ? '' : 'none';
      });
    });
  });
}

/* ─── Initialize ─────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  AdminSidebar.init();
  DataTable.init();
  Modal.init();
  initImageUpload();
  initDragDrop();
  initCharts();
  initCalendar();
  initFilters();
});

window.Toast = Toast;
window.Modal = Modal;
window.changeStatus = changeStatus;
window.deleteRow = deleteRow;
window.toggleAvailability = toggleAvailability;
