 const API_URL = 'https://kiskh.com/api/api_handler.php';
    let jwPlayerInstance = null;
    let allEpisodesData = []; 
    let currentEpisodeItem = null;
    let seriesId;
    let seriesData = null; // Store the whole series object
    const urlParams = new URLSearchParams(window.location.search);
    const startTime = urlParams.get('t'); // អានពេលវេលាពី URL
    seriesId = urlParams.get('seriesId');  

    function slugify(text) {
        if (!text) return '';
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    // SEO: ធ្វើឱ្យប្រសើរឡើងនូវមុខងារដើម្បីធ្វើបច្ចុប្បន្នភាព meta tags និង structured data
    function updateSeoAndMetaTags(seriesInfo, episodeInfo = null) {
        const pageUrl = new URL(window.location.href);
        const siteName = "kisskh Asian Dramas & Movies";
        const fallbackDescription = `kisskh ${seriesInfo.title} Asian Dramas & Movies ${siteName}`;
        const description = seriesInfo.description ? seriesInfo.description.substring(0, 160) : fallbackDescription;

        let pageTitle, ogTitle;

        if (episodeInfo) {
            pageTitle = `kisskh ${seriesInfo.title} - ep ${episodeInfo.number} | ${siteName}`;
            ogTitle = `${seriesInfo.title} - ep ${episodeInfo.number}`;
        } else {
            pageTitle = `kisskh ${seriesInfo.title} | ${siteName}`;
            ogTitle = seriesInfo.title;
        }

        document.title = pageTitle;
        document.getElementById('meta-description').setAttribute('content', description);
        
        // Open Graph & Twitter
        document.getElementById('og-title').setAttribute('content', ogTitle);
        document.getElementById('og-description').setAttribute('content', description);
        document.getElementById('og-image').setAttribute('content', seriesInfo.image_url);
        document.getElementById('og-url').setAttribute('content', pageUrl.href);
        document.getElementById('twitter-title').setAttribute('content', ogTitle);
        document.getElementById('twitter-description').setAttribute('content', description);
        document.getElementById('twitter-image').setAttribute('content', seriesInfo.image_url);

        updateStructuredData(seriesInfo, episodeInfo, pageUrl.href);
    }
    
    function updateStructuredData(seriesInfo, episodeInfo = null, pageUrl) {
        const schema = {
            "@context": "https://schema.org",
            "@graph": []
        };
        
        const organizationSchema = {
             "@type": "Organization",
             "name": "KISSKH",
             "url": window.location.origin,
             "logo": document.querySelector('.logo img')?.src || ''
        };

        const tvSeriesSchema = {
            "@type": "TVSeries",
            "name": seriesInfo.title,
            "description": seriesInfo.description || `kisskh ${seriesInfo.title} all ep`,
            "thumbnailUrl": seriesInfo.image_url,
            "numberOfEpisodes": seriesInfo.episodes.length,
            "countryOfOrigin": seriesInfo.country,
            "inLanguage": "en",
            "datePublished": seriesInfo.release_year,
            "publisher": organizationSchema,
            "provider": organizationSchema
        };
        
        schema["@graph"].push(tvSeriesSchema);

        if (episodeInfo) {
            const videoObjectSchema = {
                "@type": "VideoObject",
                "name": `ep ${episodeInfo.number}: ${seriesInfo.title}`,
                "description": seriesInfo.description || `kisskh ${seriesInfo.title} ep ${episodeInfo.number}`,
                "thumbnailUrl": seriesInfo.image_url,
                "uploadDate": seriesInfo.release_year,
                "partOfTVSeries": tvSeriesSchema,
                "episodeNumber": episodeInfo.number,
                 // ប្រើ embedUrl ប្រសិនបើវីដេអូជាប្រភេទ embed
                "embedUrl": episodeInfo.data.video_type === 'embed' ? episodeInfo.data.video_url : null,
                // ប្រើ contentUrl ប្រសិនបើមានតំណផ្ទាល់
                "contentUrl": episodeInfo.data.video_type !== 'embed' ? episodeInfo.data.video_url : null
            };
            schema["@graph"].push(videoObjectSchema);
        }

        // Breadcrumbs Schema
        const breadcrumbSchema = {
            "@type": "BreadcrumbList",
            "itemListElement": [{
                "@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": window.location.origin + "/index.html"
            }, {
                "@type": "ListItem",
                "position": 2,
                "name": seriesInfo.title,
                "item": `${window.location.pathname}?title=${slugify(seriesInfo.title)}&seriesId=${seriesId}`
            }]
        };

        if (episodeInfo) {
            breadcrumbSchema.itemListElement.push({
                "@type": "ListItem",
                "position": 3,
                "name": `ep ${episodeInfo.number}`,
                "item": pageUrl
            });
        }
        schema["@graph"].push(breadcrumbSchema);

        document.getElementById('json-ld-schema').textContent = JSON.stringify(schema, null, 2);
    }

    window.addEventListener('load', async function() {
        // ... (កូដសម្រាប់ mobile nav, theme, search overlay របស់អ្នកនៅដដែល) ...
        const menuBtn = document.querySelector('.mobile-menu-btn');
        const closeBtn = document.getElementById('closeNavBtn');
        const mobileNavPanel = document.getElementById('mobileNavPanel');
        const overlay = document.getElementById('overlay');
        if (menuBtn && mobileNavPanel && closeBtn && overlay) {
            const openMenu = () => { mobileNavPanel.classList.add('active'); overlay.classList.add('active'); };
            const closeMenu = () => { mobileNavPanel.classList.remove('active'); overlay.classList.remove('active'); };
            menuBtn.addEventListener('click', openMenu);
            closeBtn.addEventListener('click', closeMenu);
            overlay.addEventListener('click', closeMenu);
        }
        
        const descriptionHeader = document.getElementById('description-header');
        const descriptionBody = document.getElementById('description-body');
        if(descriptionHeader && descriptionBody) {
            descriptionHeader.addEventListener('click', () => {
                const isActive = descriptionHeader.classList.toggle('active');
                descriptionHeader.setAttribute('aria-expanded', isActive);
                descriptionBody.classList.toggle('open');
            });
        }
        
        const themeMenuBtn = document.getElementById('themeMenuBtn');
        const themeDropdown = document.getElementById('themeDropdown');
        if (themeMenuBtn && themeDropdown) {
            themeMenuBtn.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                themeDropdown.classList.toggle('show');
            });
            const themeOptions = document.querySelectorAll('.theme-option');
            themeOptions.forEach(option => {
                option.addEventListener('click', function() {
                    themeOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    themeDropdown.classList.remove('show');
                });
            });
        }
        window.addEventListener('click', function(event) {
            if (themeDropdown && themeDropdown.classList.contains('show')) {
                if (!themeMenuBtn.contains(event.target)) {
                    themeDropdown.classList.remove('show');
                }
            }
        });
        
        const openSearchBtns = document.querySelectorAll('.js-open-search');
        const searchOverlay = document.getElementById('searchOverlay');
        const searchBackBtn = document.getElementById('searchBackBtn');
        const openSearch = (e) => { e.preventDefault(); searchOverlay.classList.add('active'); };
        const closeSearch = () => { searchOverlay.classList.remove('active'); };
        openSearchBtns.forEach(btn => btn.addEventListener('click', openSearch));
        searchBackBtn.addEventListener('click', closeSearch);

        const urlParams = new URLSearchParams(window.location.search);
        seriesId = urlParams.get('seriesId'); 
        
        if (!seriesId) {
             document.body.innerHTML = '<h1>Series ID Incorrect</h1>';
             return;
        }
        try {
            const response = await fetch(`${API_URL}?id=${seriesId}`);
            seriesData = await response.json();
            if (!seriesData) {
                document.body.innerHTML = '<h1>no tv series</h1>';
                return;
            }
            
            const seriesSlug = slugify(seriesData.title);
            
            if (!urlParams.has('ep')) {
                const newUrl = `${window.location.pathname}?title=${seriesSlug}&seriesId=${seriesId}`;
                history.replaceState({ seriesId: seriesId }, seriesData.title, newUrl);
            }
            
            // Initial SEO setup for the series page
            updateSeoAndMetaTags(seriesData);

            document.getElementById('series-title').textContent = seriesData.title;
            document.getElementById('series-description').textContent = seriesData.description || '';
            const metaContainer = document.getElementById('series-meta');
            let metaHTML = '';
            if (seriesData.country) metaHTML += `<span>🌍 ${seriesData.country}</span>`;
            if (seriesData.status) metaHTML += `<span>🌟 ${seriesData.status}</span>`;
            if (seriesData.type) metaHTML += `<span>🎬 ${seriesData.type}</span>`;
            if (seriesData.release_year) metaHTML += `<span>🗓️ ${seriesData.release_year}</span>`;
            metaContainer.innerHTML = metaHTML;
            
            const posterUrl = seriesData.image_url;
            const episodeGridEl = document.getElementById('episode-grid');

            document.getElementById('player-container').innerHTML = `<img src="${posterUrl}" style="width:100%; height:100%; object-fit:contain;" alt="โปสเตอร์ของ ${seriesData.title}" loading="lazy">`;

            if (!seriesData.episodes || seriesData.episodes.length === 0) {
                episodeGridEl.innerHTML = '<p>no episode</p>';
                document.getElementById('episode-list-title').textContent = 'Episodes (0)';
                return;
            }
      
            allEpisodesData = seriesData.episodes; 
            const totalEpisodes = allEpisodesData.length;
            document.getElementById('episode-list-title').textContent = `Total ${totalEpisodes}`;
            
            for (let i = totalEpisodes - 1; i >= 0; i--) {
                const ep = allEpisodesData[i];
                const episodeNumber = i + 1;
                const item = document.createElement('div');
                item.className = 'episode-item';
                item.textContent = episodeNumber;
                item.dataset.index = i;
                item.dataset.title = ep.title ? `ep ${episodeNumber}: ${ep.title}` : `ep ${episodeNumber}`;
                
                if (ep.subtitles && ep.subtitles.some(sub => sub.file_url && (sub.file_url.endsWith('.vtt') || sub.file_url.endsWith('.srt')))) {
                    const ccIcon = document.createElement('span');
                    ccIcon.className = 'cc-icon';
                    ccIcon.textContent = 'CC';
                    item.appendChild(ccIcon);
                }

                item.addEventListener('click', () => playEpisode(item));
                episodeGridEl.appendChild(item);
            }

            const startEpisodeNumber = Number(urlParams.get('ep'));
            if (startEpisodeNumber > 0 && startEpisodeNumber <= allEpisodesData.length) {
                const targetEpisodeItem = Array.from(episodeGridEl.querySelectorAll('.episode-item')).find(item => Number(item.textContent.replace(/\D/g, '')) === startEpisodeNumber);
                if (targetEpisodeItem) {
                    playEpisode(targetEpisodeItem, startTime);
                }
            }
            
        } catch(error) {
            console.error("Failed to fetch series data:", error);
            document.body.innerHTML = '<h1>There was a problem downloading data.</h1>';
        }
    });

    function playEpisode(listItem, seekTime = 0) {
        if (!listItem || currentEpisodeItem === listItem) return;
        
        document.querySelectorAll('#episode-grid .episode-item').forEach(el => el.classList.remove('active'));
        listItem.classList.add('active');
        currentEpisodeItem = listItem;
        
        const episodeIndex = parseInt(listItem.dataset.index, 10);
        const episodeNumber = listItem.textContent.replace(/\D/g, '');
        const episodeData = allEpisodesData[episodeIndex];
        const playerContainer = document.getElementById('player-container');
        const seriesSlug = slugify(seriesData.title);
        
        if (seriesSlug) {
            const newUrl = `${window.location.pathname}?title=${seriesSlug}&seriesId=${seriesId}&ep=${episodeNumber}`;
            history.replaceState({ seriesId: seriesId, ep: episodeNumber }, document.title, newUrl);
            updateSeoAndMetaTags(seriesData, { number: episodeNumber, data: episodeData });
        }

        // --- ការកែប្រែនៅទីនេះ ---
        if (episodeData.video_type === 'embed') {
            if (jwPlayerInstance) {
                jwPlayerInstance.remove();
                jwPlayerInstance = null;
            }
            
            // សម្អាត player container ជាមុនសិន
            playerContainer.innerHTML = ''; 

            // បង្កើត iframe element ថ្មី
            const iframe = document.createElement('iframe');
            iframe.src = episodeData.video_url; // ប្រើ URL ពី API
            iframe.title = `kisskh player ${seriesData.title} ep ${episodeNumber}`; // សម្រាប់ SEO
            iframe.setAttribute('width', '100%');
            iframe.setAttribute('height', '100%');
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('scrolling', 'no');
            iframe.setAttribute('allowfullscreen', 'true');
            iframe.setAttribute('allow', 'autoplay; encrypted-media'); // បន្ថែមគុណលក្ខណៈ allow

            // បញ្ចូល iframe ទៅក្នុង player container
            playerContainer.appendChild(iframe);
            
            return; // បញ្ចប់ត្រឹមនេះសម្រាប់វីដេអូ embed
        }
        // --- ចប់ការកែប្រែ ---

        const tracks = formatTracksForJwPlayer(episodeData.subtitles);
        const playlistItem = {
            file: episodeData.video_url,
            image: seriesData.image_url,
            tracks: tracks,
            title: listItem.dataset.title
        };

        if (jwPlayerInstance) jwPlayerInstance.remove();

        jwPlayerInstance = jwplayer("player-container").setup({
            playlist: [playlistItem],
            width: "100%",
            aspectratio: "16:9",
            mute: false,
            autostart: true
        });
        
        if (seekTime > 0) {
            jwPlayerInstance.once('play', function() {
                console.log(`Seeking to ${seekTime} seconds.`);
                jwPlayerInstance.seek(seekTime);
            });
        }

        jwPlayerInstance.on('time', function(event) {
                const { position, duration } = event;
                if (duration > 0 && position > 5 && position < duration - 10) {
                    const progressData = JSON.parse(localStorage.getItem('continueWatching')) || {};
                    progressData[seriesId] = {
                        episodeIndex: episodeIndex,
                        episodeTitle: listItem.dataset.title,
                        position: position,
                        duration: duration,
                        lastWatched: Date.now()
                    };
                    localStorage.setItem('continueWatching', JSON.stringify(progressData));
                }
            });jwPlayerInstance.on("ready",function(){let e=jwPlayerInstance.getContainer(),r=e.querySelector(".jw-button-container");r.querySelector(".jw-spacer");let o=e.querySelector(".jw-display-icon-rewind"),n=o.cloneNode(!0),t=n.querySelector(".jw-icon-rewind");t.style.backgroundImage="url(https://www.svgrepo.com/show/340352/forward-10.svg",t.ariaLabel="Forward 10 Seconds";let l=e.querySelector(".jw-display-icon-next");l.parentNode.insertBefore(n,l),e.querySelector(".jw-display-icon-next").style.display="none";let c=r.querySelector(".jw-icon-rewind"),i=c.cloneNode(!0);i.style.backgroundImage="url(https://www.svgrepo.com/show/340352/forward-10.svg",i.ariaLabel="Forward 10 Seconds",c.parentNode.insertBefore(i,c.nextElementSibling),[t,i].forEach(e=>{e.onclick=()=>{jwPlayerInstance.seek(jwPlayerInstance.getPosition()+10)}})});
 
    }

    function formatTracksForJwPlayer(subtitleArray) {
        if (!subtitleArray || subtitleArray.length === 0) {
            return [];
        }
        const englishSubIndex = subtitleArray.findIndex(sub => sub.label.toLowerCase() === 'english');
        return subtitleArray.map((sub, index) => {
            let isDefault = false;
            if (englishSubIndex !== -1) {
                isDefault = (index === englishSubIndex);
            } else {
                isDefault = sub.default === true;
            }
            return {
                file: sub.file_url,
                label: sub.label,
                "default": isDefault
            };
        });
    }