import Alpine from 'alpinejs';

// Admin interactions are plain Alpine directives in the Blade views
// (the layout's root x-data drives the sidebar drawer + theme toggle).
// Views confirm destructive actions with native confirm() dialogs.
window.Alpine = Alpine;
Alpine.start();
