import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Livewire/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    amber: '#ffbf00',
                    pink: '#e83f6f',
                    blue: '#2274a5',
                    green: '#32936f',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Sora', 'Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [require('@tailwindcss/forms')],
};
