<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav id="navbar" aria-label="Hlavní navigace">
  <div class="nav-inner container">
    <a href="<?php echo esc_url( home_url('/') ); ?>" class="nav-logo" aria-label="Obecní dům Holčovice – domů">
      <img src="<?php echo odh_img('Logo/OD1_Full_White_průhledné.png'); ?>" alt="Obecní dům Holčovice" class="logo-white">
      <img src="<?php echo odh_img('Logo/OD1_Full_průhledné.png'); ?>" alt="Obecní dům Holčovice" class="logo-dark">
    </a>

    <button class="nav-toggle" id="nav-toggle" aria-label="Otevřít menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    <ul class="nav-links" id="nav-links" role="list">
      <li><a href="#o-nas">O nás</a></li>
      <li><a href="#restaurace">Restaurace</a></li>
      <li><a href="#rodiny">Rodiny & psi</a></li>
      <li><a href="#oslavy">Oslavy</a></li>
      <li><a href="#kontakt">Kontakt</a></li>
      <li><a href="#kontakt" class="nav-cta">Rezervovat stůl</a></li>
    </ul>
  </div>
</nav>
