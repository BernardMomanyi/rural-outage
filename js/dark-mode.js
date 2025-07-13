// Dark Mode Management System
class DarkModeManager {
  constructor() {
    this.isDarkMode = localStorage.getItem('darkMode') === '1';
    this.init();
  }

  init() {
    // Apply initial mode
    this.setMode(this.isDarkMode);
    
    // Find and setup toggle button
    const toggleBtn = document.getElementById('darkModeToggle');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        this.toggle();
      });
    }
    
    // Apply mode to all elements
    this.applyMode();
  }

  setMode(dark) {
    this.isDarkMode = dark;
    document.body.classList.toggle('dark-mode', dark);
    localStorage.setItem('darkMode', dark ? '1' : '0');
    
    // Update toggle button if it exists
    const modeIcon = document.getElementById('modeIcon');
    const modeText = document.getElementById('modeText');
    
    if (modeIcon) {
      modeIcon.className = dark ? 'fa fa-sun' : 'fa fa-moon';
    }
    
    if (modeText) {
      modeText.textContent = dark ? 'Light Mode' : 'Dark Mode';
    }
    
    // Apply mode to all elements
    this.applyMode();
  }

  toggle() {
    this.setMode(!this.isDarkMode);
  }

  applyMode() {
    // Apply dark mode to all cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
      if (this.isDarkMode) {
        card.style.background = 'var(--color-bg-card)';
        card.style.color = 'var(--color-text)';
        card.style.borderColor = 'var(--color-border)';
      } else {
        card.style.background = '';
        card.style.color = '';
        card.style.borderColor = '';
      }
    });

    // Apply to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
      if (this.isDarkMode) {
        card.style.background = 'var(--color-bg-card)';
        card.style.color = 'var(--color-text)';
      } else {
        card.style.background = '';
        card.style.color = '';
      }
    });

    // Apply to tables
    const tables = document.querySelectorAll('.styled-table');
    tables.forEach(table => {
      if (this.isDarkMode) {
        table.style.background = 'var(--color-bg-card)';
        table.style.color = 'var(--color-text)';
      } else {
        table.style.background = '';
        table.style.color = '';
      }
    });

    // Apply to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
      if (this.isDarkMode) {
        if (btn.classList.contains('btn-outline')) {
          btn.style.background = 'transparent';
          btn.style.color = 'var(--color-primary)';
          btn.style.borderColor = 'var(--color-primary)';
        } else if (btn.classList.contains('btn-primary')) {
          btn.style.background = 'var(--color-primary)';
          btn.style.color = '#fff';
        }
      } else {
        btn.style.background = '';
        btn.style.color = '';
        btn.style.borderColor = '';
      }
    });

    // Apply to text elements
    const textElements = document.querySelectorAll('h1, h2, h3, h4, h5, h6, p, .small');
    textElements.forEach(element => {
      if (this.isDarkMode) {
        if (element.tagName.match(/^H[1-6]$/)) {
          element.style.color = 'var(--color-text)';
        } else if (element.classList.contains('small')) {
          element.style.color = 'var(--color-text-muted)';
        } else {
          element.style.color = 'var(--color-text-secondary)';
        }
      } else {
        element.style.color = '';
      }
    });

    // Apply to form elements
    const formElements = document.querySelectorAll('input, select, textarea');
    formElements.forEach(element => {
      if (this.isDarkMode) {
        element.style.background = 'var(--color-bg-card)';
        element.style.color = 'var(--color-text)';
        element.style.borderColor = 'var(--color-border)';
      } else {
        element.style.background = '';
        element.style.color = '';
        element.style.borderColor = '';
      }
    });

    // Apply to dashboard sections
    const dashboardSections = document.querySelectorAll('.dashboard-section');
    dashboardSections.forEach(section => {
      if (this.isDarkMode) {
        section.style.background = 'var(--color-bg-card)';
        section.style.color = 'var(--color-text)';
      } else {
        section.style.background = '';
        section.style.color = '';
      }
    });

    // Apply to stats sections
    const statsSections = document.querySelectorAll('.stats');
    statsSections.forEach(section => {
      if (this.isDarkMode) {
        section.style.background = 'var(--color-bg-card)';
        section.style.color = 'var(--color-text)';
      } else {
        section.style.background = '';
        section.style.color = '';
      }
    });
  }
}

// Initialize dark mode when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  new DarkModeManager();
});

// Export for use in other scripts
window.DarkModeManager = DarkModeManager; 