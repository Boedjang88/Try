/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.css",
    "./app/resources/**/*.blade.php",
    "./app/resources/**/*.js",
    "./app/src/**/*.html",
    "./app/dist/**/*.html",
  ],
  theme: {
    extend: {
      colors: {
        'uph-navy': '#00205B',
        'uph-navy-dark': '#060E1E',
        'uph-navy-deep': '#0A192F',
        'uph-crimson': '#C8102E',
        'uph-crimson-hover': '#A00C24',
        'uph-gold': '#D4AF37',
        'uph-gold-warm': '#FFB800',
        'surface': '#FFFFFF',
        'surface-slate': '#F8FAFC',
        'surface-border': '#E2E8F0',
        'dark-bg': '#060E1E',
        'dark-card': '#0F172A',
        'dark-border': '#1E293B',
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
        display: ['Montserrat', '"Playfair Display"', 'Georgia', 'serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
      },
      boxShadow: {
        'uph': '0 2px 8px -2px rgba(0, 32, 91, 0.06), 0 4px 16px -4px rgba(0, 32, 91, 0.04)',
        'uph-lg': '0 10px 30px -4px rgba(0, 32, 91, 0.12)',
        'uph-crimson': '0 4px 20px -2px rgba(200, 16, 46, 0.25)',
        'bento': '0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03)',
      },
      backdropBlur: {
        'xs': '2px',
      }
    },
  },
  plugins: [],
};
