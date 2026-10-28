/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/**/*.php',
        // Tampilan paginasi bawaan Laravel & Livewire memakai kelas Tailwind
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/livewire/livewire/src/Features/SupportPagination/**/*.blade.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
