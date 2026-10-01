/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace'],
      },
      colors: {
        neon: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#22ff88',
          500: '#00ff87', // Electric Radioactive Green Primary
          600: '#10e86b',
          700: '#05df72',
          800: '#00b853',
          900: '#044e26',
          950: '#022411',
        },
        electric: {
          cyan: '#00f0ff',
          blue: '#38bdf8',
          sky: '#67e8f9',
          deep: '#0284c7',
        },
        cyber: {
          bg: '#080d0f',
          card: '#0e171b',
          border: '#1b2a30',
          accent: '#00ff87',
        }
      },
      boxShadow: {
        'neon-sm': '0 0 10px rgba(0, 255, 135, 0.35)',
        'neon-md': '0 0 20px rgba(0, 255, 135, 0.45)',
        'neon-lg': '0 0 35px rgba(0, 255, 135, 0.55)',
        'cyan-sm': '0 0 10px rgba(0, 240, 255, 0.35)',
        'cyan-md': '0 0 20px rgba(0, 240, 255, 0.45)',
        'tactile': '0 8px 24px -4px rgba(0, 0, 0, 0.18), 0 3px 8px -2px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.8)',
        'tactile-lg': '0 14px 30px -4px rgba(0, 0, 0, 0.22), 0 4px 10px -2px rgba(0, 0, 0, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.9)',
        'tactile-dark': '0 10px 30px -4px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(0, 240, 255, 0.15)',
      }
    },
  },
  plugins: [],
}
