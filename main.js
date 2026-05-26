/* =====================================================
   SCRIPT PRINCIPAL — M&C Fashion
   Gère : menu mobile, header scroll, modals, slider, filtres
   ===================================================== */

document.addEventListener('DOMContentLoaded', function () {

  // --- Header : ajouter classe "scrolled" au scroll ---
  const header = document.getElementById('site-header');
  if (header) {
    window.addEventListener('scroll', function () {
      header.classList.toggle('scrolled', window.scrollY > 20);
    });
  }

  // --- Menu Mobile : toggle ---
  const menuToggle = document.getElementById('menu-toggle');
  const mainNav    = document.getElementById('main-nav');
  if (menuToggle && mainNav) {
    menuToggle.addEventListener('click', function () {
      mainNav.classList.toggle('open');
      this.setAttribute('aria-expanded', mainNav.classList.contains('open'));
    });
    // Fermer en cliquant en dehors
    document.addEventListener('click', function (e) {
      if (!header.contains(e.target)) {
        mainNav.classList.remove('open');
      }
    });
  }

  // --- Hero Slider (sans librairie externe) ---
  const heroSlides = document.querySelectorAll('.hero-slide');
  const heroDots   = document.querySelectorAll('.slider-dot');
  const prevBtn    = document.getElementById('sliderPrev');
  const nextBtn    = document.getElementById('sliderNext');

  if (heroSlides.length > 0) {
    let current = 0;
    let sliderTimer;

    function goTo(n) {
      heroSlides[current].classList.remove('active');
      if (heroDots[current]) heroDots[current].classList.remove('active');
      current = (n + heroSlides.length) % heroSlides.length;
      heroSlides[current].classList.add('active');
      if (heroDots[current]) heroDots[current].classList.add('active');
    }

    function startAuto() {
      sliderTimer = setInterval(function () { goTo(current + 1); }, 5000);
    }

    function resetAuto() {
      clearInterval(sliderTimer);
      startAuto();
    }

    // Initialiser le premier slide
    heroSlides[0].classList.add('active');
    if (heroDots[0]) heroDots[0].classList.add('active');

    if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); resetAuto(); });
    if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); resetAuto(); });

    heroDots.forEach(function (dot, i) {
      dot.addEventListener('click', function () { goTo(i); resetAuto(); });
    });

    startAuto();
  }

  // --- Filtre Produits ---
  const filterBtns   = document.querySelectorAll('.filter-btn');
  const productCards = document.querySelectorAll('.product-card[data-cat]');

  filterBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filterBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      const cat = this.getAttribute('data-filter');

      productCards.forEach(function (card) {
        if (cat === 'all' || card.getAttribute('data-cat') === cat) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  // --- Modals génériques ---
  document.querySelectorAll('[data-modal]').forEach(function (trigger) {
    trigger.addEventListener('click', function (e) {
      e.preventDefault();
      const id = this.getAttribute('data-modal');
      openModal(id);
    });
  });

  document.querySelectorAll('.modal-close').forEach(function (btn) {
    btn.addEventListener('click', function () {
      closeModal(this.closest('.modal-overlay').id);
    });
  });

  document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
    overlay.addEventListener('click', function (e) {
      if (e.target === this) closeModal(this.id);
    });
  });

  // Fermer avec Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.open').forEach(function (m) {
        m.classList.remove('open');
      });
    }
  });

  // --- Auto-fermer les messages flash après 4s ---
  const flashBar = document.querySelector('.flash-bar');
  if (flashBar) {
    setTimeout(function () {
      flashBar.style.opacity = '0';
      flashBar.style.transition = 'opacity 0.5s';
      setTimeout(() => flashBar.remove(), 500);
    }, 4000);
  }

  // --- Onglets Auth (login / register) ---
  const authTabs  = document.querySelectorAll('.auth-tab');
  const authForms = document.querySelectorAll('.auth-form');
  authTabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      authTabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      const target = this.getAttribute('data-tab');
      authForms.forEach(function (form) {
        form.style.display = (form.id === 'form-' + target) ? 'block' : 'none';
      });
    });
  });

});

/* --- Fonctions utilitaires globales --- */
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}