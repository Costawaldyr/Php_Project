// =============================================
// INITIALISATION PRINCIPALE
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    // Supprimer le préchargement
    setTimeout(function() {
        const loading = document.querySelector('.loading');
        if (loading) {
            loading.style.opacity = '0';
            setTimeout(() => loading.style.display = 'none', 500);
        }
    }, 500);

    // Initialiser les composants
    initComponents();
    initAjaxNavigation();
    initLazyLoading();
    
    // Pour les pages chargées via AJAX
    document.addEventListener('ajaxContentLoaded', initComponents);
});

function initComponents() {
    initMenuMobile();
    initSearchTabs();
    initFavorites();
    initBestSellersCarousel();
    initDestinationsCarousel();
    initContactPopups();
    initAIChat();
    initAnimations();
    initCookieConsent();
    initHolidayCards();
    initSearchForm();
    initAjaxNavigation();
    initLazyLoading();
}

// =============================================
// MENU MOBILE
// =============================================

function initMenuMobile() {
    const menuToggle = document.getElementById('mobile-menu');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle && navMenu) {
        // Supprimer les écouteurs existants pour éviter les doublons
        menuToggle.removeEventListener('click', toggleMenu);
        menuToggle.addEventListener('click', toggleMenu);
        
        function toggleMenu() {
            navMenu.classList.toggle('active');
            menuToggle.classList.toggle('open');
        }
    }
}

// =============================================
// ONGLETS DE RECHERCHE
// =============================================

function initSearchTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    if (tabButtons.length) {
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const searchType = this.dataset.tab;
                document.getElementById('searchType').value = searchType;
                
                const destinationLabel = document.getElementById('destinationLabel');
                const departLabel = document.getElementById('departLabel');
                const returnDateGroup = document.getElementById('returnDateGroup');
                
                if (searchType === 'voyages') {
                    destinationLabel.textContent = 'Où allez-vous ?';
                    departLabel.textContent = 'Départ';
                    returnDateGroup.style.display = 'none';
                } 
                else if (searchType === 'hebergements') {
                    destinationLabel.textContent = 'Localisation';
                    departLabel.textContent = 'Arrivée';
                    returnDateGroup.style.display = 'none';
                }
            });
        });
    }
}

// =============================================
// GESTION DE LA RECHERCHE AJAX
// =============================================

function initSearchForm() {
    const searchForm = document.getElementById('searchForm');
    if (!searchForm) return;

    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const searchParams = new URLSearchParams(formData);
        
        showLoading(true);
        
        // Utilisation de Fetch API pour charger les résultats
        fetch(this.action + '?' + searchParams.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Créer un conteneur temporaire pour le nouveau contenu
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            // Extraire le contenu principal
            const newContent = tempDiv.querySelector('main') || tempDiv;
            
            // Remplacer le contenu de la page
            document.querySelector('main').innerHTML = newContent.innerHTML;
            
            // Mettre à jour l'URL sans recharger la page
            history.pushState({}, '', this.action + '?' + searchParams.toString());
            
            // Réinitialiser les composants
            initMenuMobile();
            initAnimations();
            
            // Faire défiler vers le haut
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            showToast('Résultats de recherche chargés');
        })
        .catch(error => {
            console.error('Erreur de recherche:', error);
            showToast('Erreur lors de la recherche', 'error');
        })
        .finally(() => {
            showLoading(false);
        });
    });
}

// Ajouter à la fonction d'initialisation
document.addEventListener('DOMContentLoaded', function() {
    // ... autres initialisations ...
    initSearchForm();
});

// =============================================
// GESTION DES FAVORIS (AJAX)
// =============================================
function initFavorites() {
    document.querySelectorAll('.favorite-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const offerId = formData.get('offre_id');
            const action = formData.get('action_favori');
            
            fetch('', {  // URL vide pour soumettre au même fichier
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const heartIcon = this.querySelector('.btn-favorite i');
                    const actionInput = this.querySelector('[name="action_favori"]');
                    
                    if (action === 'ajouter') {
                        heartIcon.classList.remove('far');
                        heartIcon.classList.add('fas');
                        heartIcon.parentElement.style.color = 'red';
                        actionInput.value = 'retirer';
                    } else {
                        heartIcon.classList.remove('fas');
                        heartIcon.classList.add('far');
                        heartIcon.parentElement.style.color = '';
                        actionInput.value = 'ajouter';
                    }
                    
                    // Mettre à jour le compteur si présent
                    if (document.getElementById('favoris-count')) {
                        const countElement = document.getElementById('favoris-count');
                        let count = parseInt(countElement.textContent);
                        count = action === 'ajouter' ? count + 1 : count - 1;
                        countElement.textContent = count;
                    }
                    
                    // Afficher un message de confirmation
                    showToast(data.message);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                
                showToast('Une erreur est survenue', 'error');
            });
        });
    });
}
// =============================================
// CARROUSEL BEST-SELLERS
// =============================================

function initBestSellersCarousel() {
    const slider = document.querySelector('.bestsellers-slider');
    if (!slider) return;
    
    const slides = document.querySelectorAll('.bestseller-slide');
    const prevBtn = document.querySelector('.bestsellers-section .prev-btn');
    const nextBtn = document.querySelector('.bestsellers-section .next-btn');
    const dotsContainer = document.querySelector('.bestsellers-section .carousel-dots');
    let currentIndex = 0;
    let slideInterval;
    
    // Créer les dots de navigation
    slides.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('dot');
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToSlide(index));
        dotsContainer.appendChild(dot);
    });
    
    const dots = document.querySelectorAll('.bestsellers-section .dot');
    
    function goToSlide(index) {
        currentIndex = index;
        updateSlider();
    }
    
    function updateSlider() {
        const slideWidth = slides[0].offsetWidth + 20;
        slider.scrollTo({
            left: currentIndex * slideWidth,
            behavior: 'smooth'
        });
        
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }
    
    function startAutoRotation() {
        slideInterval = setInterval(() => {
            currentIndex = (currentIndex < slides.length - 1) ? currentIndex + 1 : 0;
            updateSlider();
        }, 5000);
    }
    
    if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex > 0) ? currentIndex - 1 : slides.length - 1;
            updateSlider();
            resetAutoRotation();
        });
        
        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex < slides.length - 1) ? currentIndex + 1 : 0;
            updateSlider();
            resetAutoRotation();
        });
    }
    
    function resetAutoRotation() {
        clearInterval(slideInterval);
        startAutoRotation();
    }
    
    slider.addEventListener('mouseenter', () => clearInterval(slideInterval));
    slider.addEventListener('mouseleave', startAutoRotation);
    
    startAutoRotation();
    window.addEventListener('resize', updateSlider);
}
// =============================================
// CONTACT & CHAT AI 
// =============================================

function initContactPopups() {
    const whatsappBtn = document.getElementById('whatsappBtn');
    const phoneBtn = document.getElementById('phoneBtn');
    const aiChatBtn = document.getElementById('aiChatBtn');
    const whatsappPopup = document.querySelector('.whatsapp-popup');
    const phonePopup = document.querySelector('.phone-popup');
    const aiChatContainer = document.getElementById('aiChatContainer');
    const closeButtons = document.querySelectorAll('.close-popup, .close-chat');
    
    // Créer l'overlay si il n'existe pas
    let overlay = document.querySelector('.popup-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'popup-overlay';
        document.body.appendChild(overlay);
    }

    function openPopup(popup) {
        // Fermer d'abord tous les popups
        document.querySelectorAll('.contact-popup').forEach(p => {
            p.classList.remove('active');
        });
        
        popup.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeAllPopups() {
        document.querySelectorAll('.contact-popup').forEach(popup => {
            popup.classList.remove('active');
        });
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        aiChatContainer.classList.remove('active');
    }

    if (whatsappBtn && whatsappPopup) {
        whatsappBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openPopup(whatsappPopup);
        });
    }

    if (phoneBtn && phonePopup) {
        phoneBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openPopup(phonePopup);
        });
    }

    if (aiChatBtn && aiChatContainer) {
        aiChatBtn.addEventListener('click', function(e) {
            e.preventDefault();
            aiChatContainer.classList.toggle('active');
            closeAllPopups();
        });
    }

    closeButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            closeAllPopups();
        });
    });

    overlay.addEventListener('click', closeAllPopups);
    
    // Gestion de la touche Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllPopups();
        }
    });
}

function initAIChat() {
    const chatMessages = document.getElementById('chatMessages');
    const userMessage = document.getElementById('userMessage');
    const sendMessage = document.getElementById('sendMessage');
    const closeChat = document.querySelector('.close-chat');
    const aiChatContainer = document.getElementById('aiChatContainer');
    const aiChatTrigger = document.getElementById('aiChatTrigger');
    const chatNotification = document.getElementById('chatNotification');
    
    if (!chatMessages || !userMessage || !sendMessage) return;

    const aiResponses = {
        "bonjour": "Bonjour ! Comment puis-je vous aider pour votre prochain voyage ?",
        "salut": "Salut ! Prêt(e) à planifier votre prochaine aventure ?",
        "prix": "Nos prix sont parmi les plus compétitifs du marché. Pour une estimation précise, pouvez-vous me dire votre destination et dates ?",
        "destination": "Nous proposons des destinations partout dans le monde. Quels types de voyage recherchez-vous (plage, montagne, ville...) ?",
        "aide": "Bien sûr ! Dites-moi ce qui vous préoccupe et je ferai de mon mieux pour vous aider.",
        "merci": "Avec plaisir ! N'hésitez pas si vous avez d'autres questions.",
        "default": "Je ne suis pas sûr de comprendre. Pouvez-vous reformuler votre question ? Je peux vous aider avec des informations sur les destinations, prix, disponibilités et plus."
    };
    
    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `${sender}-message`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.textContent = text;
        
        messageDiv.appendChild(contentDiv);
        chatMessages.appendChild(messageDiv);
        
        // Faire défiler vers le bas
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function sendUserMessage() {
        const message = userMessage.value.trim();
        if (message === '') return;
        
        addMessage(message, 'user');
        userMessage.value = '';
        
        // Simuler un temps de réponse
        setTimeout(() => {
            const response = generateAIResponse(message);
            addMessage(response, 'ai');
        }, 800);
    }
    
    function generateAIResponse(message) {
        const lowerMessage = message.toLowerCase();
        
        if (lowerMessage.includes('bonjour') || lowerMessage.includes('salut')) {
            return aiResponses["bonjour"];
        } else if (lowerMessage.includes('prix') || lowerMessage.includes('cher')) {
            return aiResponses["prix"];
        } else if (lowerMessage.includes('destination') || lowerMessage.includes('voyager')) {
            return aiResponses["destination"];
        } else if (lowerMessage.includes('aide') || lowerMessage.includes('problème')) {
            return aiResponses["aide"];
        } else if (lowerMessage.includes('merci')) {
            return aiResponses["merci"];
        } else {
            return aiResponses["default"];
        }
    }
    
    // Événements
    sendMessage.addEventListener('click', sendUserMessage);
    
    userMessage.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendUserMessage();
        }
    });

    if (closeChat) {
        closeChat.addEventListener('click', function() {
            aiChatContainer.classList.remove('active');
        });
    }

    // Gestion du bouton flottant
    if (aiChatTrigger) {
        aiChatTrigger.addEventListener('click', function() {
            aiChatContainer.classList.toggle('active');
            chatNotification.style.display = 'none';
        });
    }

    // Message d'accueil
    if (chatMessages.children.length === 0) {
        addMessage("Bonjour ! Je suis l'assistant Tripster. Comment puis-je vous aider aujourd'hui ?", 'ai');
    }

    // Ouvrir automatiquement après 5 minutes
    setTimeout(() => {
        if (!aiChatContainer.classList.contains('active')) {
            chatNotification.style.display = 'flex';
            chatNotification.textContent = '1';
            
            // Animation pour attirer l'attention
            let pulseCount = 0;
            const pulseInterval = setInterval(() => {
                aiChatTrigger.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    aiChatTrigger.style.transform = 'scale(1)';
                }, 300);
                
                pulseCount++;
                if (pulseCount >= 3) clearInterval(pulseInterval);
            }, 1000);
        }
    }, 5 * 60 * 1000); // 5 minutes
}

// =============================================
// DESTINATIONS FUTURES 
// =============================================

function initDestinationsCarousel() {
    const section = document.querySelector('.future-destinations-section');
    if (!section) return;
    
    // Vérifier si la timeline existe déjà pour éviter les doublons
    if (section.querySelector('.destination-timeline')) return;

    // Initialiser la timeline
    const timeline = document.createElement('div');
    timeline.className = 'destination-timeline';
    timeline.innerHTML = `
        <div class="timeline-progress"></div>
        
        <div class="destination-item">
            <div class="destination-date">Juillet 2025</div>
            <div class="destination-card">
                <div class="destination-image" style="background-image: url('img/IMG_2957.AVIF');">
                    <div class="coming-soon-badge">Bientôt disponible</div>
                </div>
                <div class="destination-info">
                    <h3>Algarve Secret</h3>
                    <p>Découvrez les plages cachées du Portugal</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 75%"></div>
                        <span>75% des places réservées en pré-vente</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="destination-item">
            <div class="destination-date">Août 2025</div>
            <div class="destination-card">
                <div class="destination-image" style="background-image: url('img/ilegrec.jpg');">
                    <div class="coming-soon-badge">Exclusif</div>
                </div>
                <div class="destination-info">
                    <h3>Îles Grecques</h3>
                    <p>Explorez les îles moins touristiques</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 40%"></div>
                        <span>40% des places réservées en pré-vente</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="destination-item">
            <div class="destination-date">Septembre 2025</div>
            <div class="destination-card">
                <div class="destination-image" style="background-image: url('img/croate.jpg');">
                    <div class="coming-soon-badge">En préparation</div>
                </div>
                <div class="destination-info">
                    <h3>Croatie Adventure</h3>
                    <p>Randonnées et plages en Dalmatie</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 15%"></div>
                        <span>15% des places réservées en pré-vente</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    section.querySelector('.container').appendChild(timeline);
    
    // Animation de la timeline
    const timelineItems = document.querySelectorAll('.destination-item');
    const timelineProgress = document.querySelector('.timeline-progress');
    
    function animateTimeline() {
        let anyVisible = false;
        let totalHeight = 0;
        
        timelineItems.forEach((item, index) => {
            const rect = item.getBoundingClientRect();
            const isVisible = (rect.top <= window.innerHeight * 0.75) && 
                             (rect.bottom >= window.innerHeight * 0.25);
            
            if (isVisible && !item.classList.contains('visible')) {
                item.classList.add('visible');
                anyVisible = true;
            }
            
            // Calculer la hauteur totale pour la barre de progression
            if (item.classList.contains('visible')) {
                totalHeight += item.offsetHeight + 60; // 60px pour les marges
            }
        });
        
        if (anyVisible && timelineProgress) {
            const containerHeight = section.offsetHeight;
            const progress = (totalHeight / containerHeight) * 100;
            timelineProgress.style.height = `${Math.min(progress, 100)}%`;
        }
    }
    
    // Démarrer l'animation
    window.addEventListener('scroll', animateTimeline);
    animateTimeline(); 
}

// =============================================
// ANIMATIONS
// =============================================

function initAnimations() {
    // Animation au scroll
    function animateOnScroll() {
        const elements = document.querySelectorAll('.animate-img, .feature-card, .offer-card, .testimonial-card');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.2;
            
            if (elementPosition < screenPosition) {
                element.classList.add('animated');
            }
        });
    }

    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll();
}

// =============================================
// GESTION DES COOKIES
// =============================================

function initCookieConsent() {
    const cookieConsent = document.getElementById('cookieConsent');
    const acceptBtn = document.getElementById('acceptCookie');
    const configureBtn = document.getElementById('configureCookie');
    
    if (!cookieConsent || !acceptBtn || !configureBtn) return;
    
    if (!localStorage.getItem('cookieConsent')) {
        setTimeout(() => {
            cookieConsent.classList.add('show');
        }, 2000);
    }
    
    acceptBtn.addEventListener('click', function() {
        localStorage.setItem('cookieConsent', 'accepted');
        cookieConsent.classList.remove('show');
    });
    
    configureBtn.addEventListener('click', function() {
        alert('Options de configuration des cookies seront disponibles ici.');
    });
    
    document.querySelector('.continue-without')?.addEventListener('click', function(e) {
        e.preventDefault();
        cookieConsent.classList.remove('show');
    });
}

// =============================================
// CARTES DE VACANCES
// =============================================

function initHolidayCards() {
    if (window.innerWidth > 768) {
        document.querySelectorAll('.vacance').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 15px 30px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
            });
        });
    } else {
        const container = document.querySelector('.vacances-container');
        if (!container) return;
        
        let isDown = false;
        let startX;
        let scrollLeft;

        container.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - container.offsetLeft;
            scrollLeft = container.scrollLeft;
        });

        container.addEventListener('mouseleave', () => {
            isDown = false;
        });

        container.addEventListener('mouseup', () => {
            isDown = false;
        });

        container.addEventListener('mousemove', (e) => {
            if(!isDown) return;
            e.preventDefault();
            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 2;
            container.scrollLeft = scrollLeft - walk;
        });
    }
}
// =============================================
// GESTION AJAX
// =============================================

function initAjaxNavigation() {
    // Supprimer les anciens écouteurs
    document.removeEventListener('click', handleAjaxLinks);
    
    // Ajouter le nouvel écouteur
    document.addEventListener('click', handleAjaxLinks);
    
    function handleAjaxLinks(e) {
        const link = e.target.closest('a');
        if (!link) return;
        
        // Vérifier si le lien doit être géré par AJAX
        if (link.classList.contains('no-ajax') || 
            link.getAttribute('href').startsWith('#') ||
            link.getAttribute('href').startsWith('mailto:') ||
            link.getAttribute('href').startsWith('tel:') ||
            (link.getAttribute('href').startsWith('http') && !link.getAttribute('href').includes(window.location.host))) {
            return;
        }
        
        e.preventDefault();
        loadContent(link.getAttribute('href'), 'main');
    }
}

function loadContent(url, targetElement, pushState = true) {
    showLoading(true);
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector(targetElement)?.innerHTML || html;
            
            document.querySelector(targetElement).innerHTML = newContent;
            
            if (pushState) {
                history.pushState({url: url}, '', url);
            }
            
            // Déclencher un événement personnalisé pour indiquer que le contenu AJAX est chargé
            const event = new CustomEvent('ajaxContentLoaded');
            document.dispatchEvent(event);
            
            initComponents();

            window.scrollTo({top: 0, behavior: 'smooth'});
            showToast('Contenu chargé avec succès');
        })
        .catch(error => {
            console.error('Error loading content:', error);
            showToast('Erreur de chargement', 'error');
            // Fallback: rechargement normal de la page
            window.location.href = url;
        })
        .finally(() => {
            showLoading(false);
        });
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

function showLoading(show) {
    let loader = document.getElementById('ajax-loader');
    
    if (show) {
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'ajax-loader';
            loader.style.position = 'fixed';
            loader.style.top = '0';
            loader.style.left = '0';
            loader.style.width = '0';
            loader.style.height = '3px';
            loader.style.backgroundColor = 'var(--primary)';
            loader.style.zIndex = '9999';
            loader.style.transition = 'width 0.4s ease';
            document.body.appendChild(loader);
        }
        loader.style.width = '70%';
    } else if (loader) {
        loader.style.width = '100%';
        setTimeout(() => loader.remove(), 400);
    }
}

function showToast(message, type = 'success') {
    // Supprimer les toasts existants
    document.querySelectorAll('.toast-message').forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast-message toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}


// Show toast message
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-message toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Lazy loading images
function initLazyLoading() {
    const lazyImages = document.querySelectorAll('img.lazy');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => {
        imageObserver.observe(img);
    });
}


// Gestion de l'historique du navigateur
window.addEventListener('popstate', function(e) {
    if (e.state && e.state.url) {
        loadContent(e.state.url, 'main', false);
    }
});
