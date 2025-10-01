// Import library yang dibutuhkan
const { Telegraf, Markup } = require('telegraf');
const axios = require('axios');

// --- KONFIGURASI ---
const BOT_TOKEN = '8303902507:AAE0h0Qqb391fstlPGPEc5sPZ6EtlPmAgSM';
const API_BASE_URL = 'https://masricky.com/api';
// --- PERUBAHAN: URL Placeholder untuk gambar QRIS ---
const QRIS_IMAGE_URL = './qris.png'; // Ganti dengan URL gambar QRIS statis Anda jika punya

// Inisialisasi bot
const bot = new Telegraf(BOT_TOKEN);

// --- FUNGSI & DATA ---

// Objek sederhana untuk menyimpan data produk sementara
// Dalam aplikasi nyata, lebih baik menggunakan database atau cache seperti Redis
const productCache = new Map();

/**
 * Fungsi untuk mengambil dan menampilkan daftar kategori produk.
 * @param {object} ctx - Konteks dari Telegraf.
 * @param {boolean} isEdit - Menentukan apakah pesan harus diedit atau dikirim sebagai pesan baru.
 */
const showCategories = async (ctx, isEdit = false) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/categories`);
        if (response.data && response.data.success) {
            const categories = response.data.data;
            const buttons = categories.map(category =>
                Markup.button.callback(category.name, `category_${category.id}`)
            );
            const keyboard = Markup.inlineKeyboard(buttons, { columns: 2 });
            const message = "🛍️ Silakan pilih kategori produk:";

            if (isEdit) {
                // Hapus gambar jika ada (misal dari invoice) dan edit teks
                await ctx.deleteMessage().catch(() => {}); // Abaikan error jika pesan sudah terhapus
                await ctx.reply(message, keyboard);
            } else {
                await ctx.reply(message, keyboard);
            }
        } else {
            await ctx.reply('❌ Maaf, gagal mengambil data kategori.');
        }
    } catch (error) {
        console.error("Error fetching categories:", error);
        await ctx.reply('Terjadi kesalahan saat menghubungi server.');
    }
};


// --- HANDLER BOT ---

// Menu Utama (Reply Keyboard)
const mainMenu = Markup.keyboard([
    ['🛒 Daftar Produk', '📜 Riwayat Order'],
    ['💰 Saldo Saya', '📢 Info Terbaru']
]).resize();

// Handler untuk perintah /start
bot.command('start', (ctx) => {
    ctx.reply(
        'Selamat datang! 👋\n\nGunakan menu di bawah untuk berinteraksi dengan bot.',
        mainMenu
    );
});

// --- Handler untuk Tombol Menu ---
bot.hears('🛒 Daftar Produk', (ctx) => showCategories(ctx, false));
bot.hears('📜 Riwayat Order', (ctx) => ctx.reply('🚧 Fitur ini sedang dalam pengembangan!'));
bot.hears('💰 Saldo Saya', (ctx) => ctx.reply('🚧 Fitur pengecekan saldo sedang dalam pengembangan!'));
bot.hears('📢 Info Terbaru', (ctx) => ctx.reply('Tidak ada informasi terbaru saat ini.'));

// Handler untuk aksi saat tombol kategori ditekan
bot.action(/category_(.+)/, async (ctx) => {
    const categoryId = ctx.match[1];
    await ctx.answerCbQuery();
    try {
        await ctx.editMessageText('⏳ Mencari produk...');
        const response = await axios.get(`${API_BASE_URL}/product/category/${categoryId}`);
        if (response.data && response.data.success) {
            const products = response.data.data;

            // Simpan detail produk ke cache untuk digunakan nanti saat membuat invoice
            products.forEach(p => productCache.set(p.id.toString(), p));

            if (products.length === 0) {
                await ctx.editMessageText('ℹ️ Maaf, tidak ada produk di kategori ini.', Markup.inlineKeyboard([Markup.button.callback('⬅️ Kembali', 'back_to_categories')]));
                return;
            }
            const productButtons = products.map(product => {
                const priceFormatted = `Rp ${product.price.toLocaleString('id-ID')}`;
                return Markup.button.callback(`${product.name} (${priceFormatted})`, `product_${product.id}`);
            });
            productButtons.push(Markup.button.callback('⬅️ Kembali ke Kategori', 'back_to_categories'));
            const keyboard = Markup.inlineKeyboard(productButtons, { columns: 1 });
            await ctx.editMessageText('Silakan pilih produk yang Anda inginkan:', keyboard);
        } else {
            await ctx.editMessageText('❌ Gagal mengambil data produk.');
        }
    } catch (error) {
        console.error("Error fetching products:", error);
        await ctx.editMessageText('Terjadi kesalahan saat menghubungi server.');
    }
});

// --- PERUBAHAN UTAMA: Handler saat produk dipilih ---
bot.action(/product_(.+)/, async (ctx) => {
    const productId = ctx.match[1];
    await ctx.answerCbQuery();

    // Ambil detail produk dari cache
    const product = productCache.get(productId);

    if (!product) {
        await ctx.editMessageText('❌ Maaf, produk tidak ditemukan. Silakan coba lagi.', Markup.inlineKeyboard([
            Markup.button.callback('⬅️ Kembali ke Kategori', 'back_to_categories')
        ]));
        return;
    }

    // Hapus pesan daftar produk sebelumnya agar UI lebih bersih
    await ctx.deleteMessage();

    const priceFormatted = `Rp ${product.price.toLocaleString('id-ID')}`;
    const invoiceId = `INV-${Date.now()}`; // Buat ID Invoice unik sederhana

    const invoiceText = `🧾 **INVOICE PEMBAYARAN**\n\n` +
                        `**ID Pesanan:** \`${invoiceId}\`\n` +
                        `**Produk:** ${product.name}\n` +
                        `**Total Bayar:** *${priceFormatted}*\n\n` +
                        `Silakan lakukan pembayaran dengan memindai kode QRIS di atas. Setelah membayar, tekan tombol konfirmasi.`;

    const keyboard = Markup.inlineKeyboard([
        Markup.button.callback('✅ Konfirmasi Pembayaran', `confirm_${invoiceId}`),
        Markup.button.callback('❌ Batalkan', `cancel_${invoiceId}`)
    ]);

    // Kirim foto QRIS beserta caption invoice dan tombol
    await ctx.replyWithPhoto(
        { source: QRIS_IMAGE_URL },
        {
            caption: invoiceText,
            parse_mode: 'Markdown',
            ...keyboard
        }
    );
});

// --- HANDLER BARU: Untuk tombol konfirmasi pembayaran ---
bot.action(/confirm_(.+)/, async (ctx) => {
    const invoiceId = ctx.match[1];
    await ctx.answerCbQuery('Sedang mengecek pembayaran...');

    // Simulasi pengecekan
    await ctx.editMessageCaption(
        `⏳ Sedang memverifikasi pembayaran untuk \`${invoiceId}\`...\n\nHarap tunggu sebentar.`,
        { parse_mode: 'Markdown' }
    );

    // Simulasi hasil setelah beberapa detik
    setTimeout(async () => {
        // Ganti caption pesan yang ada
        await ctx.editMessageCaption(
            `✅ **Pembayaran Berhasil!**\n\n` +
            `Terima kasih! Pesanan Anda untuk \`${invoiceId}\` sedang diproses.`,
            {
                parse_mode: 'Markdown',
                ...Markup.inlineKeyboard([
                    Markup.button.callback('🛒 Beli Lagi', 'back_to_categories')
                ])
            }
        );
    }, 3000); // Tunggu 3 detik
});

// --- HANDLER BARU: Untuk tombol batalkan pesanan ---
bot.action(/cancel_(.+)/, async (ctx) => {
    await ctx.answerCbQuery();
    await ctx.deleteMessage(); // Hapus pesan invoice
    await ctx.reply('Pesanan Anda telah dibatalkan.');
    await showCategories(ctx, false); // Tampilkan kembali daftar kategori
});

// Handler untuk tombol "Kembali ke Kategori"
bot.action('back_to_categories', async (ctx) => {
    await ctx.answerCbQuery();
    await showCategories(ctx, true);
});


// --- Menjalankan Bot ---
bot.launch();
console.log('Bot Telegram sedang berjalan dengan fitur invoice QRIS...');

// Menangani proses berhenti dengan baik
process.once('SIGINT', () => bot.stop('SIGINT'));
process.once('SIGTERM', () => bot.stop('SIGTERM'));