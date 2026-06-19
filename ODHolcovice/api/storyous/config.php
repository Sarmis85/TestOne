<?php
/**
 * Konfigurace StoryOus API integrace
 *
 * Po získání API klíčů od StoryOus vyplňte client_id a client_secret.
 * merchantId a placeId zjistíte po prvním přihlášení přes /api/storyous/test.php
 */

define('STORYOUS_CLIENT_ID', '');
define('STORYOUS_CLIENT_SECRET', '');
define('STORYOUS_MERCHANT_ID', '');
define('STORYOUS_PLACE_ID', '');

define('STORYOUS_LOGIN_URL', 'https://login.storyous.com/api/auth/authorize');
define('STORYOUS_API_URL', 'https://api.storyous.com');

define('STORYOUS_TOKEN_FILE', __DIR__ . '/.token_cache.json');
