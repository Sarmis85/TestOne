/* Obecní dům Holčovice – main.js */

/* --- Navbar scroll effect --- */
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 60);
}, { passive: true });

/* --- Mobile nav toggle --- */
const navToggle = document.getElementById('nav-toggle');
const navLinks  = document.getElementById('nav-links');

navToggle.addEventListener('click', () => {
  const open = navLinks.classList.toggle('open');
  navToggle.classList.toggle('open', open);
  navToggle.setAttribute('aria-expanded', open);
});

navLinks.querySelectorAll('a').forEach(link => {
  link.addEventListener('click', () => {
    navLinks.classList.remove('open');
    navToggle.classList.remove('open');
    navToggle.setAttribute('aria-expanded', 'false');
  });
});

/* --- Menu tabs --- */
document.querySelectorAll('.menu-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    const key = tab.dataset.tab;

    document.querySelectorAll('.menu-tab').forEach(t => {
      t.classList.remove('active');
      t.setAttribute('aria-selected', 'false');
    });
    document.querySelectorAll('.menu-panel').forEach(p => p.classList.remove('active'));

    tab.classList.add('active');
    tab.setAttribute('aria-selected', 'true');
    document.getElementById('tab-' + key).classList.add('active');
  });
});

/* --- Gallery filter --- */
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const filter = btn.dataset.filter;

    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.gallery-item').forEach(item => {
      const show = filter === 'all' || item.dataset.cat === filter;
      item.style.display = show ? '' : 'none';
    });
  });
});

/* --- Reservation form (demo submit) --- */
const form    = document.getElementById('reservation-form');
const success = document.getElementById('form-success');

if (form) {
  const dateInput = form.querySelector('#date');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
  }

  form.addEventListener('submit', e => {
    e.preventDefault();
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    form.style.display = 'none';
    success.style.display = 'block';
    success.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });
}

/* --- Cookie banner --- */
if (!localStorage.getItem('cookies-decided')) {
  setTimeout(() => {
    document.getElementById('cookie-banner').classList.add('show');
  }, 1800);
}

document.getElementById('cookie-accept').addEventListener('click', () => {
  localStorage.setItem('cookies-decided', 'accepted');
  document.getElementById('cookie-banner').classList.remove('show');
});

document.getElementById('cookie-reject').addEventListener('click', () => {
  localStorage.setItem('cookies-decided', 'rejected');
  document.getElementById('cookie-banner').classList.remove('show');
});

/* --- Smooth active nav highlight on scroll --- */
const sections = document.querySelectorAll('section[id], div[id="info-bar"]');
const navAnchors = document.querySelectorAll('.nav-links a[href^="#"]');

const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      navAnchors.forEach(a => {
        a.style.color = a.getAttribute('href') === '#' + entry.target.id
          ? 'var(--clr-amber)'
          : '';
      });
    }
  });
}, { rootMargin: '-40% 0px -55% 0px' });

sections.forEach(s => observer.observe(s));
