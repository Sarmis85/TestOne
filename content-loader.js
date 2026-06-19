(function () {
  /* CSS selector → dot-path in content.json */
  var MAP = [
    /* Hero */
    ['.hero-tagline', 'hero.tagline'],
    ['.hero-text',    'hero.text'],

    /* Program — 5 karet */
    ['.priorities-grid .priority-card:nth-child(1) h3', 'program.cards.0.title'],
    ['.priorities-grid .priority-card:nth-child(1) p',  'program.cards.0.text'],
    ['.priorities-grid .priority-card:nth-child(2) h3', 'program.cards.1.title'],
    ['.priorities-grid .priority-card:nth-child(2) p',  'program.cards.1.text'],
    ['.priorities-grid .priority-card:nth-child(3) h3', 'program.cards.2.title'],
    ['.priorities-grid .priority-card:nth-child(3) p',  'program.cards.2.text'],
    ['.priorities-grid .priority-card:nth-child(4) h3', 'program.cards.3.title'],
    ['.priorities-grid .priority-card:nth-child(4) p',  'program.cards.3.text'],
    ['.priorities-grid .priority-card:nth-child(5) h3', 'program.cards.4.title'],
    ['.priorities-grid .priority-card:nth-child(5) p',  'program.cards.4.text'],

    /* Výsledky — průběžné (levý sloupec) */
    ['.vysledky-cols > div:nth-child(1) .timeline-item:nth-child(1) h4', 'vysledky.ongoing.0.title'],
    ['.vysledky-cols > div:nth-child(1) .timeline-item:nth-child(1) p',  'vysledky.ongoing.0.text'],
    ['.vysledky-cols > div:nth-child(1) .timeline-item:nth-child(2) h4', 'vysledky.ongoing.1.title'],
    ['.vysledky-cols > div:nth-child(1) .timeline-item:nth-child(2) p',  'vysledky.ongoing.1.text'],
    ['.vysledky-cols > div:nth-child(1) .timeline-item:nth-child(3) h4', 'vysledky.ongoing.2.title'],
    ['.vysledky-cols > div:nth-child(1) .timeline-item:nth-child(3) p',  'vysledky.ongoing.2.text'],

    /* Výsledky — datované (pravý sloupec) */
    ['.vysledky-cols > div:nth-child(2) .timeline-item:nth-child(1) .timeline-date', 'vysledky.dated.0.date'],
    ['.vysledky-cols > div:nth-child(2) .timeline-item:nth-child(1) h4',             'vysledky.dated.0.title'],
    ['.vysledky-cols > div:nth-child(2) .timeline-item:nth-child(1) p',              'vysledky.dated.0.text'],
    ['.vysledky-cols > div:nth-child(2) .timeline-item:nth-child(2) .timeline-date', 'vysledky.dated.1.date'],
    ['.vysledky-cols > div:nth-child(2) .timeline-item:nth-child(2) h4',             'vysledky.dated.1.title'],
    ['.vysledky-cols > div:nth-child(2) .timeline-item:nth-child(2) p',              'vysledky.dated.1.text'],
    ['.vysledky-cols > div:nth-child(2) .timeline-item:nth-child(3) .timeline-date', 'vysledky.dated.2.date'],
    ['.vysledky-cols > div:nth-child(2) .timeline-item:nth-child(3) h4',             'vysledky.dated.2.title'],
    ['.vysledky-cols > div:nth-child(2) .timeline-item:nth-child(3) p',              'vysledky.dated.2.text'],

    /* Kandidát č. 1 — lídr */
    ['.leader-badge',                    'kandidati.c1.badge'],
    ['.leader-name',                     'kandidati.c1.name'],
    ['.leader-role',                     'kandidati.c1.role'],
    ['.leader-bio',                      'kandidati.c1.quote'],
    ['.leader-desc-cols p:nth-child(1)', 'kandidati.c1.bio1'],
    ['.leader-desc-cols p:nth-child(2)', 'kandidati.c1.bio2'],

    /* Kandidáti 2–10 (candidates-grid) */
    ['.candidates-grid > :nth-child(1) h3',        'kandidati.c2.name'],
    ['.candidates-grid > :nth-child(1) .cand-role', 'kandidati.c2.role'],
    ['.candidates-grid > :nth-child(1) .cand-bio',  'kandidati.c2.bio'],
    ['.candidates-grid > :nth-child(2) h3',        'kandidati.c3.name'],
    ['.candidates-grid > :nth-child(2) .cand-role', 'kandidati.c3.role'],
    ['.candidates-grid > :nth-child(2) .cand-bio',  'kandidati.c3.bio'],
    ['.candidates-grid > :nth-child(3) h3',        'kandidati.c4.name'],
    ['.candidates-grid > :nth-child(3) .cand-role', 'kandidati.c4.role'],
    ['.candidates-grid > :nth-child(3) .cand-bio',  'kandidati.c4.bio'],
    ['.candidates-grid > :nth-child(4) h3',        'kandidati.c5.name'],
    ['.candidates-grid > :nth-child(4) .cand-role', 'kandidati.c5.role'],
    ['.candidates-grid > :nth-child(4) .cand-bio',  'kandidati.c5.bio'],
    ['.candidates-grid > :nth-child(5) h3',        'kandidati.c6.name'],
    ['.candidates-grid > :nth-child(5) .cand-role', 'kandidati.c6.role'],
    ['.candidates-grid > :nth-child(5) .cand-bio',  'kandidati.c6.bio'],
    ['.candidates-grid > :nth-child(6) h3',        'kandidati.c7.name'],
    ['.candidates-grid > :nth-child(6) .cand-role', 'kandidati.c7.role'],
    ['.candidates-grid > :nth-child(6) .cand-bio',  'kandidati.c7.bio'],
    ['.candidates-grid > :nth-child(7) h3',        'kandidati.c8.name'],
    ['.candidates-grid > :nth-child(7) .cand-role', 'kandidati.c8.role'],
    ['.candidates-grid > :nth-child(7) .cand-bio',  'kandidati.c8.bio'],
    ['.candidates-grid > :nth-child(8) h3',        'kandidati.c9.name'],
    ['.candidates-grid > :nth-child(8) .cand-role', 'kandidati.c9.role'],
    ['.candidates-grid > :nth-child(8) .cand-bio',  'kandidati.c9.bio'],
    ['.candidates-grid > :nth-child(9) h3',        'kandidati.c10.name'],
    ['.candidates-grid > :nth-child(9) .cand-role', 'kandidati.c10.role'],
    ['.candidates-grid > :nth-child(9) .cand-bio',  'kandidati.c10.bio'],

    /* Výzva k volbám */
    ['.volby-sub',        'volby.sub'],
    ['.volby-badge-text', 'volby.badge'],

    /* Program page — hero + sekce */
    ['.page-hero-sub',      'program_page.hero_sub'],
    ['#povodni .prog-lead', 'program_page.povodni.lead'],
    ['#sluzby .prog-lead',  'program_page.sluzby.lead'],
    ['#zivot .prog-lead',   'program_page.zivot.lead'],
    ['#turistika .prog-lead', 'program_page.turistika.lead'],
    ['#urad .prog-lead',    'program_page.urad.lead']
  ];

  function getPath(obj, path) {
    return path.split('.').reduce(function (o, k) {
      return (o !== null && o !== undefined && o[k] !== undefined) ? o[k] : null;
    }, obj);
  }

  fetch('./content.json?t=' + Date.now())
    .then(function (r) { return r.json(); })
    .then(function (data) {
      MAP.forEach(function (entry) {
        var sel = entry[0], path = entry[1];
        var el = document.querySelector(sel);
        if (!el) return;
        var val = getPath(data, path);
        if (val !== null && val !== undefined && val !== '') {
          el.innerHTML = val;
        }
      });
    })
    .catch(function () { /* tichá chyba — content.json neexistuje nebo je poškozený */ });
})();
