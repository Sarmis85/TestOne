/* ═══════════════════════════════════════════════════════════════════
   OBECNÍ DŮM HOLČOVICE — MAIN JAVASCRIPT
   ═══════════════════════════════════════════════════════════════════ */

'use strict';

/* ─── Navigation ────────────────────────────────────────────────── */
const Nav = (() => {
  const nav = document.querySelector('.nav');
  const hamburger = document.querySelector('.nav__hamburger');
  const mobileMenu = document.querySelector('.mobile-menu');
  let isOpen = false;

  function updateScrollState() {
    if (!nav) return;
    const threshold = 40;
    if (window.scrollY > threshold) {
      nav.classList.add('nav--scrolled');
      nav.classList.remove('nav--transparent');
    } else {
      nav.classList.remove('nav--scrolled');
      if (nav.dataset.transparent !== 'false') {
        nav.classList.add('nav--transparent');
      }
    }
  }

  function toggleMenu() {
    isOpen = !isOpen;
    hamburger?.classList.toggle('open', isOpen);
    if (mobileMenu) {
      mobileMenu.classList.toggle('open', isOpen);
      document.body.style.overflow = isOpen ? 'hidden' : '';
    }
  }

  function closeMenu() {
    isOpen = false;
    hamburger?.classList.remove('open');
    mobileMenu?.classList.remove('open');
    document.body.style.overflow = '';
  }

  function init() {
    if (!nav) return;
    updateScrollState();
    window.addEventListener('scroll', updateScrollState, { passive: true });
    hamburger?.addEventListener('click', toggleMenu);
    mobileMenu?.querySelectorAll('.mobile-menu__link').forEach(link => {
      link.addEventListener('click', closeMenu);
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenu(); });
    // Mark active link
    const currentPath = window.location.pathname.split('/').pop() || 'index.html';
    nav.querySelectorAll('.nav__link').forEach(link => {
      const href = link.getAttribute('href');
      if (href && (href === currentPath || (currentPath === '' && href === 'index.html'))) {
        link.classList.add('nav__link--active');
      }
    });
  }

  return { init };
})();

/* ─── Scroll Animations ──────────────────────────────────────────── */
const ScrollAnimate = (() => {
  function init() {
    if (!('IntersectionObserver' in window)) return;
    const elements = document.querySelectorAll('[data-animate]');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const anim = el.dataset.animate || 'fade-in-up';
          el.classList.add(`animate-${anim}`);
          el.style.opacity = '';
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    elements.forEach(el => {
      el.style.opacity = '0';
      observer.observe(el);
    });
  }
  return { init };
})();

/* ─── Menu Tabs ──────────────────────────────────────────────────── */
const MenuTabs = (() => {
  function init() {
    const tabs = document.querySelectorAll('[data-tab-trigger]');
    const panels = document.querySelectorAll('[data-tab-panel]');
    if (!tabs.length) return;

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const target = tab.dataset.tabTrigger;
        tabs.forEach(t => t.classList.remove('active'));
        panels.forEach(p => p.hidden = true);
        tab.classList.add('active');
        const panel = document.querySelector(`[data-tab-panel="${target}"]`);
        if (panel) panel.hidden = false;
      });
    });
    // Activate first by default
    if (tabs[0]) tabs[0].click();
  }
  return { init };
})();

/* ─── Day Selector (Menu Preview) ───────────────────────────────── */
const DaySelector = (() => {
  function init() {
    const btns = document.querySelectorAll('[data-day]');
    btns.forEach(btn => {
      btn.addEventListener('click', () => {
        btns.forEach(b => b.classList.remove('tag--active'));
        btn.classList.add('tag--active');
        // In a real app, fetch menu for that day
        document.querySelectorAll('[data-day-panel]').forEach(p => p.hidden = true);
        const panel = document.querySelector(`[data-day-panel="${btn.dataset.day}"]`);
        if (panel) panel.hidden = false;
      });
    });
    if (btns[0]) btns[0].click();
  }
  return { init };
})();

/* ─── Smooth Scroll ──────────────────────────────────────────────── */
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
      const id = link.getAttribute('href').slice(1);
      const target = document.getElementById(id);
      if (!target) return;
      e.preventDefault();
      const navH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h-scroll')) || 60;
      const y = target.getBoundingClientRect().top + window.scrollY - navH - 16;
      window.scrollTo({ top: y, behavior: 'smooth' });
    });
  });
}

/* ─── Gallery Lightbox ───────────────────────────────────────────── */
const Lightbox = (() => {
  let currentIndex = 0;
  let images = [];

  function open(index) {
    currentIndex = index;
    const lb = document.querySelector('.lightbox');
    if (!lb) return;
    const img = lb.querySelector('.lightbox__img');
    if (img && images[index]) {
      img.src = images[index].src;
      img.alt = images[index].alt || '';
    }
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function close() {
    const lb = document.querySelector('.lightbox');
    lb?.classList.remove('open');
    document.body.style.overflow = '';
  }

  function navigate(dir) {
    currentIndex = (currentIndex + dir + images.length) % images.length;
    open(currentIndex);
  }

  function init() {
    const items = document.querySelectorAll('.gallery-item, .gallery-page-item');
    images = Array.from(items).map(item => ({
      src: item.querySelector('img')?.src || '',
      alt: item.querySelector('img')?.alt || ''
    }));

    items.forEach((item, i) => {
      item.addEventListener('click', () => open(i));
    });

    const lb = document.querySelector('.lightbox');
    lb?.querySelector('.lightbox__close')?.addEventListener('click', close);
    lb?.querySelector('.lightbox__prev')?.addEventListener('click', () => navigate(-1));
    lb?.querySelector('.lightbox__next')?.addEventListener('click', () => navigate(1));
    lb?.addEventListener('click', e => { if (e.target === lb) close(); });
    document.addEventListener('keydown', e => {
      if (!lb?.classList.contains('open')) return;
      if (e.key === 'ArrowLeft')  navigate(-1);
      if (e.key === 'ArrowRight') navigate(1);
      if (e.key === 'Escape')     close();
    });
  }

  return { init };
})();

/* ─── Reservation Form ───────────────────────────────────────────── */
const ReservationForm = (() => {
  function init() {
    const form = document.querySelector('[data-reservation-form]');
    if (!form) return;

    form.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = form.querySelector('[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Odesílám...';

      await new Promise(r => setTimeout(r, 1200));

      Toast.show({
        title: 'Žádost odeslána',
        message: 'Vaše žádost o rezervaci byla přijata. Brzy vás kontaktujeme.',
        type: 'success'
      });
      form.reset();
      btn.disabled = false;
      btn.textContent = 'Odeslat žádost';
    });
  }
  return { init };
})();

/* ─── Toast ──────────────────────────────────────────────────────── */
const Toast = (() => {
  function show({ title, message, type = 'info', duration = 5000 }) {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const icons = {
      success: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--c-success)"><path d="M20 6L9 17l-5-5"/></svg>`,
      error:   `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--c-error)"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>`,
      warning: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--c-warning)"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
      info:    `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--c-info)"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`
    };

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.innerHTML = `
      ${icons[type] || ''}
      <div>
        <div class="toast__title">${title}</div>
        ${message ? `<div class="toast__msg">${message}</div>` : ''}
      </div>
    `;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = 'fadeIn 0.3s ease reverse both';
      setTimeout(() => toast.remove(), 300);
    }, duration);

    toast.addEventListener('click', () => toast.remove());
  }

  return { show };
})();

/* ─── Cart (Delivery) ────────────────────────────────────────────── */
const Cart = (() => {
  let items = JSON.parse(localStorage.getItem('od_cart') || '[]');

  function save() { localStorage.setItem('od_cart', JSON.stringify(items)); }

  function add(id, name, price) {
    const existing = items.find(i => i.id === id);
    if (existing) { existing.qty++; }
    else { items.push({ id, name, price, qty: 1 }); }
    save();
    render();
    Toast.show({ title: 'Přidáno do košíku', message: name, type: 'success' });
  }

  function remove(id) {
    items = items.filter(i => i.id !== id);
    save();
    render();
  }

  function changeQty(id, delta) {
    const item = items.find(i => i.id === id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) remove(id);
    else { save(); render(); }
  }

  function total() { return items.reduce((sum, i) => sum + i.price * i.qty, 0); }

  function render() {
    const sidebar = document.querySelector('.cart-sidebar');
    if (!sidebar) return;
    const count = items.reduce((sum, i) => sum + i.qty, 0);
    const list = sidebar.querySelector('.cart-items');
    const totalEl = sidebar.querySelector('.cart-total');
    const countEl = sidebar.querySelector('.cart-count');
    if (countEl) countEl.textContent = count;
    if (totalEl) totalEl.textContent = total().toFixed(0) + ' Kč';
    if (list) {
      list.innerHTML = items.length === 0
        ? `<p style="color:var(--c-text-muted);font-size:var(--text-sm);text-align:center;padding:2rem 0">Košík je prázdný</p>`
        : items.map(item => `
            <div class="cart-item">
              <div class="cart-item__qty">
                <button class="cart-item__qty-btn" onclick="Cart.changeQty('${item.id}',-1)">−</button>
                <span style="font-size:var(--text-sm);font-weight:600;min-width:16px;text-align:center">${item.qty}</span>
                <button class="cart-item__qty-btn" onclick="Cart.changeQty('${item.id}',1)">+</button>
              </div>
              <div style="flex:1;font-size:var(--text-sm)">
                <div style="font-weight:600;color:var(--c-text)">${item.name}</div>
                <div style="color:var(--c-text-muted)">${item.price} Kč / ks</div>
              </div>
              <div style="font-weight:700;color:var(--c-green-700);font-size:var(--text-sm)">${(item.price * item.qty)} Kč</div>
            </div>
          `).join('');
    }
  }

  function init() { render(); }

  return { init, add, remove, changeQty, total };
})();

/* ─── Hero Scroll CTA ────────────────────────────────────────────── */
function initHeroScroll() {
  const hint = document.querySelector('.hero__scroll-hint');
  hint?.addEventListener('click', () => {
    const next = document.querySelector('.hero')?.nextElementSibling;
    if (next) next.scrollIntoView({ behavior: 'smooth' });
  });
}

/* ─── Form Validation ─────────────────────────────────────────────── */
function initFormValidation() {
  document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', e => {
      let valid = true;
      form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
          field.classList.add('form-input--error');
          valid = false;
        } else {
          field.classList.remove('form-input--error');
        }
      });
      if (!valid) {
        e.preventDefault();
        Toast.show({ title: 'Vyplňte povinná pole', type: 'error' });
      }
    });
  });
}

/* ─── Cookie Banner ──────────────────────────────────────────────── */
function initCookieBanner() {
  if (localStorage.getItem('od_cookies_accepted')) return;
  const banner = document.createElement('div');
  banner.innerHTML = `
    <div style="position:fixed;bottom:0;left:0;right:0;background:var(--c-green-800);color:rgba(255,255,255,0.85);padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;z-index:var(--z-toast);font-size:var(--text-sm)">
      <p style="max-width:60ch;color:rgba(255,255,255,0.75)">Tento web používá cookies pro zlepšení vašeho zážitku. <a href="gdpr.html" style="color:var(--c-gold-light);text-decoration:underline">Více informací</a></p>
      <div style="display:flex;gap:0.75rem;flex-shrink:0">
        <button onclick="this.closest('div').parentElement.remove();localStorage.setItem('od_cookies_accepted','all')" class="btn btn--accent btn--sm">Přijmout vše</button>
        <button onclick="this.closest('div').parentElement.remove();localStorage.setItem('od_cookies_accepted','essential')" class="btn btn--outline-light btn--sm">Jen nezbytné</button>
      </div>
    </div>
  `;
  document.body.appendChild(banner);
}

/* ─── Initialize All ─────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  Nav.init();
  ScrollAnimate.init();
  MenuTabs.init();
  DaySelector.init();
  Lightbox.init();
  ReservationForm.init();
  Cart.init();
  initSmoothScroll();
  initHeroScroll();
  initFormValidation();
  initCookieBanner();
});

/* Expose to global for inline handlers */
window.Cart = Cart;
window.Toast = Toast;
