function formatTime(totalSeconds) {
            if (isNaN(totalSeconds) || totalSeconds < 0) return "00:00";
            const date = new Date(null);
            date.setSeconds(totalSeconds);
            const isoString = date.toISOString();
            return totalSeconds < 3600 ? isoString.substr(14, 5) : isoString.substr(11, 8);
        }

        async function addContentSchema(seriesList) {
            if (!seriesList || seriesList.length === 0) return;
            const existingSchema = document.getElementById('contentSchema');
            if(existingSchema) existingSchema.remove();

            const schema = {
                "@context": "https://schema.org",
                "@type": "ItemList",
                "name": "Kisskh - Asian Dramas & Movies",
                "itemListElement": seriesList.slice(0, 15).map((series, index) => ({
                    "@type": "ListItem",
                    "position": index + 1,
                    "item": {
                        "@type": series.category === 'Movie' ? "Movie" : "TVSeries",
                        "url": `https://kiskh.com/player.php?seriesId=${series.id}`,
                        "name": series.title,
                        "image": series.image_url,
                        "description": `Kisskh '${series.title}' Asian Dramas & Movies`,
                        "episode": {
                           "@type": "Episode",
                           "episodeNumber": series.episode_count
                        }
                    }
                }))
            };

            const scriptTag = document.createElement('script');
            scriptTag.id = 'contentSchema';
            scriptTag.type = 'application/ld+json';
            scriptTag.textContent = JSON.stringify(schema);
            document.head.appendChild(scriptTag);
        }
        
        function preloadCriticalImages(seriesList) {
            seriesList.forEach(series => {
                if (series.image_url) {
                    const link = document.createElement('link');
                    link.rel = 'preload';
                    link.as = 'image';
                    link.href = series.image_url;
                    document.head.appendChild(link);
                }
            });
        }


        window.addEventListener('load', async function() {
            const sliderContainer = document.getElementById('sliderContainer');
            const contentContainer = document.getElementById('contentContainer');
            contentContainer.innerHTML = '';
            try {
                const response = await fetch('https://kiskh.com/api/api_handler.php');
                if (!response.ok) throw new Error('Network response was not ok.');
                const data = await response.json();

                const latestUpdates = data.latestUpdates || [];
                const popularSeries = data.popularSeries || [];
                window.popularSeriesData = popularSeries;
                window.allSeriesData = [...new Map([...(latestUpdates || []), ...(popularSeries || [])].map(item => [item['id'], item])).values()];

                addContentSchema(window.allSeriesData);

                const uniquePopular = popularSeries.filter(p => !latestUpdates.some(l => l.id === p.id));
                const allSeries = [...latestUpdates, ...uniquePopular];

                if (allSeries.length === 0) {
                    contentContainer.innerHTML = '<p class="no-stories">No series have been added yet.</p>';
                    return;
                }

                const sliderSeries = allSeries.filter(s => s.is_featured == '1');
                
                preloadCriticalImages(sliderSeries);
                setupSlider(sliderSeries, sliderContainer);

                const progressData = JSON.parse(localStorage.getItem('continueWatching')) || {};
                const seriesIdsWithProgress = Object.keys(progressData);

                if (seriesIdsWithProgress.length > 0) {
                    let continueWatchingSeries = seriesIdsWithProgress.map(seriesId => {
                        const series = allSeries.find(s => s.id === seriesId);
                        if (!series) return null;
                        const savedProgress = progressData[seriesId];
                        const progressPercent = (savedProgress.position / savedProgress.duration) * 100;
                        if (progressPercent > 95) return null;

                        return { ...series, progress: progressPercent, episodeToResume: savedProgress.episodeIndex, watchingEpisodeTitle: `EP ${savedProgress.episodeIndex + 1}`, position: savedProgress.position, duration: savedProgress.duration };
                    }).filter(Boolean);
                    continueWatchingSeries.sort((a, b) => progressData[b.id].lastWatched - progressData[a.id].lastWatched);
                    if (continueWatchingSeries.length > 0) renderCategoryRow('Continue watching', continueWatchingSeries, contentContainer);
                }

                if(latestUpdates.length > 0) renderCategoryRow('Lastest Update', latestUpdates, contentContainer);
                   const categories = ['Top K-Drama', 'Top C-Drama', 'Hollywood', 'Anime', 'Upcoming'];
                categories.forEach(category => {
                    let seriesInCategory;
                    if (category === 'Top K-Drama' || category === 'Top C-Drama') {
                        seriesInCategory = popularSeries.filter(s => s.category === category);
                    } else {
                        seriesInCategory = allSeries.filter(s => s.category === category);

                        // --- កែប្រែ​នៅ​ទីនេះ៖ តម្រៀប Hollywood, Anime, និង Upcoming តាម 'updated_at' ---
                        if (category === 'Hollywood' || category === 'Anime' || category === 'Upcoming') {
                            seriesInCategory.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
                        }
                    }
                    if (seriesInCategory.length > 0) {
                        renderCategoryRow(category, seriesInCategory.slice(0, 20), contentContainer);
                    }
                });


            } catch(error) {
                console.error("Failed to fetch series:", error);
                contentContainer.innerHTML = '<p class="no-stories" style="color:red;">Unable to retrieve data from the server.</p>';
            }

            // Mobile menu functionality
            const menuBtn = document.querySelector('.mobile-menu-btn'), closeBtn = document.getElementById('closeNavBtn'), mobileNavPanel = document.getElementById('mobileNavPanel'), overlay = document.getElementById('overlay');
            if (menuBtn && mobileNavPanel && closeBtn && overlay) {
                const openMenu = () => { mobileNavPanel.classList.add('active'); overlay.classList.add('active'); };
                const closeMenu = () => { mobileNavPanel.classList.remove('active'); overlay.classList.remove('active'); };
                menuBtn.addEventListener('click', openMenu);
                closeBtn.addEventListener('click', closeMenu);
                overlay.addEventListener('click', closeMenu);
            }

            // Theme dropdown functionality
            const themeMenuBtn = document.getElementById('themeMenuBtn'), themeDropdown = document.getElementById('themeDropdown');
            if (themeMenuBtn && themeDropdown) {
                themeMenuBtn.addEventListener('click', function(event) { event.preventDefault(); event.stopPropagation(); themeDropdown.classList.toggle('show'); });
                document.querySelectorAll('.theme-option').forEach(option => { option.addEventListener('click', function() { document.querySelectorAll('.theme-option').forEach(opt => opt.classList.remove('selected')); this.classList.add('selected'); themeDropdown.classList.remove('show'); }); });
            }
            window.addEventListener('click', function(event) { if (themeDropdown && themeDropdown.classList.contains('show') && !themeMenuBtn.contains(event.target)) themeDropdown.classList.remove('show'); });

            // Search overlay functionality
            const openSearchBtns = document.querySelectorAll('.js-open-search'), searchOverlay = document.getElementById('searchOverlay'), searchBackBtn = document.getElementById('searchBackBtn'), popularSearchGrid = document.getElementById('popularSearchGrid'), searchInput = document.getElementById('searchInput');
            let originalSearchCards = null;
            const openSearch = (e) => { e.preventDefault(); searchOverlay.classList.add('active'); if (!originalSearchCards && window.allSeriesData) { renderPopularSearches(window.allSeriesData, popularSearchGrid); originalSearchCards = Array.from(popularSearchGrid.children); } searchInput.value = ''; if (originalSearchCards) { popularSearchGrid.innerHTML = ''; originalSearchCards.forEach(card => popularSearchGrid.appendChild(card.cloneNode(true))); } };
            const closeSearch = () => searchOverlay.classList.remove('active');
            openSearchBtns.forEach(btn => btn.addEventListener('click', openSearch));
            searchBackBtn.addEventListener('click', closeSearch);
            searchInput.addEventListener('input', () => { const searchTerm = searchInput.value.toLowerCase().trim(), allCards = originalSearchCards || []; if (!searchTerm) { popularSearchGrid.innerHTML = ''; allCards.forEach(card => popularSearchGrid.appendChild(card.cloneNode(true))); return; } const matchedCards = [], unmatchedCards = []; allCards.forEach(card => { const titleElement = card.querySelector('.series-card-title'); (titleElement && titleElement.textContent.toLowerCase().includes(searchTerm)) ? matchedCards.push(card) : unmatchedCards.push(card); }); popularSearchGrid.innerHTML = ''; popularSearchGrid.append(...matchedCards, ...unmatchedCards); });
            document.querySelectorAll('.filter-bar .filter-btn').forEach(btn => { btn.addEventListener('click', () => { document.querySelectorAll('.filter-bar .filter-btn').forEach(b => b.classList.remove('active')); btn.classList.add('active'); }); });
        });

        function setupSlider(sliderSeries, container) {
            if (sliderSeries.length === 0) { container.style.display = 'none'; return; }
            const dotsContainer = document.getElementById('dotsContainer');
            sliderSeries.forEach((series, index) => {
                const imageAltText = `${series.title} - ${series.category || 'Series'} poster`;
                const slide = document.createElement('div');
                slide.className = 'slide' + (index === 0 ? ' active' : '');
                slide.innerHTML = `<a href="player.php?seriesId=${series.id}" style="text-decoration: none; color: inherit;"><img src="${series.image_url}" alt="${imageAltText}" width="1600" height="900" loading="eager"><div class="slide-content"><h2 class="slide-title">${series.title}</h2></div></a>`;
                container.prepend(slide);
                const dot = document.createElement('span');
                dot.className = 'dot' + (index === 0 ? ' active' : '');
                dot.dataset.index = index;
                dotsContainer.appendChild(dot);
            });
            let currentSlide = 0;
            const slides = document.querySelectorAll('.slide'), dots = document.querySelectorAll('.dot');
            function showSlide(index) {
                slides.forEach(s => s.classList.remove('active'));
                dots.forEach(d => d.classList.remove('active'));
                currentSlide = (index + slides.length) % slides.length;
                slides[currentSlide].classList.add('active');
                dots[currentSlide].classList.add('active');
            }
            document.getElementById('nextSlide').addEventListener('click', () => showSlide(currentSlide + 1));
            document.getElementById('prevSlide').addEventListener('click', () => showSlide(currentSlide - 1));
            dots.forEach(dot => { dot.addEventListener('click', (e) => showSlide(parseInt(e.target.dataset.index))); });
            setInterval(() => { showSlide(currentSlide + 1); }, 5000);
        }

        function renderCategoryRow(title, seriesList, container) {
            const section = document.createElement('div');
            section.className = 'category-section';
            const categoryLink = document.createElement('a');
            if (title !== 'Continue watching') categoryLink.href = `category.php?category=${encodeURIComponent(title)}`;
            categoryLink.style.textDecoration = 'none';
            categoryLink.style.color = 'inherit';
            const categoryTitle = document.createElement('h2');
            categoryTitle.className = 'category-title';
            categoryTitle.textContent = title;
            categoryLink.appendChild(categoryTitle);
            section.appendChild(categoryLink);
            const row = document.createElement('div');
            row.className = 'series-row';
            const isContinueWatching = title === 'Continue watching';
            seriesList.forEach(series => {
                const card = document.createElement('div');
                card.className = 'series-card';
                if (isContinueWatching) card.classList.add('continue-watching');
                const imageUrl = series.image_url || 'https://via.placeholder.com/270x152.png?text=No+Image';
                const link = isContinueWatching ? `player.php?seriesId=${series.id}&ep=${series.episodeToResume + 1}&t=${series.position}` : `player.php?seriesId=${series.id}`;
                const imageAltText = `Poster for ${series.title}`;
                let cardHTML = '';
                
                // --- MODIFIED: Added loading spinner and onload/onerror events to the image tag ---
                const imageTag = `
                    <div class="loading-spinner"></div>
                    <img src="${imageUrl}" alt="${imageAltText}" width="270" height="152" loading="lazy"
                         onload="this.classList.add('loaded'); this.previousElementSibling.style.display='none';"
                         onerror="this.onerror=null; this.src='https://via.placeholder.com/270x152.png?text=No+Image'; this.classList.add('loaded'); this.previousElementSibling.style.display='none';">`;
                
                if (isContinueWatching) {
                    card.style.setProperty('--progress', `${series.progress}%`);
                    const timeString = `${formatTime(series.position)} / ${formatTime(series.duration)}`;
                    cardHTML = `<a href="${link}"><div class="card-image-wrapper">${imageTag}<span class="series-card-ep-count">${timeString}</span><span class="series-card-episode-label">${series.watchingEpisodeTitle}</span></div><div class="series-card-content"><div class="series-card-title">${series.title}</div><div class="card-more-options">⋮</div></div></a>`;
                } else {
                     cardHTML = `<a href="${link}"><div class="card-image-wrapper">${imageTag}<span class="series-card-ep-count">EP ${series.episode_count}</span></div><div class="series-card-content"><div class="series-card-title">${series.title}</div></div></a>`;
                }
                card.innerHTML = cardHTML;
                row.appendChild(card);
            });
            section.appendChild(row);
            const prevBtn = document.createElement('button');
            prevBtn.className = 'scroll-btn prev hidden'; prevBtn.innerHTML = '‹';
            section.appendChild(prevBtn);
            const nextBtn = document.createElement('button');
            nextBtn.className = 'scroll-btn next'; nextBtn.innerHTML = '›';
            section.appendChild(nextBtn);
            prevBtn.addEventListener('click', () => { row.scrollLeft -= row.clientWidth * 0.8; });
            nextBtn.addEventListener('click', () => { row.scrollLeft += row.clientWidth * 0.8; });
            const handleScrollButtons = () => {
                const isScrollable = row.scrollWidth > row.clientWidth;
                prevBtn.classList.toggle('hidden', !isScrollable || row.scrollLeft <= 0);
                nextBtn.classList.toggle('hidden', !isScrollable || row.scrollLeft >= row.scrollWidth - row.clientWidth - 1);
            };
            row.addEventListener('scroll', handleScrollButtons, { passive: true });
            new ResizeObserver(handleScrollButtons).observe(row);
            handleScrollButtons(); 
            container.appendChild(section);
        }

        function renderPopularSearches(seriesList, container) {
            container.innerHTML = '';
            seriesList.forEach(series => {
                const card = document.createElement('div');
                card.className = 'series-card';
                const imageUrl = series.image_url || 'https://via.placeholder.com/270x152.png?text=No+Image';
                const link = `player.php?seriesId=${series.id}`;
                const imageAltText = `Search result poster for ${series.title}`;
                 
                // --- MODIFIED: Added loading spinner and onload/onerror events to the image tag ---
                const imageTag = `
                    <div class="loading-spinner"></div>
                    <img src="${imageUrl}" alt="${imageAltText}" width="270" height="152" loading="lazy"
                         onload="this.classList.add('loaded'); this.previousElementSibling.style.display='none';"
                         onerror="this.onerror=null; this.src='https://via.placeholder.com/270x152.png?text=No+Image'; this.classList.add('loaded'); this.previousElementSibling.style.display='none';">`;

                const cardHTML = `<a href="${link}"><div class="card-image-wrapper">${imageTag}<span class="series-card-ep-count">EP ${series.episode_count}</span></div><div class="series-card-content"><div class="series-card-title">${series.title}</div></div></a>`;
                card.innerHTML = cardHTML;
                container.appendChild(card);
            });
        }