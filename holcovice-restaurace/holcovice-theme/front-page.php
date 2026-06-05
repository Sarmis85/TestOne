<?php get_header(); ?>

<!-- ═══════════════════════════════════════
     HERO
════════════════════════════════════════ -->
<section id="hero" aria-label="Obecní dům Holčovice – úvod"
  style="background-image: url('<?php echo odh_img('Cover2.png'); ?>')">
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <img src="<?php echo odh_img('Logo/OD1_Full_White_průhledné.png'); ?>"
         alt="Obecní dům Holčovice – Pivnice a Restaurace"
         class="hero-logo">
    <p class="hero-tagline">Na kraji Jeseníků – poctivá kuchyně, otevřené dveře</p>
    <div class="hero-badges" aria-label="Klíčové vlastnosti">
      <span class="badge">🍽️ Restaurace</span>
      <span class="badge">🍺 Pivnice</span>
      <span class="badge">🌳 Terasa</span>
      <span class="badge badge-dog">🐕 Psi vítáni</span>
      <span class="badge">👨‍👩‍👧 Dětské hřiště</span>
    </div>
    <div class="hero-cta">
      <a href="#restaurace" class="btn btn-amber">Naše nabídka</a>
      <a href="#kontakt" class="btn btn-outline">Rezervovat stůl</a>
    </div>
  </div>
  <div class="hero-scroll" aria-hidden="true">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
  </div>
  <!-- Wave bottom -->
  <div class="wave-bottom" aria-hidden="true">
    <svg viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0,45 C200,90 400,0 600,45 C800,90 1000,0 1200,45 C1300,67 1380,30 1440,45 L1440,90 L0,90 Z" fill="#faf7f2"/>
    </svg>
  </div>
</section>


<!-- ═══════════════════════════════════════
     PROČ K NÁM – 4 klíčová USP
════════════════════════════════════════ -->
<section id="proc-k-nam" class="section-cream" aria-label="Proč k nám přijít">
  <div class="container">
    <div class="section-label">Vítejte</div>
    <h2 class="section-title">Místo, kde se každý cítí vítán</h2>
    <p class="section-subtitle">Jsme obecním zařízením uprostřed horské přírody. Přijďte na jídlo, odpočinek nebo oslavu – rádi vás přivítáme.</p>

    <div class="usp-grid">
      <div class="usp-card">
        <div class="usp-icon">👨‍👩‍👧‍👦</div>
        <h3>Rodiny s dětmi</h3>
        <p>Nové dětské hřiště přímo u restaurace. Děti si hrají, rodiče si odpočinou – perfektní kombinace při výletu do Jeseníků.</p>
      </div>
      <div class="usp-card">
        <div class="usp-icon">🐕</div>
        <h3>Čtyřnozí vítáni</h3>
        <p>Váš pes je u nás stejně vítaný jako vy. Terasa i zahrada jsou přátelské k mazlíčkům – nemusíte ho nechat v autě.</p>
      </div>
      <div class="usp-card">
        <div class="usp-icon">🚴</div>
        <h3>Zastávka pro turisty</h3>
        <p>Stojany na kola přímo u terasy. Ideální zastávka na cyklovýletech po Jeseníkách – doplňte energii a pokračujte dál.</p>
      </div>
      <div class="usp-card">
        <div class="usp-icon">🎉</div>
        <h3>Oslavy & firemní akce</h3>
        <p>Sál pro 80 hostů, pódium, projekce, ozvučení. Navrhneme program na míru – od narozenin po svatbu nebo firemní večírek.</p>
      </div>
    </div>
  </div>
  <!-- Wave to next section -->
  <div class="wave-bottom wave-green" aria-hidden="true">
    <svg viewBox="0 0 1440 70" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0,35 C360,0 720,70 1080,35 C1260,18 1380,50 1440,35 L1440,70 L0,70 Z" fill="#2a4d20"/>
    </svg>
  </div>
</section>


<!-- ═══════════════════════════════════════
     O NÁS / LOKACE
════════════════════════════════════════ -->
<section id="o-nas" class="section-dark" aria-label="O nás a naše lokace">
  <div class="container about-grid">
    <div class="about-photo" data-aos="fade-right">
      <img src="<?php echo odh_img('Image.png'); ?>"
           alt="Obecní dům Holčovice – exteriér s terasou a lesem v pozadí"
           loading="lazy">
      <div class="about-photo-badge">
        <strong>Jeseníky</strong>
        <span>na dosah ruky</span>
      </div>
    </div>
    <div class="about-text" data-aos="fade-left">
      <div class="section-label label-light">O nás</div>
      <h2>Na kraji lesa,<br><em>uprostřed pohody</em></h2>
      <p>Obecní dům Holčovice stojí v srdci malebné horské obce obklopené lesy Jeseníků. Jsme obecním zařízením – místo, kde se schází celá obec, kam přicházejí turisté po túře a kde se slaví to nejdůležitější v životě.</p>
      <p>Vaříme poctivou českou kuchyni z čerstvých surovin. V pivnici si dáte studené pivo, na terase si vychutnáte horský vzduch a děti si zatím vyřádí na hřišti. A váš pes? Ten leží u nohou a má se skvěle.</p>
      <div class="about-stats">
        <div class="stat">
          <strong>80</strong><span>míst uvnitř</span>
        </div>
        <div class="stat">
          <strong>+</strong><span>letní terasa</span>
        </div>
        <div class="stat">
          <strong>🐕</strong><span>psí vítáni</span>
        </div>
        <div class="stat">
          <strong>🚴</strong><span>bike stojany</span>
        </div>
      </div>
      <a href="#kontakt" class="btn btn-amber">Naplánujte návštěvu</a>
    </div>
  </div>
  <!-- Wave to white -->
  <div class="wave-bottom wave-cream" aria-hidden="true">
    <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0,40 C240,80 480,0 720,40 C960,80 1200,0 1440,40 L1440,80 L0,80 Z" fill="#faf7f2"/>
    </svg>
  </div>
</section>


<!-- ═══════════════════════════════════════
     RESTAURACE & PIVNICE
════════════════════════════════════════ -->
<section id="restaurace" class="section-cream" aria-label="Restaurace a pivnice">
  <div class="container">
    <div class="section-label">Jídlo & pití</div>
    <h2 class="section-title">Restaurace & pivnice</h2>
    <p class="section-subtitle">Každý den čerstvě vařené polední menu, stálý jídelní lístek a pizza. K tomu točené pivo Pilsner Urquell a domácí atmosféra.</p>

    <div class="restaurant-grid">
      <div class="restaurant-photo restaurant-photo--main">
        <img src="<?php echo odh_img('20230301_101725.jpg'); ?>"
             alt="Interiér restaurace – světlé prostory"
             loading="lazy">
        <div class="photo-label">Restaurace</div>
      </div>
      <div class="restaurant-photo">
        <img src="<?php echo odh_img('255414891_146723234346249_3079295703175225750_n.jpg'); ?>"
             alt="Prostřený stůl s květinami"
             loading="lazy">
        <div class="photo-label">Pohostinnost</div>
      </div>
      <div class="restaurant-photo">
        <img src="<?php echo odh_img('20230301_102124.jpg'); ?>"
             alt="Pivnice s nástěnnou grafikou piva"
             loading="lazy">
        <div class="photo-label">Pivnice</div>
      </div>
    </div>

    <div class="restaurant-features">
      <div class="feat">
        <span class="feat-icon">🥘</span>
        <strong>Polední menu</strong>
        <p>Pondělí–pátek od 149 Kč. Polévka + hlavní jídlo z čerstvých surovin.</p>
      </div>
      <div class="feat">
        <span class="feat-icon">🍕</span>
        <strong>Pizza & stálý lístek</strong>
        <p>Celý týden – svíčková, guláš, smažák, pizza nebo dnešní specialita.</p>
      </div>
      <div class="feat">
        <span class="feat-icon">🍺</span>
        <strong>Točené pivo</strong>
        <p>Pilsner Urquell 12° na čepu, Radegast 10° a nealkoholické varianty.</p>
      </div>
      <div class="feat">
        <span class="feat-icon">☕</span>
        <strong>Víkendové menu</strong>
        <p>Sobota–neděle: grilované speciality, pečená kachna a sezónní menu.</p>
      </div>
    </div>

    <div class="menu-cta">
      <a href="#kontakt" class="btn btn-green">Podívejte se na menu</a>
      <span class="menu-note">Aktuální menu každé pondělí na Facebooku</span>
    </div>
  </div>
  <!-- Diagonal clip to dark -->
  <div class="clip-to-dark" aria-hidden="true"></div>
</section>


<!-- ═══════════════════════════════════════
     RODINY & PSI
════════════════════════════════════════ -->
<section id="rodiny" class="section-dark" aria-label="Rodiny s dětmi a psi vítáni">
  <div class="container family-grid">
    <div class="family-text" data-aos="fade-right">
      <div class="section-label label-light">Přátelské místo</div>
      <h2>Výlet s dětmi?<br><em>S psem?</em><br>Tady jste vítáni!</h2>
      <p>Víme, jak důležité je mít klid na oběd – i s celou rodinou. Proto máme dětské hřiště přímo u restaurace: tobogan, houpačky, pískoviště, prolézačka. Děti si hrají v bezpečné vzdálenosti, rodiče si dají oběd v klidu.</p>
      <p>A protože víme, že pes je člen rodiny – terasa i zahrada jsou plně přístupné pro čtyřnohé hosty. Voda pro psa samozřejmostí.</p>
      <ul class="family-list">
        <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Dětské hřiště zdarma</li>
        <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Psi vítáni na terase i v zahradě</li>
        <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Dětské porce a dětský lístek</li>
        <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Stojany na kola a koloběžky</li>
        <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Parkování zdarma</li>
      </ul>
    </div>
    <div class="family-photos" data-aos="fade-left">
      <div class="family-photo-stack">
        <img src="<?php echo odh_img('20230302_151038.jpg'); ?>"
             alt="Dětské hřiště – tobogan, houpačky a výhled do hor"
             loading="lazy"
             class="fp-main">
        <img src="<?php echo odh_img('20230302_151109.jpg'); ?>"
             alt="Pískoviště a hřiště přímo u budovy restaurace"
             loading="lazy"
             class="fp-secondary">
        <div class="family-dog-badge" aria-label="Psi vítáni">
          <span>🐕</span>
          <strong>Psi<br>vítáni!</strong>
        </div>
      </div>
    </div>
  </div>
  <!-- Wave to cream -->
  <div class="wave-bottom wave-cream" aria-hidden="true">
    <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0,60 C360,20 720,80 1080,40 C1260,20 1380,60 1440,45 L1440,80 L0,80 Z" fill="#faf7f2"/>
    </svg>
  </div>
</section>


<!-- ═══════════════════════════════════════
     OSLAVY & PRONÁJEM
════════════════════════════════════════ -->
<section id="oslavy" class="section-cream" aria-label="Prostory pro oslavy a akce">
  <div class="container">
    <div class="section-label">Pro skupiny</div>
    <h2 class="section-title">Oslavy, svatby & firemní akce</h2>
    <p class="section-subtitle">Disponujeme kompletně vybaveným sálem s tanečním parketem, pódiem a profesionálním ozvučením.</p>
  </div>

  <div class="events-hero" style="background-image: url('<?php echo odh_img('20230301_101930.jpg'); ?>')">
    <div class="events-hero-overlay"></div>
    <div class="container events-hero-content">
      <div class="events-capacity">
        <div class="cap-number">80</div>
        <div class="cap-label">míst uvnitř</div>
      </div>
      <div class="events-equip">
        <div class="equip-item"><span>🎭</span> Pódium pro kapelu</div>
        <div class="equip-item"><span>🎬</span> Velkoplošná projekce</div>
        <div class="equip-item"><span>🔊</span> Profesionální ozvučení</div>
        <div class="equip-item"><span>💃</span> Taneční parket</div>
        <div class="equip-item"><span>🌿</span> Letní terasa</div>
        <div class="equip-item"><span>🎱</span> Fotbálek & šipky</div>
      </div>
    </div>
  </div>

  <div class="container events-types">
    <div class="event-type">
      <div class="et-icon">💒</div>
      <h3>Svatby</h3>
      <p>Navrhneme raut, catering i dekorace. Váš velký den si zaslouží bezchybné zázemí.</p>
    </div>
    <div class="event-type">
      <div class="et-icon">🎂</div>
      <h3>Rodinné oslavy</h3>
      <p>Narozeniny, výročí, křtiny. Klidně i s dětmi a psy – místo je připravené.</p>
    </div>
    <div class="event-type">
      <div class="et-icon">💼</div>
      <h3>Firemní akce</h3>
      <p>Teambuilding, školení, vánoční večírek. Kapacita a vybavení na profesionální úrovni.</p>
    </div>
    <div class="event-type">
      <div class="et-icon">🎶</div>
      <h3>Kulturní akce</h3>
      <p>Plesy, koncerty, divadla. Pódium a ozvučení jsou k dispozici pro každou příležitost.</p>
    </div>
  </div>

  <div class="events-cta container">
    <a href="#kontakt" class="btn btn-green btn-large">Poptejte termín</a>
    <span>Kontaktujte Mariku Lioliasovou – poradíme s přípravou</span>
  </div>
  <!-- Wave to dark -->
  <div class="wave-bottom wave-green" aria-hidden="true">
    <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0,40 C480,80 960,0 1440,40 L1440,80 L0,80 Z" fill="#1c2e14"/>
    </svg>
  </div>
</section>


<!-- ═══════════════════════════════════════
     KONTAKT & REZERVACE
════════════════════════════════════════ -->
<section id="kontakt" class="section-darkest" aria-label="Kontakt a rezervace">
  <div class="container contact-grid">
    <!-- Info column -->
    <div class="contact-info">
      <div class="section-label label-light">Najděte nás</div>
      <h2>Kontakt &<br>rezervace</h2>

      <div class="contact-items">
        <div class="ci">
          <div class="ci-icon">📍</div>
          <div>
            <strong>Adresa</strong>
            <p>Holčovice 241<br>793 71 Holčovice<br><small>okres Bruntál, Moravskoslezský kraj</small></p>
          </div>
        </div>
        <div class="ci">
          <div class="ci-icon">📞</div>
          <div>
            <strong>Telefon</strong>
            <p><a href="tel:+420732732683">+420 732 732 683</a></p>
          </div>
        </div>
        <div class="ci">
          <div class="ci-icon">✉️</div>
          <div>
            <strong>E-mail</strong>
            <p><a href="mailto:obecnidum@obecholcovice.cz">obecnidum@obecholcovice.cz</a></p>
          </div>
        </div>
      </div>

      <div class="hours-block">
        <h3>Otevírací doba</h3>
        <table class="hours-tbl">
          <tr><td>Po – Čt</td><td>11:00 – 21:00</td></tr>
          <tr><td>Pá – So</td><td>11:00 – 22:00</td></tr>
          <tr><td>Neděle</td><td>11:00 – 20:00</td></tr>
        </table>
      </div>

      <div class="contact-map">
        <iframe
          src="https://maps.google.com/maps?q=Hol%C4%8Dovice+241,+793+71+Hol%C4%8Dovice&output=embed&z=14"
          title="Mapa – Obecní dům Holčovice"
          loading="lazy">
        </iframe>
      </div>
    </div>

    <!-- Reservation form -->
    <div class="contact-form-wrap">
      <h3>Rezervovat stůl</h3>
      <form id="reservation-form" class="res-form" novalidate>
        <div class="form-row">
          <div class="fg">
            <label for="r-name">Jméno a příjmení *</label>
            <input type="text" id="r-name" name="name" placeholder="Jana Nováková" required autocomplete="name">
          </div>
          <div class="fg">
            <label for="r-phone">Telefon *</label>
            <input type="tel" id="r-phone" name="phone" placeholder="+420 …" required autocomplete="tel">
          </div>
        </div>
        <div class="form-row">
          <div class="fg">
            <label for="r-date">Datum *</label>
            <input type="date" id="r-date" name="date" required>
          </div>
          <div class="fg">
            <label for="r-time">Čas *</label>
            <select id="r-time" name="time" required>
              <option value="">-- vyberte --</option>
              <option>11:00</option><option>12:00</option><option>13:00</option>
              <option>14:00</option><option>17:00</option><option>18:00</option>
              <option>19:00</option><option>20:00</option>
            </select>
          </div>
        </div>
        <div class="fg">
          <label for="r-guests">Počet osob *</label>
          <select id="r-guests" name="guests" required>
            <option value="">-- vyberte --</option>
            <option>1–2</option><option>3–4</option><option>5–6</option>
            <option>7–10</option><option>11 a více (poptávka)</option>
          </select>
        </div>
        <div class="fg">
          <label for="r-dog">Přijedete se psem?</label>
          <select id="r-dog" name="dog">
            <option value="ne">Ne</option>
            <option value="ano">Ano – přijede náš pes 🐕</option>
          </select>
        </div>
        <div class="fg">
          <label for="r-note">Poznámka</label>
          <textarea id="r-note" name="note" placeholder="Oslava narozenin, alergie, dětská židlička…"></textarea>
        </div>
        <button type="submit" class="btn btn-amber btn-large btn-full">Odeslat rezervaci</button>
        <p class="form-note">* Povinná pole. Potvrdíme do 24 h telefonicky nebo e-mailem.</p>
      </form>
      <div id="form-success" role="alert" hidden>
        <p>✅ <strong>Díky za rezervaci!</strong> Ozve se vám Marika Lioliasová do 24 hodin.</p>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
