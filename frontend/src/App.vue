<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

/* Reset y Variables */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

:root {
  /* Colores principales basados en el logo - versión mejorada */
  --primary-purple: #5B2C87;  /* Púrpura principal del logo */
  --primary-blue: #4F46E5;    /* Azul más suave y moderno */
  --primary-indigo: #6366F1;  /* Indigo complementario */
  --primary-gold: #D97706;    /* Dorado más sutil */
  --accent-yellow: #F59E0B;   /* Amarillo accent */
  --accent-orange: #EA580C;   /* Naranja suave */
  
  /* Colores de estado */
  --success-green: #10b981;
  --warning-yellow: #f59e0b;
  --danger-red: #ef4444; /* Rojo de error */
  --warning-orange: #F97316; /* Naranja de advertencia */
  
  /* Escala de grises */
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
  --white: #ffffff;
  
  /* Sombras y bordes */
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
  --border-radius: 0.5rem;
  --border-radius-lg: 1rem;
  --border-radius-full: 9999px;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  line-height: 1.6;
  color: var(--gray-800);
  background: linear-gradient(135deg, #faf5ff 0%, #f3f4f6 50%, #fef3c7 100%);
  min-height: 100vh;
}

#app {
  min-height: 100vh;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

/* Utilidades */
.hidden {
  display: none !important;
}

.text-center {
  text-align: center;
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

.justify-center {
  justify-content: center;
}

.justify-between {
  justify-content: space-between;
}

.gap-2 {
  gap: 0.5rem;
}

.gap-4 {
  gap: 1rem;
}

/* Botones */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius-full);
  font-weight: 600;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.875rem;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
  color: var(--white);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-lg);
}

.btn-ghost {
  background: transparent;
  color: var(--gray-700);
}

.btn-ghost:hover {
  background: var(--gray-100);
}

.btn-outline {
  background: transparent;
  border: 1px solid var(--gray-300);
  color: var(--gray-700);
}

.btn-outline:hover {
  background: var(--gray-50);
}

.btn-success {
  background: var(--success-green);
  color: var(--white);
}

.btn-danger {
  background: var(--danger-red);
  color: var(--white);
}

.btn-sm {
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
}

.btn-lg {
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
}
</style>
