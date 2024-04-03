htmx.config.globalViewTransitions = true;


document.addEventListener('alpine:init', () => {
  Alpine.data('broke', () => ({
    isDark: localStorage.getItem("color-theme") === "dark" || (window.matchMedia("(prefers-color-scheme: dark)").matches && !localStorage.getItem("color-theme")),
    isNavbarActive: false,
    activeTab: 0,
    isTabListenerMounted: false,
    isHamburgerListenerMounted: false,
    isThemeSwitcherListenerMounted: false,

    init() {
      this.initTabs();
      this.initHamburger();
      this.initThemeSwitcher();
    },

    initTabs() {
      if (!this.isTabListenerMounted) {
        this.activeTab = this.activeTab || document.querySelector(".tab").getAttribute("aria-controls");

        document.querySelectorAll(".tab").forEach(tab => {
          const tabClickHandler = () => {
            const tabTarget = tab.getAttribute("aria-controls");
            this.setActiveTab(tabTarget);
          };
          tab.addEventListener("click", tabClickHandler);
        });

        this.isTabListenerMounted = true;
      }
    },

    setActiveTab(tabId) {
      this.activeTab = tabId;
      const indicator = document.querySelector(".tab-indicator");
      const tab = document.querySelector(`.tab[aria-controls="${tabId}"]`);
      const panels = document.querySelectorAll(".panel");
      const previews = document.querySelectorAll(".panel-preview");

      if (indicator && tab) {
        gsap.to(indicator, { width: tab.offsetWidth, x: tab.offsetLeft, duration: 0.3 });
      }

      panels.forEach(panel => {
        if (panel.id === tabId) {
          gsap.to(panel, { autoAlpha: 1, scale: 1, duration: 0.3 });
        } else {
          gsap.to(panel, { autoAlpha: 0, scale: 0.9, duration: 0.3 });
        }
      });

      previews.forEach(preview => {
        if (preview.dataset.target === tabId) {
          gsap.to(preview, { y: 0, scale: 1, autoAlpha: 1, duration: 0.3 });
        } else {
          gsap.to(preview, { y: "100%", scale: 0.75, autoAlpha: 0, duration: 0.3 });
        }
      });
    },

    initHamburger() {
      if (!this.isHamburgerListenerMounted) {
        const hamburgerClickHandler = event => {
          if (event.target.closest("#hamburger")) {
            this.toggleNavbar();
          }
        };
        document.body.addEventListener("click", hamburgerClickHandler);

        this.isHamburgerListenerMounted = true;
      }
    },

    toggleNavbar() {
      this.isNavbarActive = !this.isNavbarActive;
      const navbar = document.querySelector("#navbar");
      navbar.classList.toggle("navbar-active", this.isNavbarActive);
      gsap.to("#hamburger", { rotation: this.isNavbarActive ? 45 : 0, duration: 0.3 });
    },

    initThemeSwitcher() {
      if (!this.isThemeSwitcherListenerMounted) {
        const themeSwitcherClickHandler = event => {
          if (event.target.closest(".switcher")) {
            this.toggleTheme();
          }
        };
        document.body.addEventListener("click", themeSwitcherClickHandler);

        this.isThemeSwitcherListenerMounted = true;
      }
    },

    toggleTheme() {
      this.isDark = !this.isDark;
      document.documentElement.classList.toggle("dark", this.isDark);
      localStorage.setItem("color-theme", this.isDark ? "dark" : "light");
    }
  }));
});