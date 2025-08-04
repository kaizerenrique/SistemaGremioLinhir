import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Paleta base
                'base': {
                    100: '#121212',
                    200: '#1D1D1D',
                    300: '#252525',
                },
                
                // Colores de texto
                'content': {
                    light: '#E8E8E8',
                    accent: '#F0E6D2'
                },
                
                // Colores principales
                'primary': '#C54B47',
                'secondary': '#9A8667',
                'accent': '#D4B44A',
                'neutro': '#6D6D6D',
                
                // Colores funcionales
                'info': '#4A9DEA',
                'success': '#5CCB45',
                'warning': '#FFC107',
                'error': '#FF5252',
            },
            
            // Extensión para colores de fondo con hover personalizados
            backgroundColor: theme => ({
                ...theme('colors'),
                'hover-primary': '#A93C38', // 20% más oscuro que primary
                'hover-accent': '#B89A3C',  // 20% más oscuro que accent
                'hover-secondary': '#806A52', // 20% más oscuro que secondary
            }),
            
            // Extensión para colores de texto hover
            textColor: theme => ({
                ...theme('colors'),
                'hover-primary': '#A93C38',
                'hover-accent': '#B89A3C',
            }),
            
            // Extensión para colores de borde
            borderColor: theme => ({
                ...theme('colors'),
                DEFAULT: theme('colors.base.300', '#252525'),
            }),
        },
    },

    plugins: [forms, typography],
};
