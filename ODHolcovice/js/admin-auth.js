/**
 * Admin Auth Module — Obecní dům Holčovice
 * Načítá se na KAŽDÉ admin stránce po admin.js.
 * Kontroluje přihlášení a dynamicky aktualizuje sidebar + navigaci dle rolí.
 */
'use strict';

const AdminAuth = (() => {
  // Mapování DB rolí na zobrazované informace
  const ROLE_CONFIG = {
    super:   { label: 'Super Admin',    roleText: 'Správce systému',     nav: ['vedouci','restaurace','obec','system'] },
    vedouci: { label: 'Vedoucí',        roleText: 'Vedoucí restaurace',  nav: ['vedouci','restaurace','obec'] },
    obsluha: { label: 'Obsluha',        roleText: 'Obsluha restaurace',  nav: ['restaurace'] },
    kuchyn:  { label: 'Kuchyně',        roleText: 'Kuchyně',            nav: ['restaurace_kuchyn'] },
    rozvoz:  { label: 'Rozvoz',         roleText: 'Řidič rozvozu',      nav: ['rozvoz'] },
    obec:    { label: 'Editor obsahu',  roleText: 'Editor Obec',        nav: ['obec'] },
    zakaznik:{ label: 'Zákazník',       roleText: 'Zákazník',           nav: [] },
  };

  const NAV_SECTIONS = {
    vedouci: {
      title: 'Vedoucí', items: [
        { href: 'menu.html',        icon: 'utensils', label: 'Jídelní lístek' },
        { href: 'weekly-menu.html', icon: 'calendar', label: 'Týdenní menu' },
        { href: 'foods.html',       icon: 'list',     label: 'Seznamy jídel' },
      ]
    },
    restaurace: {
      title: 'Restaurace', items: [
        { href: 'orders.html',       icon: 'package',  label: 'Objednávky',  badge: '' },
        { href: 'reservations.html', icon: 'calendar', label: 'Rezervace',   badge: '' },
        { href: 'delivery.html',     icon: 'truck',    label: 'Rozvoz' },
      ]
    },
    restaurace_kuchyn: {
      title: 'Kuchyně', items: [
        { href: 'orders.html', icon: 'package', label: 'Objednávky', badge: '' },
      ]
    },
    rozvoz: {
      title: 'Rozvoz', items: [
        { href: 'delivery.html', icon: 'truck', label: 'Doručení', badge: '' },
      ]
    },
    obec: {
      title: 'Obec', items: [
        { href: 'events.html',  icon: 'calendar', label: 'Akce & Události' },
        { href: 'content.html', icon: 'edit',     label: 'Články' },
      ]
    },
    system: {
      title: 'Systém', items: [
        { href: 'users.html',    icon: 'users',    label: 'Uživatelé' },
        { href: '../index.html', icon: 'external', label: 'Zobrazit web', target: '_blank' },
      ]
    }
  };

  const ICONS = {
    utensils: `<svg class="admin-nav-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 2h2l.4 2M7 13h10l4-8H5.4"/></svg>`,
    list:     `<svg class="admin-nav-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>`,
    package:  `<svg class="admin-nav-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>`,
    calendar: `<svg class="admin-nav-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
    users:    `<svg class="admin-nav-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>`,
    edit:     `<svg class="admin-nav-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>`,
    external: `<svg class="admin-nav-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>`,
    home:     `<svg class="admin-nav-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
    truck:    `<svg class="admin-nav-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>`,
  };

  let currentUser = null;

  function getInitials(firstName, lastName) {
    return ((firstName || '')[0] || '') + ((lastName || '')[0] || '');
  }

  function getPrimaryRole(roles) {
    // Vrátí hlavní roli (priorita: super > vedouci > obsluha > kuchyn > rozvoz > obec > zakaznik)
    const priority = ['super', 'vedouci', 'obsluha', 'kuchyn', 'rozvoz', 'obec', 'zakaznik'];
    for (const r of priority) {
      if (roles.includes(r)) return r;
    }
    return roles[0] || 'zakaznik';
  }

  function buildSidebar(user) {
    const nav = document.querySelector('.admin-sidebar__nav');
    if (!nav) return;

    const primaryRole = getPrimaryRole(user.roles || []);
    const cfg = ROLE_CONFIG[primaryRole] || ROLE_CONFIG.zakaznik;
    const fullName = `${user.first_name || ''} ${user.last_name || ''}`.trim() || user.username;
    const initials = getInitials(user.first_name, user.last_name) || user.username?.slice(0, 2)?.toUpperCase() || '??';

    // Aktualizujeme footer uživatele
    const avatarEl = document.querySelector('.admin-user-mini__avatar');
    const nameEl = document.querySelector('.admin-user-mini__name');
    const roleEl = document.querySelector('.admin-user-mini__role');
    const roleLabelEl = document.getElementById('sidebar-role-label') || document.querySelector('.admin-sidebar__sub');

    if (avatarEl) avatarEl.textContent = initials;
    if (nameEl) nameEl.textContent = fullName;
    if (roleEl) roleEl.textContent = cfg.roleText;
    if (roleLabelEl) roleLabelEl.textContent = cfg.roleText;

    // Vybudujeme navigaci dle role
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    let html = `<div class="admin-sidebar__section-title">Přehled</div>
      <a href="index.html" class="admin-nav-item${currentPage === 'index.html' ? ' active' : ''}">${ICONS.home}<span class="admin-nav-item__label">Dashboard</span></a>`;

    // Sloučíme navigační sekce ze VŠECH rolí uživatele
    const navSections = new Set();
    (user.roles || []).forEach(role => {
      const rcfg = ROLE_CONFIG[role];
      if (rcfg) rcfg.nav.forEach(s => navSections.add(s));
    });

    navSections.forEach(sectionKey => {
      const sec = NAV_SECTIONS[sectionKey];
      if (!sec) return;
      html += `<div class="admin-sidebar__section-title">${sec.title}</div>`;
      sec.items.forEach(item => {
        const isActive = currentPage === item.href;
        const badge = item.badge ? `<span class="admin-nav-item__badge">${item.badge}</span>` : '';
        const target = item.target ? `target="${item.target}"` : '';
        html += `<a href="${item.href}" class="admin-nav-item${isActive ? ' active' : ''}" ${target}>${ICONS[item.icon] || ''}<span class="admin-nav-item__label">${item.label}</span>${badge}</a>`;
      });
    });

    nav.innerHTML = html;
  }

  async function checkAuth() {
    try {
      const res = await fetch('../api/auth/me.php');
      if (res.ok) {
        const data = await res.json();
        if (data.logged_in) {
          currentUser = data;
          buildSidebar(data);
          return data;
        }
      }
    } catch (e) {
      console.warn('Auth check failed:', e);
    }
    // Nepřihlášen — ponecháme defaultní sidebar (Super Admin demo)
    return null;
  }

  async function logout() {
    try {
      await fetch('../api/auth/logout.php');
    } catch {}
    window.location.href = '../index.html';
  }

  return { checkAuth, logout, getUser: () => currentUser, ROLE_CONFIG, NAV_SECTIONS };
})();

window.AdminAuth = AdminAuth;

// Auto-check auth na každé stránce
document.addEventListener('DOMContentLoaded', () => {
  AdminAuth.checkAuth();
});
