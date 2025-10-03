<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-b from-white to-gray-100 px-6">
    <!-- Logo -->
    <img 
      src="https://bstore.id/assets/images/logo/1644868908236.png" 
      alt="MasRicky Logo" 
      class="w-28 mb-6 drop-shadow-lg"
    />

    <!-- Headline -->
    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 text-center">
      Selamat Datang di <span class="text-blue-600">MasRicky.com</span>
    </h1>

    <!-- Subheadline -->
    <p class="text-lg md:text-xl text-gray-600 mb-8 text-center max-w-xl">
      Solusi mudah untuk pesan produk terbaik. Klik tombol di bawah untuk order langsung via Telegram.
    </p>

    <!-- CTA Button -->
    <a 
      href="https://t.me/BStoreIDOrder_bot" 
      target="_blank" 
      rel="noopener noreferrer"
      class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-full shadow-lg transition-transform transform hover:scale-105"
    >
      🚀 Order Sekarang
    </a>

    <!-- Footer -->
    <footer class="mt-12 text-gray-500 text-sm">
      © {{ new Date().getFullYear() }} MasRicky.com. All rights reserved.
    </footer>
  </div>
</template>

<script setup>
</script>
