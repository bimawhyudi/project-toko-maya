import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                aksen: ['Poppins', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                primary: {
                    DEFAULT: '#155DFC',
                    dark: '#0046E0',
                    darker: '#0239B2',
                    soft: '#E4ECFF',
                    lighter: '#E6F1FF',
                    mist: '#F3F8FE',
                },
                navy: {
                    DEFAULT: '#002A93',
                    deep: '#002D91',
                },
                surface: '#F8FAFC',
                garis: '#D9D9D9',
                sukses: {
                    DEFAULT: '#10B981',
                    teks: '#0D7F00',
                },
                bahaya: '#EF4444',
                rating: '#FCD53F',
            },

            borderRadius: {
                kartu: '14px',
            },

            boxShadow: {
                kartu: '0 1px 3px 0 rgba(15, 23, 42, 0.08), 0 1px 2px -1px rgba(15, 23, 42, 0.08)',
                terangkat: '0 8px 24px -6px rgba(15, 23, 42, 0.16)',
                panel: '-10px 4px 16px rgba(0, 0, 0, 0.12)',
            },

            maxWidth: {
                halaman: '1440px',
            },
        },
    },

    plugins: [forms],
};