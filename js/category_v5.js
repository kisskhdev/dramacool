let allSeriesData = [];
let popularOrder = [];
let latestOrder = []; // អថេរសម្រាប់រក្សាទុក "លំដាប់" នៃរឿងដែល Update ចុងក្រោយ
const currentFilters = {
    type: 'All',
    subtitle: 'All',
    region: 'All',
    status: 'All',
    sort: 'Popular'
};
const gridEl = document.getElementById('series-grid');
const messageEl = document.getElementById('message-text');

window.addEventListener('load', async function() {
    const urlParams = new URLSearchParams(window.location.search);
    const initialCategory = urlParams.get('category');
    await fetchData();
    populateRegionFilters();
    setupFilterButtons();
    if (initialCategory) {
        const decodedCategory = decodeURIComponent(initialCategory);
        let initialButton = document.querySelector(`.filter-row[data-filter-group="type"] .filter-btn[data-filter-value="${decodedCategory}"]`);
        if (!initialButton) {
            initialButton = document.querySelector(`.filter-row[data-filter-group="sort"] .filter-btn[data-filter-value="${decodedCategory}"]`)
        }
        if (initialButton) {
            initialButton.click()
        } else {
            applyFiltersAndRender()
        }
    } else {
        applyFiltersAndRender()
    }
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const closeBtn = document.getElementById('closeNavBtn');
    const mobileNavPanel = document.getElementById('mobileNavPanel');
    const overlay = document.getElementById('overlay');
    if (menuBtn && mobileNavPanel && closeBtn && overlay) {
        const openMenu = () => {
            mobileNavPanel.classList.add('active');
            overlay.classList.add('active')
        };
        const closeMenu = () => {
            mobileNavPanel.classList.remove('active');
            overlay.classList.remove('active')
        };
        menuBtn.addEventListener('click', openMenu);
        closeBtn.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);
        document.querySelectorAll('.mobile-nav-links .js-open-search').forEach(btn => btn.addEventListener('click', closeMenu))
    }
    const themeMenuBtn = document.getElementById('themeMenuBtn');
    const themeDropdown = document.getElementById('themeDropdown');
    if (themeMenuBtn && themeDropdown) {
        themeMenuBtn.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            themeDropdown.classList.toggle('show')
        });
        const themeOptions = document.querySelectorAll('.theme-option');
        themeOptions.forEach(option => {
            option.addEventListener('click', function() {
                themeOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                themeDropdown.classList.remove('show')
            })
        })
    }
    window.addEventListener('click', function(event) {
        if (themeDropdown && themeDropdown.classList.contains('show')) {
            if (!themeMenuBtn.contains(event.target)) {
                themeDropdown.classList.remove('show')
            }
        }
    });
    const openSearchBtns = document.querySelectorAll('.js-open-search');
    const searchOverlay = document.getElementById('searchOverlay');
    const searchBackBtn = document.getElementById('searchBackBtn');
    const popularSearchGrid = document.getElementById('popularSearchGrid');
    const searchInput = document.getElementById('searchInput');
    let originalSearchCards = null;
    const openSearch = (e) => {
        e.preventDefault();
        searchOverlay.classList.add('active');
        if (!originalSearchCards && allSeriesData) {
            renderPopularSearches(allSeriesData, popularSearchGrid);
            originalSearchCards = Array.from(popularSearchGrid.children).map(card => card.cloneNode(!0))
        }
        searchInput.value = '';
        if (originalSearchCards) {
            popularSearchGrid.innerHTML = '';
            originalSearchCards.forEach(card => popularSearchGrid.appendChild(card.cloneNode(!0)))
        }
    };
    const closeSearch = () => searchOverlay.classList.remove('active');
    openSearchBtns.forEach(btn => btn.addEventListener('click', openSearch));
    searchBackBtn.addEventListener('click', closeSearch);
    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const allCards = originalSearchCards || [];
        if (!searchTerm) {
            popularSearchGrid.innerHTML = '';
            allCards.forEach(card => popularSearchGrid.appendChild(card.cloneNode(!0)));
            return
        }
        const matchedCards = allCards.filter(card => {
            const titleElement = card.querySelector('.series-card-title');
            return titleElement && titleElement.textContent.toLowerCase().includes(searchTerm)
        });
        popularSearchGrid.innerHTML = '';
        matchedCards.forEach(card => popularSearchGrid.appendChild(card.cloneNode(!0)))
    });
    const filterBtns = document.querySelectorAll('#searchOverlay .filter-bar .filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active')
        })
    })
});

async function fetchData() {
    try {
        const response = await fetch('https://kiskh.com/api/api_handler.php?source=category');
        if (!response.ok) throw new Error('Network response was not ok.');
        const data = await response.json();
        const uniqueSeriesMap = new Map();
        (data.popularSeries || []).forEach(series => {
            if (!uniqueSeriesMap.has(series.id)) {
                uniqueSeriesMap.set(series.id, series);
                popularOrder.push(series.id)
            }
        });
        (data.latestUpdates || []).forEach(series => {
            if (!uniqueSeriesMap.has(series.id)) {
                uniqueSeriesMap.set(series.id, series);
            }
            if (!latestOrder.includes(series.id)) {
                latestOrder.push(series.id);
            }
        });
        allSeriesData = Array.from(uniqueSeriesMap.values())
    } catch (error) {
        console.error("Failed to fetch series data:", error);
        messageEl.textContent = 'Unable to retrieve data from the server.';
        messageEl.style.color = 'red'
    }
}

function populateRegionFilters() {
    const regionContainer = document.getElementById('region-filter-container');
    if (!regionContainer) return;
    const regionDisplayNames = {
        'Korea': 'South Korea', 'China': 'Chinese', 'USA': 'United States', 'Thailand': 'Thailand',
        'Japan': 'Japan', 'India': 'India', 'indonesia': 'indonesia', 'Philippines': 'Philippines'
    };
    const uniqueRegions = [...new Set(allSeriesData.map(series => series.country).filter(Boolean))].sort();
    uniqueRegions.forEach(region => {
        const button = document.createElement('button');
        button.className = 'filter-btn';
        button.dataset.filterValue = region;
        button.textContent = regionDisplayNames[region] || region;
        regionContainer.appendChild(button)
    })
}

function setupFilterButtons() {
    const filterButtons = document.querySelectorAll('.filters-container .filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const group = button.closest('.filter-row').dataset.filterGroup;
            const value = button.dataset.filterValue;
            const row = button.closest('.filter-row');
            row.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            currentFilters[group] = value;
            applyFiltersAndRender()
        })
    })
}

function applyFiltersAndRender() {
    let filteredSeries = [...allSeriesData];

    // ជំហានទី 1: ធ្វើការ Filter ធម្មតាជាមុនសិន
    if (currentFilters.type !== 'All') filteredSeries = filteredSeries.filter(s => s.type === currentFilters.type || s.category === currentFilters.type);
    if (currentFilters.subtitle !== 'All') filteredSeries = filteredSeries.filter(s => s.subtitles && Array.isArray(s.subtitles) && s.subtitles.includes(currentFilters.subtitle));
    if (currentFilters.region !== 'All') filteredSeries = filteredSeries.filter(s => s.country === currentFilters.region);
    if (currentFilters.status !== 'All') filteredSeries = filteredSeries.filter(s => s.status === currentFilters.status);

    // ជំហានទី 2: ធ្វើការ Sort ទៅតាមตัวเลือกที่ถูกเลือก
    if (currentFilters.sort === 'Last Update') {
        filteredSeries.sort((a, b) => {
            const aIsUpdated = latestOrder.includes(a.id);
            const bIsUpdated = latestOrder.includes(b.id);

            if (aIsUpdated && bIsUpdated) {
                return latestOrder.indexOf(a.id) - latestOrder.indexOf(b.id);
            } else if (aIsUpdated) {
                return -1;
            } else if (bIsUpdated) {
                return 1;
            } else {
                return parseInt(b.id) - parseInt(a.id);
            }
        });
    } else if (currentFilters.sort === 'Release Date') {
        filteredSeries.sort((a, b) => parseInt(b.release_year) - parseInt(a.release_year));
    } else { // Popular
        filteredSeries.sort((a, b) => {
            const indexA = popularOrder.indexOf(a.id);
            const indexB = popularOrder.indexOf(b.id);
            if (indexA === -1) return 1;
            if (indexB === -1) return -1;
            return indexA - indexB;
        });
    }
    
    renderSeriesGrid(filteredSeries);
}


function renderSeriesGrid(seriesList) {
    gridEl.innerHTML = '';
    if (seriesList.length === 0) {
        gridEl.innerHTML = `<p class="message-text">No stories found matching your search.</p>`;
        return
    }
    seriesList.forEach(series => {
        const card = document.createElement('div');
        card.className = 'series-card';
        const link = `player.php?seriesId=${series.id}`;
        const imageUrl = series.image_url || 'https://via.placeholder.com/270x152.png?text=No+Image';
        card.innerHTML = `
            <a href="${link}">
                <div class="card-image-wrapper">
                    <div class="loading-spinner"></div>
                    <img src="${imageUrl}" alt="${series.title}" 
                         onload="this.classList.add('loaded'); this.previousElementSibling.style.display='none';"
                         onerror="this.onerror=null;this.src='https://via.placeholder.com/270x152.png?text=No+Image'; this.classList.add('loaded'); this.previousElementSibling.style.display='none';">
                    <span class="series-card-ep-count">EP ${series.episode_count || '?'}</span>
                </div>
                <div class="series-card-content">
                    <div class="series-card-title">${series.title}</div>
                </div>
            </a>`;
        gridEl.appendChild(card)
    }
    )
}

function renderPopularSearches(seriesList, container) {
    container.innerHTML = '';
    seriesList.forEach(series => {
        const card = document.createElement('div');
        card.className = 'series-card';
        const imageUrl = series.image_url || 'https://via.placeholder.com/270x152.png?text=No+Image';
        const link = `player.php?seriesId=${series.id}`;
        card.innerHTML = `
            <a href="${link}">
                <div class="card-image-wrapper">
                    <div class="loading-spinner"></div>
                    <img src="${imageUrl}" alt="${series.title}" 
                         onload="this.classList.add('loaded'); this.previousElementSibling.style.display='none';"
                         onerror="this.onerror=null;this.src='https://via.placeholder.com/270x152.png?text=No+Image'; this.classList.add('loaded'); this.previousElementSibling.style.display='none';">
                    <span class="series-card-ep-count">EP ${series.episode_count}</span>
                </div>
                <div class="series-card-content">
                    <div class="series-card-title">${series.title}</div>
                </div>
            </a>`;
        container.appendChild(card)
    })
}