/* Obecní dům Holčovice – main.js */

/* --- Navbar scroll --- */
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 60);
}, { passive: true });

/* --- Mobile nav toggle --- */
const navToggle = document.getElementById('nav-toggle');
const navLinks  = document.getElementById('nav-links');
navToggle?.addEventListener('click', () => {
  const open = navLinks.classList.toggle('open');
  navToggle.classList.toggle('open', open);
  navToggle.setAttribute('aria-expanded', String(open));
  document.body.style.overflow = open ? 'hidden' : '';
});
navLinks?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
  navLinks.classList.remove('open');
  navToggle.classList.remove('open');
  navToggle.setAttribute('aria-expanded', 'false');
  document.body.style.overflow = '';
}));

/* --- Intersection Observer (AOS-lite) --- */
const aosItems = document.querySelectorAll('[data-aos]');
if (aosItems.length) {
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('aos-animate'); obs.unobserve(e.target); } });
  }, { threshold: 0.12 });
  aosItems.forEach(el => obs.observe(el));
}

/* --- Reservation form --- */
const form    = document.getElementById('reservation-form');
const success = document.getElementById('form-success');
if (form) {
  const dateInput = form.querySelector('#r-date');
  if (dateInput) dateInput.min = new Date().toISOString().split('T')[0];

  form.addEventListener('submit', e => {
    e.preventDefault();
    if (!form.checkValidity()) { form.reportValidity(); return; }
    form.hidden = true;
    success.hidden = false;
    success.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });
}

/* --- Active nav link on scroll --- */
const sections   = document.querySelectorAll('section[id]');
const navAnchors = document.querySelectorAll('.nav-links a[href^="#"]');
const sectionObs = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (!e.isIntersecting) return;
    navAnchors.forEach(a => {
      const active = a.getAttribute('href') === '#' + e.target.id;
      a.style.color = (active && !a.classList.contains('nav-cta')) ? 'var(--amber)' : '';
    });
  });
}, { rootMargin: '-40% 0px -55% 0px' });
sections.forEach(s => sectionObs.observe(s));
