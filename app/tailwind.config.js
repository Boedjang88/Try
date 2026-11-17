/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./app/resources/**/*.blade.php",
    "./app/resources/**/*.js",
    "./src/**/*.html",
    "./dist/**/*.html",
  ],
  theme: {
    extend: {
      colors: {
        'uph-navy': '#00205B',
        'uph-navy-dark': '#0A2540',
        'uph-crimson': '#C8102E',
        'uph-crimson-hover': '#A00C24',
        'uph-gold': '#D4AF37',
        'uph-gold-warm': '#FFB800',
        'surface': '#FFFFFF',
        'surface-slate': '#F8FAFC',
        'surface-border': '#E2E8F0',
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
        display: ['Montserrat', '"Playfair Display"', 'Georgia', 'serif'],
        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
      },
      boxShadow: {
        'uph': '0 4px 20px -2px rgba(0, 32, 91, 0.08)',
        'uph-lg': '0 10px 30px -4px rgba(0, 32, 91, 0.14)',
        'uph-crimson': '0 4px 20px -2px rgba(200, 16, 46, 0.25)',
      },
    },
  },
  plugins: [],
};
