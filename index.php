<?php
  ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kissme | Kisskh Asian Dramas & Movies</title>
    <meta name="description" content="Watch drama online in high quality. Free download high quality drama. Various formats from 240p to 720p HD (or even 1080p). Feel Free To Watch!">
    <meta name="keywords" content="Watch high quality drama online, korean air, korean drama, kdrama, japanese drama, jdrama, english subtitle, watch series online, watch movies online, watch drama online, drama online, drama html5, drama streaming, drama mobile">
    <meta name="author" content="Kissme">
    <link rel="canonical" href="https://kiskh.com/">
    <meta property="og:title" content="Kisskh | Asian Dramas & Movies">
    <meta property="og:description" content="Watch drama online in high quality. Free download high quality drama. Various formats from 240p to 720p HD (or even 1080p). Feel Free To Watch!">
    <meta property="og:image" content="/images/icons/favicon.png">
    <meta property="og:url" content="https://kiskh.com/">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="km_KH" />
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Kissme | Asian Dramas & Movies">
    <meta name="twitter:description" content="Watch drama online in high quality. Free download high quality drama. Various formats from 240p to 720p HD (or even 1080p). Feel Free To Watch!">
    <meta name="twitter:image" content="/images/icons/favicon.png">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/icons/favicon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/icons/favicon.png">
    <link rel="apple-touch-icon" href="/images/icons/favicon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Kissme">
    <link id="faviconTag" rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href='css/assets/style_v1.3.css' rel='stylesheet'>
    <link href='css/assets/load_v2.css' rel='stylesheet'>
    <div id="page-loader" class="fixed inset-0 bg-[#212121] z-[9999] transition-opacity duration-500">
        <div class="splash-container">
            <div class="spinner"></div>
            <img src="/images/icons/loading.png" alt="Logo" class="splash-logo">
        </div>
        <p class="splash-text">Feel Free To Watch!</p>
        <p class="reload-text">Infinite loading ? click here <a onclick="window.location.reload()">Reload</a></p>
    </div>
<style>.series-card { will-change: transform;  transition: transform 0.2s ease; }</style>
   <script src="js/load_v2.js"></script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "Kissme",
      "url": "https://kiskh.com/",
      "potentialAction": {
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "https://kiskh.com/search?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      }
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Kissme",
        "url": "https://kiskh.com/",
        "logo": "https://kiskh.com/images/logo.png"
    }
    </script>
    <script src="js/analy.js"></script> 
</head>
<body>
    <div class="mobile-nav-panel" id="mobileNavPanel">
        <div class="nav-panel-header">
            <h1 class="logo" style="margin: 0; padding: 0; font-size: 1.6em; line-height: 0;">
                <a href="/" style="text-decoration: none; color: #e50914; display:flex; align-items:center;">
                   <img src="" alt="KISS KH" style="height: 28px; object-fit: contain; margin-right: 10px;">
                   <span style="display:none;"></span>
                </a>
            </h1>
            <button class="close-btn" id="closeNavBtn">&times;</button>
        </div>
        <nav class="mobile-nav-links">
            <a href="/"><i class="fa-solid fa-house"></i> Home</a>
            <a href="#"><i class="fa-solid fa-circle-question"></i> FAQ</a>
            <a href="#"><i class="fa-solid fa-film"></i> Request Drama</a>
            <a href="#"><i class="fa-solid fa-palette"></i> Theme</a>
            <a href="category.php?category=Lastest%20Update"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="#" class="js-open-search"><i class="fa-solid fa-magnifying-glass"></i> Search</a>
        </nav>
    </div>
    <div class="overlay" id="overlay"></div>
    <header class="main-header">
        <button class="mobile-menu-btn" aria-label="Open Navigation Menu">☰</button>
        
        <h1 class="logo" style="margin: 0; padding: 0; font-size: 1.8em; line-height: 0;">
             <a href="/" style="text-decoration: none; color: #e50914; display:flex; align-items:center;">
                <img src="" alt="KISS KH Logo - Asian Dramas & Movies" style="height: 28px; max-width: 150px; object-fit: contain;">
                <span style="display: none;">Kissme - Asian Dramas & Movies</span>
            </a>
        </h1>

        <nav class="main-nav pc-only">
            <a href="/"><i class="fa-solid fa-house"></i> Home</a>
            <a href="#"><i class="fa-solid fa-circle-question"></i> FAQ</a>
            <a href="#"><i class="fa-solid fa-film"></i> Request Drama</a>
            <div class="theme-menu-container">
                <a href="#" id="themeMenuBtn"><i class="fa-solid fa-palette"></i> Theme</a>
                <div class="theme-dropdown" id="themeDropdown">
                    <div class="theme-option" data-theme="deep-purple-amber"><span class="radio-circle"></span><span class="theme-name">Deep Purple & Amber</span><i class="fas fa-cog color-amber"></i></div>
                    <div class="theme-option" data-theme="indigo-pink"><span class="radio-circle"></span><span class="theme-name">Indigo & Pink</span><i class="fas fa-cog color-pink"></i></div>
                    <div class="theme-option" data-theme="pink-blue-grey"><span class="radio-circle"></span><span class="theme-name">Pink & Blue-grey</span><i class="fas fa-cog color-blue-grey"></i></div>
                    <div class="theme-option selected" data-theme="purple-green"><span class="radio-circle"></span><span class="theme-name">Purple & Green</span><i class="fas fa-cog color-green"></i></div>
                </div>
            </div>
            <a href="category.php?category=Lastest%20Update"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="#" class="js-open-search"><i class="fa-solid fa-magnifying-glass"></i> Search</a>
        </nav>
        <div class="header-right-panel mobile-only">
            <a href="#" class="search-icon js-open-search" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></a>
            <a href="#" class="profile-icon-wrapper"><div class="profile-icon"></div></a>
        </div>
    </header>
    <div id="sliderContainer" class="slider-container">
        <div class="slider-nav">
            <button id="prevSlide" class="prev-btn" aria-label="Previous Slide">‹</button>
            <button id="nextSlide" class="next-btn" aria-label="Next Slide">›</button>
        </div>
        <div id="dotsContainer" class="dots-container"></div>
    </div>

    <main class="container" id="contentContainer">
         <p class="no-stories">Loading...</p>
         <noscript>
            <section style="padding: 0 40px;">
                <h2>Welcome to Kisskh - Asian Dramas & Movies</h2>
                <p>Watch drama online in high quality. Free download high quality drama. Various formats from 240p to 720p HD (or even 1080p). Feel Free To Watch!</p>
                <h3>Popular</h3>
                <ul>
                    <li>Kisskh - Asian Dramas & Movies</li>
                    <li>Kisskh - Asian Dramas & Movies</li>
                    <li>Kisskh - Asian Dramas & Movies</li>
                </ul>
            </section>
         </noscript>
    </main>

    <div id="searchOverlay">
        <div class="search-header">
            <button id="searchBackBtn" class="search-back-btn">‹</button>
            <input type="text" id="searchInput" class="search-input" placeholder="Search TVSeries, Movie, Anime...">
        </div>
        <div class="filter-bar">
            <button class="filter-btn active">All</button>
            <button class="filter-btn">TVSeries</button>
            <button class="filter-btn">Movie</button>
            <button class="filter-btn">Anime</button>
            <button class="filter-btn">Hollywood</button>
        </div>
        <div class="search-content">
            <h2 class="search-category-title">Popular Search</h2>
            <div id="popularSearchGrid" class="search-grid">
            </div>
        </div>
    </div>
<script src="js/scr_v4.js"></script>  
</body>
</html>
<?php
  $html = ob_get_clean();
  $html_one_line = str_replace(array("\r\n", "\r", "\n"), "", $html);
  echo $html_one_line;
?>