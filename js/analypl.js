 // --- MODIFIED SCRIPT TO APPLY SETTINGS (LIKE index.html) ---
    (async () => {
        try {
            console.log("Attempting to load site settings...");
            const response = await fetch(`api/settings_handler.php?t=${new Date().getTime()}`);
            if (!response.ok) {
                console.error(`Failed to fetch settings. Status: ${response.status}`);
                return;
            }
            const settings = await response.json();
            console.log("Settings received:", settings);

            if (settings.logo_url) {
                const logoElements = document.querySelectorAll('.logo a');
                logoElements.forEach(el => {
                    // SEO: បន្ថែម alt text ដែលពិពណ៌នាសម្រាប់រូបភាព logo
                    el.innerHTML = `<img src="${settings.logo_url}" alt="${settings.site_name || 'Site Logo'}">`;
                });
            }

            if (settings.favicon_url) {
                const faviconTag = document.getElementById('faviconTag');
                if (faviconTag) {
                    faviconTag.href = settings.favicon_url;
                }
            }
            
            const injectHtml = (htmlString, settingName) => {
                if (!htmlString || htmlString.trim() === '') return;

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlString;

                const nodes = Array.from(tempDiv.childNodes);

                nodes.forEach(node => {
                    if (node.nodeName === 'SCRIPT') {
                        const script = document.createElement('script');
                        if (node.attributes.length > 0) {
                            for (let i = 0; i < node.attributes.length; i++) {
                                const attr = node.attributes[i];
                                script.setAttribute(attr.name, attr.value);
                            }
                        }
                        if (node.innerHTML) {
                            script.innerHTML = node.innerHTML;
                        }
                        document.head.appendChild(script);
                    } else {
                        document.head.appendChild(node.cloneNode(true));
                    }
                });

                console.log(`Successfully attempted to inject '${settingName}'.`);
            };

            injectHtml(settings.google_analytics, 'Google Analytics');
            injectHtml(settings.google_console, 'Google Search Console');
        } catch (error) {
            console.error('An error occurred while loading site settings:', error);
        }
    })();