<?php
  ob_start();
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kissme | Asian Dramas & Movies</title>
    <!-- MODIFIED: Add an ID for easy targeting by JS -->
    <link id="faviconTag" rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href='css/assets/gory_v1.2.css' rel='stylesheet'>
    <style>.series-card { will-change: transform;  transition: transform 0.2s ease; }</style>
    <script src="js/analyct.js"></script> 
</head>
<body>

    <div class="mobile-nav-panel" id="mobileNavPanel">
        <div class="nav-panel-header">
            <a href="/" class="logo"></a>
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
        <a href="/" class="logo"></a>
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

    <div class="container">
        <div class="filters-container">
             <div class="filter-row" data-filter-group="type">
                <button class="filter-btn active" data-filter-value="All">All</button>
                <button class="filter-btn" data-filter-value="TV Series">TV Series</button>
                <button class="filter-btn" data-filter-value="Movie">Movie</button>
                <button class="filter-btn" data-filter-value="Anime">Anime</button>
                <button class="filter-btn" data-filter-value="Hollywood">Hollywood</button>
            </div>
            <div class="filter-row" data-filter-group="subtitle">
                <button class="filter-btn active" data-filter-value="All">All Subtitles</button>
                <button class="filter-btn" data-filter-value="Khmer">Khmer</button>
                <button class="filter-btn" data-filter-value="English">English</button>
                <button class="filter-btn" data-filter-value="Chinese">Chinese</button>
            </div>
            <div class="filter-row" data-filter-group="region" id="region-filter-container">
                <button class="filter-btn active" data-filter-value="All">All Regions</button>
            </div>
             <div class="filter-row" data-filter-group="status">
                <button class="filter-btn active" data-filter-value="All">All</button>
                <button class="filter-btn" data-filter-value="Ongoing">Ongoing</button>
                <button class="filter-btn" data-filter-value="Completed">Completed</button>
                <button class="filter-btn" data-filter-value="Upcoming">Upcoming</button>
            </div>
            <div class="filter-row" style="border-top: 1px solid #2a2a2a; padding-top: 15px;" data-filter-group="sort">
                 <button class="filter-btn active" data-filter-value="Popular">Popular</button>
                 <button class="filter-btn" data-filter-value="Last Update">Last Update</button>
                 <button class="filter-btn" data-filter-value="Release Date">Release Date</button>
            </div>
        </div>

        <div class="series-grid" id="series-grid">
            <p class="message-text" id="message-text">Loading...</p>
        </div>
    </div>

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
                 <!-- Popular search items will be injected here by JS -->
            </div>
        </div>
    </div>
<script src="js/category_v5.js"></script>  
</body>
</html>
<?php
  $html = ob_get_clean();
  $html_one_line = str_replace(array("\r\n", "\r", "\n"), "", $html);
  echo $html_one_line;
?>